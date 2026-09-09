<?php

namespace Tests\Feature;

use App\Models\Accord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccordReceptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_secretaire_can_receive_an_accord_with_explicit_date_and_heure(): void
    {
        Notification::fake();
        Storage::fake('public');

        $secretaire = User::factory()->secretaire()->create();

        $response = $this->actingAs($secretaire)->post('/accords', [
            'titre' => 'Convention UAC',
            'institution_partenaire' => 'UAC',
            'reference' => 'MESRS-2026/001',
            'date_arrivee' => '2026-01-10',
            'heure_arrivee' => '14:30',
            'fichier' => UploadedFile::fake()->create('accord.pdf', 200, 'application/pdf'),
        ]);

        $accord = Accord::first();
        $response->assertRedirect(route('accords.show', $accord));
        $this->assertSame('2026-01-10', $accord->date_arrivee->format('Y-m-d'));
        $this->assertStringStartsWith('14:30', $accord->heure_arrivee);
        Storage::disk('public')->assertExists($accord->chemin_fichier);
        $this->assertSame('accord.pdf', $accord->nom_fichier);
        $this->assertDatabaseHas('accord_historiques', ['accord_id' => $accord->id, 'evenement' => 'Accord reçu']);
    }

    public function test_date_and_heure_arrivee_default_to_now_when_left_empty(): void
    {
        Notification::fake();
        Storage::fake('public');

        $secretaire = User::factory()->secretaire()->create();

        $this->actingAs($secretaire)->post('/accords', [
            'titre' => 'Convention UAC',
            'institution_partenaire' => 'UAC',
            'reference' => 'MESRS-2026/002',
            'fichier' => UploadedFile::fake()->create('accord.pdf', 200, 'application/pdf'),
        ]);

        $accord = Accord::first();
        $this->assertSame(now()->format('Y-m-d'), $accord->date_arrivee->format('Y-m-d'));
        $this->assertNotNull($accord->heure_arrivee);
    }

    public function test_agent_cannot_receive_an_accord(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);

        $response = $this->actingAs($agent)->post('/accords', [
            'titre' => 'Convention UAC',
            'institution_partenaire' => 'UAC',
            'reference' => 'MESRS-2026/003',
        ]);

        $response->assertForbidden();
        $this->assertSame(0, Accord::count());
    }

    public function test_fichier_is_required_on_reception(): void
    {
        $secretaire = User::factory()->secretaire()->create();

        $response = $this->actingAs($secretaire)->post('/accords', [
            'titre' => 'Convention UAC',
            'institution_partenaire' => 'UAC',
            'reference' => 'MESRS-2026/004',
        ]);

        $response->assertSessionHasErrors('fichier');
    }

    public function test_updating_an_accord_can_replace_the_fichier(): void
    {
        Storage::fake('public');
        $secretaire = User::factory()->secretaire()->create();
        $ancienChemin = UploadedFile::fake()->create('ancien.pdf', 100)->store('accords/fichiers', 'public');
        $accord = Accord::factory()->create(['chemin_fichier' => $ancienChemin, 'nom_fichier' => 'ancien.pdf']);

        $response = $this->actingAs($secretaire)->put(route('accords.update', $accord), [
            'titre' => $accord->titre,
            'institution_partenaire' => $accord->institution_partenaire,
            'reference' => $accord->reference,
            'date_arrivee' => $accord->date_arrivee->format('Y-m-d'),
            'heure_arrivee' => '09:00',
            'fichier' => UploadedFile::fake()->create('nouveau.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('accords.show', $accord));
        $accord->refresh();
        $this->assertSame('nouveau.pdf', $accord->nom_fichier);
        Storage::disk('public')->assertMissing($ancienChemin);
        Storage::disk('public')->assertExists($accord->chemin_fichier);
    }

    public function test_deleting_an_accord_removes_its_stored_file(): void
    {
        Storage::fake('public');
        $secretaire = User::factory()->secretaire()->create();
        $chemin = UploadedFile::fake()->create('accord.pdf', 100)->store('accords/fichiers', 'public');
        $accord = Accord::factory()->create(['chemin_fichier' => $chemin]);

        $this->actingAs($secretaire)->delete(route('accords.destroy', $accord));

        Storage::disk('public')->assertMissing($chemin);
        $this->assertDatabaseMissing('accords', ['id' => $accord->id]);
    }
}
