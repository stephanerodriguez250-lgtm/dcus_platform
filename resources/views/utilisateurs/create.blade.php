@extends('layouts.app')

@section('title', 'Inviter un utilisateur')
@section('page-title', 'Inviter un utilisateur')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header py-3 d-flex align-items-center gap-2">
                <i class="bi bi-envelope-plus text-primary"></i>
                <span>Inviter un nouvel utilisateur</span>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-4">
                    L'utilisateur recevra un email avec un lien pour créer son compte
                    (nom, prénom, service et mot de passe). Le lien est valable 7 jours.
                </p>

                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('utilisateurs.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="agent@dcus.bj">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Rôle <span class="text-danger">*</span></label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror">
                                <option value="agent"      {{ old('role') === 'agent'      ? 'selected' : '' }}>Agent</option>
                                <option value="secretaire" {{ old('role') === 'secretaire' ? 'selected' : '' }}>Secrétaire</option>
                                <option value="admin"      {{ old('role') === 'admin'      ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send me-2"></i>Envoyer l'invitation
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
