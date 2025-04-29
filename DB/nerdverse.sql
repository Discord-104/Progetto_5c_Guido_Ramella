-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Apr 29, 2025 alle 10:25
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nerdverse`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `attivita_anime`
--

CREATE TABLE `attivita_anime` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) NOT NULL,
  `titolo` varchar(255) NOT NULL,
  `riferimento_api` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `punteggio` decimal(3,1) DEFAULT NULL,
  `episodi_visti` int(11) DEFAULT 0,
  `data_inizio` date DEFAULT NULL,
  `data_fine` date DEFAULT NULL,
  `note` text DEFAULT NULL,
  `rewatch` int(11) DEFAULT 0,
  `preferito` tinyint(1) DEFAULT 0,
  `data_ora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `attivita_anime`
--

INSERT INTO `attivita_anime` (`id`, `utente_id`, `titolo`, `riferimento_api`, `status`, `punteggio`, `episodi_visti`, `data_inizio`, `data_fine`, `note`, `rewatch`, `preferito`, `data_ora`) VALUES
(1, 1, 'Sousou no Frieren', 154587, 'Watching', 10.0, 20, '2025-02-18', NULL, '', 0, 1, '2025-04-26 21:00:07'),
(2, 1, 'Ore dake Level Up na Ken', 151807, 'Complete', 10.0, 12, '2024-07-20', '2024-07-20', 'BEST ANIME ALL THE SERIES', 0, 0, '2025-04-26 21:04:23'),
(3, 1, 'Ore dake Level Up na Ken: ReAwakening', 184694, 'Planning', 0.0, 0, NULL, NULL, '', 0, 0, '2025-04-26 21:05:02');

-- --------------------------------------------------------

--
-- Struttura della tabella `attivita_manga`
--

CREATE TABLE `attivita_manga` (
  `id` int(11) NOT NULL,
  `utente_id` int(11) NOT NULL,
  `titolo` varchar(255) NOT NULL,
  `riferimento_api` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `punteggio` decimal(3,1) DEFAULT NULL,
  `capitoli_letti` int(11) DEFAULT 0,
  `volumi_letti` int(11) DEFAULT 0,
  `data_inizio` date DEFAULT NULL,
  `data_fine` date DEFAULT NULL,
  `note` text DEFAULT NULL,
  `rereading` int(11) DEFAULT 0,
  `preferito` tinyint(1) DEFAULT 0,
  `data_ora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `attivita_manga`
--

INSERT INTO `attivita_manga` (`id`, `utente_id`, `titolo`, `riferimento_api`, `status`, `punteggio`, `capitoli_letti`, `volumi_letti`, `data_inizio`, `data_fine`, `note`, `rereading`, `preferito`, `data_ora`) VALUES
(1, 1, 'Garouden', 37921, 'Planning', 0.0, 0, 0, NULL, NULL, 'I love man', 0, 0, '2025-04-27 12:54:00'),
(2, 2, 'Garouden', 37921, 'Complete', 9.0, 238, 25, '2024-10-07', '2024-11-16', '', 0, 0, '2025-04-27 12:56:15'),
(3, 1, 'Na Honjaman Level Up', 105398, 'Complete', 10.0, 201, 0, '2025-04-27', '2025-04-27', '', 0, 0, '2025-04-27 13:38:10');

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `birthdate` date NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `tipo` enum('admin','utente') NOT NULL DEFAULT 'utente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `utenti`
--

INSERT INTO `utenti` (`id`, `username`, `first_name`, `last_name`, `phone`, `email`, `birthdate`, `profile_image`, `password`, `tipo`) VALUES
(1, 'Mark', 'Mario', 'Rossi', '3331234567', 'mario.rossi@example.com', '1990-05-22', 'default_profiles/omni_mark.png', '5bb866eeb99bad75a54ab865b84c9159', 'utente'),
(2, 'topolino', 'Luca', 'Abete', '23243554222', 'luca.abete@gmail.com', '1999-12-01', 'default_profiles/sinister_mark.png', '17a3b995fe38081ca0f9a87796e97a50', 'utente'),
(3, 'PixelNinja', 'Luca', 'Ambrosio', '3456789012', 'luca.ambrosio@gmail.com', '2009-12-29', 'default_profiles/sung_jin_woo.png', '8eb920cc8359b29063d093a9599097be', 'utente'),
(4, '8BitHero', 'Gianluca', 'Belvedere', '1122342112', 'gianluca.belvedere@gmail.com', '2000-01-22', 'default_profiles/king.png', '851d3521a0ad2dbd1e13a93cb6981c73', 'utente'),
(5, 'Diddy', 'Sean', 'Combos', '1232341234', 'diddy.prison@gmail.com', '1969-11-04', 'uploads/2641cfc5d36960564ea52f1f58ab3898.jpg', '98142de5eca84a3044d69ca8478f1682', 'utente');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `attivita_anime`
--
ALTER TABLE `attivita_anime`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `attivita_manga`
--
ALTER TABLE `attivita_manga`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utente_id` (`utente_id`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `attivita_anime`
--
ALTER TABLE `attivita_anime`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `attivita_manga`
--
ALTER TABLE `attivita_manga`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `attivita_anime`
--
ALTER TABLE `attivita_anime`
  ADD CONSTRAINT `attivita_anime_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `attivita_manga`
--
ALTER TABLE `attivita_manga`
  ADD CONSTRAINT `attivita_manga_ibfk_1` FOREIGN KEY (`utente_id`) REFERENCES `utenti` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
