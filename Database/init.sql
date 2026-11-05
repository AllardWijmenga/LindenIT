-- users
CREATE TABLE IF NOT EXISTS `gridwatch`.`users` (
`id` INT NOT NULL AUTO_INCREMENT , 
`name` VARCHAR(99) NOT NULL , 
`pass` VARCHAR(255) NOT NULL , 
`email` VARCHAR(255) NOT NULL , 
`role` VARCHAR(99) NOT NULL , 
`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
PRIMARY KEY (`id`)) ENGINE = InnoDB;

-- stations
CREATE TABLE IF NOT EXISTS `gridwatch`.`stations` (
`id` INT NOT NULL AUTO_INCREMENT ,
`name` VARCHAR(99) NOT NULL ,
`location` VARCHAR(99) NOT NULL ,
`status` VARCHAR(99) NOT NULL , 
PRIMARY KEY (`id`)) ENGINE = InnoDB;

INSERT INTO `stations` (`id`, `name`, `location`, `status`) VALUES (
    NULL, 'Station Zwolle', 'Zwolle', 'Operationeel'), 
    (NULL, 'Station Arnhem', 'Arnhem', 'Operationeel'), 
    (NULL, 'Station Eemshaven', 'Eemshaven', 'WAARSCHUWING'), 
    (NULL, 'Station Rotterdam', 'Rotterdam', 'Operationeel'), 
    (NULL, 'Station Beverwijk', 'Beverwijk', 'Niet Operationeel'), 
    (NULL, 'Station Diemen', 'Diemen', 'Operationeel');

-- events
CREATE TABLE IF NOT EXISTS `gridwatch`.`events` (
`id` INT NOT NULL AUTO_INCREMENT ,
`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`type` VARCHAR(99) NOT NULL ,
`location` VARCHAR(99) NOT NULL,
`description` VARCHAR(255) NOT NULL ,
PRIMARY KEY (`id`)) ENGINE = InnoDB;

INSERT INTO `events` (`id`, `timestamp`, `type`, `location`, `description`) VALUES (
    NULL, current_timestamp(), 'Explosion', 'Station Eemshaven', 'Everyone died.');


-- ---------------------------------------------------------------
-- Toegevoegd voor de Docker-omgeving
-- ---------------------------------------------------------------

-- sensor_data: nodig voor /api/sensor-data en het scenario "hoge belasting"
CREATE TABLE IF NOT EXISTS `gridwatch`.`sensor_data` (
`id` INT NOT NULL AUTO_INCREMENT ,
`station_id` INT NOT NULL ,
`timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
`power_mw` DECIMAL(6,1) NOT NULL ,
`temperature` DECIMAL(5,1) NOT NULL ,
PRIMARY KEY (`id`),
INDEX `idx_station` (`station_id`),
FOREIGN KEY (`station_id`) REFERENCES `gridwatch`.`stations`(`id`)) ENGINE = InnoDB;

INSERT INTO `sensor_data` (`station_id`, `power_mw`, `temperature`) VALUES
(1, 180.5, 38.5),
(2, 220.0, 34.0),
(3,  95.0, 71.0),
(4, 205.3, 33.2),
(5,   0.0, 20.1),
(6, 198.7, 35.9);

-- Beginaccounts (inloggen met e-mailadres). Wachtwoorden zijn bcrypt-hashes:
--   admin@gridwatch.local    Gr1d!Watch2030
--   operator@gridwatch.local Operator!2030
INSERT INTO `users` (`name`, `pass`, `email`, `role`) VALUES
('admin',    '$2y$10$wAdog.prwUrz5DYuvtoZV.WidAEl7RmoNCy7iOlL4bB6QAcOAnNuS', 'admin@gridwatch.local',    'admin'),
('operator', '$2y$10$s5YOMRLWcQl8BHmANoJS7OX.rajNNYxkYgubodIrxj5thP3c4M16W', 'operator@gridwatch.local', 'operator');
