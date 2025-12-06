-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: database:3306
-- Время создания: Дек 06 2025 г., 08:29
-- Версия сервера: 5.7.44
-- Версия PHP: 8.2.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `jobboard`
--

-- --------------------------------------------------------

--
-- Структура таблицы `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `cover_letter` text,
  `resume_file` varchar(255) DEFAULT NULL,
  `status` enum('pending','accepted','rejected','interview') DEFAULT 'pending',
  `applied_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `applications`
--

INSERT INTO `applications` (`id`, `user_id`, `job_id`, `cover_letter`, `resume_file`, `status`, `applied_date`) VALUES
(1, 1, 1, 'I am very interested in this position and believe my skills match your requirements perfectly.', NULL, 'pending', '2025-12-05 07:20:03'),
(2, 1, 2, 'I have extensive experience in digital marketing and would love to contribute to your team.', NULL, 'interview', '2025-12-05 07:20:03');

-- --------------------------------------------------------

--
-- Структура таблицы `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `post_count` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `blog_categories`
--

INSERT INTO `blog_categories` (`id`, `name`, `description`, `post_count`) VALUES
(1, 'Travel news', 'Latest news from the travel industry', 1),
(2, 'Modern technology', 'Advances in technology', 1),
(3, 'Product', 'Product reviews and updates', 0),
(4, 'Inspiration', 'Motivational and inspirational content', 0),
(5, 'Health Care', 'Health and wellness tips', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text,
  `excerpt` text,
  `featured_image` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `publish_date` date DEFAULT NULL,
  `views_count` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `title`, `content`, `excerpt`, `featured_image`, `user_id`, `category_id`, `publish_date`, `views_count`) VALUES
(1, 'Google inks pact for new 35-storey office', 'Full article content here...', 'That dominion stars lights dominion divide years for fourth have don\'t stars is that he earth it first without heaven in place seed it second morning saying.', 'img/blog/single_blog_1.png', 5, 1, '2024-01-15', 0),
(2, 'The Amazing Hubble', 'Full article content here...', 'That dominion stars lights dominion divide years for fourth have don\'t stars is that he earth it first without heaven in place seed it second morning saying.', 'img/blog/single_blog_2.png', 5, 2, '2024-01-10', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `blog_post_tags`
--

CREATE TABLE `blog_post_tags` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `blog_post_tags`
--

INSERT INTO `blog_post_tags` (`id`, `post_id`, `tag_id`) VALUES
(1, 1, 1),
(2, 1, 4),
(3, 2, 1),
(4, 2, 6),
(5, 2, 8);

-- --------------------------------------------------------

--
-- Структура таблицы `blog_tags`
--

CREATE TABLE `blog_tags` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `slug` varchar(50) DEFAULT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `blog_tags`
--

INSERT INTO `blog_tags` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Technology', 'technology', 'Articles about technology'),
(2, 'Travel', 'travel', 'Travel related content'),
(3, 'Career', 'career', 'Career development tips'),
(4, 'Business', 'business', 'Business and entrepreneurship'),
(5, 'Lifestyle', 'lifestyle', 'Lifestyle and wellness'),
(6, 'Education', 'education', 'Educational content'),
(7, 'Digital Marketing', 'digital-marketing', 'Marketing in digital age'),
(8, 'Software Development', 'software-development', 'Programming and development');

-- --------------------------------------------------------

--
-- Структура таблицы `cities`
--

CREATE TABLE `cities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `country_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `cities`
--

INSERT INTO `cities` (`id`, `name`, `country_id`) VALUES
(1, 'California', 1),
(2, 'New York', 1),
(3, 'London', 2),
(4, 'Berlin', 3),
(5, 'Toronto', 4),
(6, 'Sydney', 5);

-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `comment_text` text NOT NULL,
  `parent_comment_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Структура таблицы `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `logo` varchar(255) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `address` text,
  `verified` tinyint(1) DEFAULT '0',
  `active_jobs_count` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `companies`
--

INSERT INTO `companies` (`id`, `user_id`, `name`, `description`, `logo`, `website`, `contact_email`, `contact_phone`, `address`, `verified`, `active_jobs_count`) VALUES
(1, 2, 'Tech Innovations Inc.', 'Leading technology company specializing in software development', NULL, 'https://techinnovations.com', 'hr@techinnovations.com', NULL, NULL, 1, 0),
(2, 4, 'Green Energy Solutions', 'Renewable energy company focused on sustainable solutions', NULL, 'https://greenenergy.com', 'careers@greenenergy.com', NULL, NULL, 1, 0),
(3, 7, 'Telebmobom', 'kryta', NULL, 'https://examgrgrple.com', 'bebe@gmail.com', '898333333333', 'Bem bem 17 st', 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Структура таблицы `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `countries`
--

INSERT INTO `countries` (`id`, `name`) VALUES
(5, 'Australia'),
(4, 'Canada'),
(3, 'Germany'),
(2, 'United Kingdom'),
(1, 'United States');

-- --------------------------------------------------------

--
-- Структура таблицы `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `city_id` int(11) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `job_type` enum('full-time','part-time','contract','remote') DEFAULT 'full-time',
  `salary_from` decimal(10,2) DEFAULT NULL,
  `salary_to` decimal(10,2) DEFAULT NULL,
  `requirements` text,
  `published_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('active','closed','pending') DEFAULT 'pending',
  `views_count` int(11) DEFAULT '0',
  `category_id` int(11) DEFAULT NULL,
  `applications_count` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `jobs`
--

INSERT INTO `jobs` (`id`, `company_id`, `title`, `description`, `city_id`, `address`, `postal_code`, `job_type`, `salary_from`, `salary_to`, `requirements`, `published_date`, `expiry_date`, `status`, `views_count`, `category_id`, `applications_count`) VALUES
(1, 1, 'Software Engineer', 'We are looking for a skilled software engineer to join our team', 1, 'Silicon Valley Office, 123 Tech Ave', '94000', 'part-time', 75000.00, 95000.00, '3+ years experience, Python, JavaScript', '2024-01-01', '2026-01-05', 'active', 0, 3, 0),
(2, 1, 'Digital Marketer', 'Digital marketing specialist needed for growing marketing team', 1, 'Silicon Valley Office, 123 Tech Ave', '94000', 'part-time', 50000.00, 70000.00, '2+ years experience, SEO, Social Media', '2024-01-05', '2026-01-05', 'active', 0, 2, 0),
(3, 2, 'Wordpress Developer', 'Experienced Wordpress developer for client projects', 2, 'Green Energy HQ, 456 Eco Street', '10001', 'part-time', 60000.00, 80000.00, 'WordPress, PHP, CSS, JavaScript', '2024-01-10', '2026-01-05', 'active', 0, 3, 0),
(4, 2, 'Visual Designer', 'Creative visual designer for UI/UX projects', 2, 'Green Energy HQ, 456 Eco Street', '10001', 'part-time', 55000.00, 75000.00, 'Adobe Creative Suite, Figma, UI/UX', '2024-01-15', '2026-01-05', 'active', 0, 1, 0),
(5, 3, 'geegeg', 'rnhrtht', 5, 'nafnaf', NULL, 'full-time', 400.00, 500.00, 'jtjtyj', '2025-12-06', '2026-01-05', 'pending', 0, 1, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `job_categories`
--

CREATE TABLE `job_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text,
  `icon` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `job_categories`
--

INSERT INTO `job_categories` (`id`, `name`, `description`, `icon`) VALUES
(1, 'Design & Creative', 'Graphic design, UI/UX, creative roles', 'img/svg_icon/1.svg'),
(2, 'Marketing', 'Digital marketing, SEO, advertising', 'img/svg_icon/2.svg'),
(3, 'Software & Web', 'Software development, web development', 'img/svg_icon/3.svg'),
(4, 'Engineering', 'Mechanical, electrical, civil engineering', 'img/svg_icon/4.svg'),
(5, 'Sales & Marketing', 'Sales, business development', 'img/svg_icon/5.svg'),
(6, 'Finance', 'Accounting, banking, financial services', 'img/svg_icon/1.svg'),
(7, 'Teaching & Education', 'Education, training, teaching', 'img/svg_icon/2.svg'),
(8, 'Administration', 'Office administration, management', 'img/svg_icon/3.svg');

-- --------------------------------------------------------

--
-- Структура таблицы `resumes`
--

CREATE TABLE `resumes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `desired_position` varchar(100) DEFAULT NULL,
  `experience` text,
  `education` text,
  `skills` text,
  `expected_salary` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `resumes`
--

INSERT INTO `resumes` (`id`, `user_id`, `desired_position`, `experience`, `education`, `skills`, `expected_salary`) VALUES
(1, 1, 'Senior Software Engineer', '5 years at Google, 3 years at Microsoft', 'MSc in Computer Science', 'Python, JavaScript, React, Node.js', 120000.00);

-- --------------------------------------------------------

--
-- Структура таблицы `saved_jobs`
--

CREATE TABLE `saved_jobs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `saved_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Структура таблицы `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subscribed_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','unsubscribed') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Структура таблицы `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `author_name` varchar(100) NOT NULL,
  `author_image` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `position` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Дамп данных таблицы `testimonials`
--

INSERT INTO `testimonials` (`id`, `author_name`, `author_image`, `content`, `position`) VALUES
(1, 'Micky Mouse', 'img/testmonial/author.png', 'Working in conjunction with humanitarian aid agencies, we have supported programmes to help alleviate human suffering through animal welfare when people might depend on livestock as their only source of income or food.', 'CEO at Disney');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','inactive','banned') COLLATE utf8mb4_unicode_ci DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `full_name`, `phone`, `status`) VALUES
(1, 'jobseeker@example.com', 'password123', 'John Smith', '+1234567890', 'active'),
(2, 'employer@example.com', 'password123', 'Tech Corp HR', '+1987654321', 'active'),
(3, 'admin@jobboard.com', 'admin123', 'Site Admin', '+1112223333', 'active'),
(4, 'moderator@jobboard.com', 'mod123', 'Content Moderator', '+4445556666', 'active'),
(5, 'editor@jobboard.com', 'edit123', 'Blog Editor', '+7778889999', 'active'),
(6, 'lllll@gmail.com', '$2y$10$vQ3nfXBUYJUH4DF22gl.v.LQbfX3kGbL0S7zM9mo/2J7LhgF.N/zW', 'userrr', NULL, 'active'),
(7, '4343@gmail.com', '$2y$10$wlBUK3Bc1DfCUvSQ/zPBfuFZD4RBP.YRqvpjB/485e6AwGtd5.e0a', 'usr', NULL, 'active'),
(8, 'admin@gmail.com', '$2y$10$DhihvtDCb7io20.j9h1Uhug9OUGUeKVaKjt1flMAve.ra1Ci2R/Uy', 'theadmin', NULL, 'active'),
(9, 'ffff@gmail.com', '$2y$10$HPAcUr4aRG1Sn9HbV2sdH.SLeFng0jhb8bJtAKd9l0Xei1/9iKgy6', 'ffff', NULL, 'active'),
(10, '111@gmail.com', '$2y$10$HnYMF.be.OjiZ6g5COJpguF7jxO8RMFrN0em00zJUoH66M6kj6lr.', '111', NULL, 'active');

-- --------------------------------------------------------

--
-- Структура таблицы `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `user_type_id`, `created_at`) VALUES
(1, 7, 2, '2025-12-06 07:35:38'),
(2, 8, 4, '2025-12-06 07:35:38'),
(3, 3, 4, '2025-12-06 07:35:38'),
(4, 5, 5, '2025-12-06 07:35:38'),
(5, 2, 2, '2025-12-06 07:35:38'),
(6, 1, 1, '2025-12-06 07:35:38'),
(7, 6, 1, '2025-12-06 07:35:38'),
(8, 4, 3, '2025-12-06 07:35:38'),
(16, 1, 3, '2025-12-06 07:35:38'),
(17, 1, 5, '2025-12-06 07:35:38'),
(18, 1, 6, '2025-12-06 07:35:38'),
(19, 9, 2, '2025-12-06 08:26:05'),
(20, 9, 1, '2025-12-06 08:26:05'),
(21, 10, 1, '2025-12-06 08:27:33');

-- --------------------------------------------------------

--
-- Структура таблицы `user_types`
--

CREATE TABLE `user_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `permissions` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `user_types`
--

INSERT INTO `user_types` (`id`, `type_name`, `description`, `permissions`) VALUES
(1, 'job_seeker', 'Looking for jobs, can apply to vacancies', '{\"can_save_jobs\": true, \"can_apply_jobs\": true, \"can_edit_profile\": true, \"can_upload_resume\": true}'),
(2, 'employer', 'Posts vacancies, views applications', '{\"can_edit_jobs\": true, \"can_post_jobs\": true, \"can_edit_profile\": true, \"can_manage_company\": true, \"can_view_applications\": true}'),
(3, 'moderator', 'Reviews and approves content', '{\"can_approve_jobs\": true, \"can_view_reports\": true, \"can_manage_comments\": true, \"can_moderate_content\": true}'),
(4, 'admin', 'Full system administrator access', '{\"full_access\": true, \"can_assign_roles\": true, \"can_manage_users\": true, \"can_view_all_data\": true, \"can_manage_settings\": true, \"can_manage_all_content\": true}'),
(5, 'editor', 'Creates and edits blog content', '{\"can_edit_posts\": true, \"can_manage_tags\": true, \"can_create_posts\": true, \"can_upload_media\": true, \"can_manage_categories\": true}'),
(6, 'support_manager', 'Manages contacts, newsletters and communications', '{\"can_view_contacts\": true, \"can_reply_contacts\": true, \"can_send_broadcasts\": true, \"can_manage_newsletters\": true, \"can_manage_subscribers\": true}');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_applications_status` (`status`),
  ADD KEY `idx_applications_job` (`job_id`),
  ADD KEY `idx_applications_job_status` (`job_id`,`status`),
  ADD KEY `idx_applications_user` (`user_id`);

--
-- Индексы таблицы `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_blog_posts_date` (`publish_date`),
  ADD KEY `idx_blog_posts_publish_status` (`publish_date`,`views_count`),
  ADD KEY `idx_blog_posts_user` (`user_id`),
  ADD KEY `idx_blog_posts_category` (`category_id`);

--
-- Индексы таблицы `blog_post_tags`
--
ALTER TABLE `blog_post_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_post_tag` (`post_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Индексы таблицы `blog_tags`
--
ALTER TABLE `blog_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Индексы таблицы `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `country_id` (`country_id`);

--
-- Индексы таблицы `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `parent_comment_id` (`parent_comment_id`);

--
-- Индексы таблицы `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Индексы таблицы `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_jobs_status` (`status`),
  ADD KEY `idx_jobs_category` (`category_id`),
  ADD KEY `idx_jobs_company_status` (`company_id`,`status`),
  ADD KEY `idx_jobs_city` (`city_id`);

--
-- Индексы таблицы `job_categories`
--
ALTER TABLE `job_categories`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `resumes`
--
ALTER TABLE `resumes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Индексы таблицы `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_save` (`user_id`,`job_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Индексы таблицы `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_type` (`user_id`,`user_type_id`),
  ADD KEY `user_type_id` (`user_type_id`);

--
-- Индексы таблицы `user_types`
--
ALTER TABLE `user_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `blog_post_tags`
--
ALTER TABLE `blog_post_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `blog_tags`
--
ALTER TABLE `blog_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `job_categories`
--
ALTER TABLE `job_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `resumes`
--
ALTER TABLE `resumes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `saved_jobs`
--
ALTER TABLE `saved_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT для таблицы `user_types`
--
ALTER TABLE `user_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_applications_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `fk_blog_posts_categories` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_blog_posts_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `blog_post_tags`
--
ALTER TABLE `blog_post_tags`
  ADD CONSTRAINT `blog_post_tags_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_post_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `blog_tags` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parent_comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `companies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `fk_jobs_cities` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jobs_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `job_categories` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `resumes`
--
ALTER TABLE `resumes`
  ADD CONSTRAINT `resumes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD CONSTRAINT `saved_jobs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_jobs_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`user_type_id`) REFERENCES `user_types` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
