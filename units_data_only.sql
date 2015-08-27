-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Ven 28 Août 2015 à 00:12
-- Version du serveur: 5.5.44-0ubuntu0.14.04.1
-- Version de PHP: 5.5.28-1+deb.sury.org~trusty+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `wardieval`
--

--
-- Contenu de la table `units`
--

INSERT INTO `units` (`id`, `name`, `description`, `price`, `building_time`, `damage`, `life`, `image_name`) VALUES
(0, 'Poilopat', 'Il tout moche et poilu. Il ne semble pas très utile, mais en masse, il peut faire des ravages', 4, 3, 2, 10, 'poilopat.jpg'),
(1, 'Paysous', 'Avec sa fourche, il ne sert pas a grand chose. Cela dit, que ferait-on sans eux ?', 160, 12, 8, 100, 'paysous.jpg'),
(2, 'Archer', 'L''archer est l''as de la dissimulation, ce qui le rends difficile à toucher. Mais ses dégats sont assez limités', 400, 30, 5, 150, 'archer.jpg'),
(3, 'Chevalier', 'Avec son épée et sa panoplie du tueur de dragon, il va tout casser.', 800, 60, 10, 250, 'chevalier.jpg'),
(4, 'Monstrozor', 'Il est grand, il est fort ! Quoi qu''un peu cher... m''enfin on est pas là pour enfiler de perles, si ?', 10000, 750, 40, 500, 'monstrozor.jpg');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
