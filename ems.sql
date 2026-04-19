-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 19 avr. 2026 à 22:30
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
-- Base de données : `ems`
--

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `avis_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `note` int(11) DEFAULT NULL CHECK (`note` between 1 and 5),
  `commentaire` text DEFAULT NULL,
  `date_avis` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('en_attente','approuve','rejete') DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `categorie_id` int(11) NOT NULL,
  `categorie_nom` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`categorie_id`, `categorie_nom`, `description`) VALUES
(4, 'SACS', ''),
(12, 'VÊTEMENTS ', ''),
(16, 'ORDINATEUR', ''),
(17, 'CHEMISE', '');

-- --------------------------------------------------------

--
-- Structure de la table `commandes`
--

CREATE TABLE `commandes` (
  `commande_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_commande` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `statut` enum('en_attente','confirmee','expediee','livree','annulee') DEFAULT 'en_attente',
  `statut_paiement` enum('en_attente','paye','non_paye') DEFAULT 'en_attente',
  `methode_paiement` varchar(50) DEFAULT NULL,
  `recu_paiement` varchar(255) DEFAULT NULL,
  `promo_id` int(11) DEFAULT NULL,
  `reduction_appliquee` decimal(10,2) DEFAULT 0.00,
  `adresse_livraison` text DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commandes`
--

INSERT INTO `commandes` (`commande_id`, `user_id`, `date_commande`, `total`, `statut`, `statut_paiement`, `methode_paiement`, `recu_paiement`, `promo_id`, `reduction_appliquee`, `adresse_livraison`, `notes`) VALUES
(16, 5, '2026-04-15 23:56:19', 7000.00, 'en_attente', 'en_attente', 'wave', NULL, NULL, 0.00, NULL, NULL),
(17, 5, '2026-04-16 19:18:59', 200.00, 'annulee', 'non_paye', 'wave', NULL, NULL, 0.00, NULL, NULL),
(18, 5, '2026-04-16 19:23:15', 200.00, 'en_attente', 'en_attente', 'wave', NULL, NULL, 0.00, NULL, NULL),
(19, 5, '2026-04-16 19:25:41', 200.00, 'en_attente', 'en_attente', 'wave', NULL, NULL, 0.00, NULL, NULL),
(20, 5, '2026-04-16 19:28:41', 200.00, 'annulee', '', 'mtn_money', NULL, NULL, 0.00, NULL, NULL),
(21, 5, '2026-04-16 19:32:55', 200.00, 'annulee', '', 'orange_money', NULL, NULL, 0.00, NULL, NULL),
(22, 5, '2026-04-16 20:13:08', 5000.00, 'annulee', 'non_paye', 'wave', NULL, NULL, 0.00, NULL, NULL),
(23, 5, '2026-04-16 20:13:52', 5000.00, 'annulee', 'non_paye', 'wave', NULL, NULL, 0.00, NULL, NULL),
(24, 5, '2026-04-16 20:18:00', 5000.00, 'annulee', 'non_paye', 'wave', NULL, NULL, 0.00, NULL, NULL),
(25, 5, '2026-04-16 20:19:30', 5000.00, 'livree', 'paye', 'wave', NULL, NULL, 0.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `commande_details`
--

CREATE TABLE `commande_details` (
  `detail_id` int(11) NOT NULL,
  `commande_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  `sous_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande_details`
--

INSERT INTO `commande_details` (`detail_id`, `commande_id`, `product_id`, `quantite`, `prix_unitaire`, `sous_total`) VALUES
(32, 16, 16, 1, 7000.00, 7000.00),
(33, 17, 18, 2, 100.00, 200.00),
(34, 18, 18, 2, 100.00, 200.00),
(35, 19, 18, 2, 100.00, 200.00),
(36, 20, 18, 2, 100.00, 200.00),
(37, 21, 18, 2, 100.00, 200.00),
(38, 22, 13, 1, 5000.00, 5000.00),
(39, 23, 13, 1, 5000.00, 5000.00),
(40, 24, 13, 1, 5000.00, 5000.00),
(41, 25, 13, 1, 5000.00, 5000.00);

-- --------------------------------------------------------

--
-- Structure de la table `favoris`
--

CREATE TABLE `favoris` (
  `favori_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `date_ajout` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `titre` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` enum('commande','promo','systeme','stock') DEFAULT 'systeme',
  `est_lu` tinyint(1) DEFAULT 0,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL COMMENT 'Numéro de commande',
  `user_id` int(11) NOT NULL,
  `total_montant` decimal(10,2) NOT NULL,
  `statut` varchar(50) NOT NULL DEFAULT '''EN_ATTANTE'',''VALIDER'',''ECHEC''',
  `creation_com` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `panier`
--

CREATE TABLE `panier` (
  `panier_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1,
  `date_ajout` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `disponible` varchar(255) NOT NULL,
  `stock` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `prix_promo` decimal(10,2) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`product_id`, `categorie_id`, `nom`, `prix`, `description`, `disponible`, `stock`, `image`, `prix_promo`, `date_creation`) VALUES
(12, 4, 'SAC BANDOULIÈRE', 7000.00, 'POUR TOUT USAGES', '1', 8, 'prod_69d947551b1351.41672648.png', NULL, '2026-04-12 01:18:49'),
(13, 12, 'Ensemble unique culotte', 7000.00, 'juiqgkneoihuhkjdojamsbhgkjkbjhcgysvciuhkmn;lj', '1', 3, 'prod_69d94a45b99352.42076599.png', 5000.00, '2026-04-12 01:18:49'),
(16, 12, 'Ensemble unique pantalons ', 7000.00, '', '1', 4, 'prod_69d94c67635541.50670296.png', NULL, '2026-04-12 01:18:49'),
(18, 4, 'sac luxe', 10000.00, 'sac tendance pour femme ', '1', 4, 'prod_69daf703ee9e51.23900865.png', 8000.00, '2026-04-12 01:36:04'),
(19, 4, 'konan', 10000.00, 'klhkefbkloh uofuojpjuijwelfhuwjenfihe', '1', 0, 'prod_69dc0f83b46c16.65349430.png', NULL, '2026-04-12 21:32:51'),
(22, 16, 'HP', 250000.00, 'ORDINATEUR HP ENVY 360deg AVEC UNE CAPACITÉ 500 GIGA DE DISQUE DUR ET UNE RAM INTÉGRÉ DE 16 GIGA ET UNE AUTONOMIE DE 4H DE TRAVAIL\r\nADAPTER POUR UN TRAVAIL RIGOUREUX ET LOGICIEL LOURD', '1', 0, 'prod_69dc270baeae95.44493033.jpg', 200000.00, '2026-04-12 23:13:15'),
(23, 17, '🧥 Chemise Homme Manche Longue – Bleu Élégant', 5000.00, 'Élégance assurée : Sa couleur bleu profond apporte une touche raffinée et professionnelle.\r\n\r\nConfort optimal : Confectionnée dans un tissu doux et respirant, idéale pour rester à l’aise toute la journée.\r\n\r\nPolyvalence : Se porte aussi bien au bureau qu’', '1', 0, 'prod_69de62bcc4b051.61863984.webp', 3500.00, '2026-04-14 15:52:28');

-- --------------------------------------------------------

--
-- Structure de la table `promos`
--

CREATE TABLE `promos` (
  `promo_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `reduction_type` enum('pourcentage','fixe') DEFAULT 'pourcentage',
  `reduction_valeur` decimal(10,2) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `usage_max` int(11) DEFAULT 1,
  `usage_actuel` int(11) DEFAULT 0,
  `montant_min` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` varchar(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `nom` varchar(25) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`user_id`, `nom`, `prenom`, `email`, `telephone`, `adresse`, `password`, `date_creation`, `role`) VALUES
(5, 'Élisée Lagou', '', 'lagouelisee09@gmail.com', '0705448939', '', '$2y$10$cHp8OBbnliubvkzJn5SxyO7P9m59LJt5hxmo2kUuhfolAZWOiED6K', '2026-04-12 21:05:11', 'user'),
(6, 'Administrateur', '', 'ems@shop.com', '0596029562', NULL, '$2y$10$cHp8OBbnliubvkzJn5SxyO7P9m59LJt5hxmo2kUuhfolAZWOiED6K', '2026-04-12 21:30:52', 'admin');

-- --------------------------------------------------------

--
-- Structure de la table `wave_paiement`
--

CREATE TABLE `wave_paiement` (
  `id` int(11) NOT NULL,
  `commande_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `paiement_id` varchar(100) DEFAULT NULL,
  `montant` decimal(10,2) NOT NULL,
  `devise` varchar(10) DEFAULT 'XOF',
  `reference_wave` varchar(100) NOT NULL,
  `statut` enum('en_attente','reussi','echoue','annule','rembourse') DEFAULT 'en_attente',
  `date_paiement` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_nom` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`avis_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`categorie_id`);

--
-- Index pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`commande_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `promo_id` (`promo_id`);

--
-- Index pour la table `commande_details`
--
ALTER TABLE `commande_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `commande_id` (`commande_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD PRIMARY KEY (`favori_id`),
  ADD UNIQUE KEY `unique_favori` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `panier`
--
ALTER TABLE `panier`
  ADD PRIMARY KEY (`panier_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `categorie_id` (`categorie_id`);

--
-- Index pour la table `promos`
--
ALTER TABLE `promos`
  ADD PRIMARY KEY (`promo_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Index pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `wave_paiement`
--
ALTER TABLE `wave_paiement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_commande` (`commande_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `avis_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `categorie_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `commandes`
--
ALTER TABLE `commandes`
  MODIFY `commande_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT pour la table `commande_details`
--
ALTER TABLE `commande_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT pour la table `favoris`
--
ALTER TABLE `favoris`
  MODIFY `favori_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Numéro de commande';

--
-- AUTO_INCREMENT pour la table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `panier`
--
ALTER TABLE `panier`
  MODIFY `panier_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pour la table `promos`
--
ALTER TABLE `promos`
  MODIFY `promo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `wave_paiement`
--
ALTER TABLE `wave_paiement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `commandes`
--
ALTER TABLE `commandes`
  ADD CONSTRAINT `commandes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commandes_ibfk_2` FOREIGN KEY (`promo_id`) REFERENCES `promos` (`promo_id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `commande_details`
--
ALTER TABLE `commande_details`
  ADD CONSTRAINT `commande_details_ibfk_1` FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`commande_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commande_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `favoris`
--
ALTER TABLE `favoris`
  ADD CONSTRAINT `favoris_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favoris_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `panier`
--
ALTER TABLE `panier`
  ADD CONSTRAINT `panier_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `panier_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`categorie_id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `wave_paiement`
--
ALTER TABLE `wave_paiement`
  ADD CONSTRAINT `fk_commande` FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`commande_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
