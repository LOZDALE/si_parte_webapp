-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Gen 15, 2026 alle 14:12
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `si_parte`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `citta`
--

CREATE TABLE `citta` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `id_paese` int(11) NOT NULL,
  `categoria_viaggio` varchar(50) DEFAULT NULL,
  `fascia_budget_base` decimal(10,2) DEFAULT NULL,
  `descrizione` text DEFAULT NULL,
  `immagine` varchar(500) DEFAULT NULL,
  `popolarita` int(11) DEFAULT 0,
  `lat` decimal(10,7) DEFAULT NULL,
  `lon` decimal(10,7) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `citta`
--

INSERT INTO `citta` (`id`, `nome`, `id_paese`, `categoria_viaggio`, `fascia_budget_base`, `descrizione`, `immagine`, `popolarita`, `lat`, `lon`) VALUES
(1, 'Roma', 1, 'cultura', 800.00, 'Città Eterna, patrimonio UNESCO, arte e storia', 'https://images.unsplash.com/photo-1529260830199-42c24126f198?w=800&h=600&fit=crop', 10, 41.9028000, 12.4964000),
(2, 'Firenze', 1, 'cultura', 700.00, 'Culla del Rinascimento, arte e architettura', 'https://images.unsplash.com/photo-1496356812155-5d5d6f4b4b4b?w=800&h=600&fit=crop', 9, NULL, NULL),
(3, 'Venezia', 1, 'cultura', 900.00, 'Città sull\'acqua, canali e romanticismo', 'https://images.unsplash.com/photo-1514890547357-a9ee288728e0?w=800&h=600&fit=crop', 10, 45.4408000, 12.3155000),
(4, 'Milano', 1, 'città', 850.00, 'Capitale della moda e del design', 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=800&h=600&fit=crop', 8, 45.4642000, 9.1900000),
(5, 'Napoli', 1, 'cultura', 600.00, 'Cultura, pizza e storia antica', 'https://images.unsplash.com/photo-1515542622106-78bda8ba0e5b?w=800&h=600&fit=crop', 7, NULL, NULL),
(6, 'Amalfi', 1, 'mare', 900.00, 'Costa amalfitana, mare e paesaggi mozzafiato', 'https://images.unsplash.com/photo-1514890547357-a9ee288728e0?w=800&h=600&fit=crop', 9, NULL, NULL),
(7, 'Cortina d\'Ampezzo', 1, 'montagna', 1200.00, 'Dolomiti, sci e montagna', 'https://images.unsplash.com/photo-1464822759844-d150f39b8d7d?w=800&h=600&fit=crop', 8, NULL, NULL),
(8, 'Barcellona', 2, 'mare', 1000.00, 'Architettura modernista, spiagge e vita notturna', 'https://images.unsplash.com/photo-1539037116277-4db20889f2d4?w=800&h=600&fit=crop', 10, NULL, NULL),
(9, 'Madrid', 2, 'città', 950.00, 'Capitale vibrante, arte e cultura', 'https://images.unsplash.com/photo-1539037116277-4db20889f2d4?w=800&h=600&fit=crop', 9, 40.4168000, -3.7038000),
(10, 'Siviglia', 2, 'cultura', 750.00, 'Flamenco, architettura moresca e tradizione', 'https://images.unsplash.com/photo-1539037116277-4db20889f2d4?w=800&h=600&fit=crop', 8, NULL, NULL),
(11, 'Valencia', 2, 'mare', 800.00, 'Città delle arti, spiagge e paella', 'https://images.unsplash.com/photo-1539037116277-4db20889f2d4?w=800&h=600&fit=crop', 7, NULL, NULL),
(12, 'Ibiza', 2, 'mare', 1100.00, 'Isole Baleari, vita notturna e spiagge', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', 9, NULL, NULL),
(13, 'Parigi', 3, 'città', 900.00, 'Città della Luce, arte, moda e romanticismo', 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800&h=600&fit=crop', 10, 48.8566000, 2.3522000),
(14, 'Lione', 3, 'cibo', 750.00, 'Capitale gastronomica della Francia', 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800&h=600&fit=crop', 7, NULL, NULL),
(15, 'Nizza', 3, 'mare', 950.00, 'Costa azzurra, sole e eleganza', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', 8, NULL, NULL),
(16, 'Chamonix', 3, 'montagna', 1100.00, 'Alpi francesi, sci e montagna', 'https://images.unsplash.com/photo-1464822759844-d150f39b8d7d?w=800&h=600&fit=crop', 8, NULL, NULL),
(17, 'New York', 4, 'città', 1500.00, 'La Grande Mela, grattacieli, Broadway e shopping', 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=800&h=600&fit=crop', 10, NULL, NULL),
(18, 'Los Angeles', 4, 'città', 1400.00, 'Hollywood, spiagge e lifestyle californiano', 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=800&h=600&fit=crop', 9, NULL, NULL),
(19, 'Chicago', 4, 'città', 1200.00, 'Architettura, blues e cultura urbana', 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=800&h=600&fit=crop', 8, NULL, NULL),
(20, 'Las Vegas', 4, 'divertimento', 1300.00, 'Casinò, spettacoli e intrattenimento', 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=800&h=600&fit=crop', 9, NULL, NULL),
(21, 'Miami', 4, 'mare', 1400.00, 'Spiagge, vita notturna e art deco', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', 8, NULL, NULL),
(22, 'Londra', 5, 'città', 1100.00, 'Capitale cosmopolita, storia e cultura', 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?w=800&h=600&fit=crop', 10, NULL, NULL),
(23, 'Edimburgo', 5, 'cultura', 900.00, 'Storia scozzese, castelli e festival', 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?w=800&h=600&fit=crop', 8, NULL, NULL),
(24, 'Manchester', 5, 'città', 850.00, 'Musica, cultura e innovazione', 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?w=800&h=600&fit=crop', 7, NULL, NULL),
(25, 'Tokyo', 8, 'città', 1800.00, 'Metropoli futuristico, tecnologia e tradizione', 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=800&h=600&fit=crop', 10, NULL, NULL),
(26, 'Kyoto', 8, 'cultura', 1600.00, 'Templi antichi, giardini zen e geishe', 'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=800&h=600&fit=crop', 10, NULL, NULL),
(27, 'Osaka', 8, 'cibo', 1500.00, 'Cibo eccellente, castello e intrattenimento', 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=800&h=600&fit=crop', 8, NULL, NULL),
(28, 'Zurigo', 10, 'città', 1300.00, 'Banche, lago e qualità della vita', 'https://images.unsplash.com/photo-1464822759844-d150f39b8d7d?w=800&h=600&fit=crop', 8, NULL, NULL),
(29, 'Ginevra', 10, 'città', 1400.00, 'Organizzazioni internazionali e lago', 'https://images.unsplash.com/photo-1464822759844-d150f39b8d7d?w=800&h=600&fit=crop', 7, NULL, NULL),
(30, 'Zermatt', 10, 'montagna', 1500.00, 'Matterhorn, sci e montagna', 'https://images.unsplash.com/photo-1464822759844-d150f39b8d7d?w=800&h=600&fit=crop', 9, NULL, NULL),
(31, 'Atene', 7, 'cultura', 700.00, 'Storia antica, acropoli e archeologia', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', 9, NULL, NULL),
(32, 'Santorini', 7, 'mare', 1000.00, 'Isole cicladi, tramonti e mare azzurro', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', 10, NULL, NULL),
(33, 'Mykonos', 7, 'mare', 1100.00, 'Vita notturna, spiagge e divertimento', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', 9, NULL, NULL),
(34, 'Berlino', 6, 'citt?', 1000.00, 'Capitale europea, arte e vita notturna', 'https://images.unsplash.com/photo-1508057198894-247b23fe5ade?w=800&h=600&fit=crop', 10, 52.5200000, 13.4050000),
(35, 'Monaco di Baviera', 6, 'citt?', 900.00, 'Cultura, birra e vicinanza alle Alpi', 'https://images.unsplash.com/photo-1522098543979-ffc7f79d6c82?w=800&h=600&fit=crop', 8, NULL, NULL),
(36, 'Amburgo', 6, 'citt?', 850.00, 'Porto storico, musica e architettura', 'https://images.unsplash.com/photo-1509395176047-4a66953fd231?w=800&h=600&fit=crop', 7, NULL, NULL),
(37, 'Lisbona', 9, 'mare', 900.00, 'Costa atlantica, tram e fado', 'https://images.unsplash.com/photo-1503424886302-5b1d2a8a3b67?w=800&h=600&fit=crop', 9, NULL, NULL),
(38, 'Porto', 9, 'citt?', 800.00, 'Vino Porto, fiumi e architettura', 'https://images.unsplash.com/photo-1505765054610-7a1d0c2b6f3f?w=800&h=600&fit=crop', 7, 41.1579000, -8.6291000),
(39, 'Vienna', 11, 'citt?', 950.00, 'Musica classica, palazzi e caff?', 'https://images.unsplash.com/photo-1529257414771-1963b8b9b4d0?w=800&h=600&fit=crop', 10, 48.2082000, 16.3738000),
(40, 'Salisburgo', 11, 'cultura', 850.00, 'Citt? di Mozart, storia e montagne', 'https://images.unsplash.com/photo-1531384690687-7c56b6f9d3d8?w=800&h=600&fit=crop', 8, NULL, NULL),
(41, 'Innsbruck', 11, 'montagna', 1100.00, 'Alpi, sci e natura', 'https://images.unsplash.com/photo-1508264165352-258a6a7a3a0f?w=800&h=600&fit=crop', 8, 47.2692000, 11.4041000),
(42, 'Dubrovnik', 12, 'mare', 950.00, 'Citt? fortificata, coste adriatiche', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', 9, 42.6507000, 18.0944000),
(43, 'Spalato', 12, 'mare', 850.00, 'Storia romana e coste splendide', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', 7, NULL, NULL),
(44, 'Istanbul', 13, 'citt?', 900.00, 'Ponte tra Europa e Asia, storia e mercati', 'https://images.unsplash.com/photo-1505765054610-7a1d0c2b6f3f?w=800&h=600&fit=crop', 10, 41.0082000, 28.9784000),
(45, 'Antalya', 13, 'mare', 900.00, 'Costa mediterranea, spiagge e resort', 'https://images.unsplash.com/photo-1493558103817-58b2924bce98?w=800&h=600&fit=crop', 8, NULL, NULL),
(46, 'Oslo', 14, 'citt?', 1000.00, 'Capitale norvegese, fiordi e design', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop', 9, 59.9139000, 10.7522000),
(47, 'Bergen', 14, 'natura', 950.00, 'Fiordi, natura e porti pittoreschi', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop', 8, 60.3913000, 5.3221000),
(48, 'Amsterdam', 15, 'citt?', 900.00, 'Canali, musei e biciclette', 'https://images.unsplash.com/photo-1505765054610-7a1d0c2b6f3f?w=800&h=600&fit=crop', 10, 52.3702000, 4.8952000),
(49, 'Rotterdam', 15, 'citt?', 800.00, 'Architettura moderna e porto', 'https://images.unsplash.com/photo-1509395176047-4a66953fd231?w=800&h=600&fit=crop', 7, NULL, NULL),
(50, 'Bruxelles', 16, 'citt?', 850.00, 'Capitale europea, cioccolato e birre', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop', 8, 50.8503000, 4.3517000),
(51, 'Bruges', 16, 'cultura', 800.00, 'Medioevo, canali e cioccolato', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', 7, NULL, NULL),
(52, 'Varsavia', 17, 'citt?', 800.00, 'Capitale polacca, storia e cultura', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop', 8, NULL, NULL),
(53, 'Cracovia', 17, 'cultura', 750.00, 'Storia, mercati e architettura', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop', 9, NULL, NULL),
(54, 'Praga', 18, 'citt?', 800.00, 'Capitale storica con architettura unica', 'https://images.unsplash.com/photo-1508057198894-247b23fe5ade?w=800&h=600&fit=crop', 10, 50.0755000, 14.4378000),
(55, 'Copenaghen', 19, 'citt?', 900.00, 'Design, hygge e canali', 'https://images.unsplash.com/photo-1509395176047-4a66953fd231?w=800&h=600&fit=crop', 9, NULL, NULL),
(56, 'Stoccolma', 20, 'citt?', 950.00, 'Arcipelago, design e storia', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop', 9, NULL, NULL),
(57, 'Berlino', 6, 'citt?', 1000.00, 'Capitale europea, arte e vita notturna', 'https://images.unsplash.com/photo-1508057198894-247b23fe5ade?w=800&h=600&fit=crop', 10, 52.5200000, 13.4050000),
(58, 'Monaco di Baviera', 6, 'citt?', 900.00, 'Cultura, birra e vicinanza alle Alpi', 'https://images.unsplash.com/photo-1522098543979-ffc7f79d6c82?w=800&h=600&fit=crop', 8, NULL, NULL),
(59, 'Amburgo', 6, 'citt?', 850.00, 'Porto storico, musica e architettura', 'https://images.unsplash.com/photo-1509395176047-4a66953fd231?w=800&h=600&fit=crop', 7, NULL, NULL),
(60, 'Lisbona', 9, 'mare', 900.00, 'Costa atlantica, tram e fado', 'https://images.unsplash.com/photo-1503424886302-5b1d2a8a3b67?w=800&h=600&fit=crop', 9, NULL, NULL),
(61, 'Porto', 9, 'citt?', 800.00, 'Vino Porto, fiumi e architettura', 'https://images.unsplash.com/photo-1505765054610-7a1d0c2b6f3f?w=800&h=600&fit=crop', 7, 41.1579000, -8.6291000),
(62, 'Vienna', 11, 'citt?', 950.00, 'Musica classica, palazzi e caff?', 'https://images.unsplash.com/photo-1529257414771-1963b8b9b4d0?w=800&h=600&fit=crop', 10, 48.2082000, 16.3738000),
(63, 'Salisburgo', 11, 'cultura', 850.00, 'Citt? di Mozart, storia e montagne', 'https://images.unsplash.com/photo-1531384690687-7c56b6f9d3d8?w=800&h=600&fit=crop', 8, NULL, NULL),
(64, 'Innsbruck', 11, 'montagna', 1100.00, 'Alpi, sci e natura', 'https://images.unsplash.com/photo-1508264165352-258a6a7a3a0f?w=800&h=600&fit=crop', 8, 47.2692000, 11.4041000),
(65, 'Dubrovnik', 12, 'mare', 950.00, 'Citt? fortificata, coste adriatiche', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', 9, 42.6507000, 18.0944000),
(66, 'Spalato', 12, 'mare', 850.00, 'Storia romana e coste splendide', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', 7, NULL, NULL),
(67, 'Istanbul', 13, 'citt?', 900.00, 'Ponte tra Europa e Asia, storia e mercati', 'https://images.unsplash.com/photo-1505765054610-7a1d0c2b6f3f?w=800&h=600&fit=crop', 10, 41.0082000, 28.9784000),
(68, 'Antalya', 13, 'mare', 900.00, 'Costa mediterranea, spiagge e resort', 'https://images.unsplash.com/photo-1493558103817-58b2924bce98?w=800&h=600&fit=crop', 8, NULL, NULL),
(69, 'Oslo', 14, 'citt?', 1000.00, 'Capitale norvegese, fiordi e design', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop', 9, 59.9139000, 10.7522000),
(70, 'Bergen', 14, 'natura', 950.00, 'Fiordi, natura e porti pittoreschi', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop', 8, 60.3913000, 5.3221000),
(71, 'Amsterdam', 15, 'citt?', 900.00, 'Canali, musei e biciclette', 'https://images.unsplash.com/photo-1505765054610-7a1d0c2b6f3f?w=800&h=600&fit=crop', 10, 52.3702000, 4.8952000),
(72, 'Rotterdam', 15, 'citt?', 800.00, 'Architettura moderna e porto', 'https://images.unsplash.com/photo-1509395176047-4a66953fd231?w=800&h=600&fit=crop', 7, NULL, NULL),
(73, 'Bruxelles', 16, 'citt?', 850.00, 'Capitale europea, cioccolato e birre', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop', 8, 50.8503000, 4.3517000),
(74, 'Bruges', 16, 'cultura', 800.00, 'Medioevo, canali e cioccolato', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', 7, NULL, NULL),
(75, 'Varsavia', 17, 'citt?', 800.00, 'Capitale polacca, storia e cultura', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop', 8, NULL, NULL),
(76, 'Cracovia', 17, 'cultura', 750.00, 'Storia, mercati e architettura', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop', 9, NULL, NULL),
(77, 'Praga', 18, 'citt?', 800.00, 'Capitale storica con architettura unica', 'https://images.unsplash.com/photo-1508057198894-247b23fe5ade?w=800&h=600&fit=crop', 10, 50.0755000, 14.4378000),
(78, 'Copenaghen', 19, 'citt?', 900.00, 'Design, hygge e canali', 'https://images.unsplash.com/photo-1509395176047-4a66953fd231?w=800&h=600&fit=crop', 9, NULL, NULL),
(79, 'Stoccolma', 20, 'citt?', 950.00, 'Arcipelago, design e storia', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop', 9, NULL, NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `destinations`
--

CREATE TABLE `destinations` (
  `id` int(11) NOT NULL,
  `nome_destinazione` varchar(100) DEFAULT NULL,
  `categoria_viaggio` varchar(50) DEFAULT NULL,
  `fascia_budget_base` decimal(10,2) DEFAULT NULL,
  `scenari_simulazione_fase2` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`scenari_simulazione_fase2`)),
  `attività_extra_fase3` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attività_extra_fase3`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `destinations`
--

INSERT INTO `destinations` (`id`, `nome_destinazione`, `categoria_viaggio`, `fascia_budget_base`, `scenari_simulazione_fase2`, `attività_extra_fase3`) VALUES
(1, 'Roma', 'cultura', 800.00, '{}', '{}'),
(2, 'Barcellona', 'mare', 1000.00, '{}', '{}'),
(3, 'Alpi', 'montagna', 1200.00, '{}', '{}'),
(4, 'Parigi', 'città', 900.00, '{}', '{}');

-- --------------------------------------------------------

--
-- Struttura della tabella `paesi`
--

CREATE TABLE `paesi` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `codice_iso` varchar(3) DEFAULT NULL,
  `categorie_suggerite` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`categorie_suggerite`)),
  `descrizione` text DEFAULT NULL,
  `immagine_bandiera` varchar(255) DEFAULT NULL,
  `immagine` varchar(1024) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `paesi`
--

INSERT INTO `paesi` (`id`, `nome`, `codice_iso`, `categorie_suggerite`, `descrizione`, `immagine_bandiera`, `immagine`) VALUES
(1, 'Italia', 'ITA', '[\"cultura\", \"mare\", \"montagna\", \"cibo\"]', 'Patria del patrimonio artistico, del cibo eccellente e delle coste mozzafiato', NULL, NULL),
(2, 'Spagna', 'ESP', '[\"mare\", \"cultura\", \"festa\", \"cibo\"]', 'Sole, spiagge, cultura vibrante e vita notturna', NULL, NULL),
(3, 'Francia', 'FRA', '[\"cultura\", \"cibo\", \"città\", \"montagna\"]', 'Eleganza, arte, gastronomia e paesaggi vari', NULL, NULL),
(4, 'Stati Uniti', 'USA', '[\"città\", \"natura\", \"divertimento\", \"shopping\"]', 'Metropoli iconiche, parchi nazionali e intrattenimento', NULL, NULL),
(5, 'Regno Unito', 'GBR', '[\"cultura\", \"città\", \"storia\", \"pub\"]', 'Storia, cultura, tradizione e città cosmopolite', NULL, NULL),
(6, 'Germania', 'DEU', '[\"cultura\", \"storia\", \"città\", \"montagna\"]', 'Storia, cultura, birra e paesaggi montani', NULL, NULL),
(7, 'Grecia', 'GRC', '[\"mare\", \"cultura\", \"storia\", \"isole\"]', 'Isole paradisiache, storia antica e gastronomia mediterranea', NULL, NULL),
(8, 'Giappone', 'JPN', '[\"cultura\", \"città\", \"cibo\", \"tradizione\"]', 'Tradizione, modernità, cibo eccellente e cultura unica', NULL, NULL),
(9, 'Portogallo', 'PRT', '[\"mare\", \"cultura\", \"cibo\", \"vino\"]', 'Costa atlantica, cultura affascinante e vino Porto', NULL, NULL),
(10, 'Svizzera', 'CHE', '[\"montagna\", \"natura\", \"città\", \"luxury\"]', 'Alpi, precisione, cioccolato e paesaggi mozzafiato', NULL, NULL),
(11, 'Austria', 'AUT', '[\"montagna\", \"cultura\", \"musica\", \"sci\"]', 'Alpi, musica classica, cultura e sport invernali', NULL, 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/41/Flag_of_Austria.svg/langit-960px-Flag_of_Austria.svg.png'),
(12, 'Croazia', 'HRV', '[\"mare\", \"storia\", \"natura\", \"isole\"]', 'Costa dalmata, città storiche e isole paradisiache', NULL, NULL),
(13, 'Turchia', 'TUR', '[\"cultura\", \"storia\", \"mare\", \"cibo\"]', 'Ponte tra Europa e Asia, storia ricca e cucina deliziosa', NULL, NULL),
(14, 'Norvegia', 'NOR', '[\"natura\", \"montagna\", \"fiordi\", \"nordic\"]', 'Fiordi spettacolari, aurore boreali e natura selvaggia', NULL, NULL),
(15, 'Olanda', 'NLD', '[\"città\", \"biciclette\", \"cultura\", \"tulipani\"]', 'Città storiche, canali, tulipani e cultura liberale', NULL, NULL),
(16, 'Belgio', 'BEL', '[\"cultura\", \"cibo\", \"città\", \"storia\"]', 'Cioccolato, birre, architettura gotica e cultura', NULL, 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/65/Flag_of_Belgium.svg/langit-960px-Flag_of_Belgium.svg.png'),
(17, 'Polonia', 'POL', '[\"cultura\", \"storia\", \"città\", \"cibo\"]', 'Storia ricca, città affascinanti e cucina tradizionale', NULL, NULL),
(18, 'Repubblica Ceca', 'CZE', '[\"cultura\", \"storia\", \"birra\", \"città\"]', 'Praga magica, birre eccellenti e storia affascinante', NULL, NULL),
(19, 'Danimarca', 'DNK', '[\"città\", \"design\", \"cultura\", \"nordic\"]', 'Design, hygge, città moderne e cultura nordica', NULL, NULL),
(20, 'Svezia', 'SWE', '[\"natura\", \"città\", \"design\", \"nordic\"]', 'Natura selvaggia, design, città moderne e cultura nordica', NULL, NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `risposte_quiz` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`risposte_quiz`)),
  `destinazione_assegnata` varchar(100) DEFAULT NULL,
  `paese_selezionato` varchar(100) DEFAULT NULL,
  `tipo_viaggio` varchar(100) DEFAULT NULL,
  `budget_finale` decimal(10,2) DEFAULT NULL,
  `scelte_extra` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`scelte_extra`)),
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `risposte_quiz`, `destinazione_assegnata`, `paese_selezionato`, `tipo_viaggio`, `budget_finale`, `scelte_extra`, `email`) VALUES
(1, '{\"climate\":\"mare\",\"activities\":[\"relax\",\"cultura\"],\"budget\":1000}', 'Barcellona', NULL, 'Relax & Cultura', 1000.00, '{}', 'demo@example.com'),
(2, '{\"answers_phase1\":[3,2,1],\"answers_phase2\":[3,3,3,3],\"totalScores_phase1\":{\"beach\":10,\"mountain\":15,\"city\":13,\"ski\":16},\"totalScores_phase2\":{\"beach\":10,\"mountain\":15,\"city\":13,\"ski\":16},\"bestCategory_phase1\":\"ski\",\"bestCategory_phase2\":\"ski\"}', 'Destinazione Sciistica', NULL, 'Montagna', 750.00, '[]', NULL),
(3, '{\"answers_phase1\":[0,0,0],\"answers_phase2\":[2,0,0,2],\"totalScores_phase1\":{\"beach\":18,\"mountain\":4,\"city\":13,\"ski\":6},\"totalScores_phase2\":{\"beach\":18,\"mountain\":4,\"city\":13,\"ski\":6},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"beach\"}', 'Valencia', NULL, 'Mare', 500.00, '[]', NULL),
(4, '{\"answers_phase1\":[3,2,1],\"answers_phase2\":[0,1,3,1],\"totalScores_phase1\":{\"beach\":7,\"mountain\":6,\"city\":20,\"ski\":8},\"totalScores_phase2\":{\"beach\":7,\"mountain\":6,\"city\":20,\"ski\":8},\"bestCategory_phase1\":\"city\",\"bestCategory_phase2\":\"city\"}', 'Madrid', NULL, 'Città', 750.00, '[]', NULL),
(5, '{\"answers_phase1\":[1,3,2],\"answers_phase2\":[1,3,2,3],\"totalScores_phase1\":{\"beach\":13,\"mountain\":18,\"city\":10,\"ski\":19},\"totalScores_phase2\":{\"beach\":13,\"mountain\":18,\"city\":10,\"ski\":19},\"bestCategory_phase1\":\"ski\",\"bestCategory_phase2\":\"ski\"}', 'Destinazione Sciistica', NULL, 'Montagna', 1500.00, '[]', NULL),
(6, '{\"answers_phase1\":[0,0,2],\"answers_phase2\":[1,0,2,1],\"totalScores_phase1\":{\"beach\":14,\"mountain\":8,\"city\":15,\"ski\":8},\"totalScores_phase2\":{\"beach\":14,\"mountain\":8,\"city\":15,\"ski\":8},\"bestCategory_phase1\":\"city\",\"bestCategory_phase2\":\"city\"}', 'Destinazione Urbana', NULL, 'Città', 1500.00, '[]', NULL),
(7, '{\"answers_phase1\":[1,1,0],\"answers_phase2\":[2,1,1,2],\"totalScores_phase1\":{\"beach\":16,\"mountain\":14,\"city\":8,\"ski\":8},\"totalScores_phase2\":{\"beach\":16,\"mountain\":14,\"city\":8,\"ski\":8},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"beach\"}', 'Destinazione di Mare', NULL, 'Mare', 500.00, '[]', NULL),
(8, '{\"answers_phase1\":[3,2,2],\"answers_phase2\":[0,2,1,1,null,null,null],\"totalScores_phase1\":{\"beach\":14,\"mountain\":9,\"city\":15,\"ski\":9},\"totalScores_phase2\":{\"beach\":14,\"mountain\":9,\"city\":15,\"ski\":9},\"bestCategory_phase1\":\"city\",\"bestCategory_phase2\":\"city\"}', 'Londra', NULL, 'Città', 1500.00, '[]', NULL),
(9, '{\"answers_phase1\":[2,2,2],\"answers_phase2\":[0,1,3,2],\"totalScores_phase1\":{\"beach\":11,\"mountain\":12,\"city\":17,\"ski\":11},\"totalScores_phase2\":{\"beach\":11,\"mountain\":12,\"city\":17,\"ski\":11},\"bestCategory_phase1\":\"city\",\"bestCategory_phase2\":\"city\"}', 'Tokyo', NULL, 'Città', 1500.00, '[]', NULL),
(10, '{\"answers_phase1\":[0,1,2],\"answers_phase2\":[2,1,1,0],\"totalScores_phase1\":[],\"totalScores_phase2\":{\"beach\":19,\"mountain\":14,\"city\":12,\"ski\":8},\"bestCategory_phase1\":null,\"bestCategory_phase2\":\"beach\"}', 'Destinazione di Mare', NULL, 'Mare', 1500.00, '[]', NULL),
(11, '{\"answers_phase1\":[0,0,0],\"answers_phase2\":[0,0,0,0],\"totalScores_phase1\":[],\"totalScores_phase2\":{\"beach\":10,\"mountain\":2,\"city\":1,\"ski\":0},\"bestCategory_phase1\":null,\"bestCategory_phase2\":\"beach\"}', 'Destinazione di Mare', NULL, 'Mare', 500.00, '[]', NULL),
(12, '{\"answers_phase1\":[0,0,0],\"answers_phase2\":[0,0,0,0],\"totalScores_phase1\":[],\"totalScores_phase2\":{\"beach\":10,\"mountain\":2,\"city\":1,\"ski\":0},\"bestCategory_phase1\":null,\"bestCategory_phase2\":\"mountain\"}', 'Destinazione di Montagna', NULL, 'Montagna', 500.00, '[]', NULL),
(13, '{\"answers_phase1\":[3,2,2],\"answers_phase2\":[2,1,1],\"totalScores_phase1\":{\"beach\":14,\"mountain\":9,\"city\":11,\"ski\":9},\"totalScores_phase2\":{\"beach\":14,\"mountain\":9,\"city\":11,\"ski\":9},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"city\"}', 'Destinazione Urbana', NULL, 'Città', 1500.00, '[]', NULL),
(14, '{\"answers_phase1\":[1,1,1],\"answers_phase2\":[0,2,2,1],\"totalScores_phase1\":{\"beach\":14,\"mountain\":18,\"city\":6,\"ski\":7},\"totalScores_phase2\":{\"beach\":14,\"mountain\":18,\"city\":6,\"ski\":7},\"bestCategory_phase1\":\"mountain\",\"bestCategory_phase2\":\"mountain\"}', 'Zermatt', NULL, 'Montagna', 750.00, '[]', NULL),
(15, '{\"answers_phase1\":[2,2,3],\"answers_phase2\":[3,3,3,3],\"totalScores_phase1\":{\"beach\":10,\"mountain\":13,\"city\":19,\"ski\":11},\"totalScores_phase2\":{\"beach\":10,\"mountain\":13,\"city\":19,\"ski\":11},\"bestCategory_phase1\":\"city\",\"bestCategory_phase2\":\"city\"}', 'Tokyo', NULL, 'Città', 2500.00, '[]', NULL),
(16, '{\"answers_phase1\":[1,1,1],\"answers_phase2\":[1,1,1],\"totalScores_phase1\":{\"beach\":10,\"mountain\":13,\"city\":9,\"ski\":6},\"totalScores_phase2\":{\"beach\":10,\"mountain\":13,\"city\":9,\"ski\":6},\"bestCategory_phase1\":\"mountain\",\"bestCategory_phase2\":\"mountain\"}', 'Destinazione di Montagna', NULL, 'Montagna', 750.00, '[]', NULL),
(17, '{\"answers_phase1\":[0,0,0],\"answers_phase2\":[0,0,0,0],\"totalScores_phase1\":[],\"totalScores_phase2\":{\"beach\":2,\"mountain\":8,\"city\":3,\"ski\":1},\"bestCategory_phase1\":null,\"bestCategory_phase2\":\"mountain\"}', 'Destinazione di Montagna', NULL, 'Montagna', 500.00, '[]', NULL),
(18, '{\"answers_phase1\":[0,0,0],\"answers_phase2\":[0,0,0,0],\"totalScores_phase1\":[],\"totalScores_phase2\":{\"beach\":2,\"mountain\":8,\"city\":3,\"ski\":1},\"bestCategory_phase1\":null,\"bestCategory_phase2\":\"mountain\"}', 'Innsbruck', NULL, 'Montagna', 500.00, '[]', NULL),
(19, '{\"answers_phase1\":[3,2,2],\"answers_phase2\":[3,2,3,1],\"totalScores_phase1\":{\"beach\":15,\"mountain\":10,\"city\":13,\"ski\":10},\"totalScores_phase2\":{\"beach\":15,\"mountain\":10,\"city\":13,\"ski\":10},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"city\"}', 'Tokyo', NULL, 'Città', 1500.00, '[]', NULL),
(20, '{\"answers_phase1\":[0,1,0],\"answers_phase2\":[2,1,1,1],\"totalScores_phase1\":{\"beach\":12,\"mountain\":13,\"city\":9,\"ski\":7},\"totalScores_phase2\":{\"beach\":12,\"mountain\":13,\"city\":9,\"ski\":7},\"bestCategory_phase1\":\"mountain\",\"bestCategory_phase2\":\"beach\"}', 'Spalato', NULL, 'Mare', 500.00, '[]', NULL),
(21, '{\"answers_phase1\":[0,1,0],\"answers_phase2\":[2,0,1,1],\"totalScores_phase1\":{\"beach\":11,\"mountain\":13,\"city\":10,\"ski\":7},\"totalScores_phase2\":{\"beach\":11,\"mountain\":13,\"city\":10,\"ski\":7},\"bestCategory_phase1\":\"mountain\",\"bestCategory_phase2\":\"beach\"}', 'Spalato', NULL, 'Mare', 500.00, '[]', NULL),
(22, '{\"answers_phase1\":[2,2,2],\"answers_phase2\":[2,2,2,2],\"totalScores_phase1\":{\"beach\":18,\"mountain\":12,\"city\":13,\"ski\":11},\"totalScores_phase2\":{\"beach\":18,\"mountain\":12,\"city\":13,\"ski\":11},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"beach\"}', 'Berlino', NULL, 'Citt?', 1500.00, '[]', NULL),
(23, '{\"answers_phase1\":[0,0,0],\"answers_phase2\":[0,0,0,0],\"totalScores_phase1\":[],\"totalScores_phase2\":{\"beach\":3,\"mountain\":1,\"city\":0,\"ski\":0},\"bestCategory_phase1\":null,\"bestCategory_phase2\":\"beach\"}', 'Spalato', NULL, 'Mare', 500.00, '[]', NULL),
(24, '{\"answers_phase1\":[0,0,0],\"answers_phase2\":[0,0,0,0],\"totalScores_phase1\":[],\"totalScores_phase2\":{\"beach\":3,\"mountain\":1,\"city\":0,\"ski\":0},\"bestCategory_phase1\":null,\"bestCategory_phase2\":\"beach\"}', 'Spalato', NULL, 'Mare', 500.00, '[]', NULL),
(25, '{\"answers_phase1\":[3,2,2],\"answers_phase2\":[3,3,3,3],\"totalScores_phase1\":{\"beach\":12,\"mountain\":12,\"city\":12,\"ski\":13},\"totalScores_phase2\":{\"beach\":12,\"mountain\":12,\"city\":12,\"ski\":13},\"bestCategory_phase1\":\"ski\",\"bestCategory_phase2\":\"city\"}', 'New York', NULL, 'Città', 1500.00, '[]', NULL),
(26, '{\"answers_phase1\":[3,3,3],\"answers_phase2\":[3,3,3,3],\"totalScores_phase1\":{\"beach\":13,\"mountain\":13,\"city\":10,\"ski\":17},\"totalScores_phase2\":{\"beach\":13,\"mountain\":13,\"city\":10,\"ski\":17},\"bestCategory_phase1\":\"ski\",\"bestCategory_phase2\":\"ski\"}', 'Oslo', NULL, 'Citt?', 2500.00, '[]', NULL),
(27, '{\"answers_phase1\":[1,1,1],\"answers_phase2\":[1,2,1,1],\"totalScores_phase1\":{\"beach\":13,\"mountain\":13,\"city\":11,\"ski\":6},\"totalScores_phase2\":{\"beach\":13,\"mountain\":13,\"city\":11,\"ski\":6},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"beach\"}', 'Amburgo', NULL, 'Citt?', 750.00, '[]', NULL),
(28, '{\"answers_phase1\":[3,3,3],\"answers_phase2\":[0,1,1,1],\"totalScores_phase1\":{\"beach\":12,\"mountain\":14,\"city\":11,\"ski\":13},\"totalScores_phase2\":{\"beach\":12,\"mountain\":14,\"city\":11,\"ski\":13},\"bestCategory_phase1\":\"mountain\",\"bestCategory_phase2\":\"mountain\"}', 'Zermatt', NULL, 'Montagna', 2500.00, '[]', NULL),
(29, '{\"answers_phase1\":[3,3,3],\"answers_phase2\":[3,3,3,3],\"totalScores_phase1\":{\"beach\":9,\"mountain\":17,\"city\":9,\"ski\":20},\"totalScores_phase2\":{\"beach\":9,\"mountain\":17,\"city\":9,\"ski\":20},\"bestCategory_phase1\":\"ski\",\"bestCategory_phase2\":\"ski\"}', 'Berlino', NULL, 'Citt?', 2500.00, '[]', NULL),
(30, '{\"answers_phase1\":[3,3,3],\"answers_phase2\":[3,3,3,3],\"totalScores_phase1\":{\"beach\":13,\"mountain\":13,\"city\":10,\"ski\":17},\"totalScores_phase2\":{\"beach\":13,\"mountain\":13,\"city\":10,\"ski\":17},\"bestCategory_phase1\":\"ski\",\"bestCategory_phase2\":\"mountain\"}', 'Innsbruck', NULL, 'Montagna', 2500.00, '[]', NULL),
(31, '{\"answers_phase1\":[1,1,0],\"answers_phase2\":[1,1,1,1],\"totalScores_phase1\":{\"beach\":9,\"mountain\":16,\"city\":8,\"ski\":5},\"totalScores_phase2\":{\"beach\":9,\"mountain\":16,\"city\":8,\"ski\":5},\"bestCategory_phase1\":\"mountain\",\"bestCategory_phase2\":\"mountain\"}', 'Innsbruck', NULL, 'Montagna', 500.00, '[]', NULL),
(32, '{\"answers_phase1\":[2,1,1],\"answers_phase2\":[3,3,3,3],\"totalScores_phase1\":{\"beach\":8,\"mountain\":18,\"city\":10,\"ski\":12},\"totalScores_phase2\":{\"beach\":8,\"mountain\":18,\"city\":10,\"ski\":12},\"bestCategory_phase1\":\"mountain\",\"bestCategory_phase2\":\"mountain\"}', 'Amburgo', NULL, 'Citt?', 750.00, '[]', NULL),
(33, '{\"answers_phase1\":[2,2,2],\"answers_phase2\":[0,0,1,1],\"totalScores_phase1\":{\"beach\":11,\"mountain\":9,\"city\":21,\"ski\":6},\"totalScores_phase2\":{\"beach\":11,\"mountain\":9,\"city\":21,\"ski\":6},\"bestCategory_phase1\":\"city\",\"bestCategory_phase2\":\"city\"}', 'Tokyo', NULL, 'Città', 1500.00, '[]', NULL),
(34, '{\"answers_phase1\":[1,1,2],\"answers_phase2\":[2,2,2,2],\"totalScores_phase1\":{\"beach\":13,\"mountain\":21,\"city\":6,\"ski\":12},\"totalScores_phase2\":{\"beach\":13,\"mountain\":21,\"city\":6,\"ski\":12},\"bestCategory_phase1\":\"mountain\",\"bestCategory_phase2\":\"mountain\"}', 'Chamonix', NULL, 'Montagna', 1500.00, '[]', NULL),
(35, '{\"answers_phase1\":[0,0,1],\"answers_phase2\":[1,1,1,1],\"totalScores_phase1\":{\"beach\":20,\"mountain\":7,\"city\":10,\"ski\":5},\"totalScores_phase2\":{\"beach\":20,\"mountain\":7,\"city\":10,\"ski\":5},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"beach\"}', 'Nizza', NULL, 'Mare', 750.00, '[]', NULL),
(36, '{\"answers_phase1\":[1,1,1],\"answers_phase2\":[3,null,3,3],\"totalScores_phase1\":{\"beach\":7,\"mountain\":16,\"city\":7,\"ski\":10},\"totalScores_phase2\":{\"beach\":7,\"mountain\":16,\"city\":7,\"ski\":10},\"bestCategory_phase1\":\"mountain\",\"bestCategory_phase2\":\"mountain\"}', 'Amburgo', NULL, 'Citt?', 750.00, '[]', NULL),
(37, '{\"answers_phase1\":[2,2,2],\"answers_phase2\":[2,2,2,2],\"totalScores_phase1\":{\"beach\":18,\"mountain\":12,\"city\":13,\"ski\":11},\"totalScores_phase2\":{\"beach\":18,\"mountain\":12,\"city\":13,\"ski\":11},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"beach\"}', 'Berlino', NULL, 'Citt?', 1500.00, '[]', NULL),
(38, '{\"answers_phase1\":[1,1,1],\"answers_phase2\":[2,2,2,2],\"totalScores_phase1\":{\"beach\":16,\"mountain\":16,\"city\":6,\"ski\":11},\"totalScores_phase2\":{\"beach\":16,\"mountain\":16,\"city\":6,\"ski\":11},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"beach\"}', 'Amburgo', NULL, 'Citt?', 750.00, '[]', NULL),
(39, '{\"answers_phase1\":[1,2,2],\"answers_phase2\":[3,2,2,2],\"totalScores_phase1\":{\"beach\":17,\"mountain\":14,\"city\":10,\"ski\":12},\"totalScores_phase2\":{\"beach\":17,\"mountain\":14,\"city\":10,\"ski\":12},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"beach\"}', 'Oslo', NULL, 'Citt?', 1500.00, '[]', NULL),
(40, '{\"answers_phase1\":[0,0,0],\"answers_phase2\":[1,2,2,2],\"totalScores_phase1\":{\"beach\":21,\"mountain\":9,\"city\":5,\"ski\":9},\"totalScores_phase2\":{\"beach\":21,\"mountain\":9,\"city\":5,\"ski\":9},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"beach\"}', 'Amalfi', NULL, 'Mare', 500.00, '[]', NULL),
(41, '{\"answers_phase1\":[1,1,1],\"answers_phase2\":[1,2,2,2],\"totalScores_phase1\":{\"beach\":16,\"mountain\":16,\"city\":5,\"ski\":11},\"totalScores_phase2\":{\"beach\":16,\"mountain\":16,\"city\":5,\"ski\":11},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"beach\"}', 'Nizza', NULL, 'Mare', 750.00, '[]', NULL),
(42, '{\"answers_phase1\":[3,3,3],\"answers_phase2\":[3,3,3,3],\"totalScores_phase1\":{\"beach\":13,\"mountain\":13,\"city\":10,\"ski\":17},\"totalScores_phase2\":{\"beach\":13,\"mountain\":13,\"city\":10,\"ski\":17},\"bestCategory_phase1\":\"ski\",\"bestCategory_phase2\":\"mountain\"}', 'Innsbruck', NULL, 'Montagna', 2500.00, '[]', NULL),
(43, '{\"answers_phase1\":[2,2,2],\"answers_phase2\":[2,2,2,2],\"totalScores_phase1\":{\"beach\":14,\"mountain\":12,\"city\":16,\"ski\":11},\"totalScores_phase2\":{\"beach\":14,\"mountain\":12,\"city\":16,\"ski\":11},\"bestCategory_phase1\":\"city\",\"bestCategory_phase2\":\"city\"}', 'Londra', NULL, 'Città', 1500.00, '[]', NULL),
(44, '{\"answers_phase1\":[1,1,1],\"answers_phase2\":[1,1,1,1],\"totalScores_phase1\":{\"beach\":14,\"mountain\":13,\"city\":9,\"ski\":6},\"totalScores_phase2\":{\"beach\":14,\"mountain\":13,\"city\":9,\"ski\":6},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"beach\"}', 'Amalfi', NULL, 'Mare', 750.00, '[]', NULL),
(45, '{\"answers_phase1\":[3,3,3],\"answers_phase2\":[3,3,3,3],\"totalScores_phase1\":{\"beach\":9,\"mountain\":17,\"city\":9,\"ski\":20},\"totalScores_phase2\":{\"beach\":9,\"mountain\":17,\"city\":9,\"ski\":20},\"bestCategory_phase1\":\"ski\",\"bestCategory_phase2\":\"mountain\"}', 'Cortina d\'Ampezzo', NULL, 'Montagna', 2500.00, '[]', NULL),
(46, '{\"answers_phase1\":[2,3,3],\"answers_phase2\":[3,3,3,3],\"totalScores_phase1\":{\"beach\":14,\"mountain\":13,\"city\":13,\"ski\":14},\"totalScores_phase2\":{\"beach\":14,\"mountain\":13,\"city\":13,\"ski\":14},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"city\"}', 'Londra', NULL, 'Città', 2500.00, '[]', NULL),
(47, '{\"answers_phase1\":[3,3,3],\"answers_phase2\":[3,3,3,3],\"totalScores_phase1\":{\"beach\":9,\"mountain\":17,\"city\":9,\"ski\":20},\"totalScores_phase2\":{\"beach\":9,\"mountain\":17,\"city\":9,\"ski\":20},\"bestCategory_phase1\":\"ski\",\"bestCategory_phase2\":\"ski\"}', 'Berlino', NULL, 'Citt?', 2500.00, '[]', NULL),
(48, '{\"answers_phase1\":[2,2,2],\"answers_phase2\":[2,2,2,0],\"totalScores_phase1\":{\"beach\":14,\"mountain\":8,\"city\":20,\"ski\":8},\"totalScores_phase2\":{\"beach\":14,\"mountain\":8,\"city\":20,\"ski\":8},\"bestCategory_phase1\":\"city\",\"bestCategory_phase2\":\"city\"}', 'New York', NULL, 'Città', 1500.00, '[]', NULL),
(49, '{\"answers_phase1\":[2,2,2],\"answers_phase2\":[1,2,2,2],\"totalScores_phase1\":{\"beach\":14,\"mountain\":12,\"city\":16,\"ski\":11},\"totalScores_phase2\":{\"beach\":14,\"mountain\":12,\"city\":16,\"ski\":11},\"bestCategory_phase1\":\"city\",\"bestCategory_phase2\":\"city\"}', 'Cracovia', NULL, 'Cultura', 1500.00, '[]', NULL),
(50, '{\"answers_phase1\":[3,3,3],\"answers_phase2\":[3,3,3,3],\"totalScores_phase1\":{\"beach\":13,\"mountain\":13,\"city\":10,\"ski\":17},\"totalScores_phase2\":{\"beach\":13,\"mountain\":13,\"city\":10,\"ski\":17},\"bestCategory_phase1\":\"ski\",\"bestCategory_phase2\":\"mountain\"}', 'Zermatt', NULL, 'Montagna', 2500.00, '[]', NULL),
(51, '{\"answers_phase1\":[3,3,3],\"answers_phase2\":[1,2,2,2],\"totalScores_phase1\":{\"beach\":14,\"mountain\":13,\"city\":11,\"ski\":18},\"totalScores_phase2\":{\"beach\":14,\"mountain\":13,\"city\":11,\"ski\":18},\"bestCategory_phase1\":\"ski\",\"bestCategory_phase2\":\"mountain\"}', 'Zermatt', NULL, 'Montagna', 2500.00, '[]', NULL),
(52, '{\"answers_phase1\":[2,2,2],\"answers_phase2\":[2,2,2,2],\"totalScores_phase1\":{\"beach\":18,\"mountain\":12,\"city\":13,\"ski\":11},\"totalScores_phase2\":{\"beach\":18,\"mountain\":12,\"city\":13,\"ski\":11},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"beach\"}', 'Praga', NULL, 'Citt?', 1500.00, '[]', NULL),
(53, '{\"answers_phase1\":[3,3,3],\"answers_phase2\":[2,3,3,3],\"totalScores_phase1\":{\"beach\":9,\"mountain\":13,\"city\":13,\"ski\":17},\"totalScores_phase2\":{\"beach\":9,\"mountain\":13,\"city\":13,\"ski\":17},\"bestCategory_phase1\":\"ski\",\"bestCategory_phase2\":\"city\"}', 'Salisburgo', NULL, 'Cultura', 2500.00, '[]', NULL),
(54, '{\"answers_phase1\":[3,3,1],\"answers_phase2\":[3,0,1,1],\"totalScores_phase1\":{\"beach\":9,\"mountain\":8,\"city\":10,\"lago\":4},\"totalScores_phase2\":{\"beach\":9,\"mountain\":8,\"city\":10,\"lago\":4},\"bestCategory_phase1\":\"city\",\"bestCategory_phase2\":\"lago\"}', 'Bergen', NULL, 'Natura', 750.00, '[]', NULL),
(55, '{\"answers_phase1\":[0,0,0],\"answers_phase2\":[1,1,3,3],\"totalScores_phase1\":{\"beach\":13,\"mountain\":9,\"city\":10,\"lago\":0},\"totalScores_phase2\":{\"beach\":13,\"mountain\":9,\"city\":10,\"lago\":0},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"lago\"}', 'Bergen', NULL, 'Natura', 500.00, '[]', NULL),
(56, '{\"answers_phase1\":[3,3,3],\"answers_phase2\":[3,3,3,3],\"totalScores_phase1\":{\"beach\":9,\"mountain\":13,\"city\":9,\"lago\":4},\"totalScores_phase2\":{\"beach\":9,\"mountain\":13,\"city\":9,\"lago\":4},\"bestCategory_phase1\":\"mountain\",\"bestCategory_phase2\":\"lago\"}', 'Bergen', NULL, 'Natura', 2500.00, '[]', NULL),
(57, '{\"answers_phase1\":[2,0,0],\"answers_phase2\":[1,1,1,1],\"totalScores_phase1\":{\"beach\":13,\"mountain\":11,\"city\":11,\"lago\":0},\"totalScores_phase2\":{\"beach\":13,\"mountain\":11,\"city\":11,\"lago\":0},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"beach\"}', 'Amalfi', NULL, 'Mare', 500.00, '[]', NULL),
(58, '{\"answers_phase1\":[0,1,0],\"answers_phase2\":[2,2,2,2],\"totalScores_phase1\":{\"beach\":14,\"mountain\":12,\"city\":9,\"lago\":0},\"totalScores_phase2\":{\"beach\":14,\"mountain\":12,\"city\":9,\"lago\":0},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"beach\"}', 'Lisbona', NULL, 'Mare', 500.00, '[]', NULL),
(59, '{\"answers_phase1\":[2,2,2],\"answers_phase2\":[1,2,2,2],\"totalScores_phase1\":{\"beach\":14,\"mountain\":16,\"city\":12,\"lago\":0},\"totalScores_phase2\":{\"beach\":14,\"mountain\":16,\"city\":12,\"lago\":0},\"bestCategory_phase1\":\"mountain\",\"bestCategory_phase2\":\"city\"}', 'Bruges', NULL, 'Cultura', 1500.00, '[]', NULL),
(60, '{\"answers_phase1\":[0,0,0],\"answers_phase2\":[0,0,0,0],\"totalScores_phase1\":{\"beach\":14,\"mountain\":2,\"city\":14,\"lago\":0},\"totalScores_phase2\":{\"beach\":14,\"mountain\":2,\"city\":14,\"lago\":0},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"city\"}', 'Bruges', NULL, 'Cultura', 500.00, '[]', NULL),
(61, '{\"answers_phase1\":[2,2,2],\"answers_phase2\":[3,3,3,3],\"totalScores_phase1\":{\"beach\":9,\"mountain\":12,\"city\":14,\"lago\":4},\"totalScores_phase2\":{\"beach\":9,\"mountain\":12,\"city\":14,\"lago\":4},\"bestCategory_phase1\":\"city\",\"bestCategory_phase2\":\"city\"}', 'Praga', NULL, 'Citt?', 1500.00, '[]', NULL),
(62, '{\"answers_phase1\":[0,0,0],\"answers_phase2\":[0,0,0,0],\"totalScores_phase1\":{\"beach\":14,\"mountain\":2,\"city\":14,\"lago\":0},\"totalScores_phase2\":{\"beach\":14,\"mountain\":2,\"city\":14,\"lago\":0},\"bestCategory_phase1\":\"beach\",\"bestCategory_phase2\":\"beach\"}', 'Praga', NULL, 'Citt?', 500.00, '[]', NULL),
(63, '{\"answers_phase1\":[2,3,3],\"answers_phase2\":[3,3,3,3],\"totalScores_phase1\":{\"beach\":10,\"mountain\":13,\"city\":12,\"lago\":4},\"totalScores_phase2\":{\"beach\":10,\"mountain\":13,\"city\":12,\"lago\":4},\"bestCategory_phase1\":\"mountain\",\"bestCategory_phase2\":\"city\"}', 'Bruges', NULL, 'Cultura', 2500.00, '[]', NULL);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `citta`
--
ALTER TABLE `citta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_paese` (`id_paese`);

--
-- Indici per le tabelle `destinations`
--
ALTER TABLE `destinations`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `paesi`
--
ALTER TABLE `paesi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nome` (`nome`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `citta`
--
ALTER TABLE `citta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT per la tabella `destinations`
--
ALTER TABLE `destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `paesi`
--
ALTER TABLE `paesi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `citta`
--
ALTER TABLE `citta`
  ADD CONSTRAINT `fk_citta_paese` FOREIGN KEY (`id_paese`) REFERENCES `paesi` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
