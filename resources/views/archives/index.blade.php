@extends('layouts.app')

@section('title', 'Archives')
@section('page-title', 'Archives')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1 fw-bold text-dark">Archives</h5>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item drop-cible" data-dossier-id="">
                    <a href="{{ route('archives.index') }}"><i class="bi bi-folder2-open me-1"></i>Racine</a>
                </li>
                @foreach($filAriane as $etape)
                <li class="breadcrumb-item {{ $loop->last ? 'active' : 'drop-cible' }}" data-dossier-id="{{ $loop->last ? '' : $etape->getRouteKey() }}">
                    @if($loop->last)
                        {{ $etape->nom }}
                    @else
                        <a href="{{ route('archives.index', ['dossier' => $etape]) }}">{{ $etape->nom }}</a>
                    @endif
                </li>
                @endforeach
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('archives.partages.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-share me-2"></i>Partages effectués
        </a>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#nouveauDossierModal">
            <i class="bi bi-folder-plus me-2"></i>Nouveau dossier
        </button>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#partagerModal">
            <i class="bi bi-share me-2"></i>Partager
        </button>
        <a href="{{ route('archives.fichiers.create', $dossier ? ['dossier' => $dossier] : []) }}" class="btn btn-primary">
            <i class="bi bi-file-earmark-plus me-2"></i>Ajouter un fichier
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="width: 40px;"></th>
                        <th>Nom</th>
                        <th>Numéro</th>
                        <th>Taille</th>
                        <th>Date</th>
                        <th>Heure</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sousDossiers as $sousDossier)
                    <tr class="drop-cible" draggable="true" data-type="dossier" data-id="{{ $sousDossier->getRouteKey() }}" data-dossier-id="{{ $sousDossier->getRouteKey() }}">
                        <td class="ps-4">
                            <input type="checkbox" name="dossier_ids[]" value="{{ $sousDossier->id }}" form="partage-form">
                        </td>
                        <td>
                            <a href="{{ route('archives.index', ['dossier' => $sousDossier]) }}" class="text-decoration-none">
                                <i class="bi bi-folder-fill text-warning me-2"></i>{{ $sousDossier->nom }}
                            </a>
                        </td>
                        <td colspan="4" class="text-muted small">Dossier</td>
                        <td class="text-end pe-4">
                            <button type="button" class="btn btn-sm btn-outline-secondary me-1"
                                    data-bs-toggle="modal" data-bs-target="#renommerDossier{{ $sousDossier->id }}" title="Renommer">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form method="POST" action="{{ route('archives.dossiers.destroy', $sousDossier) }}"
                                  class="d-inline" onsubmit="return confirm('Supprimer ce dossier et tout son contenu ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>

                            <div class="modal fade" id="renommerDossier{{ $sousDossier->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('archives.dossiers.update', $sousDossier) }}">
                                            @csrf @method('PATCH')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Renommer le dossier</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="text" name="nom" class="form-control" value="{{ $sousDossier->nom }}">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                                <button type="submit" class="btn btn-primary">Renommer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @forelse($fichiers as $fichier)
                    <tr draggable="true" data-type="fichier" data-id="{{ $fichier->getRouteKey() }}">
                        <td class="ps-4">
                            <input type="checkbox" name="fichier_ids[]" value="{{ $fichier->id }}" form="partage-form">
                        </td>
                        <td>
                            <i class="bi {{ $fichier->icone }} me-2"></i>{{ $fichier->intitule }}
                            @if($fichier->partageOrigine)
                            <div class="text-muted small">Partagé par {{ $fichier->partageOrigine->partagePar->nom_complet }}</div>
                            @endif
                        </td>
                        <td>{{ $fichier->numero ?? '—' }}</td>
                        <td>{{ $fichier->taille_formatee }}</td>
                        <td>{{ $fichier->created_at->format('d/m/Y') }}</td>
                        <td>{{ $fichier->created_at->format('H:i') }}</td>
                        <td class="text-end pe-4">
                            <a href="{{ route('archives.fichiers.apercu', $fichier) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary me-1" title="Visualiser">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('archives.fichiers.download', $fichier) }}" class="btn btn-sm btn-outline-primary me-1" title="Télécharger">
                                <i class="bi bi-download"></i>
                            </a>
                            <form method="POST" action="{{ route('archives.fichiers.destroy', $fichier) }}"
                                  class="d-inline" onsubmit="return confirm('Supprimer ce fichier ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @if($sousDossiers->isEmpty() && $fichiers->isEmpty())
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-folder2 fs-2 d-block mb-2"></i>
                            Ce dossier est vide
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal nouveau dossier -->
<div class="modal fade" id="nouveauDossierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('archives.dossiers.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nouveau dossier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    @if($dossier)
                    <input type="hidden" name="parent_id" value="{{ $dossier->id }}">
                    @endif
                    <label class="form-label fw-semibold">Nom du dossier</label>
                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}">
                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulaire de partage (les cases à cocher lui sont rattachées via l'attribut form, même si elles sont ailleurs dans la page) -->
<form id="partage-form" method="POST" action="{{ route('archives.partages.store') }}">
    @csrf
    @if($dossier)
    <input type="hidden" name="retour_dossier_id" value="{{ $dossier->getRouteKey() }}">
    @endif
</form>

<!-- Modal partager -->
<div class="modal fade" id="partagerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Partager la sélection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                @error('fichier_ids')<div class="alert alert-danger">{{ $message }}</div>@enderror
                @error('destinataire_ids')<div class="alert alert-danger">{{ $message }}</div>@enderror
                <label class="form-label fw-semibold">Partager à :</label>
                @forelse($autresUtilisateurs as $utilisateur)
                <div class="form-check">
                    <input type="checkbox" name="destinataire_ids[]" value="{{ $utilisateur->id }}"
                           form="partage-form" class="form-check-input" id="destinataire{{ $utilisateur->id }}">
                    <label class="form-check-label" for="destinataire{{ $utilisateur->id }}">
                        {{ $utilisateur->nom_complet }}
                    </label>
                </div>
                @empty
                <p class="text-muted small mb-0">Aucun autre utilisateur disponible.</p>
                @endforelse

                <label for="noteModal" class="form-label fw-semibold mt-3">Note (optionnelle)</label>
                <textarea name="note" id="noteModal" form="partage-form" class="form-control" rows="3"
                          placeholder="Ce message sera repris dans l'e-mail envoyé au(x) destinataire(s).">{{ old('note') }}</textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="partage-form" class="btn btn-primary">Partager</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    tr[draggable="true"] { cursor: grab; }
    tr.drag-en-cours { opacity: 0.4; }
    tr.drop-cible.survole, li.drop-cible.survole a, li.drop-cible.survole { background-color: var(--bs-primary-bg-subtle, #cfe2ff) !important; }
    li.breadcrumb-item.drop-cible { border-radius: 4px; transition: background-color 0.15s; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    let elementDeplace = null;

    document.querySelectorAll('tr[draggable="true"]').forEach(function (ligne) {
        ligne.addEventListener('dragstart', function (e) {
            elementDeplace = { type: ligne.dataset.type, id: ligne.dataset.id };
            ligne.classList.add('drag-en-cours');
            e.dataTransfer.effectAllowed = 'move';
        });
        ligne.addEventListener('dragend', function () {
            ligne.classList.remove('drag-en-cours');
            elementDeplace = null;
        });
    });

    document.querySelectorAll('.drop-cible').forEach(function (cible) {
        cible.addEventListener('dragover', function (e) {
            if (!elementDeplace) return;
            if (elementDeplace.type === 'dossier' && String(elementDeplace.id) === String(cible.dataset.dossierId)) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            cible.classList.add('survole');
        });
        cible.addEventListener('dragleave', function () {
            cible.classList.remove('survole');
        });
        cible.addEventListener('drop', function (e) {
            e.preventDefault();
            cible.classList.remove('survole');
            if (!elementDeplace) return;
            if (elementDeplace.type === 'dossier' && String(elementDeplace.id) === String(cible.dataset.dossierId)) return;

            const dossierId = cible.dataset.dossierId || '';
            const url = elementDeplace.type === 'dossier'
                ? '/archives/dossiers/' + elementDeplace.id + '/deplacer'
                : '/archives/fichiers/' + elementDeplace.id + '/deplacer';
            const champ = elementDeplace.type === 'dossier' ? 'parent_id' : 'dossier_id';

            fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: champ + '=' + encodeURIComponent(dossierId),
            }).then(function () {
                window.location.reload();
            });
        });
    });
});
</script>
@endpush
