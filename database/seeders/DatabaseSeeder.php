<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Directrice (Admin)
        User::create([
            'nom'      => 'AGOSSOU',
            'prenom'   => 'Marie',
            'email'    => 'directrice@dcus.bj',
            'password' => Hash::make('Dcus2024!'),
            'role'     => 'admin',
            'poste'    => 'Directrice DCUS',
            'actif'    => true,
        ]);

        // Secrétaire
        User::create([
            'nom'      => 'HOUNTON',
            'prenom'   => 'Isabelle',
            'email'    => 'secretaire@dcus.bj',
            'password' => Hash::make('Dcus2024!'),
            'role'     => 'secretaire',
            'poste'    => 'Secrétaire de Direction',
            'actif'    => true,
        ]);

        // Agents
        $agents = [
            ['nom' => 'DOSSOU',   'prenom' => 'Jean',    'email' => 'j.dossou@dcus.bj',   'poste' => 'Chargé de Coopération'],
            ['nom' => 'GBEDO',    'prenom' => 'Fatima',  'email' => 'f.gbedo@dcus.bj',    'poste' => 'Chargée de Coopération'],
            ['nom' => 'AHOUNOU',  'prenom' => 'Pierre',  'email' => 'p.ahounou@dcus.bj',  'poste' => 'Chargé de Suivi'],
        ];

        foreach ($agents as $agent) {
            User::create(array_merge($agent, [
                'password' => Hash::make('Dcus2024!'),
                'role'     => 'agent',
                'actif'    => true,
            ]));
        }
    }
}
