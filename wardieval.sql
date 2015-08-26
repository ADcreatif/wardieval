-- phpMyAdmin SQL Dump
-- version 4.1.4
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Mer 26 Août 2015 à 02:00
-- Version du serveur :  5.6.15-log
-- Version de PHP :  5.5.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `wardieval`
--

-- --------------------------------------------------------

--
-- Structure de la table `buildings`
--

CREATE TABLE IF NOT EXISTS `buildings` (
  `id` int(11) NOT NULL,
  `name` tinytext NOT NULL,
  `description` varchar(500) NOT NULL,
  `price` int(11) NOT NULL,
  `building_time` tinyint(4) NOT NULL,
  `bonus_type` tinyint(4) NOT NULL,
  `bonus_amount` float NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `fleets`
--

CREATE TABLE IF NOT EXISTS `fleets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `fleet` varchar(256) NOT NULL,
  `arrival_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `target_id` (`target_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author` int(11) NOT NULL,
  `recipient` int(11) NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  `topic` tinytext NOT NULL,
  `send_date` datetime NOT NULL,
  `unread` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `author` (`recipient`),
  KEY `recipient` (`author`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=91 ;

--
-- Contenu de la table `messages`
--

INSERT INTO `messages` (`id`, `author`, `recipient`, `message`, `topic`, `send_date`, `unread`) VALUES
(31, 0, 1, '<table><thead><tr><th style="width:50%">Attaquant : Alan<br>flotte : 2  unité(s)</th><th style="width:50%">Défenseur : Poil<br>flotte : 5  unité(s)</th></tr><tr><td><ul><li>2 Paysous</li></ul></td><td><ul><li>5 Monstrozor</li></ul></td></tr></thead><tbody><tr><th colspan="2">Tour 1</th></tr><tr><td>Paysous : 2(-1)(200)</td><td>Monstrozor : 5(16)</td></tr></tbody><tbody><tr><th colspan="2">Tour 2</th></tr><tr><td>Paysous : 1(-1)(200)</td><td>Monstrozor : 5(8)</td></tr></tbody><tfooter><tr><td colspan=''2''>Vainqueur : Poil</td></tr></tfooter></table>', 'Rapport de combat (Poil)', '2015-08-23 21:44:45', 1),
(32, 0, 2, '<table><thead><tr><th style="width:50%">Attaquant : Alan<br>flotte : 2  unité(s)</th><th style="width:50%">Défenseur : Poil<br>flotte : 5  unité(s)</th></tr><tr><td><ul><li>2 Paysous</li></ul></td><td><ul><li>5 Monstrozor</li></ul></td></tr></thead><tbody><tr><th colspan="2">Tour 1</th></tr><tr><td>Paysous : 2(-1)(200)</td><td>Monstrozor : 5(16)</td></tr></tbody><tbody><tr><th colspan="2">Tour 2</th></tr><tr><td>Paysous : 1(-1)(200)</td><td>Monstrozor : 5(8)</td></tr></tbody><tfooter><tr><td colspan=''2''>Vainqueur : Poil</td></tr></tfooter></table>', 'Vous avez été attaqué (Alan)', '2015-08-23 21:44:45', 1),
(33, 0, 1, '<table><thead><tr><th style="width:50%">Attaquant : Alan<br>flotte : 346  unité(s)</th><th style="width:50%">Défenseur : Poil<br>flotte : 5  unité(s)</th></tr><tr><td><ul><li>165 Chevalier</li><li>134 Archer</li><li>29 Monstrozor</li><li>18 Paysous</li></ul></td><td><ul><li>5 Monstrozor</li></ul></td></tr></thead><tbody><tr><th colspan="2">Tour 1</th></tr><tr><td>Chevalier : 165(-1), Archer : 134, Monstrozor : 29, Paysous : 18(200)</td><td>Monstrozor : 5(-5)(3624)</td></tr></tbody><tfooter><tr><td colspan=''2''>Vainqueur : Alan</td></tr></tfooter></table>', 'Rapport de combat (Poil)', '2015-08-23 21:46:13', 1),
(34, 0, 2, '<table><thead><tr><th style="width:50%">Attaquant : Alan<br>flotte : 346  unité(s)</th><th style="width:50%">Défenseur : Poil<br>flotte : 5  unité(s)</th></tr><tr><td><ul><li>165 Chevalier</li><li>134 Archer</li><li>29 Monstrozor</li><li>18 Paysous</li></ul></td><td><ul><li>5 Monstrozor</li></ul></td></tr></thead><tbody><tr><th colspan="2">Tour 1</th></tr><tr><td>Chevalier : 165(-1), Archer : 134, Monstrozor : 29, Paysous : 18(200)</td><td>Monstrozor : 5(-5)(3624)</td></tr></tbody><tfooter><tr><td colspan=''2''>Vainqueur : Alan</td></tr></tfooter></table>', 'Vous avez été attaqué (Alan)', '2015-08-23 21:46:13', 1),
(35, 0, 1, '<table><thead><tr><th style="width:50%">Attaquant : Alan<br>flotte : 346  unité(s)</th><th style="width:50%">Défenseur : Poil<br>flotte : 5  unité(s)</th></tr><tr><td><ul><li>165 Chevalier</li><li>134 Archer</li><li>29 Monstrozor</li><li>18 Paysous</li></ul></td><td><ul><li>5 Monstrozor</li></ul></td></tr></thead><tbody><tr><th colspan="2">Tour 1</th></tr><tr><td>Chevalier : 165(-1), Archer : 134, Monstrozor : 29, Paysous : 18(200)</td><td>Monstrozor : 5(-5)(3624)</td></tr></tbody><tfooter><tr><td colspan=''2''>Vainqueur : Alan</td></tr></tfooter></table>', 'Rapport de combat (Poil)', '2015-08-23 21:47:48', 1),
(36, 0, 2, '<table><thead><tr><th style="width:50%">Attaquant : Alan<br>flotte : 346  unité(s)</th><th style="width:50%">Défenseur : Poil<br>flotte : 5  unité(s)</th></tr><tr><td><ul><li>165 Chevalier</li><li>134 Archer</li><li>29 Monstrozor</li><li>18 Paysous</li></ul></td><td><ul><li>5 Monstrozor</li></ul></td></tr></thead><tbody><tr><th colspan="2">Tour 1</th></tr><tr><td>Chevalier : 165(-1), Archer : 134, Monstrozor : 29, Paysous : 18(200)</td><td>Monstrozor : 5(-5)(3624)</td></tr></tbody><tfooter><tr><td colspan=''2''>Vainqueur : Alan</td></tr></tfooter></table>', 'Vous avez été attaqué (Alan)', '2015-08-23 21:47:48', 1),
(37, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-23 21:48:27', 1),
(38, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-23 21:48:27', 1),
(39, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-24 17:50:55', 1),
(40, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-24 17:50:56', 1),
(41, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-24 17:52:40', 1),
(42, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-24 17:52:40', 1),
(43, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-24 17:53:23', 1),
(44, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-24 17:53:24', 1),
(45, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-24 18:29:55', 1),
(46, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-24 18:29:55', 1),
(47, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-24 18:29:56', 1),
(48, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-24 18:29:56', 1),
(49, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-24 18:29:58', 1),
(50, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-24 18:29:58', 1),
(51, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-24 18:29:59', 1),
(52, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-24 18:29:59', 1),
(53, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-24 18:33:06', 1),
(54, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-24 18:33:06', 1),
(55, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-24 18:33:07', 1),
(56, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-24 18:33:07', 1),
(57, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-24 18:34:41', 1),
(58, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-24 18:34:42', 1),
(59, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-25 07:00:16', 1),
(60, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-25 07:00:17', 1),
(61, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-25 07:01:56', 1),
(62, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-25 07:01:56', 1),
(63, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-25 07:01:59', 1),
(64, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-25 07:01:59', 1),
(65, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-25 07:15:50', 1),
(66, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-25 07:15:51', 1),
(67, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-25 07:16:38', 1),
(68, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-25 07:16:38', 1),
(69, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-25 07:16:39', 1),
(70, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-25 07:16:39', 1),
(71, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-25 07:16:41', 1),
(72, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-25 07:16:41', 1),
(73, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-25 07:16:43', 1),
(74, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-25 07:16:43', 1),
(75, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-25 08:01:28', 1),
(76, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-25 08:01:28', 1),
(77, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-25 08:10:20', 1),
(78, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-25 08:10:21', 1),
(79, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-25 08:10:23', 1),
(80, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-25 08:10:23', 1),
(81, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-25 08:10:25', 1),
(82, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-25 08:10:25', 1),
(83, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-25 08:10:26', 1),
(84, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-25 08:10:26', 1),
(85, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-25 08:10:27', 1),
(86, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-25 08:10:27', 1),
(87, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-25 08:10:28', 1),
(88, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-25 08:10:28', 1),
(89, 0, 1, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Rapport de combat (Poil)', '2015-08-26 01:18:58', 1),
(90, 0, 2, 'Il n''y avait personne pour défendre cet empire, vous ressortez victorieux', 'Vous avez été attaqué (Alan)', '2015-08-26 01:18:59', 1);

-- --------------------------------------------------------

--
-- Structure de la table `queue`
--

CREATE TABLE IF NOT EXISTS `queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `finished_at` datetime DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `unit_id` (`unit_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Contenu de la table `queue`
--

INSERT INTO `queue` (`id`, `unit_id`, `user_id`, `finished_at`, `quantity`) VALUES
(9, 4, 1, '2015-08-26 07:40:55', 12),
(14, 2, 1, '2015-08-26 05:57:05', 3),
(15, 4, 1, '2015-08-26 01:58:11', 6);

-- --------------------------------------------------------

--
-- Structure de la table `units`
--

CREATE TABLE IF NOT EXISTS `units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` tinytext NOT NULL,
  `description` varchar(500) NOT NULL,
  `price` int(11) NOT NULL,
  `building_time` smallint(4) DEFAULT NULL,
  `damage` smallint(4) NOT NULL,
  `life` smallint(4) NOT NULL,
  `image_name` tinytext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Contenu de la table `units`
--

INSERT INTO `units` (`id`, `name`, `description`, `price`, `building_time`, `damage`, `life`, `image_name`) VALUES
(0, 'Poilopat', 'il est tout rikiki, mais en masse peut faire des dégats', 4, 3, 2, 10, 'poilopat.jpg'),
(1, 'Paysous', 'avec ça fourche, il ne sert pas a grand chose. Cela dit, que ferait-on sans eux ?', 160, 12, 8, 100, 'paysous.jpg'),
(2, 'Archer', 'L''archer est l''as de la dissimulation, ce qui le rends difficile à toucher. Mais ses dégats sont assez limités', 400, 30, 5, 150, 'archer.jpg'),
(3, 'Chevalier', 'Avec son épée il va tout casser', 800, 60, 10, 250, 'chevalier.jpg'),
(4, 'Monstrozor', 'Il est grand, il est fort !', 10000, 750, 40, 500, 'monstrozor.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `units_owned`
--

CREATE TABLE IF NOT EXISTS `units_owned` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`unit_id`,`user_id`),
  KEY `user_id` (`user_id`),
  KEY `unit_id` (`unit_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=125 ;

--
-- Contenu de la table `units_owned`
--

INSERT INTO `units_owned` (`id`, `unit_id`, `user_id`, `quantity`) VALUES
(14, 3, 3, 3),
(21, 4, 2, 0),
(86, 0, 3, 1900),
(110, 0, 1, 76),
(111, 4, 1, 81),
(112, 2, 1, 773),
(113, 1, 1, 55),
(114, 1, 3, 600),
(118, 3, 1, 458),
(119, 0, 4, 2),
(120, 1, 4, 20),
(122, 2, 4, 20);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(250) NOT NULL,
  `pass` varchar(250) NOT NULL,
  `last_refresh` datetime NOT NULL,
  `ressources` int(11) NOT NULL DEFAULT '20000',
  `score` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`id`, `pseudo`, `pass`, `last_refresh`, `ressources`, `score`) VALUES
(1, 'alan', '91e38e63b890fbb214c8914809fde03c73e7f24d', '2015-08-26 01:55:18', 13668, 570),
(2, 'poil', '9517539441864504376c7c0bd3e8fa20c1d5495f', '2015-08-26 01:18:58', 1426392, 2),
(3, 'test', 'test', '2015-08-18 02:54:02', 1439879242, 99999999),
(4, 'croute', '222c94f7f5b4a028180db921fb0774d0614eb8f5', '2015-08-21 19:58:20', 176159, 12);

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `fleets`
--
ALTER TABLE `fleets`
  ADD CONSTRAINT `fleets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fleets_ibfk_2` FOREIGN KEY (`target_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Contraintes pour la table `queue`
--
ALTER TABLE `queue`
  ADD CONSTRAINT `queue_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `queue_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Contraintes pour la table `units_owned`
--
ALTER TABLE `units_owned`
  ADD CONSTRAINT `units_owned_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `units_owned_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
