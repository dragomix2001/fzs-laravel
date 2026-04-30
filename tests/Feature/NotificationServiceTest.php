<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\NewNotification;
use App\Jobs\BroadcastNotificationJob;
use App\Mail\ObavestenjeMail;
use App\Models\Obavestenje;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use DatabaseTransactions;

    private NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(NotificationService::class);
    }

    private function makeObavestenje(string $tip = 'admini'): Obavestenje
    {
        return Obavestenje::create([
            'naslov' => 'Test obaveštenje',
            'sadrzaj' => 'Sadržaj',
            'tip' => $tip,
            'aktivan' => true,
            'datum_objave' => now(),
        ]);
    }

    #[Test]
    public function notify_user_dispatches_new_notification_event(): void
    {
        Event::fake();

        $this->service->notifyUser(5, 'Naslov', 'Poruka', 'info');

        Event::assertDispatched(NewNotification::class, static function (NewNotification $e): bool {
            return $e->userId === 5
                && $e->title === 'Naslov'
                && $e->message === 'Poruka'
                && $e->type === 'info';
        });
    }

    #[Test]
    public function notify_admins_dispatches_event_for_each_admin(): void
    {
        Event::fake();

        User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->create(['role' => User::ROLE_PROFESSOR]);

        $this->service->notifyAdmins('Naslov', 'Poruka');

        Event::assertDispatched(NewNotification::class, 2);
    }

    #[Test]
    public function notify_admins_dispatches_no_events_when_no_admins(): void
    {
        Event::fake();

        User::factory()->create(['role' => User::ROLE_STUDENT]);

        $this->service->notifyAdmins('Naslov', 'Poruka');

        Event::assertNotDispatched(NewNotification::class);
    }

    #[Test]
    public function broadcast_obavestenje_javno_dispatches_job_with_all_user_ids(): void
    {
        Bus::fake();

        $u1 = User::factory()->create(['role' => User::ROLE_STUDENT]);
        $u2 = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $obavestenje = $this->makeObavestenje('javno');

        $this->service->broadcastObavestenje($obavestenje);

        Bus::assertDispatched(BroadcastNotificationJob::class, static function (BroadcastNotificationJob $job) use ($u1, $u2): bool {
            return in_array($u1->id, $job->userIds, true)
                && in_array($u2->id, $job->userIds, true);
        });
    }

    #[Test]
    public function broadcast_obavestenje_profesori_dispatches_job_with_only_professor_ids(): void
    {
        Bus::fake();

        $prof = User::factory()->create(['role' => User::ROLE_PROFESSOR]);
        User::factory()->create(['role' => User::ROLE_STUDENT]);
        $obavestenje = $this->makeObavestenje('profesori');

        $this->service->broadcastObavestenje($obavestenje);

        Bus::assertDispatched(BroadcastNotificationJob::class, static function (BroadcastNotificationJob $job) use ($prof): bool {
            return $job->userIds === [$prof->id];
        });
    }

    #[Test]
    public function broadcast_obavestenje_default_dispatches_job_with_admin_ids(): void
    {
        Bus::fake();

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->create(['role' => User::ROLE_STUDENT]);
        $obavestenje = $this->makeObavestenje('admini');

        $this->service->broadcastObavestenje($obavestenje);

        Bus::assertDispatched(BroadcastNotificationJob::class, static function (BroadcastNotificationJob $job) use ($admin): bool {
            return $job->userIds === [$admin->id];
        });
    }

    #[Test]
    public function notify_exam_deadline_dispatches_warning_event(): void
    {
        Event::fake();

        $this->service->notifyExamDeadline(7, 'Matematika', '2026-05-15');

        Event::assertDispatched(NewNotification::class, static function (NewNotification $e): bool {
            return $e->userId === 7
                && $e->type === 'warning';
        });
    }

    #[Test]
    public function send_obavestenje_sends_mail_to_all_student_emails(): void
    {
        Mail::fake();

        User::factory()->create(['role' => User::ROLE_STUDENT, 'email' => 'student1@test.com']);
        User::factory()->create(['role' => User::ROLE_STUDENT, 'email' => 'student2@test.com']);
        User::factory()->create(['role' => User::ROLE_ADMIN, 'email' => 'admin@test.com']);

        $this->service->sendObavestenjeToAllStudents('Naslov', 'Sadrzaj');

        Mail::assertSent(ObavestenjeMail::class, 2);
    }

    #[Test]
    public function send_obavestenje_sends_no_mail_when_no_students(): void
    {
        Mail::fake();

        User::factory()->create(['role' => User::ROLE_ADMIN, 'email' => 'admin@test.com']);

        $this->service->sendObavestenjeToAllStudents('Naslov', 'Sadrzaj');

        Mail::assertNothingSent();
    }
}
