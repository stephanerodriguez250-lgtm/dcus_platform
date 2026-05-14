<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DCUS — Connexion</title>
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
        .logo-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card login-card">
                    <div class="logo-section">
    <img src="https://enseignementsuperieur.gouv.bj/dist/img/logo.png"
                         style="height:70px;object-fit:contain;margin-bottom:0.75rem;">
                        <h4>DCUS</h4>
                        <p>Direction de la Coopération Universitaire et Scientifique</p>
                        <small class="d-block mt-2 opacity-75">DCUS — Bénin</small>
                    </div>
                    <div class="card-body p-4">
                        <h5 class="text-center text-muted mb-4">Connexion</h5>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-sm py-2">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control"
                                           value="{{ old('email') }}" placeholder="votre@email.bj" required autofocus>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" name="password" class="form-control"
                                           placeholder="••••••••" required>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                    <label class="form-check-label text-muted small" for="remember">Se souvenir de moi</label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
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
