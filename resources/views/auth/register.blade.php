<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DCUS — Créer mon compte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a3a5c 0%, #0d6efd 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .logo-section {
            background: #1a3a5c;
            border-radius: 16px 16px 0 0;
            padding: 2rem;
            text-align: center;
            color: white;
        }
        .logo-section h4 {
            font-weight: 700;
            letter-spacing: 1px;
            margin-bottom: 0.25rem;
        }
        .logo-section p {
            font-size: 0.8rem;
            opacity: 0.8;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card login-card">
                    <div class="logo-section">
                        <img src="https://enseignementsuperieur.gouv.bj/dist/img/logo.png"
                             style="height:70px;object-fit:contain;margin-bottom:0.75rem;">
                        <h4>DCUS</h4>
                        <p>Direction de la Coopération Universitaire et Scientifique</p>
                        <small class="d-block mt-2 opacity-75">DCUS — Bénin</small>
                    </div>
                    <div class="card-body p-4">
                        <h5 class="text-center text-muted mb-2">Créer mon compte</h5>
                        <p class="text-center text-muted small mb-4">
                            Vous avez été invité(e) avec l'adresse <strong>{{ $invitation->email }}</strong>
                        </p>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-sm py-2">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('invitation.store') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Nom</label>
                                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                                           value="{{ old('nom') }}" required autofocus>
                                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Prénom</label>
                                    <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror"
                                           value="{{ old('prenom') }}" required>
                                    @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Service</label>
                                    <select name="service" class="form-select @error('service') is-invalid @enderror" required>
                                        <option value="" disabled {{ old('service') ? '' : 'selected' }}>Sélectionner un service</option>
                                        @foreach(\App\Models\User::$services as $code => $label)
                                            <option value="{{ $code }}" {{ old('service') === $code ? 'selected' : '' }}>
                                                {{ $code }} — {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Mot de passe</label>
                                    <input type="password" name="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Minimum 8 caractères" required>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Confirmer le mot de passe</label>
                                    <input type="password" name="password_confirmation" class="form-control"
                                           placeholder="Répéter le mot de passe" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mt-4">
                                <i class="bi bi-check2-circle me-2"></i>Créer mon compte
                            </button>
                        </form>
                    </div>
                </div>
                <p class="text-center text-white-50 mt-3 small">
                    © {{ date('Y') }} DCUS — Ministère de l'Enseignement Supérieur du Bénin
                </p>
            </div>
        </div>
    </div>
</body>
</html>
