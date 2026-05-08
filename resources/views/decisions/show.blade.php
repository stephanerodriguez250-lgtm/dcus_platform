@extends('layouts.app')

@section('title', 'Décision')
@section('page-title', 'Suivi de la décision')

@section('content')
<div class="row g-4">
<div class="col-lg-8">

    <!-- Fiche décision -->
    <div class="card mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clipboard-check text-primary"></i>
                <span class="fw-semibold">{{ $decision->intitule }}</span>
            </div>
            <span class="badge bg-{{ $decision->statut_color }} fs-6">
                {{ $decision->statut_label }}
            </span>
        </div>
        <div class="card-body">

            <!-- Barre de progression -->
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-semibold text-muted">Progression globale</span>
                    <span class="fw-bold text-{{ $decision->progression_color }}">
                        {{ $decision->progression }}%
                    </span>
                </div>
                <div class="progress" style="height:14px;border-radius:8px;">
                    <div class="progress-bar bg-{{ $decision->progression_color }} progress-bar-striped
                                {{ $decision->progression < 100 ? 'progress-bar-animated' : '' }}"
                         style="width:{{ $decision->progression }}%;border-radius:8px;">
                    </div>
                </div>
                <!-- Étapes visuelles -->
                <div class="d-flex justify-content-between mt-2">
                    @foreach(\App\Models\Decision::$statuts as $key => $label)
                    @php
                        $keys = array_keys(\App\Models\Decision::$statuts);
                        $currentIdx = array_search($decision->statut, $keys);
                        $thisIdx = array_search($key, $keys);
                        $isDone = $thisIdx <= $currentIdx;
                    @endphp
                    <div class="text-center flex-fill">
                        <div class="rounded-circle mx-auto d-flex align-items-center
                                    justify-content-center fw-bold"
                             style="width:22px;height:22px;font-size:0.65rem;
                                    background:{{ $isDone ? '#16a34a' : '#e5e7eb' }};
                                    color:{{ $isDone ? 'white' : '#9ca3af' }};">
                            @if($isDone) ✓ @else {{ $thisIdx + 1 }} @endif
                        </div>
                        <div style="font-size:0.62rem;" class="mt-1 text-muted">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Infos -->
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="text-muted small fw-semibold text-uppercase mb-1">👤 Responsable</div>
                    <div class="fw-semibold">{{ $decision->responsable ?? '—' }}</div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small fw-semibold text-uppercase mb-1">📅 Échéance</div>
                    <div class="fw-semibold {{ $decision->isEnRetard() ? 'text-danger' : '' }}">
                        {{ $decision->echeance ? $decision->echeance->format('d/m/Y') : '—' }}
                        @if($decision->isEnRetard())
                        <span class="badge bg-danger ms-1">En retard</span>
                        @endif
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small fw-semibold text-uppercase mb-1">🔗 Source</div>
                    @if($decision->codir)
                    <a href="{{ route('codirs.show', $decision->codir) }}" class="text-decoration-none">
                        <span class="badge" style="background:#1a3a5c;">CODIR</span>
                        {{ $decision->codir->objet }}
                    </a>
                    @elseif($decision->reunion)
                    <a href="{{ route('reunions.show', $decision->reunion) }}" class="text-decoration-none">
                        <span class="badge bg-primary">Réunion</span>
                        {{ $decision->reunion->titre }}
                    </a>
                    @endif
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small fw-semibold text-uppercase mb-1">📝 Dernier commentaire</div>
                    <div>{{ $decision->commentaire ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historique -->
    <div class="card">
        <div class="card-header py-3">
            <i class="bi bi-clock-history text-primary me-2"></i>
            Historique des mises à jour
            <span class="badge bg-secondary ms-1">{{ $decision->historiques->count() }}</span>
        </div>
        <div class="card-body p-0">
            @forelse($decision->historiques as $h)
            <div class="d-flex gap-3 p-3 border-bottom">
                <div class="flex-shrink-0">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:36px;height:36px;
                                background:#{{ \App\Models\Decision::$statutColors[$h->nouveau_statut] === 'success' ? 'dcfce7' : 'dbeafe' }};">
                        <i class="bi bi-arrow-up-circle text-primary"></i>
                    </div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        @if($h->ancien_statut)
                        <span class="badge bg-{{ \App\Models\Decision::$statutColors[$h->ancien_statut] ?? 'secondary' }}">
                            {{ $h->ancien_statut_label }}
                        </span>
                        <i class="bi bi-arrow-right text-muted"></i>
                        @endif
                        <span class="badge bg-{{ \App\Models\Decision::$statutColors[$h->nouveau_statut] ?? 'secondary' }}">
                            {{ $h->nouveau_statut_label }}
                        </span>
                        <span class="badge bg-light text-dark border">
                            {{ $h->progression }}%
                        </span>
                    </div>
                    @if($h->commentaire)
                    <div class="text-muted small mb-1">{{ $h->commentaire }}</div>
                    @endif
                    <div style="font-size:0.75rem;" class="text-muted">
                        Par <strong>{{ $h->modificateur->nom_complet }}</strong> —
                        {{ \Carbon\Carbon::parse($h->date_modification)->locale('fr')->translatedFormat('d M Y à H:i') }}
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">Aucun historique disponible</div>
            @endforelse
        </div>
    </div>

</div>

<!-- Panneau de mise à jour -->
<div class="col-lg-4">
    @if(auth()->user()->canManage())
    <div class="card mb-3">
        <div class="card-header py-3">
            <i class="bi bi-arrow-repeat text-primary me-2"></i>Mettre à jour
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('decisions.update', $decision) }}">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Nouveau statut</label>
                    <select name="statut" class="form-select form-select-sm">
                        @foreach(\App\Models\Decision::$statuts as $key => $label)
                        <option value="{{ $key }}" {{ $decision->statut==$key?'selected':'' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small d-flex justify-content-between">
                        <span>Progression</span>
                        <span id="prog-val" class="text-primary">{{ $decision->progression }}%</span>
                    </label>
                    <input type="range" name="progression" class="form-range"
                           min="0" max="100" step="5"
                           value="{{ $decision->progression }}"
                           id="prog-range"
                           oninput="document.getElementById('prog-val').textContent=this.value+'%'">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">0%</small>
                        <small class="text-muted">50%</small>
                        <small class="text-muted">100%</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Commentaire</label>
                    <textarea name="commentaire" rows="3" class="form-control form-control-sm"
                              placeholder="Décrivez l'avancement..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check-lg me-2"></i>Valider la mise à jour
                </button>
            </form>
        </div>
    </div>

    @if(auth()->user()->isAdmin())
    <form method="POST" action="{{ route('decisions.destroy', $decision) }}"
          onsubmit="return confirm('Supprimer cette décision ?')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-outline-danger w-100 mb-3">
            <i class="bi bi-trash me-1"></i>Supprimer
        </button>
    </form>
    @endif
    @endif

    <a href="{{ route('decisions.index') }}" class="btn btn-outline-secondary w-100">
        <i class="bi bi-arrow-left me-2"></i>Retour à la liste
    </a>
</div>
</div>
@endsection
