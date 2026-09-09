@extends('layouts.app')

@section('title', 'Appréciation')
@section('page-title', 'Fiche d\'appréciation')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header py-3 d-flex align-items-center gap-2">
                <i class="bi bi-clipboard-check text-primary"></i>
                <span>{{ $accord->appreciation ? 'Modifier l\'appréciation' : 'Nouvelle appréciation' }} — <strong>{{ $accord->titre }}</strong></span>
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
                @endif

                <div class="alert alert-light border small mb-4">
                    Référence MESRS : <strong>{{ $accord->reference }}</strong> — Institution : <strong>{{ $accord->institution_partenaire }}</strong>
                </div>

                <form method="POST" action="{{ route('accords.apprecier.store', $accord) }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Origine <span class="text-danger">*</span></label>
                            <input type="text" name="origine"
                                   class="form-control @error('origine') is-invalid @enderror"
                                   value="{{ old('origine', $accord->appreciation?->origine) }}">
                            @error('origine')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Objet <span class="text-danger">*</span></label>
                            <input type="text" name="objet"
                                   class="form-control @error('objet') is-invalid @enderror"
                                   value="{{ old('objet', $accord->appreciation?->objet) }}">
                            @error('objet')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Avis <span class="text-danger">*</span></label>
                            <textarea name="avis" rows="4"
                                      class="form-control @error('avis') is-invalid @enderror">{{ old('avis', $accord->appreciation?->avis) }}</textarea>
                            @error('avis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Observations sur la forme</label>
                            <textarea name="observations_forme" rows="4"
                                      class="form-control">{{ old('observations_forme', $accord->appreciation?->observations_forme) }}</textarea>
                            <div class="form-text">Commencez une ligne par « - » pour en faire une puce ; indentez de 2 espaces pour un sous-point. Une ligne sans « - » reste un simple paragraphe.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Observations sur le fond</label>
                            <textarea name="observations_fond" rows="4"
                                      class="form-control">{{ old('observations_fond', $accord->appreciation?->observations_fond) }}</textarea>
                            <div class="form-text">Commencez une ligne par « - » pour en faire une puce ; indentez de 2 espaces pour un sous-point. Une ligne sans « - » reste un simple paragraphe.</div>
                        </div>
                    </div>

                    <div class="form-text mt-2">
                        L'enregistrement génère automatiquement la fiche d'appréciation au format Word (.docx).
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-2"></i>Enregistrer et générer la fiche
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
