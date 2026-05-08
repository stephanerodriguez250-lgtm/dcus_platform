-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 01 avr. 2026 à 03:46
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `dcus_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `accords`
--

CREATE TABLE `accords` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `titre` varchar(255) NOT NULL,
  `institution_partenaire` varchar(255) NOT NULL,
  `pays_partenaire` varchar(255) NOT NULL,
  `universite_beneficiaire` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date_identification` date DEFAULT NULL,
  `date_signature` date DEFAULT NULL,
  `date_expiration` date DEFAULT NULL,
  `statut` enum('identifie','en_negotiation','signe','en_execution','cloture','abandonne') NOT NULL DEFAULT 'identifie',
  `reunion_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `accord_historiques`
--

CREATE TABLE `accord_historiques` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `accord_id` bigint(20) UNSIGNED NOT NULL,
  `ancien_statut` varchar(255) DEFAULT NULL,
  `nouveau_statut` varchar(255) NOT NULL,
  `commentaire` text DEFAULT NULL,
  `modifie_par` bigint(20) UNSIGNED NOT NULL,
  `date_modification` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2024_01_01_000000_create_users_table', 1),
(4, '2024_01_01_000001_create_reunions_table', 1),
(5, '2024_01_01_000002_create_accords_table', 1),
(6, '2024_01_01_000003_create_accord_historiques_table', 1),
(7, '2026_03_30_032130_create_sessions_table', 2);

-- --------------------------------------------------------

--
-- Structure de la table `reunions`
--

CREATE TABLE `reunions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `titre` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `heure` time DEFAULT NULL,
  `lieu` varchar(255) NOT NULL,
  `ordre_du_jour` text NOT NULL,
  `compte_rendu` text DEFAULT NULL,
  `statut` enum('planifiee','en_cours','terminee','annulee') NOT NULL DEFAULT 'planifiee',
  `convocateur` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reunions`
--

INSERT INTO `reunions` (`id`, `titre`, `date`, `heure`, `lieu`, `ordre_du_jour`, `compte_rendu`, `statut`, `convocateur`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'jkkkn', '2026-03-06', '02:45:00', 'JNMM', 'JLNMMMM%N', NULL, 'planifiee', 'Ministère de l\'Enseignement Supérieur et de la Recherche Scientifique', 1, '2026-03-30 02:41:47', '2026-03-30 02:41:47'),
(2, 'Mise à jour', '2026-04-02', '09:00:00', 'Salle de conférence', 'Discuter des objectifs', NULL, 'planifiee', 'Ministère de l\'Enseignement Supérieur et de la Recherche Scientifique', 1, '2026-03-31 00:26:02', '2026-03-31 00:26:02');

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0PtsMzuClZCJvqZzopFrtw1oyXjSGuxq3x7QLdZF', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', 'eyJfdG9rZW4iOiJ5dHpVTWdWQXNJR1JlYmN6ZnJRU00zWFU5enJqanFQRG1aSGlSdTgzIiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9leHBvcnRzXC9yZXVuaW9uc1wvcGRmIiwicm91dGUiOiJleHBvcnRzLnJldW5pb25zLnBkZiJ9fQ==', 1774876933),
('3Iy2SDacP5bcGURVempIns7IgGU1hicOHCn7T7Ku', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', 'eyJfdG9rZW4iOiJ6aFhUOUlPUmtKUjZFTW5yT0tuQW8xbEs1N0pQcExGbXFqdXQxeVd5IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDAiLCJyb3V0ZSI6ImRhc2hib2FyZCJ9LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6M30=', 1774842643),
('5wYeAzaaXSEgPUTDX532aiIj8n0G2QglZ20du85x', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', 'eyJfdG9rZW4iOiJvOXdCSUFQZUo4RTcwN2F3S09Dc1hsYzI4TU5GZWhPQU9XdTVoUzYyIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL3Byb2ZpbCIsInJvdXRlIjoicHJvZmlsZS5lZGl0In0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxfQ==', 1774858555),
('eSdtdIHqvsqC3RKJprbjWHf7Nt84dTkMjq1OY7ON', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', 'eyJfdG9rZW4iOiJvNHhkU2JGOE5CYk1lSUVRUVBONlFjMG5kQXRLTmw5V3RNVWpKSmN5IiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL3JldW5pb25zIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1774920263),
('s62f6f6wAU0ewGiJpAUp95FniSHIoYxxPujOBAky', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', 'eyJfdG9rZW4iOiJEbXZPdGNtQnVaRGJmZ3o1WjM0RUFFTzVVeXMwaTdlZ3pQQjZQUVhDIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL2xvZ2luIiwicm91dGUiOiJsb2dpbiJ9LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=', 1774920476),
('SDctjSiuccypeweE3oXCbdnlBC8c2sqOfL2obIiK', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', 'eyJfdG9rZW4iOiJDa2lNQ2hYMzRCaGJadUV2dGhRQUhxZnk1eThNeU0ycHJQdzRMWkdWIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL3Byb2ZpbCJ9LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL2xvZ2luIiwicm91dGUiOiJsb2dpbiJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1774866255),
('Sp2AxDH9OENE7iAjelRwPnl5Pavyo8LfvdbF6t5x', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', 'eyJfdG9rZW4iOiJQNkFETHc2MDZvdUFOUU1uN0J4alpMOEY2MlZxVDFIR0FLd2xaSkdOIiwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC91dGlsaXNhdGV1cnMiLCJyb3V0ZSI6InV0aWxpc2F0ZXVycy5pbmRleCJ9fQ==', 1774920491),
('UaZoLFD5SQc2NVigmN8q0mm228E1QLumAc85wfd9', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 Edg/146.0.0.0', 'eyJfdG9rZW4iOiJRRlc5M1JrTThRbWEyYks4WVNqb21UWERTcmZ5bFBqUlNQZEVBMXRGIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL2xvZ2luIiwicm91dGUiOiJsb2dpbiJ9fQ==', 1774975575);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','secretaire','agent') NOT NULL DEFAULT 'agent',
  `telephone` varchar(255) DEFAULT NULL,
  `poste` varchar(255) DEFAULT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `password`, `role`, `telephone`, `poste`, `actif`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'AGOSSOU', 'Marie', 'directrice@dcus.bj', '$2y$12$u15EBJePWrogyc/PXxcSr.learRIQEw1OiCxubvlDNsvUiREy7MVS', 'admin', NULL, 'Directrice DCUS', 1, NULL, '2026-03-30 02:12:14', '2026-03-30 02:12:14'),
(2, 'HOUNTON', 'Isabelle', 'secretaire@dcus.bj', '$2y$12$LTYksqP8P.PpyHGI404Is.Dz2S7ZU5DDDBt78oen7m3SREJztJ/a.', 'secretaire', NULL, 'Secrétaire de Direction', 1, NULL, '2026-03-30 02:12:14', '2026-03-30 02:12:14'),
(3, 'DOSSOU', 'Jean', 'j.dossou@dcus.bj', '$2y$12$h5xk.sFwhglhutu.zCwysO/93tMsXptyx.cmUOcdksNff4oraWe1i', 'agent', NULL, 'Chargé de Coopération', 1, NULL, '2026-03-30 02:12:15', '2026-03-30 02:12:15'),
(4, 'GBEDO', 'Fatima', 'f.gbedo@dcus.bj', '$2y$12$gGQgk5MKNn9pMtDFUKAgSOy95428Xr3F9eu/8xzO.A73gwCtdB/C6', 'agent', NULL, 'Chargée de Coopération', 1, NULL, '2026-03-30 02:12:15', '2026-03-30 02:12:15'),
(5, 'AHOUNOU', 'Pierre', 'p.ahounou@dcus.bj', '$2y$12$bDJXXEXjED.bhbWTU/jyBuLaKhT.99PWqlhbrwsvvoYNKmCQ/y3NS', 'agent', NULL, 'Chargé de Suivi', 1, NULL, '2026-03-30 02:12:16', '2026-03-30 02:12:16');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `accords`
--
ALTER TABLE `accords`
  ADD PRIMARY KEY (`id`),
  ADD KEY `accords_reunion_id_foreign` (`reunion_id`),
  ADD KEY `accords_created_by_foreign` (`created_by`);

--
-- Index pour la table `accord_historiques`
--
ALTER TABLE `accord_historiques`
  ADD PRIMARY KEY (`id`),
  ADD KEY `accord_historiques_accord_id_foreign` (`accord_id`),
  ADD KEY `accord_historiques_modifie_par_foreign` (`modifie_par`);

--
-- Index pour la table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Index pour la table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Index pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Index pour la table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Index pour la table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `reunions`
--
ALTER TABLE `reunions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reunions_created_by_foreign` (`created_by`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `accords`
--
ALTER TABLE `accords`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `accord_historiques`
--
ALTER TABLE `accord_historiques`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `reunions`
--
ALTER TABLE `reunions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `accords`
--
ALTER TABLE `accords`
  ADD CONSTRAINT `accords_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `accords_reunion_id_foreign` FOREIGN KEY (`reunion_id`) REFERENCES `reunions` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `accord_historiques`
--
ALTER TABLE `accord_historiques`
  ADD CONSTRAINT `accord_historiques_accord_id_foreign` FOREIGN KEY (`accord_id`) REFERENCES `accords` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `accord_historiques_modifie_par_foreign` FOREIGN KEY (`modifie_par`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `reunions`
--
ALTER TABLE `reunions`
  ADD CONSTRAINT `reunions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
