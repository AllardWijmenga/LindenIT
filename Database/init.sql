-- users
CREATE TABLE `gridwatch`.`users` (
`id` INT NOT NULL AUTO_INCREMENT , 
`name` VARCHAR(99) NOT NULL , 
`pass` VARCHAR(255) NOT NULL , 
`email` VARCHAR(255) NOT NULL , 
`role` VARCHAR(99) NOT NULL , 
`date_created` TIMESTAMP NOT NULL , 
PRIMARY KEY (`id`)) ENGINE = InnoDB;

-- stations
CREATE TABLE `gridwatch`.`stations` (
`id` INT NOT NULL AUTO_INCREMENT ,
`name` VARCHAR(99) NOT NULL ,
`location` VARCHAR(99) NOT NULL ,
`status` VARCHAR(99) NOT NULL , 
PRIMARY KEY (`id`)) ENGINE = InnoDB;

-- events
CREATE TABLE `gridwatch`.`events` (
`id` INT NOT NULL AUTO_INCREMENT ,
`timestamp` TIMESTAMP NOT NULL ,
`type` VARCHAR(99) NOT NULL ,
`description` VARCHAR(255) NOT NULL ,
PRIMARY KEY (`id`)) ENGINE = InnoDB;