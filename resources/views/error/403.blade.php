<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès refusé — DCUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; display:flex; align-items:center; min-height:100vh; }
    </style>
</head>
<body>
    <div class="container text-center py-5">
        <div style="font-size:4rem;">🔒</div>
        <h1 class="fw-bold text-danger mt-3">403</h1>
        <h4 class="text-dark">Accès non autorisé</h4>
        <p class="text-muted">Vous n'avez pas les droits nécessaires pour accéder à cette page.</p>
        <a href="{{ url('/') }}" class="btn btn-primary mt-2">
            Retourner au tableau de bord
        </a>
    </div>
</body>
</html>
