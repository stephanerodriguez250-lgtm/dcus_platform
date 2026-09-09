@extends('layouts.app')

@section('title', 'Modifier l\'accord')
@section('page-title', 'Modifier l\'accord')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3 d-flex align-items-center gap-2">
                <i class="bi bi-pencil-square text-primary"></i>
                <span>Modifier : <strong>{{ $accord->titre }}</strong></span>
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('accords.update', $accord) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Intitulé <span class="text-danger">*</span></label>
                            <input type="text" name="titre"
                                   class="form-control @error('titre') is-invalid @enderror"
                                   value="{{ old('titre', $accord->titre) }}">
                            @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Université / Institution partenaire <span class="text-danger">*</span></label>
                            <input type="text" name="institution_partenaire"
                                   class="form-control @error('institution_partenaire') is-invalid @enderror"
                                   value="{{ old('institution_partenaire', $accord->institution_partenaire) }}">
                            @error('institution_partenaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Référence MESRS <span class="text-danger">*</span></label>
                            <input type="text" name="reference"
                                   class="form-control @error('reference') is-invalid @enderror"
                                   value="{{ old('reference', $accord->reference) }}">
                            @error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date d'arrivée <span class="text-danger">*</span></label>
                            <input type="date" name="date_arrivee"
                                   class="form-control @error('date_arrivee') is-invalid @enderror"
                                   value="{{ old('date_arrivee', $accord->date_arrivee?->format('Y-m-d')) }}">
                            @error('date_arrivee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Heure d'arrivée <span class="text-danger">*</span></label>
                            <input type="time" name="heure_arrivee"
                                   class="form-control @error('heure_arrivee') is-invalid @enderror"
                                   value="{{ old('heure_arrivee', $accord->heure_arrivee) }}">
                            @error('heure_arrivee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Fichier de l'accord</label>
                            <input type="file" name="fichier"
                                   class="form-control @error('fichier') is-invalid @enderror" accept=".pdf,.doc,.docx">
                            @error('fichier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">
                                Fichier actuel : {{ $accord->nom_fichier ?? '—' }}. Laissez vide pour le conserver.
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-2"></i>Enregistrer les modifications
                        </button>
                        <a href="{{ route('accords.show', $accord) }}" class="btn btn-outline-secondary px-4">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
