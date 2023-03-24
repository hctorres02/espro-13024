DROP TABLE IF EXISTS `departments`;

DROP TABLE IF EXISTS `posts`;

DROP TABLE IF EXISTS `users`;

DROP TABLE IF EXISTS `warnings`;

CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) DEFAULT NULL,
  `name` varchar(60) NOT NULL,
  `shortname` varchar(4) NOT NULL,
  `color` varchar(20) NOT NULL,
  `is_super` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`),
  UNIQUE KEY (`shortname`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `status` enum('draft', 'published', 'trash') NOT NULL DEFAULT 'draft',
  `published_at` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`title`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY (`phone`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE `warnings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `status` enum('draft', 'published', 'trash') NOT NULL,
  `published_at` date NOT NULL,
  `expires_at` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

ALTER TABLE
  `departments`
ADD
  CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);

ALTER TABLE
  `posts`
ADD
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
ADD
  CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);

ALTER TABLE
  `users`
ADD
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

ALTER TABLE
  `warnings`
ADD
  CONSTRAINT `warnings_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
ADD
  CONSTRAINT `warnings_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);