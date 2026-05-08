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
                <span class="badge bg-{{ $accord->statut_color }} fs-6">{{ $accord->statut_label }}</span>
            </div>
            <div class="card-body">

                <!-- Progression visuelle -->
                @php
                    $etapes = ['identifie','en_negotiation','signe','en_execution','cloture'];
                    $currentIndex = array_search($accord->statut, $etapes);
                    $isAbandonne = $accord->statut === 'abandonne';
                @endphp

                @if(!$isAbandonne)
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Progression</small>
                        <small class="text-muted">{{ $currentIndex !== false ? $currentIndex + 1 : 0 }}/{{ count($etapes) }}</small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary"
                             style="width: {{ $currentIndex !== false ? (($currentIndex + 1) / count($etapes)) * 100 : 0 }}%">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        @foreach($etapes as $i => $etape)
                        <div class="text-center" style="flex:1;">
                            <div class="rounded-circle mx-auto d-flex align-items-center justify-content-center
                                {{ $currentIndex !== false && $i <= $currentIndex ? 'bg-primary text-white' : 'bg-light text-muted border' }}"
                                 style="width:24px;height:24px;font-size:0.7rem;">
                                @if($currentIndex !== false && $i < $currentIndex)
                                    <i class="bi bi-check"></i>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </div>
                            <div style="font-size:0.62rem;" class="mt-1 text-muted">
                                {{ \App\Models\Accord::$statuts[$etape] }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Infos principales -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">🌍 Pays partenaire</div>
                        <div class="fw-semibold">{{ $accord->pays_partenaire }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">🏛️ Institution</div>
                        <div class="fw-semibold">{{ $accord->institution_partenaire }}</div>
                    </div>
                    @if($accord->universite_beneficiaire)
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">🎓 Université bénéficiaire</div>
                        <div>{{ $accord->universite_beneficiaire }}</div>
                    </div>
                    @endif
                    @if($accord->reunion)
                    <div class="col-sm-6">
                        <div class="text-muted small fw-semibold text-uppercase mb-1">📅 Réunion d'origine</div>
                        <a href="{{ route('reunions.show', $accord->reunion) }}" class="text-decoration-none">
                            {{ $accord->reunion->titre }}
                        </a>
                    </div>
                    @endif
                </div>

                @if($accord->description)
                <div class="mb-4">
                    <div class="text-muted small fw-semibold text-uppercase mb-2">📋 Description / Objectifs</div>
                    <div class="bg-light rounded p-3" style="white-space: pre-line;">{{ $accord->description }}</div>
                </div>
                @endif

                <!-- Dates clés -->
                <div class="row g-2">
                    <div class="col-sm-4">
                        <div class="border rounded p-2 text-center">
                            <div class="text-muted small">Identifié le</div>
                            <div class="fw-semibold">{{ $accord->date_identification?->format('d/m/Y') ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="border rounded p-2 text-center">
                            <div class="text-muted small">Signé le</div>
                            <div class="fw-semibold">{{ $accord->date_signature?->format('d/m/Y') ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="border rounded p-2 text-center">
                            <div class="text-muted small">Expire le</div>
                            <div class="fw-semibold {{ $accord->date_expiration && $accord->date_expiration->isPast() ? 'text-danger' : '' }}">
                                {{ $accord->date_expiration?->format('d/m/Y') ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>
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

        <!-- Historique des statuts -->
        <div class="card">
            <div class="card-header py-3 d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary"></i>
                <span>Historique des statuts</span>
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
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if($h->ancien_statut)
                                <span class="badge bg-secondary">{{ $h->ancien_statut_label }}</span>
                                <i class="bi bi-arrow-right text-muted"></i>
                            @endif
                            <span class="badge bg-{{ \App\Models\Accord::$statutColors[$h->nouveau_statut] ?? 'primary' }}">
                                {{ $h->nouveau_statut_label }}
                            </span>
                        </div>
                        @if($h->commentaire)
                        <div class="text-muted small mt-1">{{ $h->commentaire }}</div>
                        @endif
                        <div class="text-muted" style="font-size:0.75rem;">
                            Par {{ $h->modificateur->nom_complet }} —
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

        <!-- Mise à jour du statut -->
        @if(auth()->user()->canManage() && $accord->statut !== 'cloture' && $accord->statut !== 'abandonne')
        <div class="card mb-3">
            <div class="card-header py-3">
                <i class="bi bi-arrow-repeat text-primary me-2"></i>Mettre à jour le statut
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('accords.statut', $accord) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nouveau statut</label>
                        <select name="statut" class="form-select">
                            @foreach(\App\Models\Accord::$statuts as $key => $label)
                                @if($key !== $accord->statut)
                                <option value="{{ $key }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Commentaire</label>
                        <textarea name="commentaire" rows="3" class="form-control"
                                  placeholder="Précisez la raison du changement..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-2"></i>Valider le changement
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
                    <div class="text-muted small">Créé par</div>
                    <div class="fw-semibold">{{ $accord->createur->nom_complet }}</div>
                    <div class="text-muted small">{{ $accord->createur->poste }}</div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Date de création</div>
                    <div>{{ $accord->created_at->locale('fr')->translatedFormat('d M Y à H:i') }}</div>
                </div>
                <div>
                    <div class="text-muted small">Dernière modification</div>
                    <div>{{ $accord->updated_at->locale('fr')->translatedFormat('d M Y à H:i') }}</div>
                </div>
            </div>
        </div>

        <a href="{{ route('accords.index') }}" class="btn btn-outline-secondary w-100">
            <i class="bi bi-arrow-left me-2"></i>Retour à la liste
        </a>
    </div>
</div>
@endsection
