<?php

namespace Tests\Unit\Notifications;

use App\Models\Accord;
use App\Models\User;
use App\Notifications\AccordExpirationProcheNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccordExpirationProcheNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_sent_by_mail_only(): void
    {
        $accord = Accord::factory()->create(['date_expiration' => now()->addDays(20)]);
        $destinataire = User::factory()->create();

        $notification = new AccordExpirationProcheNotification($accord);

        $this->assertSame(['mail'], $notification->via($destinataire));
    }

    public function test_mail_message_contains_accord_title_and_expiration_date(): void
    {
        $accord = Accord::factory()->create(['titre' => 'Convention UAC', 'date_expiration' => '2026-06-01']);
        $destinataire = User::factory()->create(['prenom' => 'Awa']);

        $notification = new AccordExpirationProcheNotification($accord);
        $mail = $notification->toMail($destinataire);

        $this->assertSame('Bonjour Awa,', $mail->greeting);
        $rendered = collect($mail->introLines)->implode(' ');
        $this->assertStringContainsString('Convention UAC', $rendered);
        $this->assertStringContainsString('01/06/2026', $rendered);
        $this->assertSame(route('accords.show', $accord), $mail->actionUrl);
    }
}
