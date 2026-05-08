@extends('layouts.app')

@section('title', 'Modifier CODIR')
@section('page-title', 'Modifier le CODIR')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-9">
<div class="card">
    <div class="card-header py-3 d-flex align-items-center gap-2">
        <i class="bi bi-pencil-square text-primary"></i>
        <span>Modifier : <strong>{{ $codir->objet }}</strong></span>
    </div>
    <div class="card-body p-4">

        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form method="POST" action="{{ route('codirs.update', $codir) }}">
        @csrf @method('PUT')

        <!-- Informations générales -->
        <div class="mb-4">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                <i class="bi bi-info-circle me-2"></i>Informations générales
            </h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Objet <span class="text-danger">*</span></label>
                    <input type="text" name="objet" class="form-control"
                           value="{{ old('objet', $codir->objet) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control"
                           value="{{ old('date', $codir->date->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Heure de début</label>
                    <input type="time" name="heure_debut" class="form-control"
                           value="{{ old('heure_debut', $codir->heure_debut) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Heure de fin</label>
                    <input type="time" name="heure_fin" class="form-control"
                           value="{{ old('heure_fin', $codir->heure_fin) }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Lieu</label>
                    <input type="text" name="lieu" class="form-control"
                           value="{{ old('lieu', $codir->lieu) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Statut</label>
                    <select name="statut" class="form-select">
                        @foreach(\App\Models\Codir::$statuts as $key => $label)
                        <option value="{{ $key }}" {{ old('statut',$codir->statut)==$key?'selected':'' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Présidente</label>
                    <input type="text" name="presidente" class="form-control"
                           value="{{ old('presidente', $codir->presidente) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Rapporteur</label>
                    <input type="text" name="rapporteur" class="form-control"
                           value="{{ old('rapporteur', $codir->rapporteur) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Prochaine réunion</label>
                    <input type="date" name="prochaine_reunion" class="form-control"
                           value="{{ old('prochaine_reunion', $codir->prochaine_reunion?->format('Y-m-d')) }}">
                </div>
            </div>
        </div>

        <!-- Compte rendu -->
        <div class="mb-4">
            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                <i class="bi bi-journal-text me-2"></i>Compte rendu
            </h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Synthèse des discussions</label>
                    <textarea name="synthese" rows="5" class="form-control"
                              placeholder="Résumé des points discutés lors du CODIR...">{{ old('synthese', $codir->synthese) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Décisions prises</label>
                    <textarea name="decisions" rows="4" class="form-control"
                              placeholder="Listez les décisions prises...">{{ old('decisions', $codir->decisions) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Divers</label>
                    <textarea name="divers" rows="3" class="form-control"
                              placeholder="Points divers...">{{ old('divers', $codir->divers) }}</textarea>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-check-lg me-2"></i>Enregistrer
            </button>
            <a href="{{ route('codirs.show', $codir) }}" class="btn btn-outline-secondary px-4">
                Annuler
            </a>
        </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
