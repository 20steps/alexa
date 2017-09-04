CREATE DATABASE IF NOT EXISTS alexa_dev CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE USER IF NOT EXISTS 'alexa'@'%' IDENTIFIED BY 'alexa';
GRANT ALL ON alexa_dev.* TO 'alexa'@'%';
