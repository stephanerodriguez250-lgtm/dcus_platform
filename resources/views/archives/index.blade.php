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
                <li class="breadcrumb-item">
                    <a href="{{ route('archives.index') }}"><i class="bi bi-folder2-open me-1"></i>Racine</a>
                </li>
                @foreach($filAriane as $etape)
                <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                    @if($loop->last)
                        {{ $etape->nom }}
                    @else
                        <a href="{{ route('archives.index', ['dossier' => $etape->id]) }}">{{ $etape->nom }}</a>
                    @endif
                </li>
                @endforeach
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#nouveauDossierModal">
            <i class="bi bi-folder-plus me-2"></i>Nouveau dossier
        </button>
        <a href="{{ route('archives.fichiers.create', $dossier ? ['dossier' => $dossier->id] : []) }}" class="btn btn-primary">
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
                        <th class="ps-4">Nom</th>
                        <th>Numéro</th>
                        <th>Taille</th>
                        <th>Ajouté le</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sousDossiers as $sousDossier)
                    <tr>
                        <td class="ps-4">
                            <a href="{{ route('archives.index', ['dossier' => $sousDossier->id]) }}" class="text-decoration-none">
                                <i class="bi bi-folder-fill text-warning me-2"></i>{{ $sousDossier->nom }}
                            </a>
                        </td>
                        <td colspan="2" class="text-muted small">Dossier</td>
                        <td class="text-end pe-4">—</td>
                    </tr>
                    @empty
                    @endforelse

                    @forelse($fichiers as $fichier)
                    <tr>
                        <td class="ps-4">
                            <i class="bi {{ $fichier->icone }} me-2"></i>{{ $fichier->intitule }}
                        </td>
                        <td>{{ $fichier->numero ?? '—' }}</td>
                        <td>{{ $fichier->taille_formatee }}</td>
                        <td>{{ $fichier->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end pe-4">
                            <a href="{{ route('archives.fichiers.download', $fichier) }}" class="btn btn-sm btn-outline-primary" title="Télécharger">
                                <i class="bi bi-download"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @if($sousDossiers->isEmpty() && $fichiers->isEmpty())
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
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
@endsection
