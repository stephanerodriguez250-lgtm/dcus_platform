<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        Password::sendResetLink($request->only('email'));

        return back()->with(
            'status',
            'Si cet email existe dans notre système, un lien de réinitialisation vient de vous être envoyé.'
        );
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = null;

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $resetUser, string $password) use (&$user) {
                $resetUser->forceFill(['password' => Hash::make($password)])->save();
                $user = $resetUser;
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            if (! $user->actif) {
                return redirect()->route('login')->with(
                    'success',
                    'Mot de passe réinitialisé. Votre compte est désactivé, contactez l\'administrateur.'
                );
            }

            Auth::login($user);

            return redirect()->route('dashboard')->with('success', 'Mot de passe réinitialisé avec succès.');
        }

        $messages = [
            Password::INVALID_USER => 'Aucun compte trouvé avec cet email.',
            Password::INVALID_TOKEN => 'Ce lien de réinitialisation est invalide ou a expiré.',
            Password::RESET_THROTTLED => 'Veuillez patienter avant de réessayer.',
        ];

        return back()->withErrors(['email' => $messages[$status] ?? $status]);
    }
}
