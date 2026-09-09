@extends('layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@push('styles')
<style>
    .activity-dot { width:10px;height:10px;border-radius:50%;flex-shrink:0;margin-top:5px; }
</style>
@endpush

@section('content')

<!-- Stat cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#1a3a5c,#2563eb);">
            <div class="stat-icon"><i class="bi bi-calendar-event"></i></div>
            <div>
                <div class="stat-number">{{ $stats['reunions_total'] }}</div>
                <div class="stat-label">Réunions enregistrées</div>
                <div style="font-size:0.72rem;opacity:0.75;">{{ $stats['reunions_planifiees'] }} planifiée(s)</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#7c3aed,#a78bfa);">
            <div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
            <div>
                <div class="stat-number">{{ $stats['accords_total'] }}</div>
                <div class="stat-label">Accords au total</div>
                <div style="font-size:0.72rem;opacity:0.75;">{{ $stats['accords_signes'] }} signé(s)</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#b45309,#f59e0b);">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="stat-number">{{ $stats['accords_en_attente'] }}</div>
                <div class="stat-label">Accords en attente de signature</div>
                <div style="font-size:0.72rem;opacity:0.75;">reçu / apprécié / envoyé</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#0f766e,#14b8a6);">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-number">{{ $stats['users_total'] }}</div>
                <div class="stat-label">Agents actifs</div>
                <div style="font-size:0.72rem;opacity:0.75;">Équipe DCUS</div>
            </div>
        </div>
    </div>
</div>

<!-- Alerte accords expirants -->
@if($accords_expirants->count())
<div class="alert alert-warning d-flex align-items-start gap-3 mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-4 mt-1"></i>
    <div>
        <strong>Accords expirant dans les 60 prochains jours</strong>
        <div class="mt-2 d-flex flex-wrap gap-2">
            @foreach($accords_expirants as $acc)
            <a href="{{ route('accords.show', $acc) }}"
               class="badge bg-warning text-dark text-decoration-none px-3 py-2 fw-normal">
                {{ $acc->titre }} — expire le {{ $acc->date_expiration->format('d/m/Y') }}
            </a>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Notifications -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center py-3">
        <span>
            <i class="bi bi-bell text-primary me-2"></i>Notifications
            @if($notificationsNonLues > 0)
            <span class="badge bg-danger ms-1">{{ $notificationsNonLues }}</span>
            @endif
        </span>
        @if($notificationsNonLues > 0)
        <form method="POST" action="{{ route('notifications.tout-lire') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary">Tout marquer comme lu</button>
        </form>
        @endif
    </div>
    <div class="card-body p-0">
        @forelse($notifications as $notification)
        <form method="POST" action="{{ route('notifications.lire', $notification->id) }}">
            @csrf
            <button type="submit"
                    class="d-flex w-100 align-items-start gap-3 p-3 border-bottom text-start bg-transparent border-0 border-top-0 border-start-0 border-end-0 {{ $notification->read_at ? '' : 'bg-primary bg-opacity-10' }}">
                <i class="bi {{ $notification->data['icone'] ?? 'bi-bell' }} fs-5 text-primary mt-1"></i>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="small {{ $notification->read_at ? '' : 'fw-semibold' }}">{{ $notification->data['message'] ?? '' }}</div>
                    @if(! empty($notification->data['note']))
                    <div class="small text-muted fst-italic">« {{ $notification->data['note'] }} »</div>
                    @endif
                    <div class="text-muted" style="font-size:0.7rem;">{{ $notification->created_at->locale('fr')->diffForHumans() }}</div>
                </div>
                @unless($notification->read_at)
                <span class="activity-dot bg-primary flex-shrink-0"></span>
                @endunless
            </button>
        </form>
        @empty
        <div class="text-center text-muted py-4">
            <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>Aucune notification
        </div>
        @endforelse
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Graphique -->
    <div class="col-lg-6 mx-auto">
        <div class="card h-100">
            <div class="card-header py-3">
                <i class="bi bi-pie-chart text-primary me-2"></i>Accords par étape
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="accordsChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Prochaines réunions -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <span><i class="bi bi-calendar-event text-primary me-2"></i>Prochaines réunions</span>
                @if(auth()->user()->canManage())
                <a href="{{ route('reunions.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i></a>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse($prochaines_reunions as $reunion)
                <div class="d-flex align-items-start gap-3 p-3 border-bottom">
                    <div class="text-center bg-primary bg-opacity-10 rounded p-2" style="min-width:46px">
                        <div class="text-primary fw-bold" style="font-size:1.1rem">{{ $reunion->date->format('d') }}</div>
                        <div class="text-muted" style="font-size:0.65rem">{{ strtoupper($reunion->date->locale('fr')->translatedFormat('M')) }}</div>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <a href="{{ route('reunions.show', $reunion) }}"
                           class="fw-semibold text-decoration-none text-dark d-block text-truncate">{{ $reunion->titre }}</a>
                        <div class="text-muted small text-truncate"><i class="bi bi-geo-alt me-1"></i>{{ $reunion->lieu }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>Aucune réunion planifiée
                </div>
                @endforelse
            </div>
            <div class="card-footer text-center py-2">
                <a href="{{ route('reunions.index') }}" class="text-primary text-decoration-none small">Voir toutes →</a>
            </div>
        </div>
    </div>

    <!-- Accords récents -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <span><i class="bi bi-file-earmark-text text-primary me-2"></i>Accords récents</span>
                @if(auth()->user()->canManage())
                <a href="{{ route('accords.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i></a>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse($accords_recents as $accord)
                <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                    <div class="flex-grow-1 overflow-hidden">
                        <a href="{{ route('accords.show', $accord) }}"
                           class="fw-semibold text-decoration-none text-dark d-block text-truncate">{{ $accord->titre }}</a>
                        <div class="text-muted small"><i class="bi bi-building me-1"></i>{{ $accord->institution_partenaire }}</div>
                    </div>
                    <span class="badge bg-{{ $accord->etape_color }} flex-shrink-0">{{ $accord->etape_label }}</span>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-file-x fs-3 d-block mb-2"></i>Aucun accord
                </div>
                @endforelse
            </div>
            <div class="card-footer text-center py-2">
                <a href="{{ route('accords.index') }}" class="text-primary text-decoration-none small">Voir tous →</a>
            </div>
        </div>
    </div>

    <!-- Dernières activités -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header py-3">
                <i class="bi bi-activity text-primary me-2"></i>Dernières activités
            </div>
            <div class="card-body p-0">
                @forelse($dernieres_activites as $activite)
                <div class="d-flex gap-3 p-3 border-bottom">
                    <div class="activity-dot bg-primary mt-1"></div>
                    <div class="overflow-hidden">
                        <div class="small fw-semibold text-truncate">{{ $activite->accord->titre }}</div>
                        <div class="small text-muted">{{ $activite->evenement }}</div>
                        <div style="font-size:0.7rem;" class="text-muted">
                            {{ $activite->modificateur->prenom }} ·
                            {{ \Carbon\Carbon::parse($activite->date_modification)->locale('fr')->diffForHumans() }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-clock-history fs-3 d-block mb-2"></i>Aucune activité
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ctx = document.getElementById('accordsChart');
if (ctx) {
    const data = @json($accords_par_etape);
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(data),
            datasets: [{
                data: Object.values(data),
                backgroundColor: ['#94a3b8','#f59e0b','#3b82f6','#6366f1','#22c55e','#ef4444'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } }
            },
            cutout: '65%'
        }
    });
}
</script>
@endpush
