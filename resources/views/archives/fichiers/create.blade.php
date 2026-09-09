@extends('layouts.app')

@section('title', 'Ajouter un fichier')
@section('page-title', 'Ajouter un fichier')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3 d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-plus text-primary"></i>
                <span>Ajouter un fichier{{ $dossier ? ' dans « '.$dossier->nom.' »' : '' }}</span>
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

                <form method="POST" action="{{ route('archives.fichiers.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if($dossier)
                    <input type="hidden" name="dossier_id" value="{{ $dossier->id }}">
                    @endif

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Intitulé <span class="text-danger">*</span></label>
                            <input type="text" name="intitule" class="form-control @error('intitule') is-invalid @enderror"
                                   value="{{ old('intitule') }}" placeholder="Ex: Convention de partenariat UAC">
                            @error('intitule')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Numéro</label>
                            <input type="text" name="numero" class="form-control @error('numero') is-invalid @enderror"
                                   value="{{ old('numero') }}" placeholder="Ex: DCUS-2026-014">
                            @error('numero')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date et heure</label>
                            <input type="text" class="form-control" value="Enregistrée automatiquement" disabled>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" rows="4"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Détails sur ce fichier...">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Fichier <span class="text-danger">*</span></label>
                            <input type="file" name="fichier" class="form-control @error('fichier') is-invalid @enderror">
                            <div class="form-text">PDF, Word, Excel ou image — 100 Mo maximum.</div>
                            @error('fichier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-2"></i>Enregistrer
                        </button>
                        <a href="{{ route('archives.index', $dossier ? ['dossier' => $dossier] : []) }}" class="btn btn-outline-secondary px-4">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
