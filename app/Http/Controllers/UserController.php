<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserInvitation;
use App\Notifications\UserInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::orderBy('role')->orderBy('nom');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nom', 'like', '%'.$request->search.'%')
                    ->orWhere('prenom', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%')
                    ->orWhere('poste', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->get();
        $invitations = UserInvitation::orderByDesc('created_at')->get();

        return view('utilisateurs.index', compact('users', 'invitations'));
    }

    public function create()
    {
        return view('utilisateurs.create');
    }

    // Envoie une invitation par email : l'utilisateur complète lui-même
    // son inscription (nom, prénom, service, mot de passe).
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|unique:users,email|unique:user_invitations,email',
            'role' => 'required|in:admin,secretaire,agent',
        ]);

        $this->envoyerInvitation($data['email'], $data['role']);

        return redirect()->route('utilisateurs.index')
            ->with('success', "Invitation envoyée à {$data['email']}.");
    }

    public function edit(User $utilisateur)
    {
        return view('utilisateurs.edit', compact('utilisateur'));
    }

    public function update(Request $request, User $utilisateur)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('users')->ignore($utilisateur->id)],
            'role' => 'required|in:admin,secretaire,agent',
            'service' => 'nullable|in:'.implode(',', array_keys(User::$services)),
            'poste' => 'nullable|string|max:150',
            'telephone' => 'nullable|string|max:20',
            'password' => 'nullable|min:8|confirmed',
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $utilisateur->update($data);

        return redirect()->route('utilisateurs.index')
            ->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(User $utilisateur)
    {
        if ($utilisateur->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        $utilisateur->delete();

        return redirect()->route('utilisateurs.index')
            ->with('success', 'Utilisateur supprimé.');
    }

    // Activer / Désactiver un compte
    public function toggle(User $utilisateur)
    {
        if ($utilisateur->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }
        $utilisateur->update(['actif' => ! $utilisateur->actif]);
        $msg = $utilisateur->actif ? 'Compte activé.' : 'Compte désactivé.';

        return back()->with('success', $msg);
    }

    // Régénère le token et renvoie l'email d'invitation.
    public function renvoyerInvitation(UserInvitation $invitation)
    {
        $this->envoyerInvitation($invitation->email, $invitation->role);

        return back()->with('success', "Invitation renvoyée à {$invitation->email}.");
    }

    public function revoquerInvitation(UserInvitation $invitation)
    {
        $invitation->delete();

        return back()->with('success', 'Invitation révoquée.');
    }

    private function envoyerInvitation(string $email, string $role): UserInvitation
    {
        $token = Str::random(64);

        $invitation = UserInvitation::updateOrCreate(
            ['email' => $email],
            [
                'role' => $role,
                'token' => hash('sha256', $token),
                'invited_by' => auth()->id(),
                'expires_at' => now()->addDays(7),
            ]
        );

        Notification::route('mail', $email)->notify(new UserInvitationNotification($token));

        return $invitation;
    }
}
