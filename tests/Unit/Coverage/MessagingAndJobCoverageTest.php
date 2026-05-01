<?php

declare(strict_types=1);

namespace Tests\Unit\Coverage;

use App\Events\IspitPrijavljen;
use App\Events\KandidatCreated;
use App\Events\NewNotification;
use App\Jobs\BroadcastNotificationJob;
use App\Jobs\GenerateZapisnikPdfJob;
use App\Jobs\Job;
use App\Jobs\MassEnrollmentJob;
use App\Jobs\TestFailingJob;
use App\Listeners\LogIspitPrijavljen;
use App\Listeners\SendKandidatCreatedNotification;
use App\Mail\IspitPrijjavaMail;
use App\Mail\KandidatCreatedMail;
use App\Mail\ObavestenjeMail;
use App\Models\Kandidat;
use App\Models\Obavestenje;
use App\Models\PrijavaIspita;
use App\Services\IspitPdfService;
use App\Services\NotificationService;
use App\Services\UpisService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class MessagingAndJobCoverageTest extends TestCase
{
    #[Test]
    public function base_job_class_queueable_trait_is_usable(): void
    {
        $job = new class extends Job {};

        $this->assertSame('emails', $job->onQueue('emails')->queue);
    }

    #[Test]
    public function new_notification_event_methods_return_expected_payload(): void
    {
        $event = new NewNotification(5, 'Naslov', 'Poruka', 'info', ['id' => 1]);

        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertSame('notifications.5', $channels[0]->name);
        $this->assertSame('notification.new', $event->broadcastAs());

        $payload = $event->broadcastWith();
        $this->assertSame('Naslov', $payload['title']);
        $this->assertSame('Poruka', $payload['message']);
        $this->assertSame('info', $payload['type']);
        $this->assertArrayHasKey('timestamp', $payload);
    }

    #[Test]
    public function domain_events_store_constructor_models(): void
    {
        $prijava = new PrijavaIspita(['kandidat_id' => 7]);
        $kandidat = new Kandidat(['imeKandidata' => 'Test']);

        $prijavljen = new IspitPrijavljen($prijava);
        $created = new KandidatCreated($kandidat);

        $this->assertSame($prijava, $prijavljen->prijava);
        $this->assertSame($kandidat, $created->kandidat);
    }

    #[Test]
    public function mailables_build_with_expected_subjects(): void
    {
        $ispit = (new IspitPrijjavaMail('Student', 'Biomehanika', 'Jun', '2026-05-01'))->build();
        $kandidat = (new KandidatCreatedMail(new Kandidat(['imeKandidata' => 'Pera'])))->build();
        $obavestenje = (new ObavestenjeMail('Rok', 'Sadrzaj'))->build();

        $this->assertSame('Пријава испита - Biomehanika', $ispit->subject);
        $this->assertSame('Добродошли - Факултет за спорт', $kandidat->subject);
        $this->assertSame('Ново обавештење - Rok', $obavestenje->subject);
    }

    #[Test]
    public function listeners_log_and_send_mail(): void
    {
        Log::spy();
        Mail::fake();

        $prijava = new PrijavaIspita([
            'kandidat_id' => 10,
            'predmet_id' => 20,
            'rok_id' => 30,
        ]);

        (new LogIspitPrijavljen)->handle(new IspitPrijavljen($prijava));

        Log::shouldHaveReceived('info')->once();

        $kandidat = new Kandidat(['email' => 'kandidat@test.local']);
        (new SendKandidatCreatedNotification)->handle(new KandidatCreated($kandidat));

        Mail::assertSent(KandidatCreatedMail::class, 1);
    }

    #[Test]
    public function role_middleware_covers_all_branches(): void
    {
        $middleware = new \App\Http\Middleware\RoleMiddleware;
        $next = static fn (): Response => new Response('ok', 200);

        $anonRequest = Request::create('/test', 'GET');
        $response = $middleware->handle($anonRequest, $next, 'admin');
        $this->assertSame(302, $response->getStatusCode());

        $unauthorizedRequest = Request::create('/test', 'GET');
        $unauthorizedRequest->setUserResolver(static fn () => (object) ['role' => 'student']);
        $response = $middleware->handle($unauthorizedRequest, $next, 'admin');
        $this->assertSame(302, $response->getStatusCode());

        $ajaxUnauthorized = Request::create('/test', 'GET');
        $ajaxUnauthorized->headers->set('X-Requested-With', 'XMLHttpRequest');
        $ajaxUnauthorized->setUserResolver(static fn () => (object) ['role' => 'student']);
        $response = $middleware->handle($ajaxUnauthorized, $next, 'admin');
        $this->assertSame(403, $response->getStatusCode());

        $allowedRequest = Request::create('/test', 'GET');
        $allowedRequest->setUserResolver(static fn () => (object) ['role' => 'admin']);
        $response = $middleware->handle($allowedRequest, $next, 'admin', 'secretary');
        $this->assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function broadcast_notification_job_handle_and_failed_are_covered(): void
    {
        Route::name('obavestenja.show')->get('/obavestenja/{id}', static fn () => 'ok');

        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('notifyUser')->times(2);

        $obavestenje = (new Obavestenje)->forceFill(['id' => 7, 'naslov' => 'Naslov']);
        $job = new BroadcastNotificationJob($obavestenje, [1, 2]);

        $job->handle($notificationService);

        Log::spy();
        $job->failed(new Exception('greska'));
        Log::shouldHaveReceived('error')->once();
    }

    #[Test]
    public function generate_zapisnik_pdf_job_handle_and_failed_are_covered(): void
    {
        Storage::fake('local');

        $service = Mockery::mock(IspitPdfService::class);
        $service->shouldReceive('zapisnikStampa')
            ->once()
            ->andReturnUsing(static function (): void {
                echo 'PDF-CONTENT';
            });

        $job = new GenerateZapisnikPdfJob(10, 'reports/zapisnik.pdf');
        $job->handle($service);

        Storage::disk('local')->assertExists('reports/zapisnik.pdf');

        Log::spy();
        $job->failed(new Exception('pdf fail'));
        Log::shouldHaveReceived('error')->once();
    }

    #[Test]
    public function mass_enrollment_job_handle_and_failed_are_covered(): void
    {
        Log::spy();

        $upisService = Mockery::mock(UpisService::class);
        $upisService->shouldReceive('registrujKandidata')->with(1)->once();
        $upisService->shouldReceive('registrujKandidata')->with(2)->once()->andThrow(new Exception('fail'));
        $upisService->shouldReceive('registrujKandidata')->with(3)->once();

        $job = new MassEnrollmentJob([1, 2, 3]);
        $job->handle($upisService);

        // One error from failed enrollment inside loop and one from failed() handler.
        Log::shouldHaveReceived('error')->once();

        $job->failed(new Exception('hard fail'));
        Log::shouldHaveReceived('error')->twice();
    }

    #[Test]
    public function test_failing_job_handle_throws_and_failed_logs(): void
    {
        $job = new TestFailingJob;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('This job is designed to fail for testing purposes');
        $job->handle();
    }

    #[Test]
    public function test_failing_job_failed_logs_error(): void
    {
        Log::spy();

        (new TestFailingJob)->failed(new Exception('boom'));

        Log::shouldHaveReceived('error')->once();
    }
}
