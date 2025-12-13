-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Sob 13. pro 2025, 21:16
-- Verze serveru: 10.4.32-MariaDB
-- Verze PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `rsp`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `articles`
--

CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `editor_id` int(11) DEFAULT NULL,
  `issue_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `status` enum('podano','ceka_na_recenzenty','v_recenzi','prijato','zamitnuto','vraceno_k_oprave') NOT NULL DEFAULT 'podano',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `decision_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `articles`
--

INSERT INTO `articles` (`id`, `user_id`, `editor_id`, `issue_id`, `title`, `filename`, `status`, `created_at`, `decision_date`) VALUES
(1, 6, NULL, 1, 'How to Write a Professional Business Letter', '6_1764345869_Business Letter.docx', 'prijato', '2025-11-28 16:04:29', '2025-12-13 21:02:49');

-- --------------------------------------------------------

--
-- Struktura tabulky `article_messages`
--

CREATE TABLE `article_messages` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Vypisuji data pro tabulku `article_messages`
--

INSERT INTO `article_messages` (`id`, `article_id`, `sender_id`, `message`, `created_at`) VALUES
(1, 1, 5, 'Nelibí se mi obsah', '2025-12-13 19:44:45'),
(2, 1, 6, 'S obsahem není nic v nepořádku.', '2025-12-13 19:51:57');

-- --------------------------------------------------------

--
-- Struktura tabulky `issues`
--

CREATE TABLE `issues` (
  `id` int(11) NOT NULL,
  `nazev` varchar(255) NOT NULL,
  `rocnik` int(11) NOT NULL DEFAULT 2025,
  `cislo` int(11) NOT NULL DEFAULT 1,
  `deadline` date NOT NULL,
  `max_capacity` int(11) NOT NULL DEFAULT 10,
  `status` enum('open','closed','published') NOT NULL DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `issues`
--

INSERT INTO `issues` (`id`, `nazev`, `rocnik`, `cislo`, `deadline`, `max_capacity`, `status`) VALUES
(1, 'Jaro 2025 - Černé díry', 2025, 1, '2026-03-31', 5, 'open'),
(2, 'Léto 2025 - AI v kosmonautice', 2025, 1, '2026-06-30', 10, 'open'),
(3, 'Podzim 2025 - Speciál: Mars', 2025, 1, '2026-09-30', 3, 'open'),
(4, 'Testovací Zima 2026', 2026, 1, '2025-12-26', 10, 'open');

-- --------------------------------------------------------

--
-- Struktura tabulky `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected','completed') NOT NULL DEFAULT 'pending',
  `deadline` date DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `role`
--

CREATE TABLE `role` (
  `id_role` int(11) NOT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `role`
--

INSERT INTO `role` (`id_role`, `role`) VALUES
(1, 'Admin'),
(5, 'Autor'),
(7, 'Čtenář'),
(6, 'HelpDesk'),
(4, 'Recenzent'),
(3, 'Redaktor'),
(2, 'Šéfredaktor');

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `jmeno` varchar(50) NOT NULL,
  `prijmeni` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `heslo` varchar(255) NOT NULL,
  `id_role` int(11) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `users`
--

INSERT INTO `users` (`id`, `jmeno`, `prijmeni`, `email`, `heslo`, `id_role`, `active`) VALUES
(1, 'Test', 'User', 'testuser@example.com', '$2y$10$YWDgK0rXG8qf1AKfLZXluuFnUwEv0ol4pnXXPys3XVJOFT7Nzs4W6', 7, 1),
(2, 'Admin', 'Admin', 'admin@example.com', '$2y$10$bm.8SCGV1CwfYaIdw6tZ4u9gKul5fvAO0l53sWRzFZrlyeIfidLP6', 1, 1),
(4, 'Test', 'Redaktor', 'redaktor@example.com', '$2y$10$TXoWGXk0mAUoRQVzciEreOG.omhcgXtMGQA8j8Hx2QcQm1WcyfwDm', 3, 1),
(5, 'Test', 'Šéfredaktor', 'sefredaktor@example.com', '$2y$10$7RwKQvxvM.7hGdMRSV7m1.aytbHNhiG6Fi6MmJO77O8eqlwbV3Kgy', 2, 1),
(6, 'Test', 'Autor', 'autor@example.com', '$2y$10$gbmDWl2ZJOZImj8YkLczzegGkluW3cWUBPCfdRJleCdMrqbIvjy5C', 5, 1),
(7, 'Test', 'HelpDesk', 'helpdesk@example.com', '$2y$10$GThpOaQ07T93faflGMKBXOHX.ENewf2Sj4ukti2zOwRkFuVhL.Ctq', 6, 1),
(8, 'Test', 'Recenzent', 'recenzent@example.com', '$2y$10$slVvvE9wfro6RPjmh7zxveD.FfejKcPRU4pkG51hkSoDdtI5gGpvS', 4, 1),
(9, 'Test', 'UserMarta', 'marta@example.com', '$2y$10$D60hOduCKMSmWD3//OLX9.GY7lUDqzhzP0yKl5odxpjNcEn.bjmEm', 7, 1),
(10, 'Test', 'UserJiří', 'jiri@example.com', '$2y$10$roaX0wVHx4JFmzNi5kUv/ONCjn7/E/ytJKaznAnewws0L3L39KDuS', 7, 1),
(12, 'Test', 'UserOlga', 'olga@example.com', '$2y$10$6MyaaYpQQarXyybtPbZ9d.21/WkhyZBuLuh.D0PUrIiZUPkbvbFC2', 7, 1),
(13, 'Test', 'UserTomáš', 'tomas@example.com', '$2y$10$hEjEEvBbb2FdMKd6FBt3hOgiief2mG3ZcD37bY57MXnWXzQFHKR3a', 7, 1);

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_articles_users` (`user_id`),
  ADD KEY `fk_article_editor` (`editor_id`);

--
-- Indexy pro tabulku `article_messages`
--
ALTER TABLE `article_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexy pro tabulku `issues`
--
ALTER TABLE `issues`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

--
-- Indexy pro tabulku `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id_role`),
  ADD UNIQUE KEY `roleunique` (`role`);

--
-- Indexy pro tabulku `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mailUnique` (`email`),
  ADD KEY `fk_users_role` (`id_role`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pro tabulku `article_messages`
--
ALTER TABLE `article_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pro tabulku `issues`
--
ALTER TABLE `issues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pro tabulku `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pro tabulku `role`
--
ALTER TABLE `role`
  MODIFY `id_role` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pro tabulku `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `fk_article_editor` FOREIGN KEY (`editor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_articles_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `article_messages`
--
ALTER TABLE `article_messages`
  ADD CONSTRAINT `article_messages_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`id_role`) REFERENCES `role` (`id_role`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
