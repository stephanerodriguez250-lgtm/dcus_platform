# Archives — design

## Contexte

L'application a aujourd'hui deux sections métier principales : Réunions et Accords (plus CODIR et Décisions). On ajoute une troisième section, **Archives**, qui permet à chaque utilisateur de classer des fichiers dans une arborescence de dossiers façon explorateur de fichiers Windows (dossiers, sous-dossiers imbriqués à l'infini, icônes par type de fichier).

Contrairement aux autres sections (partagées entre tous les utilisateurs autorisés), Archives est un **espace strictement privé par utilisateur** : chaque utilisateur a sa propre arborescence, invisible aux autres (y compris les admins).

Ce document couvre l'ensemble de la fonctionnalité Archives (dossiers + fichiers). Le premier incrément d'implémentation ciblera le formulaire d'ajout de fichier et le modèle de données sous-jacent.

## Décisions actées

- **Portée** : espace privé par utilisateur, pas de partage, pas de bypass admin.
- **Champ "numéro"** : texte libre saisi par l'utilisateur (référence de courrier, numéro de dossier physique, etc.), pas de génération automatique.
- **Types de fichiers acceptés** : documents courants — PDF, Word (doc/docx), Excel (xls/xlsx), images (jpg/jpeg/png) — taille max 10 Mo, aligné sur la validation déjà utilisée pour les rapports CODIR (`CodirController::uploadRapport`).
- **Actions v1** : créer/renommer/supprimer pour les dossiers et les fichiers. Pas de déplacement par glisser-déposer dans cette version (pourra être ajouté plus tard).
- **Champ "date et heure"** : rempli automatiquement par la plateforme à l'enregistrement — implémenté via `created_at`, affiché dans l'interface ("Ajouté le ..."), ce n'est pas un champ de saisie du formulaire.

## Modèle de données

### `archive_folders`

| colonne | type | notes |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | bigint FK → users | propriétaire, `cascadeOnDelete` |
| `parent_id` | bigint FK → archive_folders, nullable | auto-référence ; `null` = dossier à la racine |
| `nom` | string | nom du dossier |
| timestamps | | |

Contrainte unique composite `(user_id, parent_id, nom)` : deux dossiers de même nom ne peuvent coexister au même niveau chez un même utilisateur.

### `archive_fichiers`

| colonne | type | notes |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | bigint FK → users | propriétaire, `cascadeOnDelete` |
| `folder_id` | bigint FK → archive_folders, nullable | `null` = fichier à la racine de l'espace de l'utilisateur |
| `intitule` | string | requis |
| `numero` | string, nullable | texte libre |
| `description` | text, nullable | |
| `nom_fichier` | string | nom original du fichier uploadé |
| `chemin_fichier` | string | chemin sur `Storage::disk('public')` |
| `type_fichier` | string | extension |
| `taille` | unsigned integer | octets |
| timestamps | | `created_at` sert de "date et heure d'enregistrement" |

## Modèles Eloquent

- `App\Models\ArchiveFolder` — relations `parent()`, `children()`, `fichiers()`, `proprietaire()` (belongsTo User). Méthode `cheminComplet()` ou accessor pour reconstruire le fil d'Ariane en remontant les `parent`.
- `App\Models\ArchiveFichier` — relations `dossier()` (belongsTo ArchiveFolder), `proprietaire()` (belongsTo User). Accessor `taille_formatee` (même logique que `CodirRapport::getTailleFormateeAttribute`). Accessor pour l'icône Bootstrap Icons selon `type_fichier` (pdf/doc/docx/xls/xlsx/jpg/jpeg/png → icône correspondante, défaut générique sinon).

## Suppression en cascade

Supprimer un dossier supprime récursivement ses sous-dossiers et leurs fichiers, y compris sur le disque (`Storage::disk('public')->delete(...)` pour chaque fichier concerné) — pas seulement en base. Implémenté via une méthode récursive dans le contrôleur ou un observer sur `ArchiveFolder::deleting`.

## Routes / Contrôleur

Nouveau `App\Http\Controllers\ArchiveController`, sous le middleware `auth` existant :

```
GET    /archives                          index (racine de l'espace de l'utilisateur connecté)
GET    /archives/{dossier}                index (contenu d'un dossier, fil d'Ariane)
POST   /archives/dossiers                 store (créer un dossier ; params: parent_id nullable, nom)
PATCH  /archives/dossiers/{dossier}       update (renommer)
DELETE /archives/dossiers/{dossier}       destroy (suppression en cascade)

GET    /archives/fichiers/create          create (formulaire d'ajout ; query: dossier_id nullable)
POST   /archives/fichiers                 store
PATCH  /archives/fichiers/{fichier}       update (modifier intitulé/numéro/description)
DELETE /archives/fichiers/{fichier}       destroy
GET    /archives/fichiers/{fichier}/download   download
```

Toutes ces routes vivent dans le groupe `auth` déjà existant dans `routes/web.php`, pas de middleware de rôle (accessible à tous les utilisateurs connectés, chacun ne voyant que son propre espace).

## Autorisation

`ArchiveFolderPolicy` et `ArchiveFichierPolicy` (auto-découvertes par convention comme les policies existantes) : chaque méthode (`view`, `update`, `delete`) vérifie `$folder->user_id === $user->id` (ou `$fichier->user_id === $user->id`). Pas de règle `canManage()`/rôle, pas de bypass admin — cohérent avec "espace strictement privé".

Le `store` (création) est ouvert à tout utilisateur authentifié pour son propre espace ; le contrôleur force `user_id = Auth::id()` à la création, jamais depuis l'input.

## Formulaire d'ajout de fichier

Champs affichés :
- **Intitulé** (texte, requis)
- **Numéro** (texte, optionnel)
- **Description** (textarea, optionnel)
- **Fichier** (input file, requis, validation `mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240`)
- Un champ caché `dossier_id` porte le dossier courant (contexte de navigation), pré-rempli par la page qui a ouvert le formulaire.

Pas de champ "date et heure" dans le formulaire — valeur automatique (`created_at`), affichée après coup dans la liste du dossier.

## Interface

- Nouvelle entrée "Archives" dans la barre latérale (`resources/views/layouts/app.blade.php`), section "Gestion", icône `bi-folder`, active sur `request()->routeIs('archives.*')`.
- Vue de navigation (`resources/views/archives/index.blade.php`) : fil d'Ariane cliquable en haut (Racine > Dossier A > Sous-dossier B), liste des sous-dossiers du dossier courant (icône `bi-folder-fill`, clic = navigation), puis liste des fichiers du dossier courant (icône selon type, intitulé, numéro, taille formatée, date d'ajout, actions renommer/supprimer/télécharger).
- Boutons "Nouveau dossier" (modal avec champ nom) et "Ajouter un fichier" (lien vers le formulaire dédié) en haut de la vue.
- Formulaire d'ajout de fichier (`resources/views/archives/fichiers/create.blade.php`) — page dédiée (pas un modal, cohérent avec les formulaires de création existants comme `reunions/create.blade.php`).

## Tests

`tests/Feature/ArchiveTest.php` :
- un utilisateur peut créer un dossier à la racine et un sous-dossier imbriqué
- deux dossiers de même nom au même niveau chez le même utilisateur sont rejetés (contrainte unique / validation)
- un utilisateur peut uploader un fichier dans un dossier (intitulé/numéro/description/fichier), le fichier apparaît avec sa date d'ajout automatique
- upload rejeté si l'intitulé est manquant ou le type de fichier n'est pas autorisé
- un utilisateur peut renommer/supprimer son propre dossier ou fichier
- supprimer un dossier supprime récursivement ses sous-dossiers et fichiers (en base et sur le disque)
- **isolation stricte** : l'utilisateur A ne peut ni voir, ni renommer, ni supprimer, ni télécharger un dossier/fichier appartenant à l'utilisateur B (403)

`database/factories/ArchiveFolderFactory.php`, `database/factories/ArchiveFichierFactory.php` — nouvelles factories suivant le même style que les factories existantes.

## Hors périmètre (v1)

- Déplacement de dossiers/fichiers par glisser-déposer
- Partage d'un dossier/fichier entre utilisateurs
- Quota de stockage par utilisateur
- Prévisualisation des fichiers dans le navigateur (juste téléchargement)
