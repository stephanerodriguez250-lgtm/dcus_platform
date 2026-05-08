@extends('layouts.app')

@section('title', 'Nouvel accord')
@section('page-title', 'Nouvel accord')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header py-3 d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-plus text-primary"></i>
                <span>Enregistrer un nouvel accord</span>
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

                <form method="POST" action="{{ route('accords.store') }}">
                    @csrf

                    <!-- Section 1 : Identification -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-info-circle me-2"></i>Identification de l'accord
                        </h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Intitulé de l'accord <span class="text-danger">*</span></label>
                                <input type="text" name="titre"
                                       class="form-control @error('titre') is-invalid @enderror"
                                       value="{{ old('titre') }}"
                                       placeholder="Ex: Accord de coopération en matière de recherche scientifique">
                                @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Pays partenaire <span class="text-danger">*</span></label>
                                <input type="text" name="pays_partenaire"
                                       class="form-control @error('pays_partenaire') is-invalid @enderror"
                                       value="{{ old('pays_partenaire') }}"
                                       placeholder="Ex: France, Canada, Maroc...">
                                @error('pays_partenaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Institution partenaire <span class="text-danger">*</span></label>
                                <input type="text" name="institution_partenaire"
                                       class="form-control @error('institution_partenaire') is-invalid @enderror"
                                       value="{{ old('institution_partenaire') }}"
                                       placeholder="Ex: Université Paris-Saclay, Ministère...">
                                @error('institution_partenaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Université béninoise bénéficiaire</label>
                                <input type="text" name="universite_beneficiaire"
                                       class="form-control"
                                       value="{{ old('universite_beneficiaire') }}"
                                       placeholder="Ex: UAC, UNSTIM, EPAC...">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Statut initial <span class="text-danger">*</span></label>
                                <select name="statut" class="form-select @error('statut') is-invalid @enderror">
                                    @foreach($statuts as $key => $label)
                                        <option value="{{ $key }}" {{ old('statut', 'identifie') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Description / Objectifs</label>
                                <textarea name="description" rows="4" class="form-control"
                                          placeholder="Décrivez les objectifs et le contenu de cet accord...">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2 : Dates -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-calendar3 me-2"></i>Dates clés
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date d'identification</label>
                                <input type="date" name="date_identification" class="form-control"
                                       value="{{ old('date_identification') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date de signature</label>
                                <input type="date" name="date_signature" class="form-control"
                                       value="{{ old('date_signature') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date d'expiration</label>
                                <input type="date" name="date_expiration" class="form-control"
                                       value="{{ old('date_expiration') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3 : Réunion d'origine -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-calendar-event me-2"></i>Réunion d'origine
                        </h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Lier à une réunion</label>
                                <select name="reunion_id" class="form-select">
                                    <option value="">— Aucune réunion liée —</option>
                                    @foreach($reunions as $reunion)
                                        <option value="{{ $reunion->id }}"
                                            {{ old('reunion_id', $reunion_id) == $reunion->id ? 'selected' : '' }}>
                                            {{ $reunion->date->format('d/m/Y') }} — {{ $reunion->titre }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Optionnel — indiquez la réunion lors de laquelle cet accord a été identifié.</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-2"></i>Enregistrer l'accord
                        </button>
                        <a href="{{ route('accords.index') }}" class="btn btn-outline-secondary px-4">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
