<?php

namespace App\Http\Controllers;

use App\Models\AccordAppreciateur;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccordAppreciateurController extends Controller
{
    public function index()
    {
        $appreciateurs = AccordAppreciateur::with('utilisateur')->get();
        $autresUtilisateurs = User::whereNotIn('id', $appreciateurs->pluck('user_id'))
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        return view('accords.appreciateurs.index', compact('appreciateurs', 'autresUtilisateurs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id|unique:accord_appreciateurs,user_id',
        ]);

        AccordAppreciateur::create([
            'user_id' => $data['user_id'],
            'accorde_par' => Auth::id(),
        ]);

        return back()->with('success', 'Agent autorisé à apprécier les accords.');
    }

    public function destroy(AccordAppreciateur $appreciateur)
    {
        $appreciateur->delete();

        return back()->with('success', 'Autorisation retirée.');
    }
}
