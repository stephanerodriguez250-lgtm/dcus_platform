@extends('layouts.app')

@section('title', 'Nouvel accord')
@section('page-title', 'Nouvel accord')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header py-3 d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-plus text-primary"></i>
                <span>Réception d'un accord</span>
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

                <form method="POST" action="{{ route('accords.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Intitulé de l'accord <span class="text-danger">*</span></label>
                            <input type="text" name="titre"
                                   class="form-control @error('titre') is-invalid @enderror"
                                   value="{{ old('titre') }}"
                                   placeholder="Ex: Accord-cadre de partenariat entre l'UAC et le Port Autonome de Cotonou">
                            @error('titre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Commencez par « Accord... » et citez les deux parties (ex: « Accord-cadre entre X et Y ») — ce texte est repris tel quel dans le titre de la fiche d'appréciation.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Université / Institution d'origine <span class="text-danger">*</span></label>
                            <input type="text" name="institution_origine"
                                   class="form-control @error('institution_origine') is-invalid @enderror"
                                   value="{{ old('institution_origine') }}"
                                   placeholder="Ex: UAC, UNSTIM...">
                            @error('institution_origine')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">L'université béninoise qui initie l'accord.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Université / Institution partenaire <span class="text-danger">*</span></label>
                            <input type="text" name="institution_partenaire"
                                   class="form-control @error('institution_partenaire') is-invalid @enderror"
                                   value="{{ old('institution_partenaire') }}"
                                   placeholder="Ex: Université Paris-Saclay...">
                            @error('institution_partenaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Référence MESRS <span class="text-danger">*</span></label>
                            <input type="text" name="reference"
                                   class="form-control @error('reference') is-invalid @enderror"
                                   value="{{ old('reference') }}"
                                   placeholder="Ex: MESRS-2026/0142">
                            @error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date d'arrivée</label>
                            <input type="date" name="date_arrivee" class="form-control" value="{{ old('date_arrivee') }}">
                            <div class="form-text">Laissez vide pour utiliser la date du jour.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Heure d'arrivée</label>
                            <input type="time" name="heure_arrivee" class="form-control" value="{{ old('heure_arrivee') }}">
                            <div class="form-text">Laissez vide pour utiliser l'heure actuelle.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Fichier de l'accord <span class="text-danger">*</span></label>
                            <input type="file" name="fichier"
                                   class="form-control @error('fichier') is-invalid @enderror" accept=".pdf,.doc,.docx">
                            @error('fichier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">PDF, DOC ou DOCX — 20 Mo maximum.</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
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
