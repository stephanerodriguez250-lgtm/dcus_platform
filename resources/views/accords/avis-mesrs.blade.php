@extends('layouts.app')

@section('title', 'Avis MESRS')
@section('page-title', 'Avis MESRS')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header py-3 d-flex align-items-center gap-2">
                <i class="bi bi-bank text-primary"></i>
                <span>Avis du MESRS — <strong>{{ $accord->titre }}</strong></span>
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
                    Chargez la version scannée de la fiche d'appréciation du ministère. L'IA lit ses
                    observations sur la forme et sur le fond et les fusionne avec celles déjà
                    rédigées par la DCUS ci-dessous — relisez attentivement le résultat avant de
                    valider, aucun point ne doit être perdu et les points répétés entre les deux
                    fiches ne doivent apparaître qu'une seule fois.
                </div>

                <div class="row g-3 align-items-end mb-4">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Fiche scannée du ministère</label>
                        <input type="file" id="champ-fichier-ministere" class="form-control" accept=".pdf,.png,.jpg,.jpeg">
                    </div>
                    <div class="col-md-4">
                        <button type="button" id="bouton-fusion-ia" class="btn btn-primary w-100"
                                data-url="{{ route('accords.avis-mesrs.suggestion', $accord) }}">
                            <i class="bi bi-stars me-1"></i>Analyser avec l'IA
                        </button>
                    </div>
                </div>

                <div id="erreur-fusion-ia" class="alert alert-danger d-none"></div>
                <div id="fichier-analyse" class="alert alert-success d-none small"></div>

                <form method="POST" action="{{ route('accords.avis-mesrs.store', $accord) }}" id="formulaire-avis-mesrs">
                    @csrf
                    <input type="hidden" name="chemin_fiche_ministere" id="champ-chemin-fiche-ministere"
                           value="{{ old('chemin_fiche_ministere', $accord->appreciation->chemin_fiche_ministere) }}">
                    <input type="hidden" name="nom_fiche_ministere" id="champ-nom-fiche-ministere"
                           value="{{ old('nom_fiche_ministere', $accord->appreciation->nom_fiche_ministere) }}">

                    <div class="row g-3">
                        @php
                            $texteForme = old('observations_forme', $accord->appreciation->observations_forme);
                            $texteFond = old('observations_fond', $accord->appreciation->observations_fond);
                        @endphp

                        <div class="col-12">
                            <label class="form-label fw-semibold">Observations sur la forme (fusionnées)</label>
                            <textarea name="observations_forme" id="champ-observations-forme" rows="14"
                                      class="form-control">{{ $texteForme }}</textarea>
                            <div class="form-text">Commencez une ligne par « - » pour en faire une puce ; indentez de 2 espaces pour un sous-point.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Observations sur le fond (fusionnées)</label>
                            <textarea name="observations_fond" id="champ-observations-fond" rows="14"
                                      class="form-control">{{ $texteFond }}</textarea>
                            <div class="form-text">Commencez une ligne par « - » pour en faire une puce ; indentez de 2 espaces pour un sous-point.</div>
                        </div>
                    </div>

                    <div class="form-text mt-2">
                        La validation régénère la fiche d'appréciation (.docx) avec ces observations fusionnées et fait passer l'accord à l'étape « Avis MESRS ».
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-2"></i>Valider l'avis MESRS
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
document.getElementById('bouton-fusion-ia').addEventListener('click', function () {
    const bouton = this;
    const erreur = document.getElementById('erreur-fusion-ia');
    const succes = document.getElementById('fichier-analyse');
    const fichier = document.getElementById('champ-fichier-ministere').files[0];
    const libelleInitial = bouton.innerHTML;

    erreur.classList.add('d-none');
    succes.classList.add('d-none');

    if (!fichier) {
        erreur.textContent = "Sélectionnez d'abord un fichier à analyser.";
        erreur.classList.remove('d-none');
        return;
    }

    const donneesFormulaire = new FormData();
    donneesFormulaire.append('fichier_ministere', fichier);

    bouton.disabled = true;
    bouton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Analyse en cours…';

    fetch(bouton.dataset.url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: donneesFormulaire,
    })
        .then((reponse) => reponse.json().then((donnees) => ({ ok: reponse.ok, donnees })))
        .then(({ ok, donnees }) => {
            if (!ok) {
                throw new Error(donnees.error || "La fusion IA a échoué.");
            }
            document.getElementById('champ-chemin-fiche-ministere').value = donnees.chemin_fiche_ministere || '';
            document.getElementById('champ-nom-fiche-ministere').value = donnees.nom_fiche_ministere || '';
            document.getElementById('champ-observations-forme').value = donnees.observations_forme || '';
            document.getElementById('champ-observations-fond').value = donnees.observations_fond || '';
            succes.textContent = 'Fichier analysé : ' + donnees.nom_fiche_ministere + '. Relisez le résultat ci-dessous avant de valider.';
            succes.classList.remove('d-none');
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
