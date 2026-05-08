@extends('layouts.app')

@section('title', 'CODIR Interne')
@section('page-title', 'CODIR Interne')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1 fw-bold text-dark">Comité de Direction (CODIR)</h5>
        <p class="text-muted small mb-0">Réunions internes et décisions de la DCUS</p>
    </div>
    @if(auth()->user()->canManage())
    <a href="{{ route('codirs.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Nouveau CODIR
    </a>
    @endif
</div>

<!-- Statistiques rapides -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="stat-card" style="background:linear-gradient(135deg,#1a3a5c,#2563eb);">
            <div class="stat-icon"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-number">{{ \App\Models\Codir::count() }}</div>
                <div class="stat-label">CODIR enregistrés</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card" style="background:linear-gradient(135deg,#16a34a,#4ade80);">
            <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            <div>
                <div class="stat-number">{{ \App\Models\Codir::where('statut','tenu')->count() }}</div>
                <div class="stat-label">CODIR tenus</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card" style="background:linear-gradient(135deg,#b45309,#f59e0b);">
            <div class="stat-icon"><i class="bi bi-calendar-event"></i></div>
            <div>
                <div class="stat-number">{{ \App\Models\Codir::where('statut','planifie')->count() }}</div>
                <div class="stat-label">CODIR planifiés</div>
            </div>
        </div>
    </div>
</div>

<!-- Liste des CODIR -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Objet du CODIR</th>
                        <th>Date</th>
                        <th>Horaires</th>
                        <th>Participants</th>
                        <th>Rapports</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($codirs as $codir)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold">{{ $codir->objet }}</div>
                            <div class="text-muted small">
                                <i class="bi bi-geo-alt me-1"></i>{{ $codir->lieu }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $codir->date->locale('fr')->translatedFormat('d M Y') }}</div>
                            @if($codir->prochaine_reunion)
                            <div class="text-muted small">
                                <i class="bi bi-arrow-right me-1"></i>{{ $codir->prochaine_reunion->format('d/m/Y') }}
                            </div>
                            @endif
                        </td>
                        <td>
                            @if($codir->heure_debut)
                            <span class="small">{{ $codir->heure_debut }}</span>
                            @if($codir->heure_fin)
                            <span class="text-muted small"> — {{ $codir->heure_fin }}</span>
                            @endif
                            @else
                            <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-people me-1"></i>{{ $codir->participants->count() }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $codir->rapports->count() > 0 ? 'bg-success' : 'bg-light text-dark border' }}">
                                <i class="bi bi-file-earmark me-1"></i>{{ $codir->rapports->count() }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $codir->statut_color }}">
                                {{ $codir->statut_label }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('codirs.show', $codir) }}"
                               class="btn btn-sm btn-outline-primary me-1">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()->canManage())
                            <a href="{{ route('codirs.edit', $codir) }}"
                               class="btn btn-sm btn-outline-secondary me-1">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('codirs.destroy', $codir) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Supprimer ce CODIR ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-people fs-2 d-block mb-2"></i>
                            Aucun CODIR enregistré
                            @if(auth()->user()->canManage())
                            <div class="mt-2">
                                <a href="{{ route('codirs.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus me-1"></i>Créer le premier CODIR
                                </a>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($codirs->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center py-3">
        <span class="text-muted small">{{ $codirs->total() }} CODIR au total</span>
        {{ $codirs->links() }}
    </div>
    @endif
</div>
@endsection
