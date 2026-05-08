<!DOCTYPE html>
<html>
<body style="font-family: Arial; color: #333;">

  <div style="background:#006B3F; padding:20px; color:white;">
    <h2>{{ $reunion->titre }}</h2>
  </div>

  <div style="padding:20px;">
    <p>Bonjour <strong>{{ $agent->prenom }} {{ $agent->nom }}</strong>,</p>
    <p>Vous êtes convoqué(e) à la réunion suivante :</p>

    <table style="width:100%; border-collapse:collapse;">
      <tr>
        <td style="padding:8px; background:#f2f2f2;"><strong>Date</strong></td>
        <td style="padding:8px;">{{ $reunion->date->format('d/m/Y') }}</td>
      </tr>
      <tr>
        <td style="padding:8px; background:#f2f2f2;"><strong>Heure</strong></td>
        <td style="padding:8px;">{{ $reunion->heure ?? 'À définir' }}</td>
      </tr>
      <tr>
        <td style="padding:8px; background:#f2f2f2;"><strong>Lieu</strong></td>
        <td style="padding:8px;">{{ $reunion->lieu }}</td>
      </tr>
      <tr>
        <td style="padding:8px; background:#f2f2f2;"><strong>Ordre du jour</strong></td>
        <td style="padding:8px;">{{ $reunion->ordre_du_jour }}</td>
      </tr>
    </table>

    <p style="margin-top:20px; color:#006B3F;">
      Cordialement,<br>
      <strong>Direction de la Coopération Universitaire et Scientifique</strong>
    </p>
  </div>

</body>
</html>