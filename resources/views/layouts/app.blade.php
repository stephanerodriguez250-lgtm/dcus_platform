<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DCUS') — Plateforme de Gestion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --topbar-height: 60px;
            --primary-dark: #1a3a5c;
        }
        body { background: #f0f4f8; font-family: 'Segoe UI', sans-serif; }

        /* ── Sidebar ── */
        .sidebar {
            position: fixed; top: 0; left: 0;
            height: 100vh; width: var(--sidebar-width);
            background: var(--primary-dark); color: white;
            z-index: 1040; overflow-y: auto;
            transition: transform 0.3s ease;
        }
        .sidebar-brand { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-brand h5 { font-weight: 700; margin: 0; letter-spacing: 0.5px; }
        .sidebar-brand small { opacity: 0.65; font-size: 0.72rem; }
        .nav-section-title {
            font-size: 0.65rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 1.5px; color: rgba(255,255,255,0.4);
            padding: 1.25rem 1.5rem 0.5rem;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.75); padding: 0.65rem 1.5rem;
            display: flex; align-items: center; gap: 0.75rem;
            font-size: 0.9rem; transition: all 0.2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.12); color: white;
        }
        .sidebar .nav-link i { font-size: 1.1rem; width: 20px; }
        .sidebar-footer {
            position: sticky; bottom: 0; width: 100%;
            background: var(--primary-dark); padding: 0.75rem 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        /* ── Overlay mobile ── */
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.5); z-index: 1039;
        }
        .sidebar-overlay.show { display: block; }

        /* ── Main ── */
        .main-wrapper { margin-left: var(--sidebar-width); min-height: 100vh; }

        /* ── Topbar ── */
        .topbar {
            background: white; height: var(--topbar-height);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1.5rem;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            position: sticky; top: 0; z-index: 100;
        }
        .topbar .page-title { font-weight: 600; color: #1a3a5c; margin: 0; font-size: 1.05rem; }
        .btn-sidebar-toggle { display: none; }

        /* ── Cards ── */
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .card-header {
            background: white; border-bottom: 1px solid #eef2f7;
            font-weight: 600; border-radius: 12px 12px 0 0 !important;
        }

        /* ── Stat cards ── */
        .stat-card {
            border-radius: 12px; padding: 1.25rem 1.5rem;
            color: white; display: flex; align-items: center; gap: 1rem;
        }
        .stat-card .stat-icon { font-size: 2.2rem; opacity: 0.85; }
        .stat-card .stat-number { font-size: 2rem; font-weight: 700; line-height: 1; }
        .stat-card .stat-label { font-size: 0.82rem; opacity: 0.85; }

        /* ── Content ── */
        .content-area { padding: 1.5rem; }

        /* ── Tables ── */
        .table th {
            font-size: 0.8rem; text-transform: uppercase;
            letter-spacing: 0.5px; color: #6c757d; border-top: none;
        }
        .table td { vertical-align: middle; }
        .badge { font-size: 0.75rem; font-weight: 500; padding: 0.4em 0.75em; }

        /* ── Responsive ── */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .btn-sidebar-toggle { display: inline-flex; }
        }
        @media (max-width: 576px) {
            .content-area { padding: 1rem; }
            .stat-card { padding: 1rem; }
            .stat-card .stat-number { font-size: 1.5rem; }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- Overlay mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h5>Direction de la Coopération Universitaire et Scienctifique(DCUS) </h5>
        <small>Gestion des accords et réunion de la DCUS</small>
    </div>

    <div class="nav-section-title">Principal</div>
    <nav class="nav flex-column">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Tableau de bord
        </a>
    </nav>

    <div class="nav-section-title">Gestion</div>
    <nav class="nav flex-column">
        <a href="{{ route('codirs.index') }}" class="nav-link {{ request()->routeIs('codirs.*') ? 'active' : '' }}">
            <i class="bi bi-people-fill"></i> CODIR Interne
        </a>
        <a href="{{ route('reunions.index') }}" class="nav-link {{ request()->routeIs('reunions.*') ? 'active' : '' }}">
            <i class="bi bi-calendar-event"></i> Réunions
        </a>
        <a href="{{ route('decisions.index') }}" class="nav-link {{ request()->routeIs('decisions.*') ? 'active' : '' }}">
    <i class="bi bi-clipboard-check"></i> Suivi décisions
       </a>
        <a href="{{ route('accords.index') }}" class="nav-link {{ request()->routeIs('accords.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i> Accords
        </a>
        <a href="{{ route('archives.index') }}" class="nav-link {{ request()->routeIs('archives.*') ? 'active' : '' }}">
            <i class="bi bi-folder"></i> Archives
        </a>
    </nav>

    @if(auth()->user()->isAdmin())
    <div class="nav-section-title">Administration</div>
    <nav class="nav flex-column">
        <a href="{{ route('utilisateurs.index') }}" class="nav-link {{ request()->routeIs('utilisateurs.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Utilisateurs
        </a>
    </nav>
    @endif

    <div class="nav-section-title">Mon compte</div>
    <nav class="nav flex-column">
        <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <i class="bi bi-person-circle"></i> Mon profil
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                 style="width:34px;height:34px;font-size:0.85rem;">
                {{ strtoupper(substr(auth()->user()->prenom,0,1)) }}{{ strtoupper(substr(auth()->user()->nom,0,1)) }}
            </div>
            <div style="overflow:hidden;">
                <div class="text-white fw-semibold" style="font-size:0.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ auth()->user()->nom_complet }}
                </div>
                <div style="font-size:0.7rem;opacity:0.6;">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-light w-100">
                <i class="bi bi-box-arrow-left me-1"></i>Déconnexion
            </button>
        </form>
    </div>
</div>

<!-- Main wrapper -->
<div class="main-wrapper">
    <!-- Topbar -->
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-outline-secondary btn-sidebar-toggle" onclick="toggleSidebar()">
                <i class="bi bi-list fs-5"></i>
            </button>
            <h6 class="page-title">@yield('page-title', 'Tableau de bord')</h6>
        </div>
        <span class="text-muted small d-none d-md-block">
            {{ now()->locale('fr')->translatedFormat('l d F Y') }}
        </span>
    </div>

    <!-- Alerts -->
    <div class="content-area pb-0">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-circle-fill"></i>
            {{ session('error') }}
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif
    </div>

    <!-- Content -->
    <div class="content-area">
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('show');
    }
</script>
@stack('scripts')
</body>
</html>
