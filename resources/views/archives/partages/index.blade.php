@extends('layouts.app')

@section('title', 'Partages effectués')
@section('page-title', 'Partages effectués')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="mb-1 fw-bold text-dark">Partages effectués</h5>
        <p class="text-muted small mb-0">Fichiers et dossiers que vous avez partagés à d'autres utilisateurs</p>
    </div>
    <a href="{{ route('archives.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Retour aux archives
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Fichier / Dossier</th>
                        <th>Destinataire</th>
                        <th>Partagé le</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($partages as $partage)
                    <tr>
                        <td class="ps-4">
                            @if($partage->fichierOriginal)
                                <i class="bi {{ $partage->fichierOriginal->icone }} me-2"></i>{{ $partage->fichierOriginal->intitule }}
                            @else
                                <span class="text-muted fst-italic">Fichier original supprimé — {{ $partage->fichierCopie->intitule }}</span>
                            @endif
                        </td>
                        <td>{{ $partage->destinataire->nom_complet }}</td>
                        <td>{{ $partage->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end pe-4">
                            <form method="POST" action="{{ route('archives.partages.destroy', $partage) }}"
                                  class="d-inline" onsubmit="return confirm('Révoquer ce partage ? Le destinataire garde sa copie déjà reçue.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Révoquer">
                                    <i class="bi bi-x-circle"></i> Révoquer
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @forelse($dossierPartages as $dossierPartage)
                    <tr>
                        <td class="ps-4">
                            <i class="bi bi-folder-fill text-warning me-2"></i>
                            @if($dossierPartage->dossierOriginal)
                                {{ $dossierPartage->dossierOriginal->nom }}
                            @else
                                <span class="text-muted fst-italic">Dossier original supprimé — {{ $dossierPartage->dossierCopie->nom }}</span>
                            @endif
                        </td>
                        <td>{{ $dossierPartage->destinataire->nom_complet }}</td>
                        <td>{{ $dossierPartage->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end pe-4">
                            <form method="POST" action="{{ route('archives.dossier-partages.destroy', $dossierPartage) }}"
                                  class="d-inline" onsubmit="return confirm('Révoquer ce partage ? Le destinataire garde sa copie déjà reçue.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Révoquer">
                                    <i class="bi bi-x-circle"></i> Révoquer
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    @if($partages->isEmpty() && $dossierPartages->isEmpty())
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
                            <i class="bi bi-share fs-2 d-block mb-2"></i>
                            Vous n'avez rien partagé
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
