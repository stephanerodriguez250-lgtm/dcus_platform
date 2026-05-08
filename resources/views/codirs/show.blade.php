@extends('layouts.app')

@section('title', $codir->objet)
@section('page-title', 'Détail CODIR')

@section('content')
<div class="row g-4">

<!-- Colonne principale -->
<div class="col-lg-8">

    <!-- Fiche CODIR -->
    <div class="card mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-people text-primary"></i>
                <span class="fw-semibold">{{ $codir->objet }}</span>
            </div>
            <span class="badge bg-{{ $codir->statut_color }} fs-6">{{ $codir->statut_label }}</span>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <div class="text-muted small fw-semibold text-uppercase mb-1">📅 Date</div>
                    <div class="fw-semibold">
                        {{ $codir->date->locale('fr')->translatedFormat('l d F Y') }}
                    </div>
                    @if($codir->heure_debut)
                    <div class="text-muted small">
                        {{ $codir->heure_debut }}
                        @if($codir->heure_fin) — {{ $codir->heure_fin }} @endif
                    </div>
                    @endif
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small fw-semibold text-uppercase mb-1">📍 Lieu</div>
                    <div class="fw-semibold">{{ $codir->lieu }}</div>
                </div>
                @if($codir->presidente)
                <div class="col-sm-6">
                    <div class="text-muted small fw-semibold text-uppercase mb-1">👑 Présidente</div>
                    <div>{{ $codir->presidente }}</div>
                </div>
                @endif
                @if($codir->rapporteur)
                <div class="col-sm-6">
                    <div class="text-muted small fw-semibold text-uppercase mb-1">📝 Rapporteur</div>
                    <div>{{ $codir->rapporteur }}</div>
                </div>
                @endif
                @if($codir->prochaine_reunion)
                <div class="col-12">
                    <div class="alert alert-info py-2 mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-calendar-check"></i>
                        <span>Prochaine réunion :
                            <strong>{{ $codir->prochaine_reunion->locale('fr')->translatedFormat('l d F Y') }}</strong>
                        </span>
                    </div>
                </div>
                @endif
            </div>

            @if($codir->synthese)
            <div class="mb-4">
                <div class="text-muted small fw-semibold text-uppercase mb-2">📋 Synthèse des discussions</div>
                <div class="bg-light rounded p-3" style="white-space:pre-line;">{{ $codir->synthese }}</div>
            </div>
            @endif

            @if($codir->decisions)
            <div class="mb-4">
                <div class="text-muted small fw-semibold text-uppercase mb-2">✅ Décisions prises</div>
                <div class="bg-light rounded p-3" style="white-space:pre-line;">{{ $codir->decisions }}</div>
            </div>
            @endif

            @if($codir->divers)
            <div class="mb-2">
                <div class="text-muted small fw-semibold text-uppercase mb-2">📌 Divers</div>
                <div class="bg-light rounded p-3" style="white-space:pre-line;">{{ $codir->divers }}</div>
            </div>
            @endif
        </div>
        @if(auth()->user()->canManage())
        <div class="card-footer d-flex gap-2 py-3">
            <a href="{{ route('codirs.edit', $codir) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil me-1"></i>Modifier
            </a>
            <form method="POST" action="{{ route('codirs.destroy', $codir) }}"
                  onsubmit="return confirm('Supprimer ce CODIR ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Supprimer
                </button>
            </form>
        </div>
        @endif
    </div>

    <!-- Participants -->
    <div class="card mb-4">
        <div class="card-header py-3">
            <i class="bi bi-people text-primary me-2"></i>
            Participants
            <span class="badge bg-primary ms-1">{{ $codir->participants->count() }}</span>
        </div>
        <div class="card-body p-0">
            @forelse($codir->participants as $p)
            <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center
                            justify-content-center fw-bold text-primary flex-shrink-0"
                     style="width:36px;height:36px;font-size:0.8rem;">
                    {{ strtoupper(substr($p->nom_complet, 0, 2)) }}
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">{{ $p->nom_complet }}</div>
                    @if($p->fonction)
                    <div class="text-muted small">{{ $p->fonction }}</div>
                    @endif
                    @if($p->email)
                    <div class="text-muted small">
                        <i class="bi bi-envelope me-1"></i>{{ $p->email }}
                    </div>
                    @endif
                </div>
                @if($p->present)
                    <span class="badge bg-success">Présent</span>
                @else
                    <span class="badge bg-secondary">Absent</span>
                @endif
            </div>
            @empty
            <div class="text-center text-muted py-4">Aucun participant enregistré</div>
            @endforelse
        </div>
    </div>

    <!-- Compte rendu PDF -->
    <div class="card">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                Compte rendu officiel
            </span>
        </div>
        <div class="card-body">
            @if($peutTelecharger)
            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                <div class="text-danger fs-2">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold">
                        CR-CODIR-{{ $codir->date->format('Y-m-d') }}.pdf
                    </div>
                    <div class="text-muted small">
                        Compte rendu généré automatiquement depuis les données du CODIR
                    </div>
                </div>
                <a href="{{ route('codirs.pdf', $codir) }}"
                   class="btn btn-danger">
                    <i class="bi bi-download me-2"></i>Télécharger le PDF
                </a>
            </div>
            @else
            <div class="text-center text-muted py-4">
                <i class="bi bi-lock fs-3 d-block mb-2"></i>
                <div class="fw-semibold">Accès restreint</div>
                <div class="small">Vous n'avez pas encore accès au compte rendu de ce CODIR.<br>
                Contactez l'administratrice pour obtenir l'autorisation.</div>
            </div>
            @endif
        </div>

        @if(auth()->user()->isAdmin())
        <div class="card-footer bg-light py-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-info-circle text-primary"></i>
                <small class="text-muted">
                    En tant qu'administratrice, vous pouvez toujours télécharger le compte rendu.
                    Gérez les accès des agents dans le panneau de droite.
                </small>
            </div>
        </div>
        @endif
    </div>

</div>

<!-- Colonne latérale -->
<div class="col-lg-4">

    <!-- Gestion des accès (admin seulement) -->
    @if(auth()->user()->isAdmin())
    <div class="card mb-3">
        <div class="card-header py-3">
            <i class="bi bi-shield-lock text-primary me-2"></i>Accès au compte rendu
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Désignez les agents autorisés à télécharger le compte rendu PDF de ce CODIR.
            </p>

            <form method="POST" action="{{ route('codirs.acces', $codir) }}" class="mb-3">
                @csrf
                <input type="hidden" name="action" value="donner">
                <label class="form-label fw-semibold small">Donner accès à</label>
                <div class="d-flex gap-2">
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">— Choisir un agent —</option>
                        @foreach($users as $u)
                            @if(!$codir->userPeutTelecharger($u->id) && !$u->isAdmin())
                            <option value="{{ $u->id }}">{{ $u->nom_complet }}</option>
                            @endif
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
            </form>

            @forelse($codir->acces as $acces)
            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                <div>
                    <div class="fw-semibold small">{{ $acces->utilisateur->nom_complet }}</div>
                    <div class="text-muted" style="font-size:0.72rem;">
                        Accordé par {{ $acces->accordePar->nom_complet }}
                    </div>
                </div>
                <form method="POST" action="{{ route('codirs.acces', $codir) }}">
                    @csrf
                    <input type="hidden" name="action" value="retirer">
                    <input type="hidden" name="user_id" value="{{ $acces->user_id }}">
                    <button type="submit" class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('Retirer l\'accès ?')">
                        <i class="bi bi-x"></i>
                    </button>
                </form>
            </div>
            @empty
            <div class="text-muted small text-center py-2">
                Aucun accès accordé pour l'instant
            </div>
            @endforelse
        </div>
    </div>
    @endif

    <!-- Infos -->
    <div class="card mb-3">
        <div class="card-header py-3">
            <i class="bi bi-info-circle text-primary me-2"></i>Informations
        </div>
        <div class="card-body">
            <div class="mb-3">
                <div class="text-muted small">Créé par</div>
                <div class="fw-semibold">{{ $codir->createur->nom_complet }}</div>
            </div>
            <div class="mb-3">
                <div class="text-muted small">Date d'enregistrement</div>
                <div>{{ $codir->created_at->locale('fr')->translatedFormat('d M Y à H:i') }}</div>
            </div>
            <div>
                <div class="text-muted small">Dernière modification</div>
                <div>{{ $codir->updated_at->locale('fr')->translatedFormat('d M Y à H:i') }}</div>
            </div>
        </div>
    </div>

    <a href="{{ route('codirs.index') }}" class="btn btn-outline-secondary w-100">
        <i class="bi bi-arrow-left me-2"></i>Retour à la liste
    </a>
</div>

</div>
@endsection
