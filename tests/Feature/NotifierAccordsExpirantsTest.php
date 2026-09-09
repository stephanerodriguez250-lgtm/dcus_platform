<?php

namespace Tests\Feature;

use App\Models\Accord;
use App\Models\User;
use App\Notifications\AccordExpirationProcheNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotifierAccordsExpirantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifies_all_active_users_for_accords_expiring_within_60_days(): void
    {
        Notification::fake();

        $actif = User::factory()->create(['actif' => true]);
        $inactif = User::factory()->create(['actif' => false]);
        $accord = Accord::factory()->create(['date_expiration' => now()->addDays(30)]);

        $this->artisan('accords:notifier-expiration');

        Notification::assertSentTo($actif, AccordExpirationProcheNotification::class);
        Notification::assertNotSentTo($inactif, AccordExpirationProcheNotification::class);
        $this->assertNotNull($accord->fresh()->alerte_expiration_envoyee_le);
    }

    public function test_does_not_notify_for_accords_expiring_beyond_60_days(): void
    {
        Notification::fake();
        $actif = User::factory()->create(['actif' => true]);
        Accord::factory()->create(['date_expiration' => now()->addDays(90)]);

        $this->artisan('accords:notifier-expiration');

        Notification::assertNotSentTo($actif, AccordExpirationProcheNotification::class);
    }

    public function test_does_not_notify_twice_for_the_same_accord(): void
    {
        Notification::fake();
        $actif = User::factory()->create(['actif' => true]);
        Accord::factory()->create([
            'date_expiration' => now()->addDays(10),
            'alerte_expiration_envoyee_le' => now()->subDay(),
        ]);

        $this->artisan('accords:notifier-expiration');

        Notification::assertNotSentTo($actif, AccordExpirationProcheNotification::class);
    }
}
