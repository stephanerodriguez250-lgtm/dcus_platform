@extends('layouts.app')
@section('title', 'Suivi des décisions')
@section('page-title', 'Suivi des décisions')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1 fw-bold text-dark">Suivi des décisions</h5>
        <p class="text-muted small mb-0">Décisions issues des CODIR, réunions et notes ministérielles</p>
    </div>
</div>
<div class="mb-4">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDecisionModal">
        <i class="bi bi-plus-circle me-1"></i>Ajouter une décision
    </button>
</div>
{{-- Erreurs de validation --}}
@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
<!-- Compteurs rapides -->
<div class="row g-3 mb-4">
    @foreach(\App\Models\Decision::$statuts as $key => $label)
    @php $count = \App\Models\Decision::where('statut', $key)->count(); @endphp
    <div class="col">
        <div class="card text-center py-3 h-100">
            <div class="fw-bold fs-4 text-{{ \App\Models\Decision::$statutColors[$key] }}">
                {{ $count }}
            </div>
    </div>
            <div class="text-muted small">{{ $label }}</div>
        </div>
    @endforeach
</div>
<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Rechercher une décision..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    @foreach($statuts as $key => $label)
                    <option value="{{ $key }}" {{ request('statut')==$key?'selected':'' }}>
                        {{ $label }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="source" class="form-select">
                    <option value="">Toutes sources</option>
                    <optgroup label="CODIR">
                        <option value="codir_interne"   {{ request('source')=='codir_interne'?'selected':'' }}>CODIR Interne</option>
                        <option value="codir_externe"   {{ request('source')=='codir_externe'?'selected':'' }}>CODIR Externe</option>
                    </optgroup>
                    <optgroup label="Réunion">
                        <option value="reunion_interne" {{ request('source')=='reunion_interne'?'selected':'' }}>Réunion Interne</option>
                        <option value="reunion_externe" {{ request('source')=='reunion_externe'?'selected':'' }}>Réunion Externe</option>
                    </optgroup>
                    <optgroup label="Ministère">
                        <option value="note_ministerielle" {{ request('source')=='note_ministerielle'?'selected':'' }}>Note Ministérielle</option>
                    </optgroup>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('decisions.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>
<!-- Liste des décisions -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Décision</th>
                        <th>Source</th>
                        <th>Responsable</th>
                        <th>Échéance</th>
                        <th style="width:180px">Progression</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($decisions as $decision)
                    <tr class="{{ $decision->isEnRetard() ? 'table-warning' : '' }}">
                        <td class="ps-4">
                            <div class="fw-semibold">{{ $decision->intitule }}</div>
                            @if($decision->commentaire)
                            <div class="text-muted small text-truncate" style="max-width:250px;">
                                {{ $decision->commentaire }}
                            </div>
                            @endif
                        </td>
                        <td>
                            @if($decision->source_type)
                                @php
                                    $icons = [
                                        'codir_interne'      => 'bi-people',
                                        'codir_externe'      => 'bi-people',
                                        'reunion_interne'    => 'bi-calendar-event',
                                        'reunion_externe'    => 'bi-calendar-event',
                                        'note_ministerielle' => 'bi-file-earmark-text',
                                    ];
                                    $colors = [
                                        'codir_interne'      => '1a3a5c',
                                        'codir_externe'      => '0d6efd',
                                        'reunion_interne'    => '6f42c1',
                                        'reunion_externe'    => '0dcaf0',
                                        'note_ministerielle' => 'ffc107',
                                    ];
                                    $labels = \App\Models\Decision::$sourceTypeLabels;
                                    $icon  = $icons[$decision->source_type]  ?? 'bi-dot';
                                    $color = $colors[$decision->source_type] ?? '6c757d';
                                    $label = $labels[$decision->source_type] ?? $decision->source_type;
                                @endphp
                                <span class="badge" style="background:#{{ $color }};">
                                    <i class="bi {{ $icon }} me-1"></i>{{ $label }}
                                </span>
                                <div class="text-muted small mt-1">{{ $decision->source_resume }}</div>
                                @if($decision->source_type === 'note_ministerielle' && $decision->note_fichier)
                                <a href="{{ route('decisions.note.download', $decision) }}" class="small text-decoration-none">
                                    <i class="bi bi-paperclip me-1"></i>Pièce jointe
                                </a>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $decision->responsable ?? '—' }}</td>
                        <td>
                            @if($decision->echeance)
                            <span class="{{ $decision->isEnRetard() ? 'text-danger fw-bold' : '' }}">
                                {{ $decision->echeance->format('d/m/Y') }}
                            </span>
                            @if($decision->isEnRetard())
                            <div><span class="badge bg-danger">En retard</span></div>
                            @endif
                            @else
                            <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height:8px;">
                                    <div class="progress-bar bg-{{ $decision->progression_color }}"
                                         style="width:{{ $decision->progression }}%">
                                    </div>
                                </div>
                                <span class="small fw-bold text-{{ $decision->progression_color }}">
                                    {{ $decision->progression }}%
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $decision->statut_color }}">
                                {{ $decision->statut_label }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('decisions.show', $decision) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-clipboard-check fs-2 d-block mb-2"></i>
                            Aucune décision enregistrée
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($decisions->hasPages())
    <div class="card-footer d-flex justify-content-between py-3">
        <span class="text-muted small">{{ $decisions->total() }} décision(s)</span>
        {{ $decisions->links() }}
    </div>
    @endif
</div>
{{-- ================================================================
     MODAL : Ajouter une décision
================================================================ --}}
<div class="modal fade" id="addDecisionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form action="{{ route('decisions.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title fw-bold">Ajouter une nouvelle décision</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" style="max-height:70vh; overflow-y:auto;">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Intitulé de la décision <span class="text-danger">*</span></label>
                        <input type="text" name="intitule" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Commentaire / Description</label>
                        <textarea name="commentaire" rows="3" class="form-control"></textarea>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Responsable</label>
                            <input type="text" name="responsable" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Échéance</label>
                            <input type="date" name="echeance" class="form-control">
                        </div>
                    </div>
                    <input type="hidden" name="statut"      value="assignee">
                    <input type="hidden" name="progression" value="0">
                    <div class="border rounded p-4 bg-light">
                        <h5 class="fw-bold mb-4">
                            <i class="bi bi-diagram-3 me-2 text-primary"></i>Source de la décision
                        </h5>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Type de source <span class="text-danger">*</span></label>
                                <select name="source_type" id="source_type" class="form-select" required onchange="toggleSourceFields()">
                                    <option value="">Choisir...</option>
                                    <optgroup label="CODIR">
                                        <option value="codir_interne">CODIR Interne</option>
                                        <option value="codir_externe">CODIR Externe</option>
                                    </optgroup>
                                    <optgroup label="Réunion">
                                        <option value="reunion_interne">Réunion Interne</option>
                                        <option value="reunion_externe">Réunion Externe</option>
                                    </optgroup>
                                    <optgroup label="Ministère">
                                        <option value="note_ministerielle">Note Ministérielle</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                        <div id="sourceExistanteFields" style="display:none;">
                            <label class="form-label fw-semibold">Sélectionner la réunion / CODIR concerné(e)</label>
                            <div id="listeReunions" style="display:none;">
                                <select name="source_id" class="form-select mb-2">
                                    <option value="">Choisir une réunion...</option>
                                    @foreach(\App\Models\Reunion::orderBy('date','desc')->get() as $reunion)
                                    <option value="reunion_{{ $reunion->id }}">
                                        {{ $reunion->titre }} — {{ \Carbon\Carbon::parse($reunion->date)->format('d/m/Y') }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="listeCodirs" style="display:none;">
                                <select name="source_id" class="form-select mb-2">
                                    <option value="">Choisir un CODIR...</option>
                                    @foreach(\App\Models\Codir::orderBy('date','desc')->get() as $codir)
                                    <option value="codir_{{ $codir->id }}">
                                        {{ $codir->objet ?? 'CODIR' }} — {{ \Carbon\Carbon::parse($codir->date)->format('d/m/Y') }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div id="noteMinisterielleFields" style="display:none;" class="mt-3 border rounded p-3 bg-warning bg-opacity-10">
                            <h6 class="fw-bold mb-3">
                                <i class="bi bi-file-earmark-text me-2"></i>Informations de la note ministérielle
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Numéro de la note</label>
                                    <input type="text" name="note_numero" class="form-control" placeholder="Ex : N°123/MESRS/2026">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date de la note</label>
                                    <input type="date" name="note_date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Expéditeur</label>
                                    <input type="text" name="note_expediteur" class="form-control" placeholder="Ex : Cabinet du Ministre">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Objet de la note</label>
                                    <input type="text" name="note_objet" class="form-control">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Joindre le fichier <span class="text-muted small">(PDF / Word – max 10 Mo)</span></label>
                                    <input type="file" name="note_fichier" class="form-control" accept=".pdf,.doc,.docx">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Enregistrer la décision
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('addDecisionModal')).show();
    });
</script>
@endif
<script>
function toggleSourceFields() {
    const sourceType    = document.getElementById('source_type').value;
    const noteFields    = document.getElementById('noteMinisterielleFields');
    const sourceFields  = document.getElementById('sourceExistanteFields');
    const listeReunions = document.getElementById('listeReunions');
    const listeCodirs   = document.getElementById('listeCodirs');
    noteFields.style.display    = 'none';
    sourceFields.style.display  = 'none';
    listeReunions.style.display = 'none';
    listeCodirs.style.display   = 'none';
    if (sourceType === 'note_ministerielle') {
        noteFields.style.display = 'block';
        return;
    }
    if (['codir_interne', 'codir_externe'].includes(sourceType)) {
        sourceFields.style.display = 'block';
        listeCodirs.style.display  = 'block';
    }
    if (['reunion_interne', 'reunion_externe'].includes(sourceType)) {
        sourceFields.style.display  = 'block';
        listeReunions.style.display = 'block';
    }
}
</script>
@endsection