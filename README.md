# Plateforme DCUS

Application de gestion administrative interne pour la Direction de la Coopération Universitaire et Scientifique (DCUS) — Ministère de l'Enseignement Supérieur du Bénin.

Gère les CODIR (comités de direction internes), les réunions, les décisions (notes de suivi) et les accords de coopération, avec export PDF/CSV et rôles d'accès (admin, secrétaire, agent).

## Stack technique

- Laravel 13 (PHP 8.3+)
- MySQL
- Tailwind CSS v4 + Vite
- Blade (pas de framework JS front)
- `barryvdh/laravel-dompdf` pour les exports PDF
- `phpoffice/phpspreadsheet` pour les exports CSV
- Brevo (API) pour l'envoi d'emails transactionnels

## Installation

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Configurer `.env` :

```env
DB_CONNECTION=mysql
DB_DATABASE=dcus_db
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=brevo
BREVO_API_KEY=xkeysib-...
MAIL_FROM_ADDRESS="votre-expediteur-verifie@exemple.bj"
```

Base de données — voir [`database/IMPORT_DB.md`](database/IMPORT_DB.md) pour importer le dump existant, ou repartir de zéro :

```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

Lancer l'application :

```bash
composer run dev   # serveur + queue + logs + vite, en parallèle
# ou individuellement :
php artisan serve
npm run dev
```

## Tests

```bash
composer test                    # ou : php artisan test
php artisan test --filter=Nom    # un seul test
vendor/bin/pint                  # formatage du code
vendor/bin/pint --test           # vérifier sans modifier
```

Un workflow GitHub Actions (`.github/workflows/tests.yml`) exécute les tests et le contrôle de style à chaque push/PR.

## Rôles

| Rôle | Droits |
|---|---|
| `admin` | Accès complet, gestion des utilisateurs, administration des rapports/accès CODIR |
| `secretaire` | Création/modification des CODIR, réunions, décisions, accords |
| `agent` | Consultation uniquement |

Le contrôle d'accès est géré via des [Policies Laravel](https://laravel.com/docs/authorization) (`app/Policies/`) et le middleware `role:admin` pour les routes réservées aux administrateurs.

## Documentation

- [`database/IMPORT_DB.md`](database/IMPORT_DB.md) — import de la base de données
- [`MANUEL_UTILISATEUR.html`](MANUEL_UTILISATEUR.html) — manuel utilisateur final
- [`CLAUDE.md`](CLAUDE.md) — architecture et conventions du projet, pour le développement assisté par IA
