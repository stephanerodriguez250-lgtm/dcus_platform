@extends('layouts.app')

@section('title', 'Modifier la réunion')
@section('page-title', 'Modifier la réunion')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3 d-flex align-items-center gap-2">
                <i class="bi bi-pencil-square text-primary"></i>
                <span>Modifier : <strong>{{ $reunion->titre }}</strong></span>
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

                <form method="POST" action="{{ route('reunions.update', $reunion) }}">
                    @csrf @method('PUT')

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Intitulé <span class="text-danger">*</span></label>
                            <input type="text" name="titre" class="form-control @error('titre') is-invalid @enderror"
                                   value="{{ old('titre', $reunion->titre) }}">
                            @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                   value="{{ old('date', $reunion->date->format('Y-m-d')) }}">
                            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Heure</label>
                            <input type="time" name="heure" class="form-control"
                                   value="{{ old('heure', $reunion->heure) }}">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Lieu <span class="text-danger">*</span></label>
                            <input type="text" name="lieu" class="form-control @error('lieu') is-invalid @enderror"
                                   value="{{ old('lieu', $reunion->lieu) }}">
                            @error('lieu')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
                            <select name="statut" class="form-select">
                                @foreach($statuts as $key => $label)
                                    <option value="{{ $key }}" {{ old('statut', $reunion->statut) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Convocateur</label>
                            <input type="text" name="convocateur" class="form-control"
                                   value="{{ old('convocateur', $reunion->convocateur) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Ordre du jour <span class="text-danger">*</span></label>
                            <textarea name="ordre_du_jour" rows="4"
                                      class="form-control @error('ordre_du_jour') is-invalid @enderror">{{ old('ordre_du_jour', $reunion->ordre_du_jour) }}</textarea>
                            @error('ordre_du_jour')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Compte rendu</label>
                            <textarea name="compte_rendu" rows="5"
                                      class="form-control"
                                      placeholder="Saisir le compte rendu après la réunion...">{{ old('compte_rendu', $reunion->compte_rendu) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-2"></i>Enregistrer les modifications
                        </button>
                        <a href="{{ route('reunions.show', $reunion) }}" class="btn btn-outline-secondary px-4">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
