@extends('layouts.app')

@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des Utilisateurs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1 fw-bold text-dark">Équipe DCUS</h5>
        <p class="text-muted small mb-0">Gérez les accès et les rôles des agents</p>
    </div>
    <a href="{{ route('utilisateurs.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-2"></i>Nouvel utilisateur
    </a>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">Rechercher</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Nom, prénom, email, poste..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Rôle</label>
                <select name="role" class="form-select">
                    <option value="">Tous les rôles</option>
                    <option value="admin"      {{ request('role') === 'admin'      ? 'selected' : '' }}>Admin</option>
                    <option value="secretaire" {{ request('role') === 'secretaire' ? 'selected' : '' }}>Secrétaire</option>
                    <option value="agent"      {{ request('role') === 'agent'      ? 'selected' : '' }}>Agent</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Tableau -->
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Agent</th>
                    <th>Poste</th>
                    <th>Rôle</th>
                    <th>Téléphone</th>
                    <th>Statut</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white"
                                 style="width:38px;height:38px;font-size:0.85rem;flex-shrink:0;
                                        background: {{ $user->isAdmin() ? '#1a3a5c' : ($user->isSecretaire() ? '#7c3aed' : '#0f766e') }}">
                                {{ strtoupper(substr($user->prenom, 0, 1)) }}{{ strtoupper(substr($user->nom, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $user->nom_complet }}</div>
                                <div class="text-muted small">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $user->poste ?? '—' }}</td>
                    <td>
                        @if($user->isAdmin())
                            <span class="badge" style="background:#1a3a5c">Admin</span>
                        @elseif($user->isSecretaire())
                            <span class="badge bg-purple" style="background:#7c3aed">Secrétaire</span>
                        @else
                            <span class="badge bg-secondary">Agent</span>
                        @endif
                    </td>
                    <td>{{ $user->telephone ?? '—' }}</td>
                    <td>
                        @if($user->actif)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-danger">Inactif</span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <a href="{{ route('utilisateurs.edit', $user) }}"
                           class="btn btn-sm btn-outline-secondary me-1" title="Modifier">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('utilisateurs.toggle', $user) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $user->actif ? 'btn-outline-warning' : 'btn-outline-success' }} me-1"
                                    title="{{ $user->actif ? 'Désactiver' : 'Activer' }}">
                                <i class="bi bi-{{ $user->actif ? 'pause-circle' : 'play-circle' }}"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('utilisateurs.destroy', $user) }}"
                              class="d-inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-people fs-2 d-block mb-2"></i>
                        Aucun utilisateur trouvé
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer py-2">
        <span class="text-muted small">{{ $users->count() }} utilisateur(s) au total</span>
    </div>
</div>
@endsection
