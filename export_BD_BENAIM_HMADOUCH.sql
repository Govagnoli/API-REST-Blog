-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : ven. 31 mars 2023 à 19:07
-- Version du serveur : 5.7.36
-- Version de PHP : 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `blog`
--

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

DROP TABLE IF EXISTS `article`;
CREATE TABLE IF NOT EXISTS `article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contenu` varchar(140) DEFAULT NULL,
  `date_publication` datetime NOT NULL,
  `auteur` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`id`, `contenu`, `date_publication`, `auteur`) VALUES
(4, 'phrase test pour le blog', '2023-03-17 09:12:05', 'Jean'),
(5, 'Hulk a voulu défier Chuck Norris en duel, maintenant on l\'appelle Shrek.', '2023-03-17 09:12:05', 'Anass'),
(2, 'phrase générer aléatoirement', '2023-03-17 09:12:05', 'Anass'),
(3, 'phrase test pour le blog', '2023-03-17 09:12:05', 'Jean'),
(1, 'Chuck Norris peut faire démarrer un Airbus A380 avec une épingle à nourrice.', '2023-03-17 09:12:05', 'Anass');

-- --------------------------------------------------------

--
-- Structure de la table `vote`
--

DROP TABLE IF EXISTS `vote`;
CREATE TABLE IF NOT EXISTS `vote` (
  `Id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `aimer` tinyint(1) NOT NULL,
  PRIMARY KEY (`Id`,`nom`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `vote`
--

INSERT INTO `vote` (`Id`, `nom`, `aimer`) VALUES
(2, 'Eric', 1),
(1, 'Eric', 1),
(1, 'Jean', 0),
(3, 'Anass', 1),
(1, 'Philippe', 1),
(3, 'Eric', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
