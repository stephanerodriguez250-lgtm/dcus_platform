<!DOCTYPE html>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convocation — {{ $reunion->titre }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            color: #333333;
            background-color: #f4f4f4;
        }
        .wrapper {
            max-width: 680px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }

```
    /* ── BANDEAU TRICOLORE ── */
    .tricolor {
        display: flex;
        height: 6px;
    }
    .tricolor .c1 { background: #006B3F; flex: 1; }
    .tricolor .c2 { background: #FCD116; flex: 1; }
    .tricolor .c3 { background: #C8102E; flex: 1; }

    /* ── EN-TÊTE ── */
    .header {
        background: #006B3F;
        padding: 28px 32px 22px;
        color: white;
    }
    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }
    .header-institution {
        font-size: 11px;
        line-height: 1.7;
        opacity: 0.9;
    }
    .header-institution strong {
        display: block;
        font-size: 12px;
        margin-bottom: 2px;
    }
    .header-ref {
        text-align: right;
        font-size: 11px;
        opacity: 0.85;
        line-height: 1.8;
    }
    .header-title {
        border-top: 1px solid rgba(255,255,255,0.3);
        padding-top: 14px;
    }
    .header-title .label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        opacity: 0.8;
        margin-bottom: 4px;
    }
    .header-title h1 {
        font-size: 20px;
        font-weight: bold;
        line-height: 1.3;
    }

    /* ── CORPS ── */
    .body {
        padding: 32px;
    }

    /* Salutation */
    .greeting {
        font-size: 15px;
        margin-bottom: 16px;
        color: #222;
    }
    .greeting strong { color: #006B3F; }

    /* Texte intro */
    .intro {
        font-size: 14px;
        line-height: 1.7;
        color: #444;
        margin-bottom: 24px;
    }

    /* Bloc détails réunion */
    .details-card {
        background: #f0f7f4;
        border: 1px solid #c3ddd4;
        border-left: 5px solid #006B3F;
        border-radius: 6px;
        padding: 20px 24px;
        margin-bottom: 24px;
    }
    .details-card h2 {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #006B3F;
        margin-bottom: 14px;
    }
    .detail-row {
        display: flex;
        align-items: flex-start;
        padding: 7px 0;
        border-bottom: 1px solid #d8eae3;
        font-size: 14px;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-label {
        width: 140px;
        flex-shrink: 0;
        font-weight: bold;
        color: #555;
        font-size: 13px;
    }
    .detail-value {
        color: #222;
        flex: 1;
        line-height: 1.5;
    }

    /* Ordre du jour */
    .odj-card {
        background: #fffbf0;
        border: 1px solid #f0d88a;
        border-left: 5px solid #FCD116;
        border-radius: 6px;
        padding: 20px 24px;
        margin-bottom: 24px;
    }
    .odj-card h2 {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #7a6000;
        margin-bottom: 12px;
    }
    .odj-content {
        font-size: 14px;
        line-height: 1.8;
        color: #333;
        white-space: pre-line;
    }

    /* Message important */
    .notice {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 6px;
        padding: 14px 18px;
        font-size: 13px;
        color: #856404;
        margin-bottom: 24px;
        line-height: 1.6;
    }
    .notice strong { display: block; margin-bottom: 4px; }

    /* Signature */
    .signature {
        border-top: 2px solid #006B3F;
        padding-top: 20px;
        margin-top: 8px;
    }
    .signature p {
        font-size: 14px;
        line-height: 1.8;
        color: #444;
    }
    .signature .sig-name {
        font-weight: bold;
        color: #006B3F;
        font-size: 15px;
        margin-top: 6px;
    }
    .signature .sig-poste {
        font-size: 13px;
        color: #666;
    }

    /* ── PIED DE PAGE ── */
    .footer {
        background: #1a3a5c;
        padding: 16px 32px;
        text-align: center;
        font-size: 11px;
        color: rgba(255,255,255,0.7);
        line-height: 1.8;
    }
    .footer a { color: rgba(255,255,255,0.9); text-decoration: none; }

    /* Badge statut */
    .badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
        color: white;
        background: #006B3F;
    }
</style>
```

</head>
<body>

<div class="wrapper">

```
{{-- Bandeau tricolore Bénin --}}
<div class="tricolor">
    <div class="c1"></div>
    <div class="c2"></div>
    <div class="c3"></div>
</div>

{{-- En-tête --}}
<div class="header">
    <div class="header-top">
        <div class="header-institution">
            <strong>RÉPUBLIQUE DU BÉNIN</strong>
            Ministère de l'Enseignement Supérieur<br>
            et de la Recherche Scientifique (MESRS)<br>
            Direction de la Coopération Universitaire<br>
            et Scientifique (DCUS)
        </div>
        <div class="header-ref">
            <strong>Réf. :</strong> CONV-{{ $reunion->id }}-{{ $reunion->date->format('Ymd') }}<br>
            <strong>Date :</strong> {{ now()->locale('fr')->translatedFormat('d F Y') }}<br>
            <span class="badge">{{ strtoupper($reunion->statut_label) }}</span>
        </div>
    </div>
    <div class="header-title">
        <div class="label">Convocation officielle</div>
        <h1>{{ $reunion->titre }}</h1>
    </div>
</div>

{{-- Corps --}}
<div class="body">

    {{-- Salutation --}}
    <p class="greeting">
        Bonjour <strong>{{ $destinataire->prenom }} {{ $destinataire->nom }}</strong>,
    </p>

    {{-- Intro --}}
    <p class="intro">
        Nous avons l'honneur de vous informer qu'une réunion a été planifiée et vous
        sollicitons de bien vouloir y prendre part. Veuillez trouver ci-dessous les
        informations relatives à cette réunion.
    </p>

    {{-- Détails de la réunion --}}
    <div class="details-card">
        <h2>📋 Détails de la réunion</h2>

        <div class="detail-row">
            <div class="detail-label">📅 Date</div>
            <div class="detail-value">
                <strong>{{ $reunion->date->locale('fr')->translatedFormat('l d F Y') }}</strong>
            </div>
        </div>

        @if($reunion->heure)
        <div class="detail-row">
            <div class="detail-label">🕐 Heure</div>
            <div class="detail-value">{{ $reunion->heure }}</div>
        </div>
        @endif

        <div class="detail-row">
            <div class="detail-label">📍 Lieu</div>
            <div class="detail-value">{{ $reunion->lieu }}</div>
        </div>

        @if($reunion->convocateur)
        <div class="detail-row">
            <div class="detail-label">🏛️ Convocateur</div>
            <div class="detail-value">{{ $reunion->convocateur }}</div>
        </div>
        @endif

        <div class="detail-row">
            <div class="detail-label">📌 Statut</div>
            <div class="detail-value">
                <span class="badge">{{ $reunion->statut_label }}</span>
            </div>
        </div>
    </div>

    {{-- Ordre du jour --}}
    @if($reunion->ordre_du_jour)
    <div class="odj-card">
        <h2>📄 Ordre du jour</h2>
        <div class="odj-content">{{ $reunion->ordre_du_jour }}</div>
    </div>
    @endif

    {{-- Notice --}}
    <div class="notice">
        <strong>⚠️ Merci de confirmer votre présence</strong>
        Votre présence à cette réunion est requise. En cas d'empêchement,
        veuillez en informer le secrétariat dès que possible afin que les dispositions
        nécessaires puissent être prises.
    </div>

    {{-- Signature --}}
    <div class="signature">
        <p>Veuillez agréer, {{ $destinataire->prenom }} {{ $destinataire->nom }},
        l'expression de nos salutations distinguées.</p>
        <p class="sig-name">La Direction — DCUS</p>
        <p class="sig-poste">
            Direction de la Coopération Universitaire et Scientifique<br>
            Ministère de l'Enseignement Supérieur et de la Recherche Scientifique
        </p>
    </div>

</div>

{{-- Pied de page --}}
<div class="footer">
    Ce message est généré automatiquement par la plateforme DCUS.<br>
    Merci de ne pas répondre directement à cet email.<br>
    <a href="#">Plateforme DCUS — MESRS — République du Bénin</a>
</div>
```

</div>

</body>
</html>