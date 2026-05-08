{{-- Composant partagé : bloc décisions --}}
{{-- Variables attendues : $decisions (collection), $source_type (codir|reunion), $source_id --}}

<div class="card">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-clipboard-check text-primary me-2"></i>
            Suivi des décisions
            <span class="badge bg-primary ms-1">{{ $decisions->count() }}</span>
        </span>
        @if(auth()->user()->canManage())
        <button class="btn btn-sm btn-primary" data-bs-toggle="collapse"
                data-bs-target="#form-decision">
            <i class="bi bi-plus me-1"></i>Ajouter
        </button>
        @endif
    </div>

    {{-- Formulaire d'ajout --}}
    @if(auth()->user()->canManage())
    <div class="collapse" id="form-decision">
        <div class="card-body border-bottom bg-light">
            <form method="POST" action="{{ route('decisions.store') }}">
                @csrf
                <input type="hidden" name="{{ $source_type }}_id" value="{{ $source_id }}">

                <div class="row g-2">
                    <div class="col-12">
                        <input type="text" name="intitule"
                               class="form-control form-control-sm"
                               placeholder="Intitulé de la décision *" required>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="responsable"
                               class="form-control form-control-sm"
                               placeholder="Responsable">
                    </div>
                    <div class="col-md-6">
                        <input type="date" name="echeance"
                               class="form-control form-control-sm"
                               title="Date d'échéance">
                    </div>
                    <div class="col-md-4">
                        <select name="statut" class="form-select form-select-sm">
                            @foreach(\App\Models\Decision::$statuts as $key => $label)
                            <option value="{{ $key }}" {{ $key=='assignee'?'selected':'' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-center gap-2">
                        <input type="range" name="progression" class="form-range"
                               min="0" max="100" step="5" value="0"
                               id="prog-new"
                               oninput="document.getElementById('prog-new-val').textContent=this.value+'%'">
                        <span id="prog-new-val" class="small fw-bold text-primary" style="min-width:35px;">0%</span>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer
                        </button>
                    </div>
                    <div class="col-12">
                        <textarea name="commentaire" rows="2"
                                  class="form-control form-control-sm"
                                  placeholder="Commentaire initial..."></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Liste des décisions --}}
    <div class="card-body p-0">
        @forelse($decisions as $decision)
        <div class="p-3 border-bottom">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="fw-semibold">{{ $decision->intitule }}</span>
                        <span class="badge bg-{{ $decision->statut_color }}">
                            {{ $decision->statut_label }}
                        </span>
                        @if($decision->isEnRetard())
                        <span class="badge bg-danger">
                            <i class="bi bi-exclamation-triangle me-1"></i>En retard
                        </span>
                        @endif
                    </div>

                    {{-- Barre de progression --}}
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="progress flex-grow-1" style="height:6px;">
                            <div class="progress-bar bg-{{ $decision->progression_color }}"
                                 style="width:{{ $decision->progression }}%"></div>
                        </div>
                        <span class="small fw-bold text-{{ $decision->progression_color }}"
                              style="min-width:35px;">
                            {{ $decision->progression }}%
                        </span>
                    </div>

                    <div class="d-flex gap-3 flex-wrap">
                        @if($decision->responsable)
                        <span class="text-muted small">
                            <i class="bi bi-person me-1"></i>{{ $decision->responsable }}
                        </span>
                        @endif
                        @if($decision->echeance)
                        <span class="text-muted small {{ $decision->isEnRetard() ? 'text-danger' : '' }}">
                            <i class="bi bi-calendar me-1"></i>{{ $decision->echeance->format('d/m/Y') }}
                        </span>
                        @endif
                        @if($decision->commentaire)
                        <span class="text-muted small">
                            <i class="bi bi-chat me-1"></i>{{ \Illuminate\Support\Str::limit($decision->commentaire, 50) }}
                        </span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('decisions.show', $decision) }}"
                   class="btn btn-sm btn-outline-primary flex-shrink-0">
                    <i class="bi bi-eye"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-4">
            <i class="bi bi-clipboard fs-3 d-block mb-2"></i>
            Aucune décision enregistrée
            @if(auth()->user()->canManage())
            <div class="small">Cliquez sur "Ajouter" pour enregistrer une décision</div>
            @endif
        </div>
        @endforelse
    </div>

    @if($decisions->count() > 0)
    <div class="card-footer text-center py-2">
        <a href="{{ route('decisions.index') }}" class="text-primary text-decoration-none small">
            Voir toutes les décisions →
        </a>
    </div>
    @endif
</div>
