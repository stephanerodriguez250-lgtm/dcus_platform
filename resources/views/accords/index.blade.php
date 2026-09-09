@extends('layouts.app')

@section('title', 'Accords')
@section('page-title', 'Gestion des Accords')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1 fw-bold text-dark">Accords</h5>
        <p class="text-muted small mb-0">Réception, appréciation et suivi des accords des universités et partenaires</p>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->isAdmin())
        <a href="{{ route('accords.appreciateurs.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-person-check me-2"></i>Agents autorisés
        </a>
        @endif
        @if(auth()->user()->canManage())
        <a href="{{ route('accords.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Nouvel accord
        </a>
        @endif
    </div>
</div>

<!-- Statistiques par étape -->
<div class="row g-2 mb-4">
    @foreach(\App\Models\Accord::$etapeLabels as $key => $label)
    <div class="col-6 col-md-3">
        <div class="card text-center py-2 px-1 h-100">
            <div class="fw-bold fs-5">{{ $etapeCounts[$key] }}</div>
            <div class="text-muted" style="font-size:0.72rem;">{{ $label }}</div>
        </div>
    </div>
    @endforeach
</div>

<!-- Recherche -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-8">
                <label class="form-label small fw-semibold text-muted">Rechercher</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Titre, référence, institution..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('accords.index') }}" class="btn btn-outline-secondary w-100">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

<!-- Tableau -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Accord</th>
                        <th>Institution</th>
                        <th>Arrivé le</th>
                        <th>Étape</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accords as $accord)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold">{{ $accord->titre }}</div>
                            <div class="text-muted small">Réf. {{ $accord->reference }} — {{ $accord->createur->nom_complet }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $accord->institution_partenaire }}</div>
                        </td>
                        <td>
                            <div class="small">{{ $accord->date_arrivee->format('d/m/Y') }}</div>
                            <div class="text-muted small">{{ substr($accord->heure_arrivee, 0, 5) }}</div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $accord->etape_color }}">{{ $accord->etape_label }}</span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('accords.show', $accord) }}"
                               class="btn btn-sm btn-outline-primary me-1" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()->canManage())
                            <a href="{{ route('accords.edit', $accord) }}"
                               class="btn btn-sm btn-outline-secondary me-1" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('accords.destroy', $accord) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Supprimer cet accord ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            <i class="bi bi-file-x fs-2 d-block mb-2"></i>
                            Aucun accord trouvé
                            @if(auth()->user()->canManage())
                            <div class="mt-2">
                                <a href="{{ route('accords.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus me-1"></i>Enregistrer le premier accord
                                </a>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($accords->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-3">
        <span class="text-muted small">{{ $accords->total() }} accord(s) au total</span>
        {{ $accords->links() }}
    </div>
    @endif
</div>
@endsection
