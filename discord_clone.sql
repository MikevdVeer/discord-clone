-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Gegenereerd op: 19 mrt 2025 om 17:40
-- Serverversie: 10.4.32-MariaDB
-- PHP-versie: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `discord_clone`
--
CREATE DATABASE IF NOT EXISTS `discord_clone` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `discord_clone`;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `channels`
--

CREATE TABLE `channels` (
  `id` int(11) NOT NULL,
  `server_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('text','voice') DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `channels`
--

INSERT INTO `channels` (`id`, `server_id`, `name`, `type`, `created_at`) VALUES
(1, 1, 'general', 'text', '2025-03-19 15:59:57'),
(2, 1, 'gaming', 'text', '2025-03-19 15:59:57'),
(3, 1, 'off-topic', 'text', '2025-03-19 15:59:57'),
(4, 1, 'voice-chat', 'voice', '2025-03-19 15:59:57'),
(5, 2, 'general', 'text', '2025-03-19 15:59:57'),
(6, 2, 'math', 'text', '2025-03-19 15:59:57'),
(7, 2, 'science', 'text', '2025-03-19 15:59:57'),
(8, 2, 'study-room', 'voice', '2025-03-19 15:59:57'),
(9, 3, 'general', 'text', '2025-03-19 15:59:57'),
(10, 3, 'programming', 'text', '2025-03-19 15:59:57'),
(11, 3, 'hardware', 'text', '2025-03-19 15:59:57'),
(12, 3, 'tech-talk', 'voice', '2025-03-19 15:59:57'),
(13, 4, 'general', 'text', '2025-03-19 15:59:57'),
(14, 4, 'artwork', 'text', '2025-03-19 15:59:57'),
(15, 4, 'critique', 'text', '2025-03-19 15:59:57'),
(16, 4, 'voice-chat', 'voice', '2025-03-19 15:59:57');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `channel_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `messages`
--

INSERT INTO `messages` (`id`, `channel_id`, `user_id`, `content`, `created_at`) VALUES
(1, 3, 1, 'Hello', '2025-03-19 16:00:58'),
(2, 3, 1, 'Hello', '2025-03-19 16:02:06'),
(3, 2, 1, 'Hello', '2025-03-19 16:05:48'),
(4, 2, 1, 'asd', '2025-03-19 16:17:30'),
(5, 2, 1, 'asd', '2025-03-19 16:17:31'),
(6, 2, 1, 'asd', '2025-03-19 16:17:31'),
(7, 2, 1, 'Hi', '2025-03-19 16:35:45'),
(8, 2, 1, 'asd', '2025-03-19 16:36:03'),
(9, 2, 1, 'Hi', '2025-03-19 16:39:36');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `servers`
--

CREATE TABLE `servers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `servers`
--

INSERT INTO `servers` (`id`, `name`, `description`, `owner_id`, `created_at`) VALUES
(1, 'Gaming Community', 'A place for gamers to hang out', 1, '2025-03-19 15:59:57'),
(2, 'Study Group', 'Study and homework help', 2, '2025-03-19 15:59:57'),
(3, 'Tech Enthusiasts', 'Discussion about technology', 3, '2025-03-19 15:59:57'),
(4, 'Art Gallery', 'Share and discuss artwork', 4, '2025-03-19 15:59:57');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `server_members`
--

CREATE TABLE `server_members` (
  `id` int(11) NOT NULL,
  `server_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role` enum('owner','admin','member') DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `server_members`
--

INSERT INTO `server_members` (`id`, `server_id`, `user_id`, `role`, `joined_at`) VALUES
(1, 1, 1, 'owner', '2025-03-19 15:59:57'),
(2, 1, 2, 'member', '2025-03-19 15:59:57'),
(3, 1, 3, 'member', '2025-03-19 15:59:57'),
(4, 1, 4, 'member', '2025-03-19 15:59:57'),
(5, 2, 2, 'owner', '2025-03-19 15:59:57'),
(6, 2, 1, 'member', '2025-03-19 15:59:57'),
(7, 2, 3, 'member', '2025-03-19 15:59:57'),
(8, 3, 3, 'owner', '2025-03-19 15:59:57'),
(9, 3, 1, 'member', '2025-03-19 15:59:57'),
(10, 3, 4, 'member', '2025-03-19 15:59:57'),
(11, 4, 4, 'owner', '2025-03-19 15:59:57'),
(12, 4, 2, 'member', '2025-03-19 15:59:57'),
(13, 4, 3, 'member', '2025-03-19 15:59:57');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT 'default.png',
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `avatar`, `last_activity`, `created_at`) VALUES
(1, 'John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png', '2025-03-19 16:34:56', '2025-03-19 15:59:57'),
(2, 'Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png', '2025-03-19 15:59:57', '2025-03-19 15:59:57'),
(3, 'Mike Johnson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png', '2025-03-19 15:59:57', '2025-03-19 15:59:57'),
(4, 'Sarah Wilson', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png', '2025-03-19 15:59:57', '2025-03-19 15:59:57');

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `channels`
--
ALTER TABLE `channels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `server_id` (`server_id`);

--
-- Indexen voor tabel `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `channel_id` (`channel_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexen voor tabel `servers`
--
ALTER TABLE `servers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexen voor tabel `server_members`
--
ALTER TABLE `server_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `server_id` (`server_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexen voor tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `channels`
--
ALTER TABLE `channels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT voor een tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT voor een tabel `servers`
--
ALTER TABLE `servers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT voor een tabel `server_members`
--
ALTER TABLE `server_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT voor een tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `channels`
--
ALTER TABLE `channels`
  ADD CONSTRAINT `channels_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`);

--
-- Beperkingen voor tabel `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Beperkingen voor tabel `servers`
--
ALTER TABLE `servers`
  ADD CONSTRAINT `servers_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);

--
-- Beperkingen voor tabel `server_members`
--
ALTER TABLE `server_members`
  ADD CONSTRAINT `server_members_ibfk_1` FOREIGN KEY (`server_id`) REFERENCES `servers` (`id`),
  ADD CONSTRAINT `server_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
