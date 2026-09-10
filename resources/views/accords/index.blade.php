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

<!-- Statistiques par étape (cliquables : filtrent la liste ci-dessous) -->
<div class="row g-2 mb-4">
    @foreach(\App\Models\Accord::$etapeLabels as $key => $label)
    @php $estActif = request('etape') === $key; @endphp
    <div class="col-6 col-md-3">
        <a href="{{ route('accords.index', array_merge(request()->except(['etape', 'page']), $estActif ? [] : ['etape' => $key])) }}"
           class="text-decoration-none">
            <div class="card text-center py-2 px-1 h-100 {{ $estActif ? 'border-primary border-2' : '' }}">
                <div class="fw-bold fs-5 {{ $estActif ? 'text-primary' : '' }}">{{ $etapeCounts[$key] }}</div>
                <div class="{{ $estActif ? 'text-primary fw-semibold' : 'text-muted' }}" style="font-size:0.72rem;">{{ $label }}</div>
            </div>
        </a>
    </div>
    @endforeach
</div>

<!-- Recherche -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="etape" value="{{ request('etape') }}">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted">Rechercher</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Titre, référence, institution..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Institution d'origine</label>
                <select name="institution" class="form-select">
                    <option value="">Toutes</option>
                    @foreach($institutions as $institution)
                    <option value="{{ $institution }}" @selected(request('institution') === $institution)>{{ $institution }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted">Arrivé du</label>
                <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-muted">au</label>
                <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-1 d-flex flex-column gap-1">
                <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
                <a href="{{ route('accords.index') }}" class="btn btn-outline-secondary btn-sm">Réinit.</a>
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
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Supprimer"
                                    onclick="document.getElementById('formSupprimerAccord').action = '{{ route('accords.destroy', $accord) }}'"
                                    data-bs-toggle="modal" data-bs-target="#supprimerAccordModal">
                                <i class="bi bi-trash"></i>
                            </button>
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

{{-- ================================================================
     MODAL : Confirmer la suppression d'un accord (formulaire partagé,
     l'action est réécrite dynamiquement par le bouton "Supprimer" cliqué)
================================================================ --}}
<div class="modal fade" id="supprimerAccordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="formSupprimerAccord">
                @csrf @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Supprimer cet accord ?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-danger">Cette action est irréversible : l'accord, sa fiche d'appréciation et son rapport de conformité éventuels seront définitivement supprimés.</p>
                    <label class="form-label fw-semibold">Confirmez votre mot de passe <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" autocomplete="current-password" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Supprimer définitivement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
