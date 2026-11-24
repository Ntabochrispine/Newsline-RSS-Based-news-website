-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2026 at 02:37 PM
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
-- Database: `rss_news_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `region` enum('kenya','africa','world') NOT NULL DEFAULT 'world',
  `country_code` char(2) DEFAULT NULL,
  `classification_confidence` decimal(5,2) DEFAULT NULL,
  `classification_source` enum('manual','rss_rules','author_review') NOT NULL DEFAULT 'manual',
  `classification_needs_review` tinyint(1) NOT NULL DEFAULT 0,
  `classification_notes` varchar(500) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_breaking` tinyint(1) NOT NULL DEFAULT 0,
  `scheduled_at` datetime DEFAULT NULL,
  `rss_feed_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `featured_image` varchar(2048) DEFAULT NULL,
  `source_name` varchar(150) DEFAULT NULL,
  `source_url` varchar(2048) DEFAULT NULL,
  `article_type` enum('user','rss') NOT NULL DEFAULT 'user',
  `status` enum('draft','pending','published','rejected') NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `user_id`, `category_id`, `region`, `country_code`, `classification_confidence`, `classification_source`, `classification_needs_review`, `classification_notes`, `is_featured`, `is_breaking`, `scheduled_at`, `rss_feed_id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `source_name`, `source_url`, `article_type`, `status`, `published_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 5, 'kenya', NULL, NULL, 'manual', 0, NULL, 0, 0, NULL, NULL, 'My first Article', 'my-first-article', 'dfgh', 'fghjksdndjksjk', NULL, NULL, NULL, 'user', 'published', '2026-11-24 15:26:15', '2026-11-24 12:16:32', '2026-11-24 12:26:15', NULL),
(2, 3, 7, 'world', NULL, NULL, 'manual', 0, NULL, 0, 0, NULL, NULL, 'fasfd', 'fasfd', 'man lottery', 'A man win millions in lottery', 'assets/images/uploads/958f929fd0c4e00202426365cce5e500.jpg', NULL, NULL, 'user', 'published', '2026-11-24 15:26:18', '2026-11-24 12:17:22', '2026-11-24 12:26:18', NULL),
(3, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Cheers and applause as Jensen Huang returns to Nvidia office', 'cheers-and-applause-as-jensen-huang-returns-to-nvidia-office', 'BBC\'s Lily Jamali reports from Nvidia\'s headquarters, where she gauges the reaction to Huang\'s return after Trump says he brokered a deal.', 'BBC\'s Lily Jamali reports from Nvidia\'s headquarters, where she gauges the reaction to Huang\'s return after Trump says he brokered a deal.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/dd69/live/495b9350-e7d3-11f1-99b6-a35a62fa4264.jpg', 'BBC News', 'https://www.bbc.co.uk/news/videos/ckedq2edqegzo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 02:21:41', '2026-11-24 12:20:31', '2026-11-24 12:20:31', NULL),
(4, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Prince William meets Burnham on first Downing Street visit', 'prince-william-meets-burnham-on-first-downing-street-visit', 'The Prince of Wales is meeting Andy Burnham with homelessness expected to be among the topics.', 'The Prince of Wales is meeting Andy Burnham with homelessness expected to be among the topics.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/394d/live/5d721e40-e800-11f1-99b6-a35a62fa4264.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cm5y1xk1gpqvo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 12:37:53', '2026-11-24 12:20:32', '2026-11-24 12:20:32', NULL),
(5, NULL, NULL, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Triple killer admits murdering Ian Huntley in jail', 'triple-killer-admits-murdering-ian-huntley-in-jail', 'Anthony Russell changes his plea on the second day of his trial at Newcastle Crown Court.', 'Anthony Russell changes his plea on the second day of his trial at Newcastle Crown Court.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/be90/live/aad9c570-e729-11f1-914b-6d226d813a52.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cm930y07p0j2o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 12:59:53', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(6, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Inquiry expected into Birmingham housing scheme \'exploitation\' after BBC investigation', 'inquiry-expected-into-birmingham-housing-scheme-exploitation-after-bbc-investigation', 'Birmingham has more than 34,000 people living in Supported Exempt Accommodation.', 'Birmingham has more than 34,000 people living in Supported Exempt Accommodation.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/8509/live/e31149b0-e7f9-11f1-99a1-253766e7ad8b.png', 'BBC News', 'https://www.bbc.co.uk/news/articles/cm0rk19kl52do?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 11:47:53', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(7, NULL, 6, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Zoe Kleinman: My week with Meta\'s AI agent Muse ahead of its UK launch', 'zoe-kleinman-my-week-with-meta-s-ai-agent-muse-ahead-of-its-uk-launch', 'Meta wants Muse to do for AI what WhatsApp did for messaging - here\'s what the BBC\'s technology editor found when she used it.', 'Meta wants Muse to do for AI what WhatsApp did for messaging - here\'s what the BBC\'s technology editor found when she used it.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/806c/live/2d26fe50-e7f5-11f1-99a1-253766e7ad8b.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c9gkld2l38y6o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 10:33:00', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(8, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Gisèle Pelicot \'humiliated\' after nude photos sent to media to promote book', 'gis-le-pelicot-humiliated-after-nude-photos-sent-to-media-to-promote-book', 'The publisher of an essay on coercive control apologises for \"an unfortunate and inappropriate communication\".', 'The publisher of an essay on coercive control apologises for \"an unfortunate and inappropriate communication\".', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/b1cd/live/0fff68f0-e776-11f1-a8a9-d7c9e8a923b4.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cm3eg502nxw2o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-23 22:24:47', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(9, NULL, 10, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Afghan women with UK university offers \'heartbroken\' after visas stopped', 'afghan-women-with-uk-university-offers-heartbroken-after-visas-stopped', 'In March this year, the UK Home Office announced an \"emergency visa brake\" on study visas for applicants from Afghanistan.', 'In March this year, the UK Home Office announced an \"emergency visa brake\" on study visas for applicants from Afghanistan.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/7c60/live/8e8eebb0-e77f-11f1-8707-dd4660fa8d9d.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c6vg25e1v17wo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 07:00:47', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(10, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Nato chief vows defence against space threat after Russian nuclear satellite reports', 'nato-chief-vows-defence-against-space-threat-after-russian-nuclear-satellite-reports', 'German media said Russia planned to launch a satellite with a nuclear warhead into space in coming weeks.', 'German media said Russia planned to launch a satellite with a nuclear warhead into space in coming weeks.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/f9e8/live/05da8d30-e7d7-11f1-a094-5fdc692f3e33.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c9x2er1ry2mzo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 11:58:51', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(11, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Pope Francis\' diary reveals claims he saw the devil three times', 'pope-francis-diary-reveals-claims-he-saw-the-devil-three-times', 'Published extracts from the late pontiff\'s diary claim one of the visions happened \"in the choir loft\" during Mass.', 'Published extracts from the late pontiff\'s diary claim one of the visions happened \"in the choir loft\" during Mass.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/6bfc/live/e3f9d920-e7f2-11f1-ab60-79086b6495b8.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cm78270l4z7no?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 12:10:57', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(12, NULL, NULL, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'First Children\'s Booker prize reveals shortlist - and its child judges', 'first-children-s-booker-prize-reveals-shortlist-and-its-child-judges', 'The eight books in contention include War by Artemis Fowl author Eoin Colfer and Andrew Donkin.', 'The eight books in contention include War by Artemis Fowl author Eoin Colfer  and Andrew Donkin.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/693c/live/38716690-e74d-11f1-88dc-f746e0cde621.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c65y18gl23gko?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 08:29:37', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(13, NULL, 5, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'I pay £250 a month for someone else to do my life admin - it\'s worth it', 'i-pay-250-a-month-for-someone-else-to-do-my-life-admin-it-s-worth-it', 'Companies say demand is growing for their services as people\'s lives become increasingly frantic.', 'Companies say demand is growing for their services as people\'s lives become increasingly frantic.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/a207/live/9dc35180-e378-11f1-b879-03485a55c082.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c6zjr3g13zkno?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 01:01:04', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(14, NULL, 5, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'The shake-up facing renters as a new kind of landlord emerges', 'the-shake-up-facing-renters-as-a-new-kind-of-landlord-emerges', 'Companies building flats that they then rent out could usher in big changes for renters.', 'Companies building flats that they then rent out could usher in big changes for renters.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/8db7/live/089748f0-e7e7-11f1-99b6-a35a62fa4264.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c6n90g2l82d1o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 01:28:48', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(15, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Chris Mason: Can Burnham turn volume down on EU debate?', 'chris-mason-can-burnham-turn-volume-down-on-eu-debate', 'The BBC\'s Chris Mason analyses what the EU conversation means for Labour and the PM.', 'The BBC\'s Chris Mason analyses what the EU conversation means for Labour and the PM.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/8856/live/b74ecfe0-e7d2-11f1-99a1-253766e7ad8b.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c6pw47jyyz5no?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 01:45:53', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(16, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'From dancing on the Tube to being sectioned: Sabrina Bahsoon on her psychosis misdiagnosis', 'from-dancing-on-the-tube-to-being-sectioned-sabrina-bahsoon-on-her-psychosis-misdiagnosis', 'The content creator\'s self-shot videos of her dancing on the London Underground blew up online in 2023.', 'The content creator\'s self-shot videos of her dancing on the London Underground blew up online in 2023.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/4a09/live/61ab5300-e76f-11f1-99a1-253766e7ad8b.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c65y1vk4wp7po?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 10:47:20', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(17, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'What Jensen Huang\'s return teaches China\'s Xi about Trump', 'what-jensen-huang-s-return-teaches-china-s-xi-about-trump', 'Huang being back at the helm of Nvidia is no guarantee of relief for Beijing.', 'Huang being back at the helm of Nvidia is no guarantee of relief for Beijing.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/47a8/live/2d61caf0-e7d5-11f1-939f-471cde339c0e.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c63rgpy50x98o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 08:26:27', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(18, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Why I don\'t always tell potential employers I\'m dyslexic', 'why-i-don-t-always-tell-potential-employers-i-m-dyslexic', 'Many dyslexic people say it harms their confidence, a report about experiences in the workplace suggests.', 'Many dyslexic people say it harms their confidence, a report about experiences in the workplace suggests.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/49d8/live/1c75ed80-e770-11f1-99a1-253766e7ad8b.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c6rmgjynynero?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 01:00:47', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(19, NULL, NULL, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Dad who discovered daughter\'s murder on Facebook feels ignored by South African police', 'dad-who-discovered-daughter-s-murder-on-facebook-feels-ignored-by-south-african-police', 'South African communities tell the BBC of measures they\'re taking to keep women safe after a wave of horrific killings.', 'South African communities tell the BBC of measures they\'re taking to keep women safe after a wave of horrific killings.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/2542/live/90680630-e737-11f1-99a1-253766e7ad8b.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cqglr0d5jejxo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 01:02:15', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(20, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Watch: Moment troops raid home and capture Mexican drug lord', 'watch-moment-troops-raid-home-and-capture-mexican-drug-lord', 'Newly-released bodycam footage shows the moment troops detained \'El Chapo Isidro\', who had managed to evade capture for years.', 'Newly-released bodycam footage shows the moment troops detained \'El Chapo Isidro\', who had managed to evade capture for years.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/3d08/live/65108220-e7df-11f1-a094-5fdc692f3e33.jpg', 'BBC News', 'https://www.bbc.co.uk/news/videos/cm62p9y3y04qo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 10:09:41', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(21, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Man baffled after accidentally travelling to Spain on wife\'s passport', 'man-baffled-after-accidentally-travelling-to-spain-on-wife-s-passport', 'Ben Moore says he is still baffled over how he was able to make the journey without being stopped.', 'Ben Moore says he is still baffled over how he was able to make the journey without being stopped.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/7d20/live/bb56d2f0-e777-11f1-8f1d-19326d289ceb.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cmzx9ygq1x2wo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 07:28:45', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(22, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'BBC secret filming shows dog breeder selling sick puppies for £2k each', 'bbc-secret-filming-shows-dog-breeder-selling-sick-puppies-for-2k-each', 'The breeder\'s rating was downgraded but he continued to advertise as five-star, investigation finds.', 'The breeder\'s rating was downgraded but he continued to advertise as five-star, investigation finds.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/bbbd/live/db7cd3b0-e7d4-11f1-939f-471cde339c0e.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c8d36d43y8po?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 01:11:08', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(23, NULL, 4, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 1, '115 days, 39 private-jet flights, one election - Infantino\'s globetrotting', '115-days-39-private-jet-flights-one-election-infantino-s-globetrotting', 'The Fifa president has travelled more than 102,800 air miles and spent just under nine days on a plane since July.', 'The Fifa president has travelled more than 102,800 air miles and spent just under nine days on a plane since July.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/8a18/live/c4779660-e5bc-11f1-a188-ff49e70cc466.jpg', 'BBC News', 'https://www.bbc.co.uk/sport/football/articles/cmkgk22w3vlvo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 07:16:53', '2026-11-24 12:20:33', '2026-11-24 12:20:33', NULL),
(24, NULL, 4, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Car bomb tears through Colombian city wounding more than 20', 'car-bomb-tears-through-colombian-city-wounding-more-than-20', 'The explosion is the latest in a string of attacks on buildings housing the security forces and government offices.', 'The explosion is the latest in a string of attacks on buildings housing the security forces and government offices.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/6d18/live/326608e0-e7e9-11f1-8b26-c1adae33e7f2.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cjqlq2n4np7do?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 12:45:52', '2026-11-24 12:20:34', '2026-11-24 12:20:34', NULL),
(25, NULL, 5, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Almost a tonne of cocaine found in  orange crates', 'almost-a-tonne-of-cocaine-found-in-orange-crates', 'The drugs were found in boxes at New Spitalfields Market and on a lorry in Maidstone, Kent.', 'The drugs were found in boxes at New Spitalfields Market and on a lorry in Maidstone, Kent.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/b06d/live/71296640-e7ed-11f1-a094-5fdc692f3e33.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/crn804eejq7do?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 10:21:31', '2026-11-24 12:20:34', '2026-11-24 12:20:34', NULL),
(26, NULL, NULL, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Cold War missile tucked away in a field hides love story for man\'s late wife', 'cold-war-missile-tucked-away-in-a-field-hides-love-story-for-man-s-late-wife', 'Adrian Hyde and his wife Janette spent years talking about owning a military vehicle.', 'Adrian Hyde and his wife Janette spent years talking about owning a military vehicle.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/a9d7/live/6977f830-e4f0-11f1-8781-bf15c9202663.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cre3d45key2xo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 07:00:43', '2026-11-24 12:20:34', '2026-11-24 12:20:34', NULL),
(27, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Download now', 'download-now', 'Top stories, breaking news, live reporting, and follow news topics that match your interests', 'Top stories, breaking news, live reporting, and follow news topics that match your interests', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/2cf6/live/d1e71250-9509-11ee-8df3-1d2983d8814f.png', 'BBC News', 'https://www.bbc.co.uk/news/10628994?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2025-04-30 16:04:28', '2026-11-24 12:20:34', '2026-11-24 12:20:34', NULL),
(28, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Welshmen Donald & Salt demolish Australia as England level series', 'welshmen-donald-salt-demolish-australia-as-england-level-series', 'A remarkable opening partnership of 150 in 10 overs between Aneurin Donald and Phil Salt helps England chase 184 against Australia to level the T20 series.', 'A remarkable opening partnership of 150 in 10 overs between Aneurin Donald and Phil Salt helps England chase 184 against Australia to level the T20 series.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/1cc2/live/cf7fbeb0-e7f7-11f1-ab60-79086b6495b8.jpg', 'BBC News', 'https://www.bbc.co.uk/sport/cricket/articles/ck30g8ed598no?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 12:57:04', '2026-11-24 12:20:34', '2026-11-24 12:20:34', NULL),
(29, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Why has De Bruyne struggled at Napoli as he returns to Man City?', 'why-has-de-bruyne-struggled-at-napoli-as-he-returns-to-man-city', 'Kevin de Bruyne\'s move to Napoli last summer was supposed to mark a continuation of his glittering decade at Manchester City as he approaches the twilight years of his career.', 'Kevin de Bruyne\'s move to Napoli last summer was supposed to mark a continuation of his glittering decade at Manchester City as he approaches the twilight years of his career.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/20b6/live/837998e0-e745-11f1-88dc-f746e0cde621.jpg', 'BBC News', 'https://www.bbc.co.uk/sport/football/articles/cqglreexkk06o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 07:57:16', '2026-11-24 12:20:34', '2026-11-24 12:20:34', NULL),
(30, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Is Hamilton and Leclerc\'s Ferrari partnership sustainable? F1 Q&A', 'is-hamilton-and-leclerc-s-ferrari-partnership-sustainable-f1-q-a', 'BBC F1 correspondent Andrew Benson answers your latest questions after the Las Vegas Grand Prix.', 'BBC F1 correspondent Andrew Benson answers your latest questions after the Las Vegas Grand Prix.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/c34c/live/e0b9a770-e7e0-11f1-82bb-6b3be4dcbf70.jpg', 'BBC News', 'https://www.bbc.co.uk/sport/formula1/articles/cw30gq2633ljo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 09:10:15', '2026-11-24 12:20:34', '2026-11-24 12:20:34', NULL),
(31, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Why Potts swapped West Ham for Club Brugge after 16 years', 'why-potts-swapped-west-ham-for-club-brugge-after-16-years', 'When Liverpool visit Club Brugge on Wendesday they are likely to come up against English midfelder Freddie Potts.', 'When Liverpool visit Club Brugge on Wendesday they are likely to come up against English midfelder Freddie Potts.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/acb1/live/4368f080-e40b-11f1-ab35-e1af93fa5cce.jpg', 'BBC News', 'https://www.bbc.co.uk/sport/football/articles/cxe89j8gj96jo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 09:36:50', '2026-11-24 12:20:34', '2026-11-24 12:20:34', NULL),
(32, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Palace docked points for using four sub windows in win', 'palace-docked-points-for-using-four-sub-windows-in-win', 'Crystal Palace say they are \"disappointed\" by the ruling which they \"do not agree is reasonable or proportionate\".', 'Crystal Palace say they are \"disappointed\" by the ruling which they \"do not agree is reasonable or proportionate\".', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/3050/live/a5fbf0d0-e7fd-11f1-ab60-79086b6495b8.jpg', 'BBC News', 'https://www.bbc.co.uk/sport/football/articles/c8wy5vy160kpo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 13:14:19', '2026-11-24 12:20:34', '2026-11-24 12:20:34', NULL),
(33, NULL, 7, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Dead hippo to be honoured on football club tifo', 'dead-hippo-to-be-honoured-on-football-club-tifo', 'Major League Soccer side FC Cincinnati will honour Fiona, a hippo that recently died at the city\'s zoo, with a tifo display at their match with Nashville SC on Saturday.', 'Major League Soccer side FC Cincinnati will honour Fiona, a hippo that recently died at the city\'s zoo, with a tifo display at their match with Nashville SC on Saturday.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/5345/live/8e981730-e7f2-11f1-8b26-c1adae33e7f2.jpg', 'BBC News', 'https://www.bbc.co.uk/sport/football/articles/c6n90jrvxrzpo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 10:43:38', '2026-11-24 12:20:34', '2026-11-24 12:20:34', NULL),
(34, NULL, NULL, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Israeli air strike kills Hamas internal security chief in Gaza', 'israeli-air-strike-kills-hamas-internal-security-chief-in-gaza', 'Naeem Abu Nahel, who had only been in the position for two months, was targeted in western Gaza City, Gaza\'s interior ministry says.', 'Naeem Abu Nahel, who had only been in the position for two months, was targeted in western Gaza City, Gaza\'s interior ministry says.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/9451/live/7a525660-e7fb-11f1-99b6-a35a62fa4264.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c6rrg7ggq190o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 13:12:48', '2026-11-24 12:54:08', '2026-11-24 12:54:08', NULL),
(35, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Baby P\'s mum given open prison recommendation', 'baby-p-s-mum-given-open-prison-recommendation', 'Tracey Connelly was first jailed in 2009 over the death of her toddler son Peter.', 'Tracey Connelly was first jailed in 2009 over the death of her toddler son Peter.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/7ba1/live/832ce060-af31-11f0-b1c6-736a05ed5483.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cwn70z7ryp8ro?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 13:24:36', '2026-11-24 12:54:09', '2026-11-24 12:54:09', NULL),
(36, NULL, 7, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 1, 'Sooryavanshi, 15, breaks sixes record after scoring 204 off 53 balls', 'sooryavanshi-15-breaks-sixes-record-after-scoring-204-off-53-balls', 'India\'s 15-year-old sensation Vaibhav Sooryavanshi blazes 204 from 53 balls for India A, almost halving the record for the fastest double-century in List A cricket.', 'India\'s 15-year-old sensation Vaibhav Sooryavanshi blazes 204 from 53 balls for India A, almost halving the record for the fastest double-century in List A cricket.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/f124/live/9f6b8b40-e802-11f1-95bb-3f37d3e026b0.jpg', 'BBC News', 'https://www.bbc.co.uk/sport/cricket/articles/c6y9709xx4qwo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 13:38:39', '2026-11-24 12:54:09', '2026-11-24 12:54:09', NULL),
(37, NULL, NULL, 'kenya', 'KE', 30.00, 'rss_rules', 1, 'Topic classification is uncertain', 0, 0, NULL, 2, 'Meru lawyers welcome new courts but ask for more', 'meru-lawyers-welcome-new-courts-but-ask-for-more', 'Lawyers in Mount Kenya east have welcomed the Judiciary\'s move to establish new courts in Meru County, to expedite delivery of justice.', 'Lawyers in Mount Kenya east have welcomed the Judiciary\'s move to establish new courts in Meru County, to expedite delivery of justice.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/bRyON2wIwSJvOhn5Mnet9npbn5NaeFzhKd8phArs.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001562913/meru-lawyers-welcome-new-courts-but-ask-for-more', 'rss', 'published', '2026-11-24 10:54:00', '2026-11-24 12:54:10', '2026-11-24 12:54:10', NULL),
(38, NULL, 4, 'kenya', 'KE', 60.00, 'rss_rules', 1, 'Topic classification is uncertain', 0, 0, NULL, 2, 'Nzoia Sugar promises farmers\' better returns as revival gains momentum', 'nzoia-sugar-promises-farmers-better-returns-as-revival-gains-momentum', 'President William Ruto has renewed his administration\'s commitment to reviving Kenya\'s sugar industry, with Sh1.2 billion in bonus payments.', 'President William Ruto has renewed his administration\'s commitment to reviving Kenya\'s sugar industry, with Sh1.2 billion in bonus payments.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/005zqS9nPITwMWJG89uiCxau2FSuDzBuXs0F5ukj.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001562848/nzoia-sugar-promises-farmers-better-returns-as-revival-gains-momentum', 'rss', 'published', '2026-11-23 09:31:53', '2026-11-24 12:54:11', '2026-11-24 12:54:11', NULL),
(39, NULL, NULL, 'kenya', 'KE', 30.00, 'rss_rules', 1, 'Topic classification is uncertain', 0, 0, NULL, 2, 'Farmers call for implementation of Tea Act', 'farmers-call-for-implementation-of-tea-act', 'Tea farmers in the Mt Kenya region have demanded full implementation of the Tea Act to stop the erosion of the gains achieved since 2020.', 'Tea farmers in the Mt Kenya region have demanded full implementation of the Tea Act to stop the erosion of the gains achieved since 2020.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/wZfZqccLIVCyMFGm8YBi8p4aIAf3KRb3gpGyMLKx.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001562794/farmers-call-for-implementation-of-tea-act', 'rss', 'published', '2026-11-22 10:45:00', '2026-11-24 12:54:11', '2026-11-24 12:54:11', NULL),
(40, NULL, 4, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Government begins construction of sports academy in Busia', 'government-begins-construction-of-sports-academy-in-busia', 'The academy is expected to provide an environment for talent identification, nurturing and structured training.', 'The academy is expected to provide an environment for talent identification, nurturing and structured training.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/0ODfnwdeGJUNiwvVBtyYVOMnstpXKhaSCu5GNCUB.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001562755/government-begins-construction-of-sports-academy-in-busia', 'rss', 'published', '2026-11-21 14:00:00', '2026-11-24 12:54:11', '2026-11-24 12:54:11', NULL),
(41, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Meru residents raise concerns over soaring milk prices', 'meru-residents-raise-concerns-over-soaring-milk-prices', 'He said the processors had agreed not to hike milk processors, even as he cautioned consumers against buying in large quantities in a panic.', 'He said the processors had agreed not to hike milk processors, even as he cautioned consumers against buying in large quantities in a panic.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/G6rCW4UkKUhXKE81W1Cay4xOhuq03sMZ0Lgm2Omw.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001562686/meru-residents-raise-concerns-over-soaring-milk-prices', 'rss', 'published', '2026-11-20 13:06:00', '2026-11-24 12:54:12', '2026-11-24 12:54:12', NULL),
(42, NULL, NULL, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Four arrested over machete attack on Natembeya\'s aide, blogger', 'four-arrested-over-machete-attack-on-natembeya-s-aide-blogger', 'DCI detectives arrest four suspects over attack on Trans Nzoia blogger Amos Masika Boaz, alias Hammer Hammer; blogger\'s vehicle was torched before he was hacked.', 'DCI detectives arrest four suspects over attack on Trans Nzoia blogger Amos Masika Boaz, alias Hammer Hammer; blogger\'s vehicle was torched before he was hacked.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/POb9xjwIQuLHNtq7BvHa2kyLsmRlMjZUgp2ZqRUi.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001562645/four-arrested-over-machete-attack-on-natembeya-s-aide-blogger', 'rss', 'published', '2026-11-19 18:39:45', '2026-11-24 12:54:12', '2026-11-24 12:54:12', NULL),
(43, NULL, 4, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Kirinyaga county banks on early screening and healthy living campaign to reduce diabetes burden', 'kirinyaga-county-banks-on-early-screening-and-healthy-living-campaign-to-reduce-diabetes-burden', 'Kirinyaga County is now banking on an early screening and healthy living campaign to sensitise residents on the risks of diabetes.', 'Kirinyaga County is now banking on an early screening and healthy living campaign to sensitise residents on the risks of diabetes.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/YIBgYKO2NtEvbReaVB99fz59nfE2bGWxZ1umRBgs.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001562612/kirinyaga-county-banks-on-early-screening-and-healthy-living-campaign-to-reduce-diabetes-burden', 'rss', 'published', '2026-11-19 11:39:00', '2026-11-24 12:54:12', '2026-11-24 12:54:12', NULL),
(44, NULL, 8, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Kajiado partners with Nawiri to boost food security and health', 'kajiado-partners-with-nawiri-to-boost-food-security-and-health', 'Kajiado County has entered into a five-year development partnership with Nawiri Impact Africa in a renewed effort to address persistent socio-economic challenges affecting children the vulnurable.', 'Kajiado County has entered into a five-year development partnership with Nawiri Impact Africa in a renewed effort to address persistent socio-economic challenges affecting children the vulnurable.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/uN5uJE24kB5RDTjerEljBwpFkC28ARt5ZkOMpcQZ.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001562601/kajiado-partners-with-nawiri-to-boost-food-security-and-health', 'rss', 'published', '2026-11-19 10:18:00', '2026-11-24 12:54:12', '2026-11-24 12:54:12', NULL),
(45, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Kilifi sets up ward committees to monitor projects and track complaints', 'kilifi-sets-up-ward-committees-to-monitor-projects-and-track-complaints', 'Kilifi County is setting up grievance committees at sub-county level to give residents a direct role in monitoring development projects, tracking complaints and holding officials to account.', 'Kilifi County is setting up grievance committees at sub-county level to give residents a direct role in monitoring development projects, tracking complaints and holding officials to account.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/aAuQpnB8iw0zS2mr1MItbRL1Xueto0yO6wE2MovI.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001562498/kilifi-sets-up-ward-committees-to-monitor-projects-and-track-complaints', 'rss', 'published', '2026-11-18 08:23:00', '2026-11-24 12:54:12', '2026-11-24 12:54:12', NULL),
(46, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Maasai communities race to restore degraded grazing land ahead of El Nio rains', 'maasai-communities-race-to-restore-degraded-grazing-land-ahead-of-el-nio-rains', 'More than 600 Maasai workers are racing to dig 50,000 miniature earth dams in the Amboseli ecosystem ahead of expected El Niño rains, in an effort to restore degraded grazing land.', 'More than 600 Maasai workers are racing to dig 50,000 miniature earth dams in the Amboseli ecosystem ahead of expected El Niño rains, in an effort to restore degraded grazing land.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/4PSzlWCgYfK94hJXba67ArCygW2yLnlGHMVq0rDz.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001562497/maasai-communities-race-to-restore-degraded-grazing-land-ahead-of-el-nio-rains', 'rss', 'published', '2026-11-18 08:07:00', '2026-11-24 12:54:12', '2026-11-24 12:54:12', NULL),
(47, NULL, 4, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Senate demands answers over Sh300m Webuye milk plant vandalism', 'senate-demands-answers-over-sh300m-webuye-milk-plant-vandalism', 'The Senate Standing Committee on Agriculture, Livestock and Fisheries has raised concerns over the neglect and extensive vandalism of the Webuye Milk Processing Plant, Bungoma County.', 'The Senate Standing Committee on Agriculture, Livestock and Fisheries has raised concerns over the neglect and extensive vandalism of the Webuye Milk Processing Plant, Bungoma County.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/27wZGo4tfWduuL98sk3bHEAUVOYku0kbJpHtZogV.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001562377/senate-demands-answers-over-sh300m-webuye-milk-plant-vandalism', 'rss', 'published', '2026-11-16 12:00:59', '2026-11-24 12:54:12', '2026-11-24 12:54:12', NULL),
(48, NULL, NULL, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Police investigate death in love triangle', 'police-investigate-death-in-love-triangle', 'In Trans Nzoia County, detectives are pursuing a suspect who allegedly stabbed a man to death after he confronted him over his missing wife.', 'In Trans Nzoia County, detectives are pursuing a suspect who allegedly stabbed a man to death after he confronted him over his missing wife.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/rAVxnfyUwbFRah0HHfiLVzJA1LcjIjVRE1OLYG7D.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001562326/police-investigate-death-in-love-triangle', 'rss', 'published', '2026-11-15 13:54:24', '2026-11-24 12:54:12', '2026-11-24 12:54:12', NULL),
(49, NULL, NULL, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Moyale humanitarian crisis strains county resources', 'moyale-humanitarian-crisis-strains-county-resources', 'Marsabit County is grappling with an escalating security and humanitarian crisis as cross-border fighting between Ethiopian forces and armed groups continues to spill into Kenyan territory.', 'Marsabit County is grappling with an escalating security and humanitarian crisis as cross-border fighting between Ethiopian forces and armed groups continues to spill into Kenyan territory.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/6aak1TjHgvYUqTWAG1X01uTbMOrFkR0Ofxhtbqro.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001562156/moyale-humanitarian-crisis-strains-county-resources', 'rss', 'published', '2026-11-12 15:00:36', '2026-11-24 12:54:12', '2026-11-24 12:54:12', NULL),
(50, NULL, 5, 'kenya', 'KE', 60.00, 'rss_rules', 1, 'Topic classification is uncertain', 0, 0, NULL, 2, 'Dry taps in Nairobi for two days from Monday', 'dry-taps-in-nairobi-for-two-days-from-monday', 'Nairobi Water Company warns of supply interruption in Westlands, Parklands, Kilimani, Lavington, Kibera and other areas from Nov 9 to Nov 10, urges residents to store enough water.', 'Nairobi Water Company warns of supply interruption in Westlands, Parklands, Kilimani, Lavington, Kibera and other areas from Nov 9 to Nov 10, urges residents to store enough water.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/tqUJNIxYqhTEE2NF2hPIhq4qBJCwfcvNFKLWDu3S.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001561830/dry-taps-in-nairobi-for-two-days-from-monday', 'rss', 'published', '2026-11-08 13:47:59', '2026-11-24 12:54:13', '2026-11-24 12:54:13', NULL),
(51, NULL, 5, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Wajir residents call for more investment to transform livelihoods', 'wajir-residents-call-for-more-investment-to-transform-livelihoods', 'Residents of Wajir County have called for increased government investment to accelerate socio-economic transformation.', 'Residents of Wajir County have called for increased government investment to accelerate socio-economic transformation.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/sheqkblvGi9MsGy3W9jPHybD7bFRtKjNZ2NZSMqp.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001561574/wajir-residents-call-for-more-investment-to-transform-livelihoods', 'rss', 'published', '2026-11-04 20:05:12', '2026-11-24 12:54:13', '2026-11-24 12:54:13', NULL),
(52, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 2, 'County workers fight attempts to downsize them', 'county-workers-fight-attempts-to-downsize-them', 'County workers\' union threatens legal action against proposed job cuts and recruitment freezes.', 'County workers\' union threatens legal action against proposed job cuts and recruitment freezes.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/F2Wgrhfcgin3QcM8OEbWMC6LQ0l4Bq565DfyVQuB.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001560505/county-workers-fight-attempts-to-downsize-them', 'rss', 'published', '2026-10-20 11:15:56', '2026-11-24 12:54:13', '2026-11-24 12:54:13', NULL),
(53, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Five arrested over chaos at Raila\'s memorial in Bondo', 'five-arrested-over-chaos-at-raila-s-memorial-in-bondo', 'Five people arrested over Thursday\'s chaos during Raila Odinga\'s memorial in Bondo, authorities say machete, sword and knife among weapons recovered.', 'Five people arrested over Thursday\'s chaos during Raila Odinga\'s memorial in Bondo, authorities say machete, sword and knife among weapons recovered.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/dCdU6gf9aYw4Js93y2tJ4zPI7jRst4Q6UnUA0mEl.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001560285/five-arrested-over-chaos-at-raila-s-memorial-in-bondo', 'rss', 'published', '2026-10-16 17:00:55', '2026-11-24 12:54:13', '2026-11-24 12:54:13', NULL),
(54, NULL, 7, 'kenya', 'KE', 60.00, 'rss_rules', 1, 'Topic classification is uncertain', 0, 0, NULL, 2, 'Kisumu woman begins hugging pillar for 72 hours in honour of Raila', 'kisumu-woman-begins-hugging-pillar-for-72-hours-in-honour-of-raila', 'Lavender Wesonga began the challenge at 3.20 pm on Friday, October 9, at Jaramogi Oginga Odinga Sports Complex in Kisumu.', 'Lavender Wesonga began the challenge at 3.20 pm on Friday, October 9, at Jaramogi Oginga Odinga Sports Complex in Kisumu.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/9bkFvv7muq3fOnPzYLeF1dhThbQhSBZnQPw27sDo.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001559869/kisumu-woman-begins-hugging-pillar-for-72-hours-in-honour-of-raila', 'rss', 'published', '2026-10-10 14:54:11', '2026-11-24 12:54:13', '2026-11-24 12:54:13', NULL),
(55, NULL, 5, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Gazebo fire raises concerns over investment in Mambrui', 'gazebo-fire-raises-concerns-over-investment-in-mambrui', 'Concerns over insecurity and the safety of tourism and community investments in Mambrui, Malindi, have intensified following the latest burning of a beach gazebo.', 'Concerns over insecurity and the safety of tourism and community investments in Mambrui, Malindi, have intensified following the latest burning of a beach gazebo.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/fPY4AEOYF14pRn16eCeJCfqcV55psjBMvj2PNC6E.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001559395/gazebo-fire-raises-concerns-over-investment-in-mambrui', 'rss', 'published', '2026-10-04 15:54:12', '2026-11-24 12:54:13', '2026-11-24 12:54:13', NULL),
(56, NULL, 4, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Petition seeks regulation of quad-bike tourism', 'petition-seeks-regulation-of-quad-bike-tourism', 'A community organisation in Kilifi has petitioned the Senate to urgently regulate quad-bike tourism on the coastal sand dunes of Mamburi-Magarini.', 'A community organisation in Kilifi has petitioned the Senate to urgently regulate quad-bike tourism on the coastal sand dunes of Mamburi-Magarini.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/nhEONvdOhouhrNeGYNFqMzwxQa39R1Rx7DnXNk1k.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001559371/petition-seeks-regulation-of-quad-bike-tourism', 'rss', 'published', '2026-10-04 12:22:16', '2026-11-24 12:54:13', '2026-11-24 12:54:13', NULL),
(57, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Miners suffocate to death in Migori', 'miners-suffocate-to-death-in-migori', 'Four people have died after allegedly suffocating in a gold mining shaft at Ntunyigi in Kuria West, Migori County.', 'Four people have died after allegedly suffocating in a gold mining shaft at Ntunyigi in Kuria West, Migori County.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/KF6pPArnNYpHnF7QO2EMIR5YtepR4yIALlVKNKNI.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001558811/miners-suffocate-to-death-in-migori', 'rss', 'published', '2026-09-27 15:08:19', '2026-11-24 12:54:13', '2026-11-24 12:54:13', NULL),
(58, NULL, NULL, 'kenya', 'KE', 30.00, 'rss_rules', 1, 'Topic classification is uncertain', 0, 0, NULL, 2, 'Nairobi MCAs turn up heat on Sakaja over poor services', 'nairobi-mcas-turn-up-heat-on-sakaja-over-poor-services', 'Growing concerns over service delivery, revenue accountability and uneven development are increasingly placing Nairobi Governor Johnson Sakaja\'s administration under scrutiny.', 'Growing concerns over service delivery, revenue accountability and uneven development are increasingly placing Nairobi Governor Johnson Sakaja\'s administration under scrutiny.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/y3qKXK3BE09C4ukA0DAYS1yPylf6nPAcZP2u1vu9.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001558713/nairobi-mcas-turn-up-heat-on-sakaja-over-poor-services', 'rss', 'published', '2026-09-25 21:12:26', '2026-11-24 12:54:13', '2026-11-24 12:54:13', NULL),
(59, NULL, 10, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Borehole ends water vendor reliance for 1,900 Kajiado learners', 'borehole-ends-water-vendor-reliance-for-1-900-kajiado-learners', 'A rehabilitated borehole at Olekasasi Primary School in Kajiado County has ended the school\'s reliance on water vendors and opened the way for learners to practise irrigated agriculture.', 'A rehabilitated borehole at Olekasasi Primary School in Kajiado County has ended the school\'s reliance on water vendors and opened the way for learners to practise irrigated agriculture.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/6yZevlGkZGi2KYgjV55DhpM3jv7rdO36cYspbiqW.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001558627/borehole-ends-water-vendor-reliance-for-1-900-kajiado-learners', 'rss', 'published', '2026-09-24 21:33:38', '2026-11-24 12:54:13', '2026-11-24 12:54:13', NULL),
(60, NULL, 4, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Kahiga questions night-time use of county vehicle', 'kahiga-questions-night-time-use-of-county-vehicle', 'Nyeri Governor Mutahi Kahiga has raised questions over the alleged use of a county government vehicle outside official duties.', 'Nyeri Governor Mutahi Kahiga has raised questions over the alleged use of a county government vehicle outside official duties.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/s2d2rv8xgrHP5suIX4tmTtFnUdaZVLIl1rMayWCr.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001558499/kahiga-questions-night-time-use-of-county-vehicle', 'rss', 'published', '2026-09-23 17:50:14', '2026-11-24 12:54:13', '2026-11-24 12:54:13', NULL);
INSERT INTO `articles` (`id`, `user_id`, `category_id`, `region`, `country_code`, `classification_confidence`, `classification_source`, `classification_needs_review`, `classification_notes`, `is_featured`, `is_breaking`, `scheduled_at`, `rss_feed_id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `source_name`, `source_url`, `article_type`, `status`, `published_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(61, NULL, NULL, 'kenya', 'KE', 30.00, 'rss_rules', 1, 'Topic classification is uncertain', 0, 0, NULL, 2, 'KMA warns of rough seas, strong winds along Kenyan coast', 'kma-warns-of-rough-seas-strong-winds-along-kenyan-coast', 'The Kenya Maritime Authority (KMA) has issued a public safety warning over anticipated adverse weather and dangerous sea conditions along the Kenyan coast.', 'The Kenya Maritime Authority (KMA) has issued a public safety warning over anticipated adverse weather and dangerous sea conditions along the Kenyan coast.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/5kJPzP3xEG7Xaiphxlu4flbHXkeiqrtSw9B818s5.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001558486/kma-warns-of-rough-seas-strong-winds-along-kenyan-coast', 'rss', 'published', '2026-09-23 15:43:11', '2026-11-24 12:54:13', '2026-11-24 12:54:13', NULL),
(62, NULL, 4, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Kilifi pending bills hit Sh10.7bn as suppliers decry payment delays', 'kilifi-pending-bills-hit-sh10-7bn-as-suppliers-decry-payment-delays', 'Kilifi County\'s financial troubles have come under renewed scrutiny after suppliers and contractors told the Senate County Public Accounts Committee.', 'Kilifi County\'s financial troubles have come under renewed scrutiny after suppliers and contractors told the Senate County Public Accounts Committee.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/XRCwwRwIZr3c2XRGZaTdXDyG1cc65d5gnlyDuZ3U.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001558335/kilifi-pending-bills-hit-sh10-7bn-as-suppliers-decry-payment-delays', 'rss', 'published', '2026-09-21 22:47:09', '2026-11-24 12:54:14', '2026-11-24 12:54:14', NULL),
(63, NULL, 4, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Killings, border violence and political tensions: KNCHR sounds alarm ahead of 2027', 'killings-border-violence-and-political-tensions-knchr-sounds-alarm-ahead-of-2027', 'KNCHR warns of threats to life over killings in Isiolo, Embu and Turkana border banditry ahead of 2027 General Election, demands State action.', 'KNCHR warns of threats to life over killings in Isiolo, Embu and Turkana border banditry ahead of 2027 General Election, demands State action.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/eHbQZvIuHXUrXxidGJVS0X57RRASLo4Otxkt1jqp.png', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001558261/killings-border-violence-and-political-tensions-knchr-sounds-alarm-ahead-of-2027', 'rss', 'published', '2026-09-20 17:10:16', '2026-11-24 12:54:14', '2026-11-24 12:54:14', NULL),
(64, NULL, NULL, 'kenya', 'KE', 80.00, 'rss_rules', 0, 'Automatic classification matched clear signals', 0, 0, NULL, 2, 'Plate of rice exposes plot to smuggle escape tools to suspects in police cell', 'plate-of-rice-exposes-plot-to-smuggle-escape-tools-to-suspects-in-police-cell', 'Police at Ruai Police Station in Nairobi have arrested an 18-year-old woman accused of smuggling escape tools hidden in a plate of rice.', 'Police at Ruai Police Station in Nairobi have arrested an 18-year-old woman accused of smuggling escape tools hidden in a plate of rice.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/JmLNwt52eGu87hEChFUMXFoyYNd5sH6Y0PJQ57aY.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001557898/plate-of-rice-exposes-plot-to-smuggle-escape-tools-to-suspects-in-police-cell', 'rss', 'published', '2026-09-16 09:24:06', '2026-11-24 12:54:14', '2026-11-24 12:54:14', NULL),
(65, NULL, NULL, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 2, 'Woman killed in hit and run at Jacaranda rally', 'woman-killed-in-hit-and-run-at-jacaranda-rally', 'One person killed after being knocked down and run over by a Toyota Prado during Linda Mwananchi rally at Jacaranda Grounds, police say.', 'One person killed after being knocked down and run over by a Toyota Prado during Linda Mwananchi rally at Jacaranda Grounds, police say.', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/CK8ByBxzYjdlPsdX55U3JmHabO3VHfdw9yXzjuRx.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001557722/woman-killed-in-hit-and-run-at-jacaranda-rally', 'rss', 'published', '2026-09-13 22:07:53', '2026-11-24 12:54:14', '2026-11-24 12:54:14', NULL),
(66, NULL, NULL, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 2, '16 injured, vehicles damaged as Golbo protests force suspension of land registration', '16-injured-vehicles-damaged-as-golbo-protests-force-suspension-of-land-registration', 'Twelve police officers and four demonstrators were injured and 19 vehicles damaged during protests over a disputed community land registration exercise in Golbo Ward, Moyale', 'Twelve police officers and four demonstrators were injured and 19 vehicles damaged during protests over a disputed community land registration exercise in Golbo Ward, Moyale', 'https://cdn.standardmedia.co.ke/images/articles/thumbnails/Vi6PrEcFH2miE0hzqdpZJGZ9uwCWbLHsOdORYJVI.jpg', 'Standard Media', 'https://www.standardmedia.co.ke/article/2001557640/16-injured-vehicles-damaged-as-golbo-protests-force-suspension-of-land-registration', 'rss', 'published', '2026-09-12 23:00:00', '2026-11-24 12:54:14', '2026-11-24 12:54:14', NULL),
(67, NULL, 4, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, 'Trump faces backlash after saying he \'convinced\' Nvidia to reinstate CEO Jensen Huang', 'trump-faces-backlash-after-saying-he-convinced-nvidia-to-reinstate-ceo-jensen-huang', 'Nvidia had come under pressure from President Trump to reappoint Huang, whose comeback helped add $1tn to the company\'s stock value.', 'Nvidia had come under pressure from President Trump to reappoint Huang, whose comeback helped add $1tn to the company\'s stock value.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/c94d/live/cfb736f0-e78a-11f1-a5c2-c57ab9df3111.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cq5y1k8ez407o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 11:53:37', '2026-11-24 12:54:15', '2026-11-24 12:54:15', NULL),
(68, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, 'What Jensen Huang\'s return teaches China\'s Xi about Trump', 'what-jensen-huang-s-return-teaches-china-s-xi-about-trump-2', 'Huang being back at the helm of Nvidia is no guarantee of relief for Beijing.', 'Huang being back at the helm of Nvidia is no guarantee of relief for Beijing.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/47a8/live/2d61caf0-e7d5-11f1-939f-471cde339c0e.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c63rgpy50x98o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 08:26:27', '2026-11-24 12:54:15', '2026-11-24 12:54:15', NULL),
(69, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, 'Nato chief vows defence against space threat after Russian nuclear satellite reports', 'nato-chief-vows-defence-against-space-threat-after-russian-nuclear-satellite-reports-2', 'German media said Russia planned to launch a satellite with a nuclear warhead into space in coming weeks.', 'German media said Russia planned to launch a satellite with a nuclear warhead into space in coming weeks.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/f9e8/live/05da8d30-e7d7-11f1-a094-5fdc692f3e33.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c9x2er1ry2mzo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 11:58:51', '2026-11-24 12:54:15', '2026-11-24 12:54:15', NULL),
(70, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, 'Gisèle Pelicot \'humiliated\' after nude photos sent to media to promote book', 'gis-le-pelicot-humiliated-after-nude-photos-sent-to-media-to-promote-book-2', 'The publisher of an essay on coercive control apologises for \"an unfortunate and inappropriate communication\".', 'The publisher of an essay on coercive control apologises for \"an unfortunate and inappropriate communication\".', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/b1cd/live/0fff68f0-e776-11f1-a8a9-d7c9e8a923b4.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cm3eg502nxw2o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-23 22:24:47', '2026-11-24 12:54:15', '2026-11-24 12:54:15', NULL),
(71, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, 'India\'s \'cockroach\' party leader says he was stopped from flying to UK', 'india-s-cockroach-party-leader-says-he-was-stopped-from-flying-to-uk', 'Saurav Das said that immigration authorities at Delhi airport told him that his passport had been \"flagged\".', 'Saurav Das said that immigration authorities at Delhi airport told him that his passport had been \"flagged\".', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/82d4/live/df8de260-e7c4-11f1-998f-6307777df073.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cm5y1ezx2gqyo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 06:33:07', '2026-11-24 12:54:15', '2026-11-24 12:54:15', NULL),
(72, NULL, NULL, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 3, 'Israeli air strike kills Hamas internal security chief in Gaza', 'israeli-air-strike-kills-hamas-internal-security-chief-in-gaza-2', 'Naeem Abu Nahel, who had only been in the position for two months, was targeted in western Gaza City, Gaza\'s interior ministry says.', 'Naeem Abu Nahel, who had only been in the position for two months, was targeted in western Gaza City, Gaza\'s interior ministry says.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/9451/live/7a525660-e7fb-11f1-99b6-a35a62fa4264.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c6rrg7ggq190o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 13:12:48', '2026-11-24 12:54:15', '2026-11-24 12:54:15', NULL),
(73, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, 'Pope Francis\' diary reveals claims he saw the devil three times', 'pope-francis-diary-reveals-claims-he-saw-the-devil-three-times-2', 'Published extracts from the late pontiff\'s diary claim one of the visions happened \"in the choir loft\" during Mass.', 'Published extracts from the late pontiff\'s diary claim one of the visions happened \"in the choir loft\" during Mass.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/6bfc/live/e3f9d920-e7f2-11f1-ab60-79086b6495b8.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cm78270l4z7no?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 12:10:57', '2026-11-24 12:54:15', '2026-11-24 12:54:15', NULL),
(74, NULL, 4, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, 'Car bomb tears through Colombian city wounding more than 20', 'car-bomb-tears-through-colombian-city-wounding-more-than-20-2', 'The explosion is the latest in a string of attacks on buildings housing the security forces and government offices.', 'The explosion is the latest in a string of attacks on buildings housing the security forces and government offices.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/6d18/live/326608e0-e7e9-11f1-8b26-c1adae33e7f2.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cjqlq2n4np7do?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 12:45:52', '2026-11-24 12:54:15', '2026-11-24 12:54:15', NULL),
(75, NULL, NULL, 'africa', 'CD', 68.00, 'rss_rules', 0, 'Automatic classification matched clear signals', 0, 0, NULL, 3, 'DR Congo says military aircraft missing as rebels claim to shoot down plane', 'dr-congo-says-military-aircraft-missing-as-rebels-claim-to-shoot-down-plane', 'A South African fighter pilot and a Ugandan officer are reportedly among those aboard the downed plane.', 'A South African fighter pilot and a Ugandan officer are reportedly among those aboard the downed plane.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/467b/live/5cf1a7a0-e7d4-11f1-939f-471cde339c0e.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cwy7r5z8gg65o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 11:48:27', '2026-11-24 12:54:15', '2026-11-24 12:54:15', NULL),
(76, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, 'Russian governor detained in rare criminal case against regional leader', 'russian-governor-detained-in-rare-criminal-case-against-regional-leader', 'Andrei Chibis, head of Russia\'s northern Murmansk region, is the first governor to be detained for five years.', 'Andrei Chibis, head of Russia\'s northern Murmansk region, is the first governor to be detained for five years.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/db31/live/1b41ee00-e7f7-11f1-ab60-79086b6495b8.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cmwy868v3nneo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 13:45:43', '2026-11-24 12:54:15', '2026-11-24 12:54:15', NULL),
(77, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, 'Iranian authorities flog singer who performed without hijab, lawyers say', 'iranian-authorities-flog-singer-who-performed-without-hijab-lawyers-say', 'Parastoo Ahmadi, three musicians and a producer were reportedly given 74 lashes in Qom.', 'Parastoo Ahmadi, three musicians and a producer were reportedly given 74 lashes in Qom.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/6a11/live/e201bf70-bb4c-11f1-bc1f-3f186ca4140c.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cq1j6585l5g6o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-23 19:04:20', '2026-11-24 12:54:15', '2026-11-24 12:54:15', NULL),
(78, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, 'Bangladesh\'s ex-PM Sheikh Hasina says she plans to return on 16 December', 'bangladesh-s-ex-pm-sheikh-hasina-says-she-plans-to-return-on-16-december', 'Hasina has spoken several times of her desire to return, but this is the first time she has set a date.', 'Hasina has spoken several times of her desire to return, but this is the first time she has set a date.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/5191/live/b1f565b0-e7c1-11f1-b55c-09f84acff1b4.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c60rk1yj07xeo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 07:23:01', '2026-11-24 12:54:15', '2026-11-24 12:54:15', NULL),
(79, NULL, 5, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 3, 'Comedian impersonates Trump\'s dance moves, mocks trade talks at Canada press dinner', 'comedian-impersonates-trump-s-dance-moves-mocks-trade-talks-at-canada-press-dinner', 'The impersonator, Canadian comedian Mark Critch, poked fun at the US president\'s search for a new press secretary and US-Canada trade talks.', 'The impersonator, Canadian comedian Mark Critch, poked fun at the US president\'s search for a new press secretary and US-Canada trade talks.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/9f36/live/7d4e5560-e758-11f1-b407-33329b1ec67a.png', 'BBC News', 'https://www.bbc.co.uk/news/articles/cr930y92dyx4o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-23 18:14:50', '2026-11-24 12:54:15', '2026-11-24 12:54:15', NULL),
(80, NULL, 9, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 3, 'King Charles and Elton John pay tribute to Australian music legend Molly Meldrum', 'king-charles-and-elton-john-pay-tribute-to-australian-music-legend-molly-meldrum', 'The promoter and broadcaster helped define the music tastes of Australians for decades.', 'The promoter and broadcaster helped define the music tastes of Australians for decades.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/0cfc/live/c3bcfc70-e6ce-11f1-a9db-c39c38a2d17c.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c3qj57rkd2k1o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 01:23:25', '2026-11-24 12:54:15', '2026-11-24 12:54:15', NULL),
(81, NULL, NULL, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 3, 'UK sanctions top Myanmar military leaders after Rohingya genocide ruling', 'uk-sanctions-top-myanmar-military-leaders-after-rohingya-genocide-ruling', 'The UK is joined by several other nations in recognising an ICJ ruling that Myanmar carried out genocide.', 'The UK is joined by several other nations in recognising an ICJ ruling that Myanmar carried out genocide.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/46f8/live/11f74480-e77a-11f1-978f-df545a5c5c20.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/c6wy4ng1rznxo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-23 22:03:34', '2026-11-24 12:54:16', '2026-11-24 12:54:16', NULL),
(82, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, 'Watch: Moment troops raid home and capture Mexican drug lord', 'watch-moment-troops-raid-home-and-capture-mexican-drug-lord-2', 'Newly-released bodycam footage shows the moment troops detained \'El Chapo Isidro\', who had managed to evade capture for years.', 'Newly-released bodycam footage shows the moment troops detained \'El Chapo Isidro\', who had managed to evade capture for years.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/3d08/live/65108220-e7df-11f1-a094-5fdc692f3e33.jpg', 'BBC News', 'https://www.bbc.co.uk/news/videos/cm62p9y3y04qo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 10:09:41', '2026-11-24 12:54:16', '2026-11-24 12:54:16', NULL),
(83, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, 'Cheers and applause as Jensen Huang returns to Nvidia office', 'cheers-and-applause-as-jensen-huang-returns-to-nvidia-office-2', 'BBC\'s Lily Jamali reports from Nvidia\'s headquarters, where she gauges the reaction to Huang\'s return after Trump says he brokered a deal.', 'BBC\'s Lily Jamali reports from Nvidia\'s headquarters, where she gauges the reaction to Huang\'s return after Trump says he brokered a deal.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/6a2c/live/cd1673f0-e804-11f1-99b6-a35a62fa4264.jpg', 'BBC News', 'https://www.bbc.co.uk/news/videos/ckedq2edqegzo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 02:21:41', '2026-11-24 12:54:16', '2026-11-24 12:54:16', NULL),
(84, NULL, NULL, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 3, 'Outrage after ICE arrest father and daughter at Seattle airport', 'outrage-after-ice-arrest-father-and-daughter-at-seattle-airport', 'ICE officers arrested two passengers, a father and daughter, as they were boarding a flight in Seattle on Saturday.', 'ICE officers arrested two passengers, a father and daughter, as they were boarding a flight in Seattle on Saturday.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/1167/live/142d5c60-e790-11f1-99a1-253766e7ad8b.jpg', 'BBC News', 'https://www.bbc.co.uk/news/videos/crd910g5x882o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-23 23:59:07', '2026-11-24 12:54:16', '2026-11-24 12:54:16', NULL),
(85, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, 'First Lady Melania Trump welcomes this year\'s White House Christmas tree', 'first-lady-melania-trump-welcomes-this-year-s-white-house-christmas-tree', 'The official 2026 White House Christmas tree is an 18-foot fir from Silent Night Evergreens, a farm in Endeavor, Wisconsin.', 'The official 2026 White House Christmas tree is an 18-foot fir from Silent Night Evergreens, a farm in Endeavor, Wisconsin.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/cda1/live/ad61f0f0-e76b-11f1-a094-5fdc692f3e33.jpg', 'BBC News', 'https://www.bbc.co.uk/news/videos/c51k9vp48d2vo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-23 19:49:19', '2026-11-24 12:54:16', '2026-11-24 12:54:16', NULL),
(86, NULL, 4, 'africa', 'LY', 60.00, 'rss_rules', 1, 'Topic classification is uncertain', 0, 0, NULL, 3, 'Ukraine shares video of strikes on Russian ships off Libya', 'ukraine-shares-video-of-strikes-on-russian-ships-off-libya', 'President Zelensky said a Russia warship was sunk, with a Russian cargo ship also hit.', 'President Zelensky said a Russia warship was sunk, with a Russian cargo ship also hit.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/9cc6/live/7a950f50-e738-11f1-99b6-a35a62fa4264.jpg', 'BBC News', 'https://www.bbc.co.uk/news/videos/c6wy93q5wwqvo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-23 12:34:10', '2026-11-24 12:54:16', '2026-11-24 12:54:16', NULL),
(87, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, '\'Danger seems greater all the time\': Ukrainians search for missing after Russian strikes', 'danger-seems-greater-all-the-time-ukrainians-search-for-missing-after-russian-strikes', 'The BBC\'s Sarah Rainsford reports from the scene of a collapsed residential building in Ukraine\'s Kyiv region.', 'The BBC\'s Sarah Rainsford reports from the scene of a collapsed residential building in Ukraine\'s Kyiv region.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/f57f/live/25f17b40-e69e-11f1-9d9b-5dad4444a651.jpg', 'BBC News', 'https://www.bbc.co.uk/news/videos/cre3dvd1k9y1o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-22 23:37:43', '2026-11-24 12:54:16', '2026-11-24 12:54:16', NULL),
(88, NULL, NULL, 'world', NULL, 45.00, 'rss_rules', 1, 'Geographical coverage needs confirmation', 0, 0, NULL, 3, 'Dad who discovered daughter\'s murder on Facebook feels ignored by South African police', 'dad-who-discovered-daughter-s-murder-on-facebook-feels-ignored-by-south-african-police-2', 'South African communities tell the BBC of measures they\'re taking to keep women safe after a wave of horrific killings.', 'South African communities tell the BBC of measures they\'re taking to keep women safe after a wave of horrific killings.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/2542/live/90680630-e737-11f1-99a1-253766e7ad8b.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cqglr0d5jejxo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-24 01:02:15', '2026-11-24 12:54:16', '2026-11-24 12:54:16', NULL),
(89, NULL, NULL, 'africa', 'LY', 80.00, 'rss_rules', 0, 'Automatic classification matched clear signals', 0, 0, NULL, 3, 'What strike on Russian ship in Libya says about spread of Ukraine war', 'what-strike-on-russian-ship-in-libya-says-about-spread-of-ukraine-war', 'Ukraine\'s sinking of a Russian battleship at a port in Libya raises the stakes of the war a notch, writes Frank Gardner.', 'Ukraine\'s sinking of a Russian battleship at a port in Libya raises the stakes of the war a notch, writes Frank Gardner.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/363b/live/dab0da20-e771-11f1-99a1-253766e7ad8b.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cm894p88jzxpo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-23 21:48:35', '2026-11-24 12:54:16', '2026-11-24 12:54:16', NULL),
(90, NULL, 4, 'world', NULL, 45.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, '\'I want to destroy capitalism, not France\': On the streets with the French ultra left', 'i-want-to-destroy-capitalism-not-france-on-the-streets-with-the-french-ultra-left', 'The BBC meets the far-left protesters French President Emmanuel Macron says are trying to bring down the Republic.', 'The BBC meets the far-left protesters French President Emmanuel Macron says are trying to bring down the Republic.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/87ae/live/24717fd0-e73f-11f1-802a-678be58471bb.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cw074km48myeo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-23 14:09:52', '2026-11-24 12:54:16', '2026-11-24 12:54:16', NULL),
(91, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, 'The rise and fall and rise again of Jensen Huang', 'the-rise-and-fall-and-rise-again-of-jensen-huang', 'Days after resigning while mired in scandal of extra-marital affairs, Nvidia\'s Jensen Huang is making a remarkable return.', 'Days after resigning while mired in scandal of extra-marital affairs, Nvidia\'s Jensen Huang is making a remarkable return.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/38db/live/e8339120-deb0-11f1-93e1-3b657b665bcf.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cmze750zg0jeo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-23 16:49:52', '2026-11-24 12:54:16', '2026-11-24 12:54:16', NULL),
(92, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, 'Five takeaways from the failed climate talks COP31', 'five-takeaways-from-the-failed-climate-talks-cop31', 'The COP31 summit in Antalya was the longest-running climate talks in UN history.', 'The COP31 summit in Antalya was the longest-running climate talks in UN history.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/55f7/live/e38ff1a0-e71f-11f1-99b6-a35a62fa4264.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cm75237wlp3lo?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-23 07:33:50', '2026-11-24 12:54:17', '2026-11-24 12:54:17', NULL),
(93, NULL, NULL, 'world', NULL, 30.00, 'rss_rules', 1, 'Topic classification is uncertain; Geographical coverage needs confirmation', 0, 0, NULL, 3, 'San Francisco is enjoying an AI boom, but worries about a crash are growing', 'san-francisco-is-enjoying-an-ai-boom-but-worries-about-a-crash-are-growing', 'AI employees in the industry\'s epicentre - San Francisco - worry that the sector is overheated.', 'AI employees in the industry\'s epicentre - San Francisco - worry that the sector is overheated.', 'https://ichef.bbci.co.uk/ace/standard/240/cpsprodpb/06d6/live/b3d40440-e712-11f1-a094-5fdc692f3e33.jpg', 'BBC News', 'https://www.bbc.co.uk/news/articles/cqe9k09j9jv4o?at_medium=RSS&at_campaign=rss', 'rss', 'published', '2026-11-23 01:08:00', '2026-11-24 12:54:17', '2026-11-24 12:54:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `article_categories`
--

CREATE TABLE `article_categories` (
  `article_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `assigned_by` enum('manual','rss_rules','author_review') NOT NULL DEFAULT 'manual',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `article_categories`
--

INSERT INTO `article_categories` (`article_id`, `category_id`, `assigned_by`, `created_at`) VALUES
(1, 5, 'manual', '2026-11-24 12:16:32'),
(2, 7, 'manual', '2026-11-24 12:17:22'),
(7, 6, 'rss_rules', '2026-11-24 12:20:33'),
(9, 10, 'rss_rules', '2026-11-24 12:20:33'),
(13, 5, 'rss_rules', '2026-11-24 12:20:33'),
(14, 5, 'rss_rules', '2026-11-24 12:20:33'),
(23, 4, 'rss_rules', '2026-11-24 12:20:34'),
(24, 4, 'rss_rules', '2026-11-24 12:20:34'),
(25, 5, 'rss_rules', '2026-11-24 12:20:34'),
(33, 7, 'rss_rules', '2026-11-24 12:20:34'),
(36, 7, '', '2026-11-24 12:54:09'),
(38, 4, '', '2026-11-24 12:54:11'),
(40, 4, '', '2026-11-24 12:54:11'),
(40, 7, '', '2026-11-24 12:54:11'),
(40, 13, '', '2026-11-24 12:54:11'),
(43, 4, '', '2026-11-24 12:54:12'),
(44, 5, '', '2026-11-24 12:54:12'),
(44, 8, '', '2026-11-24 12:54:12'),
(44, 12, '', '2026-11-24 12:54:12'),
(47, 4, '', '2026-11-24 12:54:12'),
(50, 5, '', '2026-11-24 12:54:13'),
(51, 5, '', '2026-11-24 12:54:13'),
(54, 7, '', '2026-11-24 12:54:13'),
(55, 5, '', '2026-11-24 12:54:13'),
(56, 4, '', '2026-11-24 12:54:13'),
(59, 10, '', '2026-11-24 12:54:13'),
(60, 4, '', '2026-11-24 12:54:13'),
(62, 4, '', '2026-11-24 12:54:14'),
(63, 4, '', '2026-11-24 12:54:14'),
(67, 4, '', '2026-11-24 12:54:15'),
(67, 5, '', '2026-11-24 12:54:15'),
(74, 4, '', '2026-11-24 12:54:15'),
(79, 5, '', '2026-11-24 12:54:15'),
(80, 9, '', '2026-11-24 12:54:15'),
(86, 4, '', '2026-11-24 12:54:16'),
(90, 4, '', '2026-11-24 12:54:16');

-- --------------------------------------------------------

--
-- Table structure for table `article_revisions`
--

CREATE TABLE `article_revisions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `article_id` int(10) UNSIGNED NOT NULL,
  `changed_by` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `revision_note` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `article_tags`
--

CREATE TABLE `article_tags` (
  `article_id` int(10) UNSIGNED NOT NULL,
  `tag_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `article_views`
--

CREATE TABLE `article_views` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `article_id` int(10) UNSIGNED NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `viewed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `article_views`
--

INSERT INTO `article_views` (`id`, `article_id`, `ip_address`, `user_agent`, `viewed_at`) VALUES
(1, 23, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', '2026-11-24 15:55:14');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `target_id` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `category_type` enum('topic','legacy_geo') NOT NULL DEFAULT 'topic',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `category_type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'World', 'world', 'International news and global developments.', 'legacy_geo', 'active', '2026-11-24 12:05:10', '2026-11-24 12:05:10'),
(2, 'Africa', 'africa', 'News from across the African continent.', 'legacy_geo', 'active', '2026-11-24 12:05:10', '2026-11-24 12:05:10'),
(3, 'Kenya', 'kenya', 'News and current affairs from Kenya.', 'legacy_geo', 'active', '2026-11-24 12:05:10', '2026-11-24 12:05:10'),
(4, 'Politics', 'politics', 'Political news, policy and governance.', 'topic', 'active', '2026-11-24 12:05:10', '2026-11-24 12:05:10'),
(5, 'Business', 'business', 'Business, finance and economic news.', 'topic', 'active', '2026-11-24 12:05:10', '2026-11-24 12:05:10'),
(6, 'Technology', 'technology', 'Technology, innovation and digital news.', 'topic', 'active', '2026-11-24 12:05:10', '2026-11-24 12:05:10'),
(7, 'Sports', 'sports', 'Sports news and competitions.', 'topic', 'active', '2026-11-24 12:05:10', '2026-11-24 12:05:10'),
(8, 'Health', 'health', 'Health, medicine and public health news.', 'topic', 'active', '2026-11-24 12:05:10', '2026-11-24 12:05:10'),
(9, 'Entertainment', 'entertainment', 'Entertainment, culture and lifestyle news.', 'topic', 'active', '2026-11-24 12:05:10', '2026-11-24 12:05:10'),
(10, 'Education', 'education', 'Education news and learning.', 'topic', 'active', '2026-11-24 12:05:10', '2026-11-24 12:05:10'),
(11, 'Science', 'science', 'Science, research and discovery.', 'topic', 'active', '2026-11-24 12:05:10', '2026-11-24 12:07:57'),
(12, 'Lifestyle', 'lifestyle', 'Lifestyle and human-interest stories.', 'topic', 'active', '2026-11-24 12:05:10', '2026-11-24 12:05:10'),
(13, 'Environment', 'environment', 'Environment and climate news.', 'topic', 'active', '2026-11-24 12:05:10', '2026-11-24 12:05:10'),
(14, 'Security', 'security', 'Security and public safety news.', 'topic', 'inactive', '2026-11-24 12:05:10', '2026-11-24 12:07:19'),
(15, 'General', 'general', 'General news and stories.', 'topic', 'inactive', '2026-11-24 12:05:10', '2026-11-24 12:07:09');

-- --------------------------------------------------------

--
-- Table structure for table `rss_feeds`
--

CREATE TABLE `rss_feeds` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `feed_url` varchar(2048) NOT NULL,
  `source_name` varchar(150) NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `last_fetched_at` datetime DEFAULT NULL,
  `articles_imported_total` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_imported_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_import_status` enum('never','success','warning','error') NOT NULL DEFAULT 'never',
  `last_error` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rss_feeds`
--

INSERT INTO `rss_feeds` (`id`, `name`, `feed_url`, `source_name`, `category_id`, `status`, `last_fetched_at`, `articles_imported_total`, `last_imported_count`, `last_import_status`, `last_error`, `created_at`, `updated_at`) VALUES
(1, 'BBC World', 'https://feeds.bbci.co.uk/news/rss.xml', 'BBC News', NULL, 'active', '2026-11-24 15:54:09', 34, 3, 'success', NULL, '2026-11-24 12:20:24', '2026-11-24 12:54:09'),
(2, 'Standard Media Kenya', 'https://www.standardmedia.co.ke/rss/kenya.php', 'Standard Media', NULL, 'active', '2026-11-24 15:54:14', 30, 30, 'success', NULL, '2026-11-24 12:53:08', '2026-11-24 12:54:14'),
(3, 'BBC World', 'https://feeds.bbci.co.uk/news/world/rss.xml', 'BBC News', NULL, 'active', '2026-11-24 15:54:17', 27, 27, 'success', NULL, '2026-11-24 12:54:00', '2026-11-24 12:54:17');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','author','user') NOT NULL DEFAULT 'user',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'System', 'Admin', 'Admin', 'admin@gmail.com', '$2y$10$Q1MoVSwo76n6Z4Ilq8Mk/u6zh1acHAqkl0kfrodeN0.ZjUQqXwofS', 'admin', 'active', '2026-11-24 12:11:10', '2026-11-24 12:18:13'),
(2, 'System', 'Author', 'Author', 'author@gmail.com', '$2y$10$mPYGBDVkAd3HG1psg264guxugECtCGoui1rd/ogWq49BapK4T4rhu', 'author', 'active', '2026-11-24 12:12:42', '2026-11-24 12:20:59'),
(3, 'User', 'One', 'User1', 'user1@gmail.com', '$2y$10$F5UfM4beSH8YPC.pjWUiNO6WGiH1LoFsHczDPPlct3iua5H.ocr96', 'user', 'active', '2026-11-24 12:14:44', '2026-11-24 12:14:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_articles_slug` (`slug`),
  ADD KEY `idx_articles_status_date` (`status`,`published_at`),
  ADD KEY `idx_articles_category_status` (`category_id`,`status`),
  ADD KEY `idx_articles_region_status_date` (`region`,`status`,`published_at`),
  ADD KEY `idx_articles_featured` (`is_featured`,`is_breaking`,`status`),
  ADD KEY `idx_articles_user` (`user_id`),
  ADD KEY `idx_articles_feed_url` (`rss_feed_id`,`source_url`(191)),
  ADD KEY `idx_articles_classification_review` (`article_type`,`classification_needs_review`,`status`);

--
-- Indexes for table `article_categories`
--
ALTER TABLE `article_categories`
  ADD PRIMARY KEY (`article_id`,`category_id`),
  ADD KEY `idx_article_categories_category` (`category_id`,`article_id`);

--
-- Indexes for table `article_revisions`
--
ALTER TABLE `article_revisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_revision_article_date` (`article_id`,`created_at`),
  ADD KEY `fk_revision_user` (`changed_by`);

--
-- Indexes for table `article_tags`
--
ALTER TABLE `article_tags`
  ADD PRIMARY KEY (`article_id`,`tag_id`),
  ADD KEY `idx_article_tags_tag` (`tag_id`);

--
-- Indexes for table `article_views`
--
ALTER TABLE `article_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_article_views_article_date` (`article_id`,`viewed_at`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_created` (`created_at`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_action` (`action`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_categories_slug` (`slug`),
  ADD KEY `idx_categories_status` (`status`),
  ADD KEY `idx_categories_type_status` (`category_type`,`status`);

--
-- Indexes for table `rss_feeds`
--
ALTER TABLE `rss_feeds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rss_feeds_status` (`status`),
  ADD KEY `idx_rss_feeds_category` (`category_id`),
  ADD KEY `idx_rss_feeds_last_fetch` (`last_fetched_at`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tags_slug` (`slug`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_username` (`username`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_role_status` (`role`,`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `article_revisions`
--
ALTER TABLE `article_revisions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `article_views`
--
ALTER TABLE `article_views`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `rss_feeds`
--
ALTER TABLE `rss_feeds`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `fk_articles_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_articles_feed` FOREIGN KEY (`rss_feed_id`) REFERENCES `rss_feeds` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_articles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `article_categories`
--
ALTER TABLE `article_categories`
  ADD CONSTRAINT `fk_article_categories_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_article_categories_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `article_revisions`
--
ALTER TABLE `article_revisions`
  ADD CONSTRAINT `fk_revision_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_revision_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `article_tags`
--
ALTER TABLE `article_tags`
  ADD CONSTRAINT `fk_article_tags_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_article_tags_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `article_views`
--
ALTER TABLE `article_views`
  ADD CONSTRAINT `fk_article_views_article` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `rss_feeds`
--
ALTER TABLE `rss_feeds`
  ADD CONSTRAINT `fk_rss_feeds_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
