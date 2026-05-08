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
.decisions-table { width:100%; border-collapse:collapse; font-size:8.5px; }
.decisions-table th { background:#006B3F; color:white; padding:5px 8px; text-align:left; font-weight:bold; border:1px solid #006B3F; }
.decisions-table td { border:1px solid #d0ddd5; padding:5px 8px; vertical-align:middle; }
.decisions-table tr:nth-child(even) td { background:#f4f6f4; }
.accords-table { width:100%; border-collapse:collapse; font-size:8.5px; }
.accords-table th { background:#006B3F; color:white; padding:5px 8px; text-align:left; font-weight:bold; border:1px solid #006B3F; }
.accords-table td { border:1px solid #d0ddd5; padding:5px 8px; vertical-align:middle; }
.accords-table tr:nth-child(even) td { background:#f4f6f4; }
.prog-wrap { background:#e0e0e0; border-radius:4px; height:8px; width:70px; display:inline-block; vertical-align:middle; overflow:hidden; }
.prog-fill  { height:8px; border-radius:4px; background:#006B3F; }
.bs { padding:2px 7px; border-radius:10px; font-size:7.5px; font-weight:bold; display:inline-block; color:white; }
.s-assignee { background:#9e9e9e; }
.s-en_cours { background:#FF8F00; }
.s-validee  { background:#0288D1; }
.s-cloturee { background:#006B3F; }
.s-annulee  { background:#c62828; }
.s-accord-identifie      { background:#9e9e9e; }
.s-accord-en_negotiation { background:#FF8F00; }
.s-accord-signe          { background:#0288D1; }
.s-accord-en_execution   { background:#1565C0; }
.s-accord-cloture        { background:#006B3F; }
.s-accord-abandonne      { background:#c62828; }
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
      <strong>Réf.</strong> : CR-REUNION-{{ $reunion->date->format('Ymd') }}<br>
      <strong>Édité le</strong> : {{ now()->locale('fr')->translatedFormat('d F Y') }}<br>
      <strong>Heure</strong> : {{ now()->format('H:i') }}
    </div>
  </div>
</div>

<div class="doc-title-bar">
  <h2>Compte Rendu — Réunion</h2>
  <p>{{ $reunion->titre }} &mdash; {{ $reunion->date->locale('fr')->translatedFormat('l d F Y') }}</p>
</div>

<div class="body">



  <table class="identity-table">
    <tr>
      <td class="th">Titre</td>
      <td class="td-w" colspan="3"><strong>{{ $reunion->titre }}</strong></td>
    </tr>
    <tr>
      <td class="th">Date</td>
      <td class="td">{{ $reunion->date->locale('fr')->translatedFormat('l d F Y') }}</td>
      <td class="th">Heure</td>
      <td class="td">{{ $reunion->heure ?? '—' }}</td>
    </tr>
    <tr>
      <td class="th">Lieu</td>
      <td class="td" colspan="3">{{ $reunion->lieu }}</td>
    </tr>
    <tr>
      <td class="th">Convocateur</td>
      <td class="td" colspan="3">{{ $reunion->convocateur ?? '—' }}</td>
    </tr>
    <tr>
      <td class="th">Statut</td>
      <td class="td" colspan="3">{{ $reunion->statut_label }}</td>
    </tr>
  </table>



  <div class="section">
    <div class="section-header">Ordre du jour</div>
    <div class="section-body {{ empty($reunion->ordre_du_jour) ? 'empty' : '' }}">
      {{ $reunion->ordre_du_jour ?? 'Aucun ordre du jour saisi.' }}
    </div>
  </div>



  <div class="section">
    <div class="section-header">Compte rendu des discussions</div>
    <div class="section-body {{ empty($reunion->compte_rendu) ? 'empty' : '' }}">
      {{ $reunion->compte_rendu ?? 'Aucun compte rendu saisi.' }}
    </div>
  </div>



  <div class="section">
    <div class="section-header">Décisions prises et état d'avancement</div>
    @if($reunion->decisions && $reunion->decisions->count() > 0)
    <table class="decisions-table">
      <thead>
        <tr>
          <th style="width:30%">Décision</th>
          <th style="width:15%">Responsable</th>
          <th style="width:11%">Échéance</th>
          <th style="width:18%">Progression</th>
          <th style="width:12%">Statut</th>
          <th style="width:14%">Commentaire</th>
        </tr>
      </thead>
      <tbody>
        @foreach($reunion->decisions as $d)
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
          <td style="font-size:8px;">{{ $d->commentaire ? \Illuminate\Support\Str::limit($d->commentaire, 40) : '—' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @else
    <div class="section-body empty">Aucune décision enregistrée pour cette réunion.</div>
    @endif
  </div>

@if($reunion->accords && $reunion->accords->count() > 0)

  <div class="section">
    <div class="section-header">Accords issus de cette réunion</div>
    <table class="accords-table">
      <thead>
        <tr>
          <th style="width:28%">Titre</th>
          <th style="width:22%">Institution partenaire</th>
          <th style="width:12%">Pays</th>
          <th style="width:15%">Date signature</th>
          <th style="width:13%">Statut</th>
        </tr>
      </thead>
      <tbody>
        @foreach($reunion->accords as $accord)
        <tr>
          <td>{{ $accord->titre }}</td>
          <td>{{ $accord->institution_partenaire ?? '—' }}</td>
          <td>{{ $accord->pays_partenaire ?? '—' }}</td>
          <td>{{ $accord->date_signature ? $accord->date_signature->format('d/m/Y') : '—' }}</td>
          <td>
            <span class="bs s-accord-{{ $accord->statut }}">{{ $accord->statut_label }}</span>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif



  <div class="signatures-section">
    <table style="width:100%;border-collapse:collapse;">
      <tr>
        <td style="width:50%;padding:0 20px 0 0;vertical-align:top;">
          <div class="sig-title">Le Convocateur</div>
          <div class="sig-name">{{ $reunion->convocateur ?? '.................................' }}</div>
        </td>
        <td style="width:50%;padding:0 0 0 20px;vertical-align:top;">
          <div class="sig-title">Le Rapporteur</div>
          <div class="sig-name">.................................</div>
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