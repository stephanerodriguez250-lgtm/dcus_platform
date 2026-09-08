# Partage de fichiers Archives — design

## Contexte

[[2026-09-08-archives-design]] (déjà implémenté) définit Archives comme un espace **strictement privé par utilisateur** — sans partage. Cette fonctionnalité ajoute le partage : un utilisateur sélectionne un ou plusieurs fichiers de son espace Archives et les partage à un ou plusieurs autres utilisateurs.

## Décisions actées

- **Mécanique** : partage = **copie indépendante**, pas un accès en direct. Le fichier physique et la ligne `ArchiveFichier` sont dupliqués pour le destinataire ; une fois livrée, la copie lui appartient au même titre que n'importe lequel de ses fichiers Archives (même policy, mêmes droits de renommage/suppression).
- **Emplacement de la copie** : à la racine de l'espace Archives du destinataire (`folder_id = null`) — pas de sélection de dossier de destination en v1, cohérent avec l'absence de déplacement de fichiers dans Archives v1.
- **Téléchargement** : le destinataire peut télécharger sa copie normalement (c'est un `ArchiveFichier` comme un autre).
- **Révocation** : "annuler un partage" supprime uniquement l'entrée d'historique (`archive_partages`). Ça ne touche **pas** à la copie déjà livrée — le destinataire la garde, exactement comme n'importe lequel de ses fichiers. Seul l'expéditeur (`partage_par`) peut révoquer.
- **Notification** : un email est envoyé au destinataire au moment du partage (Notification Laravel classique — le destinataire est un `User` existant et `Notifiable`, donc `$destinataire->notify(...)`, pas une notification "on-demand" comme pour les invitations).
- **Portée** : fichiers uniquement, pas de partage de dossier entier.
- **Pas de prévention des doublons** : partager plusieurs fois le même fichier au même destinataire crée plusieurs copies indépendantes — comportement accepté, pas de validation anti-doublon en v1.

## Modèle de données

### `archive_partages`

| colonne | type | notes |
|---|---|---|
| `id` | bigint PK | |
| `fichier_original_id` | bigint FK → archive_fichiers, nullable | `nullOnDelete()` — si l'original est supprimé plus tard, l'historique reste (pointeur mis à null) |
| `fichier_copie_id` | bigint FK → archive_fichiers | `cascadeOnDelete()` — si le destinataire supprime sa copie, l'entrée d'historique disparaît (rien de significatif à afficher sans la copie) |
| `partage_par` | bigint FK → users | expéditeur |
| `destinataire_id` | bigint FK → users | destinataire |
| timestamps | | `created_at` = date du partage |

Pas de nouvelle colonne sur `archive_fichiers` : la provenance ("Partagé par X") se lit via une relation inverse depuis `archive_partages` (`fichier_copie_id`), pas stockée en double.

## Modèles Eloquent

- `App\Models\ArchivePartage` — relations `fichierOriginal()`, `fichierCopie()` (belongsTo `ArchiveFichier`), `partagePar()`, `destinataire()` (belongsTo `User`).
- `App\Models\ArchiveFichier` — nouvelle relation `partageOrigine()` : `hasOne(ArchivePartage::class, 'fichier_copie_id')`, utilisée pour afficher "Partagé par X" sur une copie reçue.

## Copie du fichier physique

Au partage, pour chaque (fichier sélectionné × destinataire sélectionné) :
1. Un nouveau chemin de stockage est généré (même convention que l'upload initial : nom aléatoire dans `archives/fichiers/` sur `Storage::disk('public')`).
2. `Storage::disk('public')->copy($original->chemin_fichier, $nouveauChemin)`.
3. Une nouvelle ligne `ArchiveFichier` est créée : `user_id` = destinataire, `folder_id` = null, `intitule`/`numero`/`description`/`nom_fichier`/`type_fichier`/`taille` copiés depuis l'original, `chemin_fichier` = le nouveau chemin.
4. Une ligne `ArchivePartage` est créée (`fichier_original_id`, `fichier_copie_id`, `partage_par` = utilisateur connecté, `destinataire_id`).
5. Une notification email est envoyée au destinataire.

## Routes / Contrôleur

Nouveau `App\Http\Controllers\ArchivePartageController`, sous le groupe `auth` existant :

```
GET    archives/partages                index (partages envoyés par l'utilisateur connecté)
POST   archives/partages                store (partager : fichier_ids[], destinataire_ids[])
DELETE archives/partages/{partage}      destroy (révoquer : supprime l'entrée d'historique)
```

**Ordre de déclaration critique** : `GET archives/partages` doit être déclarée **avant** `GET archives/{dossier?}` (déjà existante) dans `routes/web.php`. Les deux ont la même forme (`archives/<un-segment>`), donc sans cet ordre, Laravel tenterait de résoudre "partages" comme un ID de dossier via le binding implicite de `{dossier?}` et ne trouverait jamais la route dédiée.

### `store` — validation et logique

- `fichier_ids` : requis, tableau, min 1, chaque valeur `exists:archive_fichiers,id`.
- `destinataire_ids` : requis, tableau, min 1, chaque valeur `exists:users,id`, et aucune ne peut être l'utilisateur connecté (règle de validation personnalisée ou vérification manuelle → erreur si présent).
- Vérification d'appartenance : tous les `fichier_ids` doivent appartenir à l'utilisateur connecté (`ArchiveFichier::whereIn('id', $ids)->where('user_id', Auth::id())->count() === count($ids)`), sinon `abort(403)` — échec net, pas de partage partiel silencieux.
- Pour chaque paire (fichier, destinataire) : exécute la mécanique de copie ci-dessus.
- Redirige vers `archives.index` (le dossier d'où l'utilisateur est parti, via un champ cascondé `retour_dossier_id` dans le formulaire) avec un message de succès résumant le nombre de partages effectués.

### `destroy`

- `$this->authorize('delete', $partage)` (policy : `partage_par === user.id`).
- `$partage->delete()` — ne touche pas à `fichierCopie`.

## Autorisation

`App\Policies\ArchivePartagePolicy` (auto-découverte) :
```php
public function delete(User $user, ArchivePartage $partage): bool
{
    return $partage->partage_par === $user->id;
}
```

## Notification

`App\Notifications\FichierPartageNotification` — `Illuminate\Notifications\Notification` classique (pas on-demand, le destinataire est un `User` existant), `via() => ['mail']`, `toMail()` en français : objet du fichier partagé, nom de l'expéditeur, lien vers `archives.index`. Envoyée via `$destinataire->notify(new FichierPartageNotification($fichierOriginal, $expediteur))`.

## Interface

- `resources/views/archives/index.blade.php` : une case à cocher `name="fichier_ids[]"` sur chaque ligne de fichier (pas sur les dossiers), avec l'attribut `form="partage-form"` pointant vers un `<form id="partage-form" method="POST" action="{{ route('archives.partages.store') }}">` déclaré une seule fois sur la page (contient `@csrf` et le champ caché `retour_dossier_id`). Un bouton "Partager" (toujours visible, pas de JS conditionnel) ouvre une modale Bootstrap listant les autres utilisateurs actifs (`User::where('id', '!=', Auth::id())->where('actif', true)->orderBy('nom')->get()`) en cases à cocher — le bouton de soumission de la modale porte aussi `form="partage-form"`. Aucun JavaScript custom : uniquement l'attribut HTML5 `form` pour associer des éléments à un formulaire situé ailleurs dans le DOM. Validation (au moins un fichier, au moins un destinataire) faite côté serveur.
- Une ligne de fichier reçu par partage affiche un petit texte discret sous l'intitulé : "Partagé par {{ $fichier->partageOrigine?->partagePar?->nom_complet }}" si `partageOrigine` existe.
- Un lien "Partages effectués" dans l'en-tête de `archives/index.blade.php`, à côté de "Nouveau dossier"/"Ajouter un fichier", menant à `resources/views/archives/partages/index.blade.php` : tableau des partages envoyés (fichier, destinataire, date, bouton "Révoquer").

## Tests

`tests/Feature/ArchivePartageTest.php` :
- un utilisateur peut partager un fichier à un destinataire (copie créée, ligne `archive_partages` créée, notification envoyée)
- un utilisateur peut partager plusieurs fichiers à plusieurs destinataires en un seul envoi (produit copies × destinataires)
- partage refusé si aucun fichier sélectionné
- partage refusé si aucun destinataire sélectionné
- partage refusé (403) si un des fichiers sélectionnés n'appartient pas à l'utilisateur
- un utilisateur ne peut pas se sélectionner lui-même comme destinataire
- la copie reçue est indépendante : supprimer l'original ne supprime pas la copie ; supprimer la copie ne supprime pas l'original
- la copie reçue affiche la provenance ("Partagé par X")
- l'expéditeur voit ses partages envoyés sur `archives.partages.index`
- l'expéditeur peut révoquer un partage (l'entrée d'historique disparaît, la copie du destinataire reste intacte)
- un utilisateur autre que l'expéditeur ne peut pas révoquer un partage (403), y compris le destinataire lui-même

`database/factories/ArchivePartageFactory.php` — nouvelle factory.

## Hors périmètre (v1)

- Partage d'un dossier entier
- Sélection d'un dossier de destination pour la copie reçue (atterrit toujours à la racine)
- Prévention des partages en double
- Accès en direct révocable (option écartée au profit de la copie indépendante)
