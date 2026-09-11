<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'telephone' => 'nullable|digits_between:1,20',
        ]);

        // Changement de mot de passe optionnel
        if ($request->filled('password')) {
            $request->validate([
                'current_password' => 'required',
                'password' => 'required|min:8|confirmed',
            ]);

            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.']);
            }

            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }
}
