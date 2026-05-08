@extends('layouts.app')
@section('title', $reunion->titre)
@section('page-title', 'Détail de la réunion')
@section('content')
<div class="row g-4">
    <!-- Colonne principale -->
    <div class="col-lg-8">
        <!-- Infos réunion -->
        <div class="card mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-event text-primary"></i>
                    <span class="fw-semibold">{{ $reunion->titre }}</span>
                </div>
                <span class="badge bg-{{ $reunion->statut_color }} fs-6">{{ $reunion->statut_label }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1"> Date</div>
                        <div class="fw-semibold">{{ $reunion->date->locale('fr')->translatedFormat('l d F Y') }}</div>
                        @if($reunion->heure)
                        <div class="text-muted small">à {{ $reunion->heure }}</div>
                        @endif
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1"> Lieu</div>
                        <div class="fw-semibold">{{ $reunion->lieu }}</div>
                    </div>
                    @if($reunion->convocateur)
                    <div class="col-12">
                        <div class="text-muted small fw-semibold text-uppercase mb-1"> Convocateur</div>
                        <div>{{ $reunion->convocateur }}</div>
                    </div>
                    @endif
                </div>
                <div class="mb-4">
                    <div class="text-muted small fw-semibold text-uppercase mb-2"> Ordre du jour</div>
                    <div class="bg-light rounded p-3" style="white-space: pre-line;">{{ $reunion->ordre_du_jour }}</div>
                </div>
                @if($reunion->compte_rendu)
                <div>
                    <div class="text-muted small fw-semibold text-uppercase mb-2"> Compte rendu</div>
                    <div class="bg-light rounded p-3" style="white-space: pre-line;">{{ $reunion->compte_rendu }}</div>
                </div>
                @else
                @if(auth()->user()->canManage())
                <div class="alert alert-warning d-flex align-items-center gap-2 mb-0">
                    <i class="bi bi-exclamation-triangle"></i>
                    <div>
                        Aucun compte rendu saisi.
                        <a href="{{ route('reunions.edit', $reunion) }}" class="alert-link">Ajouter le compte rendu</a>
                    </div>
                </div>
                @endif
                @endif
            </div>
            @if(auth()->user()->canManage())
            <div class="card-footer d-flex gap-2 py-3">
                <a href="{{ route('reunions.edit', $reunion) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                <form method="POST" action="{{ route('reunions.destroy', $reunion) }}"
                      onsubmit="return confirm('Supprimer cette réunion ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </form>
            </div>
            @endif
        </div>
        <!-- Accords liés -->
        <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    <span>Accords issus de cette réunion</span>
                    <span class="badge bg-primary">{{ $reunion->accords->count() }}</span>
                </div>
                @if(auth()->user()->canManage())
                <a href="{{ route('accords.create', ['reunion_id' => $reunion->id]) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus me-1"></i>Lier un accord
                </a>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse($reunion->accords as $accord)
                <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $accord->titre }}</div>
                        <div class="text-muted small">
                            <i class="bi bi-globe me-1"></i>{{ $accord->pays_partenaire }} — {{ $accord->institution_partenaire }}
                        </div>
                    </div>
                    <span class="badge bg-{{ $accord->statut_color }}">{{ $accord->statut_label }}</span>
                    <a href="{{ route('accords.show', $accord) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i>
                    </a>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-file-x fs-3 d-block mb-2"></i>
                    Aucun accord lié à cette réunion
                </div>
                @endforelse
            </div>
        </div>
    </div>
    <!-- Colonne latérale -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header py-3">
                <i class="bi bi-info-circle text-primary me-2"></i>Informations
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Créé par</div>
                    <div class="fw-semibold">{{ $reunion->createur->nom_complet }}</div>
                    <div class="text-muted small">{{ $reunion->createur->poste }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Date de création</div>
                    <div>{{ $reunion->created_at->locale('fr')->translatedFormat('d M Y à H:i') }}</div>
                </div>
                <div>
                    <div class="text-muted small">Dernière modification</div>
                    <div>{{ $reunion->updated_at->locale('fr')->translatedFormat('d M Y à H:i') }}</div>
                </div>
            </div>
        </div>
        <div class="mt-3 d-grid gap-2">
            <a href="{{ route('reunions.pdf', $reunion) }}" class="btn btn-danger w-100">
                <i class="bi bi-file-earmark-pdf me-2"></i>Télécharger le PDF
            </a>
            <a href="{{ route('reunions.index') }}" class="btn btn-outline-secondary w-100">
                <i class="bi bi-arrow-left me-2"></i>Retour à la liste
            </a>
        </div>
    </div>
</div>
@endsection