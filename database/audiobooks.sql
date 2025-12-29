-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2025 at 05:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `audiobooks`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password_hash`, `role`, `created_at`, `profile_image_path`) VALUES
(1, 'drashti.616', 'drashtir.616@gmail.com', '$2y$10$DVuD3gVWEp93VBFN7rjVNelWQMo.M5KSpJzPnMduyWYpNs5rljmte', 'admin', '2025-08-15 06:14:09', '');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `genre` varchar(255) NOT NULL,
  `cover_image_path` varchar(255) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `genre`, `cover_image_path`, `description`) VALUES
(1, 'Dracula', 'Bram Stoker', 'Horror and supernatural', 'saved_image/cover_1757174674.jpg', 'Bram Stoker\'s Dracula is a Gothic horror novel that tells the story of the vampire Count Dracula\'s attempt to move from Transylvania to England to find new victims and spread the curse of vampirism. The novel, told through a series of letters, diary entries, and newspaper articles, begins with solicitor Jonathan Harker visiting Dracula\'s castle to finalize a real estate deal, only to realize he is a prisoner and barely escapes alive from the vampire and his three Brides. Dracula travels to England with boxes of soil, which he needs for his strength and to rest during the day, and begins to target Jonathan\'s fiancée Mina Murray and her friend Lucy Westenra. As Lucy falls victim to Dracula\'s attacks and transforms into a vampire, a group led by Professor Abraham Van Helsing, a Dutch doctor and vampire expert, uncovers Dracula\'s true nature and begins to hunt him down. They track down and neutralize most of Dracula\'s lairs in London, forcing him to flee back to Transylvania, but not before he attacks Mina and forces her to drink his blood, creating a psychic link between them and cursing her with vampirism. The hunters pursue Dracula back to his castle, and after a final confrontation, they manage to destroy him by decapitation and stabbing him through the heart, freeing Mina from her curse.'),
(2, 'Peter Pan', 'J.M. Barrie', 'Action and Adventure', 'saved_image/cover_1757177471.jpg', 'The story follows Peter Pan, a magical boy who refuses to grow up and lives in the whimsical realm of Neverland. He befriends Wendy Darling and her brothers, John and Michael, convincing them to fly with him to Neverland with the help of his pixie friend, Tinker Bell, where Wendy assumes the role of a mother to Peter\'s gang of Lost Boys. In Neverland, they embark on adventures, battling the villainous pirate Captain Hook, whom Peter defeated by famously feeding Hook\'s hand to a crocodile. Eventually, the Darling children and the Lost Boys decide to return home to London, realizing the importance of family and growing up. Peter, however, chooses to remain in Neverland, forever young and carefree, but promising to return periodically for Wendy\'s \"spring cleaning,\" a promise he eventually forgets as Wendy grows into adulthood. The story ends with Peter returning years later to find Wendy grown, but taking her daughter, Jane, to Neverland, continuing the cycle of eternal youth and adventure.'),
(4, 'The Thirty-nine Steps', 'John Buchan', 'Mystery', 'saved_image/cover_1757253279.png', 'The story follows Richard Hannay, a mining engineer who has returned to London from South Africa. Bored with his life, he is suddenly thrust into a life-or-death situation when a mysterious American journalist, who is a freelance spy, is murdered in his apartment. Hannay is now the main suspect and goes on the run to Scotland, pursued by both the police and a German spy ring known as \"The Black Stone\". To clear his name, Hannay must decipher a code left by the dead journalist and expose the spy ring, who are trying to steal British military secrets. He learns that the key to the mystery lies in a baffling enigma: the \"thirty-nine steps\".'),
(5, 'Janet of the Dunes', 'Harriet Theresa Comstock ', 'Nautical & Marine Fiction', 'saved_image/cover_1757253971.jpg', 'The story is a \"simple story of shorelife in a coastal town on Long Island\". It follows Janet, the daughter of a \"wayward mother,\" who is raised by a man named Captain \"Billy Daddy\" who works at a life-saving station. An artist comes to the dunes, falls in love with Janet, and asks to paint her. Janet\'s wealthy biological father then arrives. The narrative explores Janet\'s struggle to transition into womanhood while preserving her longing for the freedom of her youth against the backdrop of an encroaching urban presence.'),
(6, 'Bunny Rabbit\'s Diary', 'Mary Frances Blaisdell', 'Animals & Nature', 'saved_image/cover_1757254318.jpg', 'Bunny Rabbit was given a blank book to record his adventures with his brothers and his friends. Hear what happens when they try to go sledding, try to learn to swim, and try to fly, and keep away from Jip, the farmer\'s dog. Listen to these and other exciting stories about Bunny, Bobtail, and Billy Rabbit, Sammy Red Squirrel, Bobby Gray Squirrel, and others.'),
(7, 'The Romance of the Forest', 'Ann Radcliffe', 'Gothic Fiction', 'saved_image/cover_1757254916.jpg', 'The Romance of the Forest is a gothic novel by Ann Radcliffe, published in 1791. It is considered an early and well-known example of the gothic genre. The plot follows a young woman named Adeline who, after fleeing Paris with her family, finds refuge in a ruined abbey in the forest. The abbey is a mysterious and sinister place inhabited by the Marquis de Montalt, a man with a threatening connection to Adeline. The book combines a romantic subplot and a murder mystery while exploring themes of suspense through seemingly supernatural events that are later given rational explanations. The novel is also recognized for its detailed descriptions of nature and its focus on the heroine\'s emotions.'),
(8, 'Wuthering Heights ', 'Emily Brontë', 'Romance Novel', 'saved_image/cover_1757258006.jpg', 'Emily Brontë\'s Wuthering Heights unfolds a tumultuous tale of passionate yet destructive love, revenge, and social stratification set amidst the wild and rugged Yorkshire moors. It tells the story of Heathcliff, a mysterious orphan adopted by the Earnshaw family of Wuthering Heights, and his intense bond with Catherine, the family\'s spirited daughter. However, societal expectations lead Catherine to marry the wealthy and refined Edgar Linton, despite her deep love for Heathcliff. Consumed by jealousy and resentment, Heathcliff embarks on a path of revenge, manipulating the lives and fortunes of both families, which results in further tragedy and suffering spanning across generations. Ultimately, the narrative concludes with the deaths of Catherine and Heathcliff, but a glimmer of hope emerges through the eventual union of the next generation, Catherine Linton and Hareton Earnshaw, suggesting the possibility of healing and redemption.'),
(9, 'The Picture of Dorian Gray', 'Oscar Wilde', 'Horror & Supernatural Fiction', 'saved_image/cover_1757261735.jpg', 'Dorian Gray, a young man of wealth and stature in late 1800\'s London, meets Lord Henry Wotton while posing for a portrait by his friend Basil Hallward. Once the painting is complete, Dorian realizes that it will always be young and attractive, while he will be forced to age and wither with the years. Carelessly, he wishes the opposite were true. What happens is a treatise on morals, self-indulgence and how crucial personal responsibility is towards one\'s self.');

-- --------------------------------------------------------

--
-- Table structure for table `book_parts`
--

CREATE TABLE `book_parts` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `part_number` int(11) NOT NULL,
  `audio_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_parts`
--

INSERT INTO `book_parts` (`id`, `book_id`, `part_number`, `audio_path`) VALUES
(1, 1, 1, 'saved_audio/book_1/part_1.mp3'),
(2, 1, 2, 'saved_audio/book_1/part_2.mp3'),
(3, 2, 1, 'saved_audio/book_2/part_1.mp3'),
(5, 4, 1, 'saved_audio/book_4/part_1.mp3'),
(6, 4, 2, 'saved_audio/book_4/part_2.mp3'),
(7, 4, 3, 'saved_audio/book_4/part_3.mp3'),
(8, 1, 3, 'saved_audio/book_1/part_3.mp3'),
(9, 1, 4, 'saved_audio/book_1/part_4.mp3'),
(10, 6, 1, 'saved_audio/book_6/part_1.mp3'),
(11, 6, 2, 'saved_audio/book_6/part_2.mp3'),
(12, 6, 3, 'saved_audio/book_6/part_3.mp3'),
(13, 6, 4, 'saved_audio/book_6/part_4.mp3'),
(14, 6, 5, 'saved_audio/book_6/part_5.mp3'),
(15, 6, 6, 'saved_audio/book_6/part_6.mp3'),
(16, 6, 7, 'saved_audio/book_6/part_7.mp3'),
(17, 6, 8, 'saved_audio/book_6/part_8.mp3'),
(18, 6, 9, 'saved_audio/book_6/part_9.mp3'),
(19, 6, 10, 'saved_audio/book_6/part_10.mp3'),
(20, 6, 11, 'saved_audio/book_6/part_11.mp3'),
(21, 6, 12, 'saved_audio/book_6/part_12.mp3'),
(22, 6, 13, 'saved_audio/book_6/part_13.mp3'),
(23, 6, 14, 'saved_audio/book_6/part_14.mp3'),
(24, 6, 15, 'saved_audio/book_6/part_15.mp3'),
(25, 5, 1, 'saved_audio/book_5/part_1.mp3'),
(26, 5, 2, 'saved_audio/book_5/part_2.mp3'),
(27, 5, 3, 'saved_audio/book_5/part_3.mp3'),
(29, 5, 5, 'saved_audio/book_5/part_5.mp3'),
(30, 5, 6, 'saved_audio/book_5/part_6.mp3'),
(31, 5, 7, 'saved_audio/book_5/part_7.mp3'),
(32, 5, 8, 'saved_audio/book_5/part_8.mp3'),
(33, 5, 9, 'saved_audio/book_5/part_9.mp3'),
(34, 5, 10, 'saved_audio/book_5/part_10.mp3'),
(36, 5, 12, 'saved_audio/book_5/part_12.mp3'),
(37, 5, 13, 'saved_audio/book_5/part_13.mp3'),
(38, 5, 14, 'saved_audio/book_5/part_14.mp3'),
(39, 5, 11, 'saved_audio/book_5/part_11.mp3'),
(40, 5, 4, 'saved_audio/book_5/part_4.mp3'),
(41, 7, 1, 'saved_audio/book_7/part_1.mp3'),
(42, 7, 2, 'saved_audio/book_7/part_2.mp3'),
(43, 7, 3, 'saved_audio/book_7/part_3.mp3'),
(44, 7, 4, 'saved_audio/book_7/part_4.mp3'),
(45, 7, 5, 'saved_audio/book_7/part_5.mp3'),
(46, 7, 6, 'saved_audio/book_7/part_6.mp3'),
(47, 7, 7, 'saved_audio/book_7/part_7.mp3'),
(48, 2, 2, 'saved_audio/book_2/part_2.mp3'),
(49, 2, 3, 'saved_audio/book_2/part_3.mp3'),
(50, 2, 4, 'saved_audio/book_2/part_4.mp3'),
(51, 2, 5, 'saved_audio/book_2/part_5.mp3'),
(52, 2, 6, 'saved_audio/book_2/part_6.mp3'),
(53, 8, 1, 'saved_audio/book_8/part_1.mp3'),
(54, 8, 2, 'saved_audio/book_8/part_2.mp3'),
(55, 8, 3, 'saved_audio/book_8/part_3.mp3'),
(56, 8, 4, 'saved_audio/book_8/part_4.mp3'),
(57, 8, 5, 'saved_audio/book_8/part_5.mp3'),
(58, 9, 1, 'saved_audio/book_9/part_1.mp3'),
(59, 9, 2, 'saved_audio/book_9/part_2.mp3'),
(60, 9, 3, 'saved_audio/book_9/part_3.mp3'),
(61, 9, 4, 'saved_audio/book_9/part_4.mp3'),
(62, 9, 5, 'saved_audio/book_9/part_5.mp3'),
(63, 9, 6, 'saved_audio/book_9/part_6.mp3'),
(64, 9, 7, 'saved_audio/book_9/part_7.mp3'),
(65, 9, 8, 'saved_audio/book_9/part_8.mp3'),
(66, 9, 9, 'saved_audio/book_9/part_9.mp3'),
(67, 9, 10, 'saved_audio/book_9/part_10.mp3'),
(68, 9, 11, 'saved_audio/book_9/part_11.mp3'),
(69, 9, 12, 'saved_audio/book_9/part_12.mp3'),
(70, 9, 13, 'saved_audio/book_9/part_13.mp3');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','approved','ignored') NOT NULL DEFAULT 'pending',
  `reply_message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `username`, `email`, `subject`, `message`, `status`, `reply_message`, `sent_at`) VALUES
(1, 1, 'priya.99', 'poonyesha1@gmail.com', 'Problem with Audio Playback on Mobile', 'Hello Support Team,\r\nI’ve been trying to listen to an audiobook on my Android phone, but the playback keeps stopping after a few minutes. I’ve checked my internet connection and it works fine for other apps. Could you please look into this issue?', 'approved', 'Hello priya.99,\r\n\r\nSorry to hear your audiobook keeps stopping. Please try these quick steps:\r\n\r\nRestart the app\r\n\r\nClear the app cache\r\n\r\nCheck battery saver/background restrictions\r\n\r\nSwitch between Wi-Fi and mobile data\r\n\r\nUpdate the app to the latest version\r\n\r\nIf the issue continues, kindly share your device model, Android version, and audiobook title so we can assist further.\r\n\r\nBest regards,\r\nThe Storyverse Support Team', '2025-08-19 13:39:36'),
(2, 2, 'vishwa.2169', 'vishwa2169@gmail.com', 'About your website', 'Your website is like a trash...', 'ignored', '', '2025-08-19 14:29:39'),
(3, 2, 'vishwa.2169', 'vishwa2169@gmail.com', 'About your website', 'Your website\'s UI is not so good and the Storybooks you provide are so boring', 'ignored', '', '2025-08-19 14:45:12'),
(7, 3, 'meetmand_1146', 'meet123@gmail.com', 'About your website', 'I will give you 0 out of 5 in rating', 'pending', NULL, '2025-11-25 15:15:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `role` varchar(7) NOT NULL DEFAULT 'user',
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `profile_image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `role`, `email`, `password_hash`, `created_at`, `profile_image_path`) VALUES
(2, 'priya.99', 'user', 'poonyesha1@gmail.com', '$2y$10$oAKlplnHLGWOi26m0k4dyeBi7zIdFuSvoWZmjgBaBn3l16anpH/aC', '2025-09-05 18:23:59', ''),
(3, 'meetmand_1146', 'user', 'meet123@gmail.com', '$2y$10$ckOzNolG.6YURw3m2MMXFudp6uDxyiGMYqS3hk6btOArl1SR2cZru', '2025-09-08 07:58:43', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `book_parts`
--
ALTER TABLE `book_parts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `book_parts`
--
ALTER TABLE `book_parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `book_parts`
--
ALTER TABLE `book_parts`
  ADD CONSTRAINT `book_parts_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
