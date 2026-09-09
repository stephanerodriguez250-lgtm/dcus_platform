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

                <div class="alert alert-light border small mb-4 d-flex justify-content-between align-items-center gap-3">
                    <span>Référence MESRS : <strong>{{ $accord->reference }}</strong> — Institution : <strong>{{ $accord->institution_partenaire }}</strong></span>
                    <button type="button" id="bouton-suggestion-ia" class="btn btn-sm btn-outline-primary text-nowrap"
                            data-url="{{ route('accords.apprecier.suggestion', $accord) }}">
                        <i class="bi bi-stars me-1"></i>Générer avec l'IA
                    </button>
                </div>

                <div id="erreur-suggestion-ia" class="alert alert-danger d-none"></div>

                <form method="POST" action="{{ route('accords.apprecier.store', $accord) }}" id="formulaire-appreciation">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Origine <span class="text-danger">*</span></label>
                            <input type="text" name="origine" id="champ-origine"
                                   class="form-control @error('origine') is-invalid @enderror"
                                   value="{{ old('origine', $accord->appreciation?->origine) }}">
                            @error('origine')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Objet <span class="text-danger">*</span></label>
                            <input type="text" name="objet" id="champ-objet"
                                   class="form-control @error('objet') is-invalid @enderror"
                                   value="{{ old('objet', $accord->appreciation?->objet) }}">
                            @error('objet')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Numéro d'avis <span class="text-danger">*</span></label>
                            <input type="text" name="avis" id="champ-avis" placeholder="Ex. 0053"
                                   class="form-control @error('avis') is-invalid @enderror"
                                   value="{{ old('avis', $accord->appreciation?->avis) }}">
                            <div class="form-text">Identifiant de la fiche, inscrit sur le document généré après « Avis N° » (ex. « Avis N° 0053 /MESRS/DCUS/{{ now()->format('Y') }} »).</div>
                            @error('avis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @php
                            $texteForme = old('observations_forme', $accord->appreciation?->observations_forme);
                            $texteFond = old('observations_fond', $accord->appreciation?->observations_fond);
                        @endphp

                        <div class="col-12">
                            <button type="button"
                                    class="btn btn-outline-secondary w-100 d-flex justify-content-between align-items-center"
                                    data-bs-toggle="collapse" data-bs-target="#bloc-observations-forme">
                                <span class="fw-semibold">Observations sur la forme</span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="collapse mt-2 {{ $texteForme ? 'show' : '' }}" id="bloc-observations-forme">
                                <textarea name="observations_forme" id="champ-observations-forme" rows="16"
                                          class="form-control">{{ $texteForme }}</textarea>
                                <div class="form-text">Commencez une ligne par « - » pour en faire une puce ; indentez de 2 espaces pour un sous-point. Une ligne sans « - » reste un simple paragraphe.</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="button"
                                    class="btn btn-outline-secondary w-100 d-flex justify-content-between align-items-center"
                                    data-bs-toggle="collapse" data-bs-target="#bloc-observations-fond">
                                <span class="fw-semibold">Observations sur le fond</span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="collapse mt-2 {{ $texteFond ? 'show' : '' }}" id="bloc-observations-fond">
                                <textarea name="observations_fond" id="champ-observations-fond" rows="16"
                                          class="form-control">{{ $texteFond }}</textarea>
                                <div class="form-text">Commencez une ligne par « - » pour en faire une puce ; indentez de 2 espaces pour un sous-point. Une ligne sans « - » reste un simple paragraphe.</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-text mt-2">
                        L'enregistrement génère automatiquement la fiche d'appréciation au format Word (.docx), ainsi que la Conclusion (formulation fixe, à partir de l'intitulé de l'accord — elle ne se saisit pas). Le bouton « Générer avec l'IA » ne fait que pré-remplir Origine/Objet/Observations — vous pouvez tout modifier avant d'enregistrer.
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

@push('scripts')
<script>
document.getElementById('bouton-suggestion-ia').addEventListener('click', function () {
    const bouton = this;
    const erreur = document.getElementById('erreur-suggestion-ia');
    const libelleInitial = bouton.innerHTML;

    erreur.classList.add('d-none');
    bouton.disabled = true;
    bouton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Génération en cours…';

    fetch(bouton.dataset.url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
        .then((reponse) => reponse.json().then((donnees) => ({ ok: reponse.ok, donnees })))
        .then(({ ok, donnees }) => {
            if (!ok) {
                throw new Error(donnees.error || "La génération IA a échoué.");
            }
            document.getElementById('champ-origine').value = donnees.origine || '';
            document.getElementById('champ-objet').value = donnees.objet || '';
            document.getElementById('champ-observations-forme').value = donnees.observations_forme || '';
            document.getElementById('champ-observations-fond').value = donnees.observations_fond || '';

            ['bloc-observations-forme', 'bloc-observations-fond'].forEach(function (id) {
                bootstrap.Collapse.getOrCreateInstance(document.getElementById(id)).show();
            });
        })
        .catch((e) => {
            erreur.textContent = e.message;
            erreur.classList.remove('d-none');
        })
        .finally(() => {
            bouton.disabled = false;
            bouton.innerHTML = libelleInitial;
        });
});
</script>
@endpush
@endsection
