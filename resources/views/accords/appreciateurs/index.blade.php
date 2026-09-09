@extends('layouts.app')

@section('title', 'Agents autorisés à apprécier')
@section('page-title', 'Agents autorisés à apprécier les accords')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1 fw-bold text-dark">Agents autorisés à apprécier</h5>
        <p class="text-muted small mb-0">Les admins et secrétaires peuvent toujours apprécier — cette liste ajoute des agents ponctuellement autorisés.</p>
    </div>
    <a href="{{ route('accords.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Retour aux accords
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header py-3">Agents autorisés</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <tbody>
                        @forelse($appreciateurs as $appreciateur)
                        <tr>
                            <td class="ps-3">{{ $appreciateur->utilisateur->nom_complet }}</td>
                            <td class="text-end pe-3">
                                <form method="POST" action="{{ route('accords.appreciateurs.destroy', $appreciateur) }}"
                                      onsubmit="return confirm('Retirer cette autorisation ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-x-circle"></i> Retirer
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td class="text-center text-muted py-4">Aucun agent supplémentaire autorisé pour l'instant.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header py-3">Autoriser un agent</div>
            <div class="card-body">
                @error('user_id')<div class="alert alert-danger">{{ $message }}</div>@enderror
                <form method="POST" action="{{ route('accords.appreciateurs.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Utilisateur</label>
                        <select name="user_id" class="form-select">
                            @forelse($autresUtilisateurs as $utilisateur)
                            <option value="{{ $utilisateur->id }}">{{ $utilisateur->nom_complet }}</option>
                            @empty
                            <option value="">— Aucun agent disponible —</option>
                            @endforelse
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-2"></i>Autoriser
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
