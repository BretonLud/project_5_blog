CREATE DATABASE /*!32312 IF NOT EXISTS */ `project5` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION = 'N' */;

USE `project5`;

CREATE TABLE user
(
    id        INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(255) NOT NULL,
    lastname  VARCHAR(255) NOT NULL,
    email     VARCHAR(255) NOT NULL UNIQUE,
    password  VARCHAR(255) NOT NULL,
    role      VARCHAR(255) NOT NULL,
    validated BOOLEAN      NOT NULL,
    slug      VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE blog
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    title      VARCHAR(255) NOT NULL,
    created_at DATETIME     NOT NULL,
    updated_at DATETIME     NOT NULL,
    content    TEXT         NOT NULL,
    slug       VARCHAR(255) NOT NULL UNIQUE,
    FOREIGN KEY (user_id) REFERENCES user (id)
);

CREATE TABLE comment
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT      NOT NULL,
    blog_id    INT      NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    content    TEXT     NOT NULL,
    validated  BOOLEAN  NOT NULL,
    FOREIGN KEY (user_id) REFERENCES user (id),
    FOREIGN KEY (blog_id) REFERENCES blog (id)
);

CREATE TABLE picture
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    blog_id    INT          NOT NULL,
    created_at DATETIME     NOT NULL,
    name       VARCHAR(255) NOT NULL,
    header     BOOLEAN      NOT NULL,
    slug       VARCHAR(255) NOT NULL UNIQUE,
    FOREIGN KEY (blog_id) REFERENCES blog (id)
);