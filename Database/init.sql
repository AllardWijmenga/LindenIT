-- users
CREATE TABLE IF NOT EXISTS `gridwatch`.`users` (
`id` INT NOT NULL AUTO_INCREMENT , 
`name` VARCHAR(99) NOT NULL , 
`pass` VARCHAR(255) NOT NULL , 
`email` VARCHAR(255) NOT NULL , 
`role` VARCHAR(99) NOT NULL , 
`date_created` TIMESTAMP NOT NULL , 
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
`timestamp` TIMESTAMP NOT NULL ,
`type` VARCHAR(99) NOT NULL ,
`location` VARCHAR(99) NOT NULL,
`description` VARCHAR(255) NOT NULL ,
PRIMARY KEY (`id`)) ENGINE = InnoDB;

INSERT INTO `events` (`id`, `timestamp`, `type`, `location`, `description`) VALUES (
    NULL, current_timestamp(), 'Explosion', 'Station Eemshaven', 'Everyone died.');