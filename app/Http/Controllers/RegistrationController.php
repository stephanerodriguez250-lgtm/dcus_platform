<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    public function show(string $token)
    {
        $invitation = $this->findValidInvitation($token);

        if (! $invitation) {
            return redirect()->route('login')
                ->withErrors(['email' => "Ce lien d'invitation est invalide ou a expiré."]);
        }

        return view('auth.register', [
            'invitation' => $invitation,
            'token' => $token,
        ]);
    }

    public function store(Request $request)
    {
        $invitation = $this->findValidInvitation($request->input('token'));

        if (! $invitation) {
            return redirect()->route('login')
                ->withErrors(['email' => "Ce lien d'invitation est invalide ou a expiré."]);
        }

        $data = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'service' => 'required|in:'.implode(',', array_keys(User::$services)),
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $invitation->email,
            'role' => $invitation->role,
            'service' => $data['service'],
            'password' => Hash::make($data['password']),
            'actif' => true,
        ]);

        $invitation->delete();

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Compte créé avec succès. Bienvenue !');
    }

    private function findValidInvitation(?string $token): ?UserInvitation
    {
        if (! $token) {
            return null;
        }

        $invitation = UserInvitation::where('token', hash('sha256', $token))->first();

        if (! $invitation || $invitation->isExpired()) {
            return null;
        }

        return $invitation;
    }
}
