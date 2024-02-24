<?php

class CatalogTest extends CTestCase{
    
 /*   public static function setUpBeforeClass(){
        Yii::app()->db->createCommand("
-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1
-- Время создания: Фев 24 2014 г., 13:08
-- Версия сервера: 5.5.32
-- Версия PHP: 5.4.16

SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";
SET time_zone = \"+00:00\";

--
-- База данных: `dalbazar`
--
CREATE DATABASE IF NOT EXISTS `dalbazar` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `dalbazar`;

-- --------------------------------------------------------

--
-- Структура таблицы `catalog`
--

CREATE TABLE IF NOT EXISTS `catalog` (
  `catalog_id` int(11) NOT NULL AUTO_INCREMENT,
  `catalog_parent_id` int(11) DEFAULT NULL,
  `name` text,
  `path` text COMMENT 'Путь до родительского каталога, строится через точку, например 0.10.12',
  `pos` smallint(6) DEFAULT NULL COMMENT 'Позиция в списке',
  `create_time` int(11) DEFAULT NULL COMMENT 'Дата создания элемента',
  `removed` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`catalog_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Дерево категорий каталога предприятий, сам каталог предприятий находится в таблице company' AUTO_INCREMENT=14 ;

--
-- Дамп данных таблицы `catalog`
--

INSERT INTO `catalog` (`catalog_id`, `catalog_parent_id`, `name`, `path`, `pos`, `create_time`, `removed`) VALUES
(1, 0, 'one', '0.1', NULL, NULL, 0),
(2, 0, 'two', '0.2', NULL, NULL, 0),
(3, 0, 'three', '0.3', NULL, NULL, 0),
(4, 0, 'four', '0.4', NULL, NULL, 0),
(5, 1, 'one.one', '0.1.5', NULL, NULL, 0),
(6, 1, 'one.two', '0.1.6', NULL, NULL, 0),
(7, 1, 'one.three', '0.1.7', NULL, NULL, 0),
(8, 1, 'one.four', '0.1.8', NULL, NULL, 0),
(9, 6, 'one.two.one', '0.1.6.9', NULL, NULL, 0),
(10, 6, 'one.two.two', '0.1.6.10', NULL, NULL, 0),
(11, 10, 'one.two.two.one', '0.1.6.10.11', NULL, NULL, 0),
(12, 3, 'three.one', '0.3.12', NULL, NULL, 0),
(13, 3, 'three.two', '0.3.13', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `company`
--

CREATE TABLE IF NOT EXISTS `company` (
  `company_id` int(11) NOT NULL AUTO_INCREMENT,
  `catalog_id` int(11) DEFAULT NULL,
  `link` tinytext COMMENT 'Ссылка на сайт предприятия',
  `adress` text COMMENT 'Рабочий адресс предприятия',
  `description` text COMMENT 'Описание предприятия',
  `schedule` tinytext COMMENT 'График работы предприятия',
  `district` tinytext COMMENT 'Район города',
  `create_time` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `removed` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Предприятия для каталога предприятий' AUTO_INCREMENT=1 ;
");
    }*/
    
    public function testInsertion(){
        $this->assertFalse(false);
        $this->assertFalse(true);
    }
    /*
    public static function tearDownAfterClass(){
        $comm = Yii::app()->db->createCommand();
        $comm->dropTable("company");
        $comm->dropTable("catalog");
    }*/
}

