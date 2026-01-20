-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Gen 20, 2026 alle 18:47
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
-- Database: `menu`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `allergeni`
--

CREATE TABLE `allergeni` (
  `id_allergene` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `allergeni`
--

INSERT INTO `allergeni` (`id_allergene`, `nome`) VALUES
(1, 'Glutine'),
(2, 'Lattosio'),
(3, 'Frutta a guscio'),
(4, 'Soia'),
(5, 'Uova');

-- --------------------------------------------------------

--
-- Struttura della tabella `caratteristiche`
--

CREATE TABLE `caratteristiche` (
  `id_caratteristica` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `caratteristiche`
--

INSERT INTO `caratteristiche` (`id_caratteristica`, `nome`) VALUES
(1, 'Vegetariano'),
(2, 'Vegano'),
(3, 'Senza glutine'),
(4, 'Piccante'),
(5, 'Biologico');

-- --------------------------------------------------------

--
-- Struttura della tabella `categorie`
--

CREATE TABLE `categorie` (
  `id_categoria` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `ordine` int(11) NOT NULL,
  `visibile` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `categorie`
--

INSERT INTO `categorie` (`id_categoria`, `nome`, `ordine`, `visibile`) VALUES
(1, 'Pizze Classiche', 1, 1),
(2, 'Pizze Speciali', 2, 1),
(3, 'Antipasti', 3, 1),
(4, 'Bevande', 4, 1),
(5, 'Dolci', 5, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `prodotti`
--

CREATE TABLE `prodotti` (
  `id_prodotto` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descrizione` varchar(255) DEFAULT NULL,
  `prezzo` decimal(6,2) NOT NULL,
  `disponibile` tinyint(1) NOT NULL DEFAULT 1,
  `immagine` varchar(255) DEFAULT NULL,
  `id_categoria` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `prodotti`
--

INSERT INTO `prodotti` (`id_prodotto`, `nome`, `descrizione`, `prezzo`, `disponibile`, `immagine`, `id_categoria`) VALUES
(1, 'Margherita', 'Pomodoro, mozzarella e basilico', 6.50, 1, 'margherita.jpg', 1),
(2, 'Diavola', 'Pomodoro, mozzarella e salame piccante', 7.50, 1, 'diavola.jpg', 1),
(3, 'Quattro Formaggi', 'Mozzarella, gorgonzola, fontina, parmigiano', 8.50, 1, '4formaggi.jpg', 2),
(4, 'Pizza Vegana', 'Pomodoro, verdure grigliate', 8.00, 1, 'vegana.jpg', 2),
(5, 'Bruschette', 'Pane tostato con pomodoro', 4.50, 1, 'bruschette.jpg', 3),
(6, 'Acqua Naturale', 'Bottiglia 0.5L', 1.50, 1, 'acqua.jpg', 4),
(7, 'Tiramisu', 'Dolce al mascarpone', 4.00, 1, 'tiramisu.jpg', 5),
(8, 'Capricciosa', 'Pomodoro, mozzarella, prosciutto, funghi, carciofi', 8.50, 1, 'capricciosa.jpg', 1),
(9, 'Quattro Stagioni', 'Pomodoro, mozzarella, prosciutto, funghi, carciofi, olive', 8.50, 1, '4stagioni.jpg', 1),
(10, 'Ortolana', 'Pomodoro, mozzarella, verdure grigliate', 7.50, 1, 'ortolana.jpg', 2),
(11, 'Marinara', 'Pomodoro, aglio, origano', 6.00, 1, 'marinara.jpg', 1),
(12, 'Pizza Senza Glutine', 'Pomodoro, mozzarella senza lattosio', 9.50, 1, 'sglutine.jpg', 2),
(13, 'Focaccia', 'Olio extravergine e rosmarino', 4.00, 1, 'focaccia.jpg', 3),
(14, 'Birra Media', 'Birra bionda 0.4L', 4.50, 1, 'birra.jpg', 4),
(15, 'Coca Cola', 'Bibita in lattina 33cl', 3.00, 1, 'cocacola.jpg', 4),
(16, 'Sorbetto al Limone', 'Sorbetto artigianale', 4.00, 1, 'sorbetto.jpg', 5);

-- --------------------------------------------------------

--
-- Struttura della tabella `prodotti_allergeni`
--

CREATE TABLE `prodotti_allergeni` (
  `id_prodotto` int(11) NOT NULL,
  `id_allergene` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `prodotti_allergeni`
--

INSERT INTO `prodotti_allergeni` (`id_prodotto`, `id_allergene`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(3, 2),
(5, 1),
(7, 2),
(7, 5),
(8, 1),
(8, 2),
(9, 1),
(9, 2),
(10, 2),
(11, 1),
(14, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `prodotti_caratteristiche`
--

CREATE TABLE `prodotti_caratteristiche` (
  `id_prodotto` int(11) NOT NULL,
  `id_caratteristica` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `prodotti_caratteristiche`
--

INSERT INTO `prodotti_caratteristiche` (`id_prodotto`, `id_caratteristica`) VALUES
(1, 1),
(2, 4),
(4, 1),
(4, 2),
(5, 5),
(8, 1),
(9, 2),
(10, 3),
(11, 5),
(15, 2);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `allergeni`
--
ALTER TABLE `allergeni`
  ADD PRIMARY KEY (`id_allergene`);

--
-- Indici per le tabelle `caratteristiche`
--
ALTER TABLE `caratteristiche`
  ADD PRIMARY KEY (`id_caratteristica`);

--
-- Indici per le tabelle `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indici per le tabelle `prodotti`
--
ALTER TABLE `prodotti`
  ADD PRIMARY KEY (`id_prodotto`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indici per le tabelle `prodotti_allergeni`
--
ALTER TABLE `prodotti_allergeni`
  ADD PRIMARY KEY (`id_prodotto`,`id_allergene`),
  ADD KEY `id_allergene` (`id_allergene`);

--
-- Indici per le tabelle `prodotti_caratteristiche`
--
ALTER TABLE `prodotti_caratteristiche`
  ADD PRIMARY KEY (`id_prodotto`,`id_caratteristica`),
  ADD KEY `id_caratteristica` (`id_caratteristica`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `allergeni`
--
ALTER TABLE `allergeni`
  MODIFY `id_allergene` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `caratteristiche`
--
ALTER TABLE `caratteristiche`
  MODIFY `id_caratteristica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `prodotti`
--
ALTER TABLE `prodotti`
  MODIFY `id_prodotto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `prodotti`
--
ALTER TABLE `prodotti`
  ADD CONSTRAINT `prodotti_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categorie` (`id_categoria`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Limiti per la tabella `prodotti_allergeni`
--
ALTER TABLE `prodotti_allergeni`
  ADD CONSTRAINT `prodotti_allergeni_ibfk_1` FOREIGN KEY (`id_prodotto`) REFERENCES `prodotti` (`id_prodotto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `prodotti_allergeni_ibfk_2` FOREIGN KEY (`id_allergene`) REFERENCES `allergeni` (`id_allergene`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Limiti per la tabella `prodotti_caratteristiche`
--
ALTER TABLE `prodotti_caratteristiche`
  ADD CONSTRAINT `prodotti_caratteristiche_ibfk_1` FOREIGN KEY (`id_prodotto`) REFERENCES `prodotti` (`id_prodotto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `prodotti_caratteristiche_ibfk_2` FOREIGN KEY (`id_caratteristica`) REFERENCES `caratteristiche` (`id_caratteristica`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
