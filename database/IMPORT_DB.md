# Base de données — Guide d'importation

## Fichier fourni
- `dcus_db.sql` — Export complet de la base de données DCUS

---

## Option 1 — Importer via phpMyAdmin (recommandé)

1. Démarrer **XAMPP / WAMP / Laragon** et s'assurer que MySQL est actif
2. Ouvrir **phpMyAdmin** dans le navigateur : `http://localhost/phpmyadmin`
3. Créer une nouvelle base de données :
   - Cliquer sur **"Nouvelle base de données"**
   - Nom : `dcus_db`
   - Interclassement : `utf8mb4_unicode_ci`
   - Cliquer sur **"Créer"**
4. Cliquer sur la base `dcus_db` dans le menu gauche
5. Aller sur l'onglet **"Importer"**
6. Cliquer sur **"Choisir un fichier"** → sélectionner `dcus_db.sql`
7. Cliquer sur **"Importer"**

---

## Option 2 — Importer via la ligne de commande

```bash
mysql -u root -p -e "CREATE DATABASE dcus_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p dcus_db < database/dcus_db.sql
```

---

## Option 3 — Repartir de zéro avec les migrations Laravel

Si vous préférez ne pas utiliser le fichier SQL et recréer la base proprement :

```bash
php artisan migrate:fresh --seed
```

Cette commande recrée toutes les tables et insère les données initiales.

---

## Configurer le fichier .env

Après l'importation, ouvrir le fichier `.env` à la racine du projet et vérifier :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dcus_db
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

---

## Comptes utilisateurs par défaut

| Rôle | Email | Mot de passe |
|---|---|---|
| Administratrice | directrice@dcus.bj | Dcus2024! |
| Secrétaire | secretaire@dcus.bj | Dcus2024! |
| Agent | j.dossou@dcus.bj | Dcus2024! |

> ⚠️ Changez ces mots de passe immédiatement après la première connexion !

---

## Démarrer le projet

```bash
composer install
php artisan config:clear
php artisan serve
```

Accéder à la plateforme : **http://localhost:8000**
