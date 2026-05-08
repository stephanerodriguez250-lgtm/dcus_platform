@extends('layouts.app')

@section('title', 'Modifier l\'accord')
@section('page-title', 'Modifier l\'accord')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
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

                <form method="POST" action="{{ route('accords.update', $accord) }}">
                    @csrf @method('PUT')

                    <div class="mb-4">
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-info-circle me-2"></i>Identification
                        </h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Intitulé <span class="text-danger">*</span></label>
                                <input type="text" name="titre"
                                       class="form-control @error('titre') is-invalid @enderror"
                                       value="{{ old('titre', $accord->titre) }}">
                                @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Pays partenaire <span class="text-danger">*</span></label>
                                <input type="text" name="pays_partenaire"
                                       class="form-control @error('pays_partenaire') is-invalid @enderror"
                                       value="{{ old('pays_partenaire', $accord->pays_partenaire) }}">
                                @error('pays_partenaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Institution partenaire <span class="text-danger">*</span></label>
                                <input type="text" name="institution_partenaire"
                                       class="form-control @error('institution_partenaire') is-invalid @enderror"
                                       value="{{ old('institution_partenaire', $accord->institution_partenaire) }}">
                                @error('institution_partenaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Université béninoise bénéficiaire</label>
                                <input type="text" name="universite_beneficiaire" class="form-control"
                                       value="{{ old('universite_beneficiaire', $accord->universite_beneficiaire) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Lier à une réunion</label>
                                <select name="reunion_id" class="form-select">
                                    <option value="">— Aucune réunion liée —</option>
                                    @foreach($reunions as $reunion)
                                        <option value="{{ $reunion->id }}"
                                            {{ old('reunion_id', $accord->reunion_id) == $reunion->id ? 'selected' : '' }}>
                                            {{ $reunion->date->format('d/m/Y') }} — {{ $reunion->titre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Description / Objectifs</label>
                                <textarea name="description" rows="4" class="form-control">{{ old('description', $accord->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-calendar3 me-2"></i>Dates clés
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date d'identification</label>
                                <input type="date" name="date_identification" class="form-control"
                                       value="{{ old('date_identification', $accord->date_identification?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date de signature</label>
                                <input type="date" name="date_signature" class="form-control"
                                       value="{{ old('date_signature', $accord->date_signature?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date d'expiration</label>
                                <input type="date" name="date_expiration" class="form-control"
                                       value="{{ old('date_expiration', $accord->date_expiration?->format('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning d-flex gap-2 align-items-start">
                        <i class="bi bi-exclamation-triangle mt-1"></i>
                        <div>
                            <strong>Note :</strong> Pour changer le statut de l'accord, utilisez le bouton
                            <em>"Mettre à jour le statut"</em> sur la page de détail — cela conserve l'historique complet.
                        </div>
                    </div>

                    <div class="d-flex gap-2">
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
