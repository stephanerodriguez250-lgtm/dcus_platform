@extends('layouts.app')

@section('title', 'Nouvelle réunion')
@section('page-title', 'Nouvelle réunion')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3 d-flex align-items-center gap-2">
                <i class="bi bi-calendar-plus text-primary"></i>
                <span>Enregistrer une nouvelle réunion</span>
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('reunions.store') }}">
                    @csrf

                    <div class="row g-3">
                        <!-- Titre -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Intitulé de la réunion <span class="text-danger">*</span></label>
                            <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror"
                                   value="{{ old('titre') }}"
                                   placeholder="Ex: Réunion de définition des besoins — UAC">
                            @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Date & Heure -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                   value="{{ old('date') }}">
                            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Heure</label>
                            <input type="time" name="heure" class="form-control @error('heure') is-invalid @enderror"
                                   value="{{ old('heure') }}">
                            @error('heure')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Lieu -->
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Lieu <span class="text-danger">*</span></label>
                            <input type="text" name="lieu" class="form-control @error('lieu') is-invalid @enderror"
                                   value="{{ old('lieu') }}"
                                   placeholder="Ex: Salle de conférence du MESRS, Cotonou">
                            @error('lieu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Statut -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
                            <select name="statut" class="form-select @error('statut') is-invalid @enderror">
                                @foreach($statuts as $key => $label)
                                    <option value="{{ $key }}" {{ old('statut', 'planifiee') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Convocateur -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Convocateur</label>
                            <input type="text" name="convocateur" class="form-control @error('convocateur') is-invalid @enderror"
                                   value="{{ old('convocateur', 'Ministère de l\'Enseignement Supérieur et de la Recherche Scientifique') }}"
                                   placeholder="Ex: Ministère de l'Enseignement Supérieur">
                            @error('convocateur')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Ordre du jour -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Ordre du jour <span class="text-danger">*</span></label>
                            <textarea name="ordre_du_jour" rows="5"
                                      class="form-control @error('ordre_du_jour') is-invalid @enderror"
                                      placeholder="Décrivez les points à l'ordre du jour...">{{ old('ordre_du_jour') }}</textarea>
                            @error('ordre_du_jour')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <!-- Alerte mail -->
                    <div class="alert alert-info mt-4 mb-0 d-flex align-items-start gap-2">
                        <i class="bi bi-envelope-check fs-5 mt-1"></i>
                        <div>
                            <strong>Notification automatique</strong><br>
                            <small>Un email contenant la date, le lieu et l'ordre du jour sera automatiquement envoyé à tous les agents DCUS lors de l'enregistrement.</small>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-2"></i>Enregistrer et notifier
                        </button>
                        <a href="{{ route('reunions.index') }}" class="btn btn-outline-secondary px-4">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
