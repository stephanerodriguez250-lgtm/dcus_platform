@extends('layouts.app')

@section('title', 'Réunions')
@section('page-title', 'Gestion des Réunions')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1 fw-bold text-dark">Liste des réunions</h5>
        <p class="text-muted small mb-0">Toutes les réunions enregistrées par la DCUS</p>
    </div>
    @if(auth()->user()->canManage())
    <a href="{{ route('reunions.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nouvelle réunion
    </a>
    @endif
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
                           placeholder="Titre, lieu, convocateur..." value="{{ request('search') }}">
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
                <a href="{{ route('reunions.index') }}" class="btn btn-outline-secondary w-100">Réinitialiser</a>
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
                        <th class="ps-4">Réunion</th>
                        <th>Date</th>
                        <th>Lieu</th>
                        <th>Convocateur</th>
                        <th>Accords</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reunions as $reunion)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold">{{ $reunion->titre }}</div>
                            <div class="text-muted small">Ajouté par {{ $reunion->createur->nom_complet }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $reunion->date->locale('fr')->translatedFormat('d M Y') }}</div>
                            @if($reunion->heure)
                            <div class="text-muted small">{{ $reunion->heure }}</div>
                            @endif
                        </td>
                        <td>{{ $reunion->lieu }}</td>
                        <td>
                            <span class="text-muted small">{{ $reunion->convocateur ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $reunion->accords_count ?? $reunion->accords()->count() }} accord(s)
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $reunion->statut_color }}">{{ $reunion->statut_label }}</span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('reunions.show', $reunion) }}"
                               class="btn btn-sm btn-outline-primary me-1" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()->canManage())
                            <a href="{{ route('reunions.edit', $reunion) }}"
                               class="btn btn-sm btn-outline-secondary me-1" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('reunions.destroy', $reunion) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Supprimer cette réunion ?')">
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
                            <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                            Aucune réunion trouvée
                            @if(auth()->user()->canManage())
                            <div class="mt-2">
                                <a href="{{ route('reunions.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus me-1"></i>Créer la première réunion
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
    @if($reunions->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-3">
        <span class="text-muted small">{{ $reunions->total() }} réunion(s) au total</span>
        {{ $reunions->links() }}
    </div>
    @endif
</div>
@endsection
