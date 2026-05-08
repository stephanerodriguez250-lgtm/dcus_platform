<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::orderBy('role')->orderBy('nom');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('prenom', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('poste', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->get();

        return view('utilisateurs.index', compact('users'));
    }

    public function create()
    {
        return view('utilisateurs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:8|confirmed',
            'role'      => 'required|in:admin,secretaire,agent',
            'poste'     => 'nullable|string|max:150',
            'telephone' => 'nullable|string|max:20',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['actif']    = true;

        User::create($data);

        return redirect()->route('utilisateurs.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $utilisateur)
    {
        return view('utilisateurs.edit', compact('utilisateur'));
    }

    public function update(Request $request, User $utilisateur)
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:100',
            'prenom'    => 'required|string|max:100',
            'email'     => ['required', 'email', Rule::unique('users')->ignore($utilisateur->id)],
            'role'      => 'required|in:admin,secretaire,agent',
            'poste'     => 'nullable|string|max:150',
            'telephone' => 'nullable|string|max:20',
            'password'  => 'nullable|min:8|confirmed',
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
        $utilisateur->update(['actif' => !$utilisateur->actif]);
        $msg = $utilisateur->actif ? 'Compte activé.' : 'Compte désactivé.';
        return back()->with('success', $msg);
    }
}
