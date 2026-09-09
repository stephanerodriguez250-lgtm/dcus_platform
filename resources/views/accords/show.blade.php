@extends('layouts.app')

@section('title', $accord->titre)
@section('page-title', 'Détail de l\'accord')

@section('content')
<div class="row g-4">

    <!-- Colonne principale -->
    <div class="col-lg-8">

        <!-- Fiche accord -->
        <div class="card mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    <span class="fw-semibold">{{ $accord->titre }}</span>
                </div>
                <span class="badge bg-{{ $accord->etape_color }} fs-6">{{ $accord->etape_label }}</span>
            </div>
            <div class="card-body">

                <!-- Progression visuelle -->
                @php
                    $etapes = ['recu', 'apprecie', 'envoye', 'signe'];
                    $currentIndex = array_search($accord->etape, $etapes);
                @endphp
                <div class="mb-4">
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: {{ (($currentIndex + 1) / count($etapes)) * 100 }}%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        @foreach($etapes as $i => $etape)
                        <div class="text-center" style="flex:1;">
                            <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center
                                {{ $i <= $currentIndex ? 'bg-primary text-white' : 'bg-light text-muted border' }}"
                                 style="width:24px;height:24px;font-size:0.7rem;">
                                @if($i < $currentIndex)
                                    <i class="bi bi-check"></i>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </div>
                            <div style="font-size:0.62rem;" class="mt-1 text-muted">
                                {{ \App\Models\Accord::$etapeLabels[$etape] }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Infos principales -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Université / Institution</div>
                        <div class="fw-semibold">{{ $accord->institution_partenaire }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Référence MESRS</div>
                        <div class="fw-semibold">{{ $accord->reference }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Arrivé le</div>
                        <div>{{ $accord->date_arrivee->format('d/m/Y') }} à {{ substr($accord->heure_arrivee, 0, 5) }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Fichier de l'accord</div>
                        @if($accord->chemin_fichier)
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($accord->chemin_fichier) }}"
                           target="_blank" rel="noopener" class="text-decoration-none">
                            <i class="bi bi-download me-1"></i>{{ $accord->nom_fichier }}
                        </a>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>

                @if($accord->envoye_le || $accord->date_signature)
                <div class="row g-2">
                    @if($accord->envoye_le)
                    <div class="col-sm-4">
                        <div class="border rounded p-2 text-center">
                            <div class="text-muted small">Envoyé le</div>
                            <div class="fw-semibold">{{ $accord->envoye_le->format('d/m/Y') }}</div>
                        </div>
                    </div>
                    @endif
                    @if($accord->date_signature)
                    <div class="col-sm-4">
                        <div class="border rounded p-2 text-center">
                            <div class="text-muted small">Signé le</div>
                            <div class="fw-semibold">{{ $accord->date_signature->format('d/m/Y') }}</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="border rounded p-2 text-center">
                            <div class="text-muted small">Expire le</div>
                            <div class="fw-semibold {{ $accord->date_expiration && $accord->date_expiration->isPast() ? 'text-danger' : '' }}">
                                {{ $accord->date_expiration?->format('d/m/Y') ?? '—' }}
                                @if($accord->duree_label)<div class="text-muted small">({{ $accord->duree_label }})</div>@endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @if(auth()->user()->canManage())
            <div class="card-footer d-flex gap-2 py-3">
                <a href="{{ route('accords.edit', $accord) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                <form method="POST" action="{{ route('accords.destroy', $accord) }}"
                      onsubmit="return confirm('Supprimer cet accord ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i>Supprimer
                    </button>
                </form>
            </div>
            @endif
        </div>

        <!-- Appréciation -->
        <div class="card mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clipboard-check text-primary me-2"></i>Fiche d'appréciation</span>
                @if(auth()->user()->peutApprecierAccords())
                <a href="{{ route('accords.apprecier.create', $accord) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-{{ $accord->appreciation ? 'pencil' : 'plus' }} me-1"></i>{{ $accord->appreciation ? 'Modifier' : 'Apprécier' }}
                </a>
                @endif
            </div>
            <div class="card-body">
                @if($accord->appreciation)
                @php $a = $accord->appreciation; @endphp
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Origine</div>
                        <div>{{ $a->origine }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Objet</div>
                        <div>{{ $a->objet }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Avis</div>
                        <div style="white-space: pre-line;">{{ $a->avis }}</div>
                    </div>
                    @if($a->observations_forme)
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Observations sur la forme</div>
                        <div style="white-space: pre-line;">{{ $a->observations_forme }}</div>
                    </div>
                    @endif
                    @if($a->observations_fond)
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">Observations sur le fond</div>
                        <div style="white-space: pre-line;">{{ $a->observations_fond }}</div>
                    </div>
                    @endif
                </div>
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted small">
                        Par {{ $a->redacteur->nom_complet }} — {{ $a->created_at->locale('fr')->translatedFormat('d M Y à H:i') }}
                    </div>
                    @if($a->chemin_fiche_word)
                    <a href="{{ route('accords.appreciations.telecharger', $a) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-earmark-word me-1"></i>Télécharger la fiche (.docx)
                    </a>
                    @endif
                </div>
                @else
                <div class="text-center text-muted py-4">
                    <i class="bi bi-clipboard-x fs-3 d-block mb-2"></i>Aucune appréciation enregistrée
                </div>
                @endif
            </div>
        </div>

        <!-- Historique -->
        <div class="card">
            <div class="card-header py-3 d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary"></i>
                <span>Historique</span>
                <span class="badge bg-secondary">{{ $accord->historiques->count() }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($accord->historiques as $h)
                <div class="d-flex gap-3 p-3 border-bottom">
                    <div class="text-center" style="min-width: 40px;">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto"
                             style="width:36px;height:36px;">
                            <i class="bi bi-arrow-right-circle text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-semibold small">{{ $h->evenement }}</div>
                        @if($h->commentaire)
                        <div class="text-muted small mt-1">{{ $h->commentaire }}</div>
                        @endif
                        <div class="text-muted" style="font-size:0.75rem;">
                            {{ $h->modificateur->nom_complet }} —
                            {{ \Carbon\Carbon::parse($h->date_modification)->locale('fr')->translatedFormat('d M Y à H:i') }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">Aucun historique disponible.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Colonne latérale -->
    <div class="col-lg-4">

        @if(auth()->user()->canManage() && $accord->appreciation && ! $accord->envoye_le)
        <div class="card mb-3">
            <div class="card-header py-3"><i class="bi bi-send text-primary me-2"></i>Envoi au destinataire</div>
            <div class="card-body">
                <p class="text-muted small">Marquez l'accord et sa fiche d'appréciation comme envoyés au destinataire.</p>
                <form method="POST" action="{{ route('accords.envoyer', $accord) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-2"></i>Marquer comme envoyé
                    </button>
                </form>
            </div>
        </div>
        @endif

        @if(auth()->user()->canManage() && $accord->envoye_le && ! $accord->date_signature)
        <div class="card mb-3">
            <div class="card-header py-3"><i class="bi bi-pen text-primary me-2"></i>Enregistrer la signature</div>
            <div class="card-body">
                <form method="POST" action="{{ route('accords.signer', $accord) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date de signature</label>
                        <input type="date" name="date_signature"
                               class="form-control @error('date_signature') is-invalid @enderror"
                               value="{{ old('date_signature') }}">
                        <div class="form-text">Laissez vide pour utiliser la date du jour.</div>
                        @error('date_signature')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Durée</label>
                            <input type="number" min="1" name="duree_valeur"
                                   class="form-control @error('duree_valeur') is-invalid @enderror"
                                   value="{{ old('duree_valeur') }}">
                            @error('duree_valeur')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Unité</label>
                            <select name="duree_unite" class="form-select @error('duree_unite') is-invalid @enderror">
                                <option value="ans" {{ old('duree_unite') == 'ans' ? 'selected' : '' }}>Ans</option>
                                <option value="mois" {{ old('duree_unite') == 'mois' ? 'selected' : '' }}>Mois</option>
                            </select>
                            @error('duree_unite')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-2"></i>Enregistrer la signature
                    </button>
                </form>
            </div>
        </div>
        @endif

        <!-- Métadonnées -->
        <div class="card mb-3">
            <div class="card-header py-3">
                <i class="bi bi-info-circle text-primary me-2"></i>Informations
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="text-muted small">Enregistré par</div>
                    <div class="fw-semibold">{{ $accord->createur->nom_complet }}</div>
                    <div class="text-muted small">{{ $accord->createur->poste }}</div>
                </div>
                <div>
                    <div class="text-muted small">Date d'enregistrement</div>
                    <div>{{ $accord->created_at->locale('fr')->translatedFormat('d M Y à H:i') }}</div>
                </div>
            </div>
        </div>

        <a href="{{ route('accords.index') }}" class="btn btn-outline-secondary w-100">
            <i class="bi bi-arrow-left me-2"></i>Retour à la liste
        </a>
    </div>
</div>
@endsection
