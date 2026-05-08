<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:9.5px; color:#1a1a1a; }
.tricolor { display:flex; height:7px; width:100%; }
.tricolor .c1 { background:#006B3F; flex:1; }
.tricolor .c2 { background:#FCD116; flex:1; }
.tricolor .c3 { background:#C8102E; flex:1; }
.header { background:#006B3F; color:white; padding:16px 20px 14px; }
.header-inner { display:flex; justify-content:space-between; align-items:flex-start; }
.header-left h1 { font-size:12px; font-weight:bold; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:3px; }
.header-left p  { font-size:8px; opacity:0.85; line-height:1.6; }
.header-right   { text-align:right; font-size:8px; opacity:0.85; line-height:1.8; }
.doc-title-bar  { background:#e8f5ee; border-left:5px solid #006B3F; border-bottom:2px solid #006B3F; padding:10px 20px; margin-bottom:14px; }
.doc-title-bar h2 { font-size:13px; font-weight:bold; color:#006B3F; text-transform:uppercase; letter-spacing:0.8px; }
.doc-title-bar p  { font-size:9px; color:#4a6a4a; margin-top:2px; }
.body { padding:0 20px 65px; }
.identity-table { width:100%; border-collapse:collapse; margin-bottom:14px; font-size:9px; }
.identity-table td { padding:5px 9px; border:1px solid #d0ddd5; vertical-align:top; }
.identity-table .th { background:#006B3F; color:white; font-weight:bold; width:25%; }
.identity-table .td { background:#f4f6f4; }
.identity-table .td-w { background:white; }
.section { margin-bottom:14px; }
.section-header { background:#006B3F; color:white; font-weight:bold; font-size:9px; padding:6px 10px; text-transform:uppercase; letter-spacing:0.8px; }
.section-body   { border:1px solid #d0ddd5; border-top:none; padding:10px; background:white; font-size:9px; line-height:1.7; min-height:28px; white-space:pre-line; }
.section-body.empty { color:#999; font-style:italic; }
.participants-table { width:100%; border-collapse:collapse; font-size:8.5px; }
.participants-table th { background:#006B3F; color:white; padding:5px 8px; text-align:left; font-weight:bold; border:1px solid #006B3F; }
.participants-table td { border:1px solid #d0ddd5; padding:5px 8px; vertical-align:middle; }
.participants-table tr:nth-child(even) td { background:#f4f6f4; }
.bp { background:#006B3F; color:white; padding:2px 7px; border-radius:10px; font-size:7.5px; font-weight:bold; display:inline-block; }
.ba { background:#9e9e9e; color:white; padding:2px 7px; border-radius:10px; font-size:7.5px; font-weight:bold; display:inline-block; }
.decisions-table { width:100%; border-collapse:collapse; font-size:8.5px; }
.decisions-table th { background:#006B3F; color:white; padding:5px 8px; text-align:left; font-weight:bold; border:1px solid #006B3F; }
.decisions-table td { border:1px solid #d0ddd5; padding:5px 8px; vertical-align:middle; }
.decisions-table tr:nth-child(even) td { background:#f4f6f4; }
.prog-wrap { background:#e0e0e0; border-radius:4px; height:8px; width:70px; display:inline-block; vertical-align:middle; overflow:hidden; }
.prog-fill  { height:8px; border-radius:4px; background:#006B3F; }
.bs { padding:2px 7px; border-radius:10px; font-size:7.5px; font-weight:bold; display:inline-block; color:white; }
.s-assignee { background:#9e9e9e; }
.s-en_cours { background:#FF8F00; }
.s-validee  { background:#0288D1; }
.s-cloturee { background:#006B3F; }
.s-annulee  { background:#c62828; }
.next-meeting { background:#e8f5ee; border:1px solid #006B3F; border-left:5px solid #006B3F; padding:8px 12px; margin-bottom:14px; font-size:9px; }
.next-meeting strong { color:#006B3F; }
.signatures-section { margin-top:28px; border-top:2px solid #006B3F; padding-top:16px; }
.sig-title { font-size:9px; font-weight:bold; color:#006B3F; text-transform:uppercase; letter-spacing:0.5px; text-align:center; border-bottom:1px solid #006B3F; padding-bottom:3px; margin-bottom:50px; }
.sig-name  { font-size:8.5px; color:#555; text-align:center; }
.footer { position:fixed; bottom:0; left:0; right:0; background:#006B3F; color:rgba(255,255,255,0.9); font-size:7.5px; padding:5px 20px; display:flex; justify-content:space-between; align-items:center; }
</style>
</head>
<body>
<div class="tricolor"><div class="c1"></div><div class="c2"></div><div class="c3"></div></div>
<div class="header">
  <div class="header-inner">
    <div class="header-left">
      <h1>Direction de la Coopération Universitaire et Scientifique</h1>
      <p>Direction des Bourses et Aide Universitaire (DBAU)<br>
         Ministère de l'Enseignement Supérieur et de la Recherche Scientifique<br>
         République du Bénin</p>
    </div>
    <div class="header-right">
      <strong>Réf.</strong> : CR-CODIR-{{ $codir->date->format('Ymd') }}<br>
      <strong>Édité le</strong> : {{ now()->locale('fr')->translatedFormat('d F Y') }}<br>
      <strong>Heure</strong> : {{ now()->format('H:i') }}
    </div>
  </div>
</div>
<div class="doc-title-bar">
  <h2>Compte Rendu — Comité de Direction (CODIR)</h2>
  <p>{{ $codir->objet }} &mdash; {{ $codir->date->locale('fr')->translatedFormat('l d F Y') }}</p>
</div>
<div class="body">

<table class="identity-table">
  <tr>
    <td class="th">Objet</td>
    <td class="td-w" colspan="3"><strong>{{ $codir->objet }}</strong></td>
  </tr>
  <tr>
    <td class="th">Date</td>
    <td class="td">{{ $codir->date->locale('fr')->translatedFormat('l d F Y') }}</td>
    <td class="th">Lieu</td>
    <td class="td">{{ $codir->lieu }}</td>
  </tr>
  <tr>
    <td class="th">Heure de début</td>
    <td class="td">{{ $codir->heure_debut ?? '—' }}</td>
    <td class="th">Heure de fin</td>
    <td class="td">{{ $codir->heure_fin ?? '—' }}</td>
  </tr>
  <tr>
    <td class="th">Présidente</td>
    <td class="td">{{ $codir->presidente ?? '—' }}</td>
    <td class="th">Rapporteur</td>
    <td class="td">{{ $codir->rapporteur ?? '—' }}</td>
  </tr>
  <tr>
    <td class="th">Participants</td>
    <td class="td" colspan="3">
      <strong>{{ $codir->participants->count() }}</strong> participant(s) —
      <strong>{{ $codir->participants->where('present', true)->count() }}</strong> présent(s) /
      <strong>{{ $codir->participants->where('present', false)->count() }}</strong> absent(s)
    </td>
  </tr>
</table>

<div class="section">
  <div class="section-header">Participants</div>
  <table class="participants-table">
    <thead>
      <tr>
        <th style="width:35%">Nom et Prénom</th>
        <th style="width:30%">Fonction</th>
        <th style="width:25%">Email</th>
        <th style="width:10%;text-align:center;">Présence</th>
      </tr>
    </thead>
    <tbody>
      @forelse($codir->participants as $p)
      <tr>
        <td><strong>{{ $p->nom_complet }}</strong></td>
        <td>{{ $p->fonction ?? '—' }}</td>
        <td>{{ $p->email ?? '—' }}</td>
        <td style="text-align:center;">
          @if($p->present)
            <span class="bp">Présent</span>
          @else
            <span class="ba">Absent</span>
          @endif
        </td>
      </tr>
      @empty
      <tr><td colspan="4" style="text-align:center;color:#999;">Aucun participant</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="section">
  <div class="section-header">Synthèse des discussions</div>
  <div class="section-body {{ empty($codir->synthese) ? 'empty' : '' }}">{{ $codir->synthese ?? 'Aucune synthèse saisie.' }}</div>
</div>

<div class="section">
  <div class="section-header">Décisions prises et état d'avancement</div>
  @if(isset($decisions) && $decisions->count() > 0)
  <table class="decisions-table">
    <thead>
      <tr>
        <th style="width:28%">Décision</th>
        <th style="width:15%">Responsable</th>
        <th style="width:11%">Échéance</th>
        <th style="width:18%">Progression</th>
        <th style="width:12%">Statut</th>
        <th style="width:16%">Commentaire</th>
      </tr>
    </thead>
    <tbody>
      @foreach($decisions as $d)
      <tr>
        <td>{{ $d->intitule }}</td>
        <td>{{ $d->responsable ?? '—' }}</td>
        <td>{{ $d->echeance ? $d->echeance->format('d/m/Y') : '—' }}</td>
        <td>
          <div style="display:flex;align-items:center;gap:4px;">
            <div class="prog-wrap">
              <div class="prog-fill" style="width:{{ $d->progression }}%;"></div>
            </div>
            <span style="font-size:8px;font-weight:bold;">{{ $d->progression }}%</span>
          </div>
        </td>
        <td><span class="bs s-{{ $d->statut }}">{{ $d->statut_label }}</span></td>
        <td style="font-size:8px;">{{ $d->commentaire ? \Illuminate\Support\Str::limit($d->commentaire,40) : '—' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @else
  <div class="section-body {{ empty($codir->decisions) ? 'empty' : '' }}">{{ $codir->decisions ?? 'Aucune décision enregistrée.' }}</div>
  @endif
</div>

@if($codir->divers)
<div class="section">
  <div class="section-header">Points divers</div>
  <div class="section-body">{{ $codir->divers }}</div>
</div>
@endif

@if($codir->prochaine_reunion)
<div class="next-meeting">
  <strong>Prochaine réunion :</strong>
  {{ $codir->prochaine_reunion->locale('fr')->translatedFormat('l d F Y') }}
</div>
@endif

<div class="signatures-section">
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="width:50%;padding:0 20px 0 0;vertical-align:top;">
        <div class="sig-title">La Présidente</div>
        <div class="sig-name">{{ $codir->presidente ?? '.................................' }}</div>
      </td>
      <td style="width:50%;padding:0 0 0 20px;vertical-align:top;">
        <div class="sig-title">Le Rapporteur</div>
        <div class="sig-name">{{ $codir->rapporteur ?? '.................................' }}</div>
      </td>
    </tr>
  </table>
</div>

</div>
<div class="footer">
  <span><strong>DCUS — DBAU — République du Bénin</strong></span>
  <span>Ministère de l'Enseignement Supérieur et de la Recherche Scientifique</span>
  <span>Document officiel — {{ now()->format('d/m/Y à H:i') }}</span>
</div>
</body>
</html>
