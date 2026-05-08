@extends('layouts.app')

@section('title', 'Nouveau CODIR')
@section('page-title', 'Nouveau CODIR')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-9">
<div class="card">
    <div class="card-header py-3 d-flex align-items-center gap-2">
        <i class="bi bi-people text-primary"></i>
        <span>Enregistrer un nouveau CODIR</span>
    </div>
    <div class="card-body p-4">

        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('codirs.store') }}" id="form-codir">
        @csrf

        <!-- Informations générales -->
        <div class="mb-4">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                <i class="bi bi-info-circle me-2"></i>Informations générales
            </h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Objet du CODIR <span class="text-danger">*</span></label>
                    <input type="text" name="objet" class="form-control @error('objet') is-invalid @enderror"
                           value="{{ old('objet', 'CODIR hebdomadaire') }}"
                           placeholder="Ex: CODIR hebdomadaire">
                    @error('objet')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                           value="{{ old('date') }}">
                    @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Heure de début</label>
                    <input type="time" name="heure_debut" class="form-control"
                           value="{{ old('heure_debut', '09:00') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Heure de fin</label>
                    <input type="time" name="heure_fin" class="form-control"
                           value="{{ old('heure_fin') }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Lieu</label>
                    <input type="text" name="lieu" class="form-control"
                           value="{{ old('lieu', 'Salle de réunion DCUS') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
                    <select name="statut" class="form-select">
                        <option value="planifie" {{ old('statut','planifie')=='planifie'?'selected':'' }}>Planifié</option>
                        <option value="tenu"     {{ old('statut')=='tenu'?'selected':'' }}>Tenu</option>
                        <option value="annule"   {{ old('statut')=='annule'?'selected':'' }}>Annulé</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Présidente</label>
                    <input type="text" name="presidente" class="form-control"
                           value="{{ old('presidente') }}"
                           placeholder="Ex: Mme AHOUNOU Judith">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Rapporteur</label>
                    <input type="text" name="rapporteur" class="form-control"
                           value="{{ old('rapporteur') }}"
                           placeholder="Ex: ABOUTA F. Bernis">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Prochaine réunion</label>
                    <input type="date" name="prochaine_reunion" class="form-control"
                           value="{{ old('prochaine_reunion') }}">
                </div>
            </div>
        </div>

        <!-- Participants -->
        <div class="mb-4">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                <i class="bi bi-people me-2"></i>Participants
                <small class="text-muted fw-normal">(les emails seront notifiés)</small>
            </h6>

            <div id="participants-container">
                <!-- Ligne par défaut -->
                <div class="participant-row row g-2 mb-2 align-items-center">
                    <div class="col-md-4">
                        <input type="text" name="participants[0][nom_complet]"
                               class="form-control form-control-sm"
                               placeholder="Nom complet *">
                    </div>
                    <div class="col-md-3">
                        <input type="email" name="participants[0][email]"
                               class="form-control form-control-sm"
                               placeholder="Email (optionnel)">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="participants[0][fonction]"
                               class="form-control form-control-sm"
                               placeholder="Fonction">
                    </div>
                    <div class="col-md-1 text-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="participants[0][present]" checked>
                            <label class="form-check-label small">Présent</label>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-participant"
                                style="display:none;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-participant">
                <i class="bi bi-plus me-1"></i>Ajouter un participant
            </button>
        </div>

        <div class="alert alert-info d-flex gap-2 align-items-start mb-4">
            <i class="bi bi-envelope-check fs-5 mt-1"></i>
            <div>
                <strong>Notifications automatiques</strong><br>
                <small>Un email de convocation sera envoyé à chaque participant disposant d'une adresse email,
                ainsi qu'à tous les agents DCUS actifs de la plateforme.</small>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-2"></i>Enregistrer et notifier
            </button>
            <a href="{{ route('codirs.index') }}" class="btn btn-outline-secondary px-4">Annuler</a>
        </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
let count = 1;

document.getElementById('add-participant').addEventListener('click', function () {
    const container = document.getElementById('participants-container');
    const row = document.createElement('div');
    row.className = 'participant-row row g-2 mb-2 align-items-center';
    row.innerHTML = `
        <div class="col-md-4">
            <input type="text" name="participants[${count}][nom_complet]"
                   class="form-control form-control-sm" placeholder="Nom complet *">
        </div>
        <div class="col-md-3">
            <input type="email" name="participants[${count}][email]"
                   class="form-control form-control-sm" placeholder="Email (optionnel)">
        </div>
        <div class="col-md-3">
            <input type="text" name="participants[${count}][fonction]"
                   class="form-control form-control-sm" placeholder="Fonction">
        </div>
        <div class="col-md-1 text-center">
            <div class="form-check">
                <input class="form-check-input" type="checkbox"
                       name="participants[${count}][present]" checked>
                <label class="form-check-label small">Présent</label>
            </div>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-outline-danger remove-participant">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
    count++;
});

document.addEventListener('click', function (e) {
    if (e.target.closest('.remove-participant')) {
        e.target.closest('.participant-row').remove();
    }
});
</script>
@endpush
