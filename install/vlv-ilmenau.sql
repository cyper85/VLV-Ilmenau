-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 30. Jan 2016 um 12:45
-- Server Version: 5.5.46-MariaDB-1ubuntu0.14.04.2
-- PHP-Version: 5.5.9-1ubuntu4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `vlvilmenau`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `block_bad_persons`
--
-- Erzeugt am: 12. Okt 2014 um 14:50
-- Aktualisiert am: 12. Okt 2014 um 14:50
--

DROP TABLE IF EXISTS `block_bad_persons`;
CREATE TABLE IF NOT EXISTS `block_bad_persons` (
  `uname` varchar(100) NOT NULL DEFAULT '0',
  `ip` varchar(16) NOT NULL DEFAULT '',
  `count` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`uname`,`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `location`
--
-- Erzeugt am: 26. Nov 2015 um 09:43
--

DROP TABLE IF EXISTS `location`;
CREATE TABLE IF NOT EXISTS `location` (
  `location` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `personal`
--
-- Erzeugt am: 26. Nov 2015 um 09:43
--

DROP TABLE IF EXISTS `personal`;
CREATE TABLE IF NOT EXISTS `personal` (
  `id` varchar(255) NOT NULL,
  `titel` varchar(100) NOT NULL,
  `vorname` varchar(255) NOT NULL,
  `nachname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefon` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `social_networks` varchar(255) NOT NULL,
  `fachgebiet` varchar(255) NOT NULL,
  `fakultät` varchar(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sysvar`
--
-- Erzeugt am: 26. Nov 2015 um 09:43
--

DROP TABLE IF EXISTS `sysvar`;
CREATE TABLE IF NOT EXISTS `sysvar` (
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--
-- Erzeugt am: 07. Nov 2014 um 02:37
-- Aktualisiert am: 19. Jan 2016 um 01:24
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `uname` varchar(100) CHARACTER SET utf8 NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '0',
  `new_password` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `new_password_time` datetime DEFAULT NULL,
  `verify` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8 NOT NULL,
  `iCal` tinyint(1) NOT NULL DEFAULT '0',
  `iCal_string` varchar(255) NOT NULL DEFAULT 'MD5(CURRENT_TIME())',
  `last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `su` tinyint(1) NOT NULL DEFAULT '0',
  `lastupdate` timestamp(6) NOT NULL DEFAULT '0000-00-00 00:00:00.000000',
  `block` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `uname` (`uname`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user_rules`
--
-- Erzeugt am: 26. Nov 2015 um 09:43
--

DROP TABLE IF EXISTS `user_rules`;
CREATE TABLE IF NOT EXISTS `user_rules` (
  `uid` int(11) NOT NULL,
  `vlv_id` varchar(64) NOT NULL,
  `vlv_studiengang` varchar(10) NOT NULL,
  `vlv_semester` int(2) NOT NULL,
  `vlv_seminargruppe` varchar(11) NOT NULL,
  `type` varchar(150) NOT NULL,
  PRIMARY KEY (`uid`,`vlv_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELATIONEN DER TABELLE `user_rules`:
--   `uid`
--       `user` -> `uid`
--   `vlv_id`
--       `vlv_zusammenfassung` -> `vlv_id`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vlv2_object`
--
-- Erzeugt am: 26. Nov 2015 um 09:43
--

DROP TABLE IF EXISTS `vlv2_object`;
CREATE TABLE IF NOT EXISTS `vlv2_object` (
  `id` int(11) NOT NULL,
  `Titel` varchar(255) NOT NULL,
  `Fachgebiet` int(11) NOT NULL,
  `Fachverantwortlicher` varchar(255) NOT NULL,
  `lang` varchar(2) CHARACTER SET utf8 NOT NULL,
  `LP` int(11) NOT NULL,
  `exam` varchar(255) CHARACTER SET utf8 NOT NULL,
  `Vorkenntnisse` text CHARACTER SET utf8 NOT NULL,
  `Lernergebnisse` text CHARACTER SET utf8 NOT NULL,
  `Inhalt` text CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vlv_entry`
--
-- Erzeugt am: 30. Jan 2016 um 11:10
-- Aktualisiert am: 30. Jan 2016 um 11:10
-- Letzter Check am: 30. Jan 2016 um 11:10
--

DROP TABLE IF EXISTS `vlv_entry`;
CREATE TABLE IF NOT EXISTS `vlv_entry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vlv_id` varchar(64) NOT NULL,
  `location` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `rules` varchar(50) NOT NULL,
  `time_period` varchar(15) NOT NULL,
  `weekday` varchar(15) DEFAULT NULL,
  `last_change` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `vlv_id` (`vlv_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4438 ;

--
-- RELATIONEN DER TABELLE `vlv_entry`:
--   `vlv_id`
--       `vlv_zusammenfassung` -> `vlv_id`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vlv_entry2date`
--
-- Erzeugt am: 30. Jan 2016 um 11:10
-- Aktualisiert am: 30. Jan 2016 um 11:10
-- Letzter Check am: 30. Jan 2016 um 11:10
--

DROP TABLE IF EXISTS `vlv_entry2date`;
CREATE TABLE IF NOT EXISTS `vlv_entry2date` (
  `id` int(11) NOT NULL,
  `from` datetime NOT NULL,
  `to` datetime NOT NULL,
  PRIMARY KEY (`id`,`from`,`to`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- RELATIONEN DER TABELLE `vlv_entry2date`:
--   `id`
--       `vlv_entry` -> `id`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vlv_entry2stud`
--
-- Erzeugt am: 30. Jan 2016 um 11:10
-- Aktualisiert am: 30. Jan 2016 um 11:10
-- Letzter Check am: 30. Jan 2016 um 11:10
--

DROP TABLE IF EXISTS `vlv_entry2stud`;
CREATE TABLE IF NOT EXISTS `vlv_entry2stud` (
  `id` int(11) NOT NULL,
  `studiengang` varchar(10) NOT NULL,
  `seminargruppe` varchar(11) NOT NULL,
  `semester` int(11) NOT NULL,
  PRIMARY KEY (`id`,`studiengang`,`seminargruppe`,`semester`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- RELATIONEN DER TABELLE `vlv_entry2stud`:
--   `id`
--       `vlv_entry` -> `id`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vlv_extra_entry`
--
-- Erzeugt am: 12. Okt 2014 um 14:50
-- Aktualisiert am: 12. Okt 2014 um 14:50
--

DROP TABLE IF EXISTS `vlv_extra_entry`;
CREATE TABLE IF NOT EXISTS `vlv_extra_entry` (
  `studiengang` varchar(10) NOT NULL,
  `semester` varchar(11) DEFAULT NULL,
  `seminargruppe` int(11) DEFAULT NULL,
  `von` datetime NOT NULL,
  `bis` datetime NOT NULL,
  `title` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vlv_isbn`
--
-- Erzeugt am: 26. Nov 2015 um 09:43
--

DROP TABLE IF EXISTS `vlv_isbn`;
CREATE TABLE IF NOT EXISTS `vlv_isbn` (
  `literatur_ref` varchar(255) NOT NULL,
  `isbn` varchar(255) NOT NULL,
  PRIMARY KEY (`literatur_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vlv_isbn_data`
--
-- Erzeugt am: 29. Okt 2014 um 01:40
-- Aktualisiert am: 12. Okt 2014 um 14:50
--

DROP TABLE IF EXISTS `vlv_isbn_data`;
CREATE TABLE IF NOT EXISTS `vlv_isbn_data` (
  `vlv_id` varchar(64) NOT NULL,
  `empfehlungen` text NOT NULL,
  PRIMARY KEY (`vlv_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vlv_issn`
--
-- Erzeugt am: 26. Nov 2015 um 09:43
--

DROP TABLE IF EXISTS `vlv_issn`;
CREATE TABLE IF NOT EXISTS `vlv_issn` (
  `literatur_ref` varchar(255) NOT NULL,
  `issn` varchar(255) NOT NULL,
  PRIMARY KEY (`literatur_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vlv_literatur`
--
-- Erzeugt am: 26. Nov 2015 um 09:43
--

DROP TABLE IF EXISTS `vlv_literatur`;
CREATE TABLE IF NOT EXISTS `vlv_literatur` (
  `vlv_id` int(11) NOT NULL,
  `literatur_ref` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vlv_literatur_blocked`
--
-- Erzeugt am: 26. Nov 2015 um 09:43
--

DROP TABLE IF EXISTS `vlv_literatur_blocked`;
CREATE TABLE IF NOT EXISTS `vlv_literatur_blocked` (
  `literatur_ref` varchar(255) NOT NULL,
  PRIMARY KEY (`literatur_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `vlv_zusammenfassung`
--
-- Erzeugt am: 30. Jan 2016 um 11:10
-- Aktualisiert am: 30. Jan 2016 um 11:10
--

DROP TABLE IF EXISTS `vlv_zusammenfassung`;
CREATE TABLE IF NOT EXISTS `vlv_zusammenfassung` (
  `vlv_id` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `author` varchar(255) NOT NULL,
  `url` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`vlv_id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- RELATIONEN DER TABELLE `vlv_zusammenfassung`:
--   `description`
--       `vlv2_object` -> `id`
--
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
