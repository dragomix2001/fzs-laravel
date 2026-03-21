<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class FailedJobsMonitor extends Command
{
    protected $signature = 'failed-jobs:monitor
                            {action=list : Action to run (list, show, retry, retry-all, delete, delete-all, clear)}
                            {id? : Failed job UUID/ID (required for show, retry, delete)}
                            {--page=1 : Page number for the list action}
                            {--per-page=15 : Results per page for the list action}
                            {--connection= : Filter list by connection name}
                            {--queue= : Filter list by queue name}
                            {--search= : Filter list by UUID, exception text, or payload display name}';

    protected $description = 'Monitor and manage failed queue jobs from a single command.';

    protected $help = <<<'HELP'
Examples:
  php artisan failed-jobs:monitor list
  php artisan failed-jobs:monitor list --page=2 --per-page=20
  php artisan failed-jobs:monitor list --connection=redis --queue=emails --search=SendInvoice
  php artisan failed-jobs:monitor show a8d6ea10-3ca8-4ec7-9f4f-b1ad7fca8f8c
  php artisan failed-jobs:monitor retry a8d6ea10-3ca8-4ec7-9f4f-b1ad7fca8f8c
  php artisan failed-jobs:monitor retry-all
  php artisan failed-jobs:monitor delete a8d6ea10-3ca8-4ec7-9f4f-b1ad7fca8f8c
  php artisan failed-jobs:monitor delete-all
  php artisan failed-jobs:monitor clear

Notes:
  - show/retry/delete require the {id} argument.
  - delete-all and clear both flush the failed_jobs table.
HELP;

    public function handle(): int
    {
        return match (Str::lower((string) $this->argument('action'))) {
            'list' => $this->listFailedJobs(),
            'show' => $this->showFailedJob(),
            'retry' => $this->retryFailedJob(),
            'retry-all' => $this->retryAllFailedJobs(),
            'delete' => $this->deleteFailedJob(),
            'delete-all', 'clear' => $this->clearFailedJobs(),
            default => $this->invalidAction(),
        };
    }

    protected function failer(): FailedJobProviderInterface
    {
        $failer = app('queue.failer');

        return $failer;
    }

    protected function listFailedJobs(): int
    {
        $page = max((int) $this->option('page'), 1);
        $perPage = max((int) $this->option('per-page'), 1);

        $jobs = collect($this->failer()->all())
            ->map(fn ($job) => (array) $job)
            ->filter(function (array $job): bool {
                if ($connection = $this->option('connection')) {
                    if (($job['connection'] ?? null) !== $connection) {
                        return false;
                    }
                }

                if ($queue = $this->option('queue')) {
                    if (($job['queue'] ?? null) !== $queue) {
                        return false;
                    }
                }

                if ($search = $this->option('search')) {
                    $haystack = Str::lower(implode(' ', [
                        (string) ($job['uuid'] ?? ''),
                        (string) ($job['exception'] ?? ''),
                        (string) ($this->payloadDisplayName($job) ?? ''),
                    ]));

                    return Str::contains($haystack, Str::lower((string) $search));
                }

                return true;
            })
            ->values();

        $total = $jobs->count();
        $rows = $jobs->forPage($page, $perPage)->map(function (array $job): array {
            return [
                'ID/UUID' => $job['uuid'] ?? $job['id'] ?? 'N/A',
                'Connection' => $job['connection'] ?? 'N/A',
                'Queue' => $job['queue'] ?? 'N/A',
                'Job' => $this->payloadDisplayName($job) ?? 'N/A',
                'Failed At' => (string) ($job['failed_at'] ?? 'N/A'),
            ];
        })->all();

        if (empty($rows)) {
            $this->info('No failed jobs found for the selected filters/page.');

            return self::SUCCESS;
        }

        $this->table(['ID/UUID', 'Connection', 'Queue', 'Job', 'Failed At'], $rows);

        $totalPages = (int) ceil($total / $perPage);
        $this->line(sprintf('Showing page %d of %d (%d total failed jobs).', $page, max($totalPages, 1), $total));

        return self::SUCCESS;
    }

    protected function showFailedJob(): int
    {
        $id = (string) $this->argument('id');

        if ($id === '') {
            $this->error('The {id} argument is required for the show action.');

            return self::FAILURE;
        }

        $job = $this->findJob($id);

        if ($job === null) {
            $this->error("Failed job [{$id}] not found.");

            return self::FAILURE;
        }

        $this->line('Failed Job Details');
        $this->line(str_repeat('-', 60));
        $this->line('ID/UUID    : '.($job['uuid'] ?? $job['id'] ?? 'N/A'));
        $this->line('Connection : '.($job['connection'] ?? 'N/A'));
        $this->line('Queue      : '.($job['queue'] ?? 'N/A'));
        $this->line('Job        : '.($this->payloadDisplayName($job) ?? 'N/A'));
        $this->line('Failed At  : '.($job['failed_at'] ?? 'N/A'));
        $this->newLine();
        $this->line('Exception:');
        $this->line((string) ($job['exception'] ?? 'N/A'));

        return self::SUCCESS;
    }

    protected function retryFailedJob(): int
    {
        $id = (string) $this->argument('id');

        if ($id === '') {
            $this->error('The {id} argument is required for the retry action.');

            return self::FAILURE;
        }

        if ($this->findJob($id) === null) {
            $this->error("Failed job [{$id}] not found.");

            return self::FAILURE;
        }

        $exitCode = Artisan::call('queue:retry', ['id' => [$id]]);
        $this->output->write(Artisan::output());

        return $exitCode === self::SUCCESS
            ? self::SUCCESS
            : self::FAILURE;
    }

    protected function retryAllFailedJobs(): int
    {
        $exitCode = Artisan::call('queue:retry', ['id' => ['all']]);
        $this->output->write(Artisan::output());

        return $exitCode === self::SUCCESS
            ? self::SUCCESS
            : self::FAILURE;
    }

    protected function deleteFailedJob(): int
    {
        $id = (string) $this->argument('id');

        if ($id === '') {
            $this->error('The {id} argument is required for the delete action.');

            return self::FAILURE;
        }

        if (! $this->failer()->forget($id)) {
            $this->error("Failed job [{$id}] not found or could not be deleted.");

            return self::FAILURE;
        }

        $this->info("Deleted failed job [{$id}].");

        return self::SUCCESS;
    }

    protected function clearFailedJobs(): int
    {
        $this->failer()->flush();
        $this->info('All failed jobs have been removed from the failed jobs table.');

        return self::SUCCESS;
    }

    protected function invalidAction(): int
    {
        $this->error('Invalid action. Allowed actions: list, show, retry, retry-all, delete, delete-all, clear.');

        return self::FAILURE;
    }

    protected function findJob(string $id): ?array
    {
        $job = $this->failer()->find($id);

        if ($job === null) {
            return null;
        }

        return (array) $job;
    }

    protected function payloadDisplayName(array $job): ?string
    {
        $payload = Arr::get($job, 'payload');

        if (! is_string($payload) || $payload === '') {
            return null;
        }

        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            return null;
        }

        return Arr::get($decoded, 'displayName')
            ?? Arr::get($decoded, 'job')
            ?? Arr::get($decoded, 'data.commandName');
    }
}
