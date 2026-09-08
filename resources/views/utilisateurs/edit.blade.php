@extends('layouts.app')

@section('title', 'Modifier l\'utilisateur')
@section('page-title', 'Modifier l\'utilisateur')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header py-3 d-flex align-items-center gap-2">
                <i class="bi bi-person-gear text-primary"></i>
                <span>Modifier : <strong>{{ $utilisateur->nom_complet }}</strong></span>
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('utilisateurs.update', $utilisateur) }}">
                    @csrf @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                                   value="{{ old('nom', $utilisateur->nom) }}">
                            @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror"
                                   value="{{ old('prenom', $utilisateur->prenom) }}">
                            @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $utilisateur->email) }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Rôle <span class="text-danger">*</span></label>
                            <select name="role" class="form-select">
                                <option value="agent"      {{ old('role', $utilisateur->role) === 'agent'      ? 'selected' : '' }}>Agent</option>
                                <option value="secretaire" {{ old('role', $utilisateur->role) === 'secretaire' ? 'selected' : '' }}>Secrétaire</option>
                                <option value="admin"      {{ old('role', $utilisateur->role) === 'admin'      ? 'selected' : '' }}>Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Service</label>
                            <select name="service" class="form-select @error('service') is-invalid @enderror">
                                <option value="">— Aucun —</option>
                                @foreach(\App\Models\User::$services as $code => $label)
                                    <option value="{{ $code }}" {{ old('service', $utilisateur->service) === $code ? 'selected' : '' }}>
                                        {{ $code }} — {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Poste / Fonction</label>
                            <input type="text" name="poste" class="form-control"
                                   value="{{ old('poste', $utilisateur->poste) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Téléphone</label>
                            <input type="text" name="telephone" class="form-control"
                                   value="{{ old('telephone', $utilisateur->telephone) }}">
                        </div>

                        <div class="col-12">
                            <hr>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-lock me-1"></i>
                                Laissez vide pour conserver le mot de passe actuel.
                            </p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nouveau mot de passe</label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Minimum 8 caractères">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Confirmer le mot de passe</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-2"></i>Enregistrer
                        </button>
                        <a href="{{ route('utilisateurs.index') }}" class="btn btn-outline-secondary px-4">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
