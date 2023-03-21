CREATE TABLE `departments` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `shortname` VARCHAR(255) NOT NULL,
    `color` VARCHAR(255) NOT NULL,
    `is_visible` BOOLEAN NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE `users` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `department_id` INT NULL,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `birth_date` DATE NULL,
    `is_active` BOOLEAN NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE = InnoDB;

CREATE TABLE `posts` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `department_id` INT NULL,
    `author_id` INT NULL,
    `image` VARCHAR(255) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    `status` ENUM ('draft', 'published', 'trash') NOT NULL,
    `published_at` DATE NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
    FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
) ENGINE = InnoDB;

CREATE TABLE `warnings` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `department_id` INT NULL,
    `author_id` INT NULL,
    `title` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    `status` ENUM ('draft', 'published', 'trash') NOT NULL,
    `published_at` DATE NOT NULL,
    `expires_at` DATE NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
    FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
) ENGINE = InnoDB;