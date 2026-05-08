@extends('layouts.app')

@section('title', 'Mon profil')
@section('page-title', 'Mon profil')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">

        <!-- Carte identité -->
        <div class="card mb-4">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-bold mx-auto mb-3"
                     style="width:70px;height:70px;font-size:1.5rem;">
                    {{ strtoupper(substr($user->prenom,0,1)) }}{{ strtoupper(substr($user->nom,0,1)) }}
                </div>
                <h5 class="fw-bold mb-1">{{ $user->nom_complet }}</h5>
                <div class="text-muted small">{{ $user->email }}</div>
                <div class="mt-2">
                    @if($user->isAdmin())
                        <span class="badge" style="background:#1a3a5c;">Admin</span>
                    @elseif($user->isSecretaire())
                        <span class="badge" style="background:#7c3aed;">Secrétaire</span>
                    @else
                        <span class="badge bg-secondary">Agent</span>
                    @endif
                    @if($user->poste)
                        <span class="badge bg-light text-dark border ms-1">{{ $user->poste }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <div class="card">
            <div class="card-header py-3 d-flex align-items-center gap-2">
                <i class="bi bi-person-gear text-primary"></i>
                <span>Modifier mes informations</span>
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf @method('PUT')

                    <div class="mb-4">
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Informations personnelles</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nom</label>
                                <input type="text" name="nom" class="form-control"
                                       value="{{ old('nom', $user->nom) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Prénom</label>
                                <input type="text" name="prenom" class="form-control"
                                       value="{{ old('prenom', $user->prenom) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" class="form-control bg-light"
                                       value="{{ $user->email }}" disabled>
                                <div class="form-text">L'email ne peut être modifié que par l'admin.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Téléphone</label>
                                <input type="text" name="telephone" class="form-control"
                                       value="{{ old('telephone', $user->telephone) }}"
                                       placeholder="+229 ...">
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                            Changer le mot de passe
                            <small class="text-muted fw-normal">(optionnel)</small>
                        </h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Mot de passe actuel</label>
                                <input type="password" name="current_password"
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       placeholder="Saisissez votre mot de passe actuel">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nouveau mot de passe</label>
                                <input type="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Minimum 8 caractères">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Confirmer</label>
                                <input type="password" name="password_confirmation"
                                       class="form-control" placeholder="Répéter le nouveau mot de passe">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-2"></i>Enregistrer
                        </button>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
