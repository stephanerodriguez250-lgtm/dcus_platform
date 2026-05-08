<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: 'Segoe UI', Arial, sans-serif; background:#f5f7fa; margin:0; padding:0; }
  .container { max-width:600px; margin:30px auto; background:white; border-radius:12px;
               overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08); }
  .header { background:linear-gradient(135deg,#1a3a5c,#2563eb); padding:28px; text-align:center; color:white; }
  .header h1 { margin:0; font-size:1.3rem; font-weight:700; }
  .header p  { margin:6px 0 0; opacity:0.8; font-size:0.85rem; }
  .badge { display:inline-block; background:rgba(255,255,255,0.2); border-radius:20px;
           padding:4px 14px; font-size:0.8rem; margin-top:8px; }
  .body { padding:28px; }
  .greeting { font-size:1rem; color:#1a3a5c; font-weight:600; margin-bottom:14px; }
  .intro { color:#555; line-height:1.6; margin-bottom:22px; }
  .info-card { background:#f0f4ff; border-left:4px solid #2563eb; border-radius:8px;
               padding:18px; margin-bottom:22px; }
  .info-row { display:flex; gap:10px; margin-bottom:10px; align-items:flex-start; }
  .info-row:last-child { margin-bottom:0; }
  .info-label { font-size:0.75rem; font-weight:700; color:#2563eb; text-transform:uppercase;
                letter-spacing:0.5px; min-width:110px; padding-top:2px; }
  .info-value { color:#333; font-size:0.9rem; line-height:1.5; }
  .footer-note { color:#888; font-size:0.8rem; line-height:1.6;
                 border-top:1px solid #eee; padding-top:18px; margin-top:10px; }
  .footer { background:#1a3a5c; padding:16px 28px; text-align:center;
            color:rgba(255,255,255,0.6); font-size:0.75rem; }
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1>🏛️ DCUS — Convocation CODIR</h1>
    <p>Direction de la Coopération Universitaire et Scientifique</p>
    <span class="badge">Réunion interne</span>
  </div>
  <div class="body">
    <div class="greeting">Bonjour {{ $destinataire_nom }},</div>
    <div class="intro">
      Vous êtes convié(e) au Comité de Direction (CODIR) de la DCUS.
      Veuillez prendre note des informations ci-dessous.
    </div>

    <div class="info-card">
      <div class="info-row">
        <span class="info-label">📋 Objet</span>
        <span class="info-value"><strong>{{ $codir->objet }}</strong></span>
      </div>
      <div class="info-row">
        <span class="info-label">📅 Date</span>
        <span class="info-value">
          {{ $codir->date->locale('fr')->translatedFormat('l d F Y') }}
          @if($codir->heure_debut)
            à {{ $codir->heure_debut }}
          @endif
          @if($codir->heure_fin)
            — {{ $codir->heure_fin }}
          @endif
        </span>
      </div>
      <div class="info-row">
        <span class="info-label">📍 Lieu</span>
        <span class="info-value">{{ $codir->lieu }}</span>
      </div>
      @if($codir->presidente)
      <div class="info-row">
        <span class="info-label">👑 Présidente</span>
        <span class="info-value">{{ $codir->presidente }}</span>
      </div>
      @endif
      @if($codir->rapporteur)
      <div class="info-row">
        <span class="info-label">📝 Rapporteur</span>
        <span class="info-value">{{ $codir->rapporteur }}</span>
      </div>
      @endif
    </div>

    <div class="footer-note">
      Ce message est généré automatiquement par la plateforme DCUS.<br>
      Pour plus d'informations, connectez-vous à la plateforme.
    </div>
  </div>
  <div class="footer">
    © {{ date('Y') }} DCUS — DBAU — Ministère de l'Enseignement Supérieur du Bénin
  </div>
</div>
</body>
</html>
