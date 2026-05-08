@extends('layouts.app')

@section('title', 'Accords')
@section('page-title', 'Gestion des Accords')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1 fw-bold text-dark">Liste des accords</h5>
        <p class="text-muted small mb-0">Suivi de tous les accords internationaux de la DCUS</p>
    </div>
    @if(auth()->user()->canManage())
    <a href="{{ route('accords.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nouvel accord
    </a>
    @endif
</div>

<!-- Statistiques rapides -->
<div class="row g-2 mb-4">
    @foreach(\App\Models\Accord::$statuts as $key => $label)
    <div class="col">
        <div class="card text-center py-2 px-1 h-100">
            <div class="fw-bold fs-5">{{ \App\Models\Accord::where('statut', $key)->count() }}</div>
            <div class="text-muted" style="font-size:0.72rem;">{{ $label }}</div>
        </div>
    </div>
    @endforeach
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-semibold text-muted">Rechercher</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Titre, pays, institution, université..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Statut</label>
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    @foreach($statuts as $key => $label)
                        <option value="{{ $key }}" {{ request('statut') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
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
                        <th>Partenaire</th>
                        <th>Université</th>
                        <th>Réunion d'origine</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($accords as $accord)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold">{{ $accord->titre }}</div>
                            <div class="text-muted small">Créé par {{ $accord->createur->nom_complet }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $accord->pays_partenaire }}</div>
                            <div class="text-muted small">{{ $accord->institution_partenaire }}</div>
                        </td>
                        <td>
                            <span class="text-muted small">{{ $accord->universite_beneficiaire ?? '—' }}</span>
                        </td>
                        <td>
                            @if($accord->reunion)
                                <a href="{{ route('reunions.show', $accord->reunion) }}"
                                   class="text-decoration-none small">
                                    <i class="bi bi-calendar-event me-1"></i>
                                    {{ \Illuminate\Support\Str::limit($accord->reunion->titre, 30) }}
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            @if($accord->date_signature)
                                <div class="small">Signé le</div>
                                <div class="fw-semibold small">{{ $accord->date_signature->format('d/m/Y') }}</div>
                            @elseif($accord->date_identification)
                                <div class="small">Identifié le</div>
                                <div class="fw-semibold small">{{ $accord->date_identification->format('d/m/Y') }}</div>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $accord->statut_color }}">{{ $accord->statut_label }}</span>
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
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-file-x fs-2 d-block mb-2"></i>
                            Aucun accord trouvé
                            @if(auth()->user()->canManage())
                            <div class="mt-2">
                                <a href="{{ route('accords.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus me-1"></i>Créer le premier accord
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
