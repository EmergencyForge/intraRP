-- --------------------------------------------------------
-- Host:                         plesk2.living-bots.net
-- Server-Version:               10.11.10-MariaDB-ubu2004 - mariadb.org binary distribution
-- Server-Betriebssystem:        debian-linux-gnu
-- HeidiSQL Version:             12.10.0.7000
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Exportiere Struktur von Tabelle intradev.intra_antrag_bef
CREATE TABLE IF NOT EXISTS `intra_antrag_bef` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uniqueid` int(11) NOT NULL,
  `name_dn` varchar(255) NOT NULL,
  `dienstgrad` varchar(255) NOT NULL,
  `time_added` datetime NOT NULL DEFAULT current_timestamp(),
  `freitext` text NOT NULL,
  `cirs_manager` varchar(255) DEFAULT NULL,
  `cirs_time` datetime DEFAULT NULL,
  `cirs_status` tinyint(3) NOT NULL DEFAULT 0,
  `cirs_text` text DEFAULT NULL,
  `discordid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=652 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Daten-Export vom Benutzer nicht ausgewählt

CREATE TABLE IF NOT EXISTS `intra_users_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priority` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) NOT NULL,
  `color` varchar(255) DEFAULT NULL,
  `permissions` longtext DEFAULT '[]',
  `default` tinyint(1) NOT NULL DEFAULT 0,
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `intra_users_roles` (`id`, `priority`, `name`, `color`, `permissions`, `created_at`) VALUES
	(1, 10, 'Admin', 'danger', '["admin"]', '2025-03-23 22:17:15'),
	(2, 100, 'SGL', 'primary', '["application.view", "application.edit", "edivi.view", "personnel.view", "personnel.edit", "personnel.documents.manage", "users.view", "users.edit", "users.create", "files.upload", "files.log.view"]', '2025-03-23 22:27:45'),
	(3, 110, 'TL', 'primary', '["personnel.view", "personnel.documents.manage"]', '2025-03-23 22:28:16'),
	(4, 200, 'QM-RD', 'info', '["personnel.view", "edivi.view", "edivi.edit"]', '2025-03-23 22:30:31'),
	(5, 210, 'Ausbilder', 'success', '["personnel.view", "personnel.documents.manage"]', '2025-03-23 22:31:57'),
	(6, 220, 'Personaler', 'success', '["personnel.view", "personnel.edit", "personnel.documents.manage"]', '2025-03-23 22:32:18'),
	(7, 999, 'Gast', 'secondary', '[]', '2025-03-23 22:33:25');

CREATE TABLE IF NOT EXISTS `intra_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `discord_id` varchar(255) NOT NULL,
  `aktenid` int(11) DEFAULT NULL,
  `role` int(11) NOT NULL DEFAULT 0,
  `full_admin` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `FK_intra_users_intra_users_roles` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE IF NOT EXISTS `intra_audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL DEFAULT 0,
  `module` varchar(255) DEFAULT NULL,
  `action` text DEFAULT NULL,
  `details` text DEFAULT NULL,
  `global` tinyint(1) NOT NULL DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_intra_audit_log_intra_users` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Now add foreign keys
ALTER TABLE `intra_users`
  ADD CONSTRAINT `FK_intra_users_intra_users_roles`
  FOREIGN KEY (`role`) REFERENCES `intra_users_roles` (`id`)
  ON DELETE NO ACTION ON UPDATE CASCADE;

ALTER TABLE `intra_audit_log`
  ADD CONSTRAINT `FK_intra_audit_log_intra_users`
  FOREIGN KEY (`user`) REFERENCES `intra_users` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;


-- Daten-Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle intradev.intra_dashboard_categories
CREATE TABLE IF NOT EXISTS `intra_dashboard_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Daten-Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle intradev.intra_dashboard_tiles
CREATE TABLE IF NOT EXISTS `intra_dashboard_tiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` int(11) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '#',
  `icon` varchar(255) NOT NULL DEFAULT 'las la-external-link-alt',
  `priority` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_intra_dashboard_tiles_intra_dashboard_categories` (`category`),
  CONSTRAINT `FK_intra_dashboard_tiles_intra_dashboard_categories` FOREIGN KEY (`category`) REFERENCES `intra_dashboard_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Daten-Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle intradev.intra_edivi
CREATE TABLE IF NOT EXISTS `intra_edivi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patname` varchar(255) DEFAULT NULL,
  `patgebdat` date DEFAULT NULL,
  `patsex` tinyint(1) DEFAULT NULL,
  `edatum` date DEFAULT NULL,
  `ezeit` varchar(255) DEFAULT NULL,
  `enr` varchar(255) NOT NULL,
  `eort` varchar(255) DEFAULT NULL,
  `sendezeit` datetime DEFAULT current_timestamp(),
  `awfrei_1` tinyint(1) DEFAULT NULL,
  `awfrei_2` tinyint(1) DEFAULT NULL,
  `awfrei_3` tinyint(1) DEFAULT NULL,
  `awsicherung_1` tinyint(1) DEFAULT NULL,
  `awsicherung_2` tinyint(1) DEFAULT NULL,
  `awsicherung_neu` tinyint(1) DEFAULT NULL,
  `zyanose_1` tinyint(1) DEFAULT NULL,
  `zyanose_2` tinyint(1) DEFAULT NULL,
  `o2gabe` tinyint(15) DEFAULT 0,
  `b_symptome` tinyint(4) DEFAULT NULL,
  `b_auskult` tinyint(3) DEFAULT NULL,
  `b_beatmung` tinyint(3) DEFAULT NULL,
  `spo2` varchar(255) DEFAULT NULL,
  `atemfreq` varchar(255) DEFAULT NULL,
  `etco2` varchar(255) DEFAULT NULL,
  `c_kreislauf` tinyint(2) DEFAULT NULL,
  `rrsys` varchar(255) DEFAULT NULL,
  `rrdias` varchar(255) DEFAULT NULL,
  `herzfreq` varchar(255) DEFAULT NULL,
  `c_ekg` tinyint(9) DEFAULT NULL,
  `c_zugang_art_1` tinyint(9) DEFAULT NULL,
  `c_zugang_gr_1` tinyint(9) DEFAULT NULL,
  `c_zugang_ort_1` varchar(255) DEFAULT NULL,
  `c_zugang_art_2` tinyint(9) DEFAULT NULL,
  `c_zugang_gr_2` tinyint(9) DEFAULT NULL,
  `c_zugang_ort_2` varchar(255) DEFAULT NULL,
  `c_zugang_art_3` tinyint(9) DEFAULT NULL,
  `c_zugang_gr_3` tinyint(9) DEFAULT NULL,
  `c_zugang_ort_3` varchar(255) DEFAULT NULL,
  `d_bewusstsein` tinyint(3) DEFAULT NULL,
  `d_pupillenw_1` tinyint(3) DEFAULT NULL,
  `d_pupillenw_2` tinyint(3) DEFAULT NULL,
  `d_lichtreakt_1` tinyint(2) DEFAULT NULL,
  `d_lichtreakt_2` tinyint(2) DEFAULT NULL,
  `d_gcs_1` tinyint(3) DEFAULT NULL,
  `d_gcs_2` tinyint(4) DEFAULT NULL,
  `d_gcs_3` tinyint(5) DEFAULT NULL,
  `d_ex_1` tinyint(2) DEFAULT NULL,
  `bz` varchar(255) DEFAULT NULL,
  `temp` varchar(255) DEFAULT NULL,
  `v_muster_k` tinyint(3) DEFAULT NULL,
  `v_muster_k1` tinyint(2) DEFAULT NULL,
  `v_muster_w` tinyint(3) DEFAULT NULL,
  `v_muster_w1` tinyint(2) DEFAULT NULL,
  `v_muster_t` tinyint(3) DEFAULT NULL,
  `v_muster_t1` tinyint(2) DEFAULT NULL,
  `v_muster_a` tinyint(3) DEFAULT NULL,
  `v_muster_a1` tinyint(2) DEFAULT NULL,
  `v_muster_al` tinyint(3) DEFAULT NULL,
  `v_muster_al1` tinyint(2) DEFAULT NULL,
  `v_muster_ar` tinyint(3) DEFAULT NULL,
  `v_muster_ar1` tinyint(2) DEFAULT NULL,
  `v_muster_bl` tinyint(3) DEFAULT NULL,
  `v_muster_bl1` tinyint(2) DEFAULT NULL,
  `v_muster_br` tinyint(3) DEFAULT NULL,
  `v_muster_br1` tinyint(2) DEFAULT NULL,
  `sz_nrs` tinyint(2) DEFAULT NULL,
  `sz_toleranz_1` tinyint(2) DEFAULT NULL,
  `sz_toleranz_2` tinyint(2) DEFAULT NULL,
  `medis` longtext DEFAULT NULL,
  `diagnose` text DEFAULT NULL,
  `anmerkungen` text DEFAULT NULL,
  `pfname` varchar(255) DEFAULT NULL,
  `fzg_transp` varchar(255) DEFAULT NULL,
  `fzg_transp_perso` varchar(255) DEFAULT NULL,
  `fzg_na` varchar(255) DEFAULT NULL,
  `fzg_na_perso` varchar(255) DEFAULT NULL,
  `fzg_sonst` varchar(255) DEFAULT NULL,
  `naname` varchar(255) DEFAULT NULL,
  `transportziel` varchar(255) DEFAULT NULL,
  `protokoll_status` tinyint(3) DEFAULT 0,
  `bearbeiter` varchar(255) DEFAULT NULL,
  `qmkommentar` text DEFAULT NULL,
  `freigegeben` tinyint(1) DEFAULT 0,
  `freigeber_name` varchar(255) DEFAULT NULL,
  `last_edit` timestamp NULL DEFAULT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2498 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Daten-Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle intradev.intra_edivi_fahrzeuge
CREATE TABLE IF NOT EXISTS `intra_edivi_fahrzeuge` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priority` int(11) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `veh_type` varchar(255) NOT NULL,
  `doctor` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Daten-Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle intradev.intra_edivi_qmlog
CREATE TABLE IF NOT EXISTS `intra_edivi_qmlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `protokoll_id` int(11) NOT NULL,
  `kommentar` longtext NOT NULL,
  `log_aktion` tinyint(1) DEFAULT NULL,
  `bearbeiter` varchar(255) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_intra_edivi_qmlog_intra_edivi` (`protokoll_id`),
  CONSTRAINT `FK_intra_edivi_qmlog_intra_edivi` FOREIGN KEY (`protokoll_id`) REFERENCES `intra_edivi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=938 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Daten-Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle intradev.intra_edivi_ziele
CREATE TABLE IF NOT EXISTS `intra_edivi_ziele` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priority` int(11) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `transport` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `intra_edivi_ziele` (`id`, `priority`, `identifier`, `name`, `transport`, `active`, `created_at`) VALUES
	(2, 98, 'amb', 'Ambulante Versorgung', 0, 1, '2025-03-19 22:32:15'),
	(3, 99, 'ubg', 'Übergabe Notfallteam', 0, 1, '2025-03-19 22:32:22'),
	(4, 96, 'kp', 'Kein Patient', 0, 1, '2025-03-19 22:32:36'),
	(5, 97, 'sf', 'Sozialfahrt', 0, 1, '2025-03-19 22:32:42');

-- Exportiere Struktur von Tabelle intradev.intra_mitarbeiter_dienstgrade
CREATE TABLE IF NOT EXISTS `intra_mitarbeiter_dienstgrade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priority` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_m` varchar(255) NOT NULL,
  `name_w` varchar(255) NOT NULL,
  `badge` varchar(255) DEFAULT NULL,
  `archive` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

INSERT INTO `intra_mitarbeiter_dienstgrade` (`id`, `priority`, `name`, `name_m`, `name_w`, `badge`, `archive`, `created_at`) VALUES
	(1, 1, 'Angestellte/-r', 'Angestellter', 'Angestellte', NULL, 0, '2025-03-20 00:51:26'),
	(2, 2, 'Brandmeisteranwärter/-in', 'Brandmeisteranwärter', 'Brandmeisteranwärterin', '/assets/img/dienstgrade/bf/1.png', 0, '2025-03-20 00:52:59'),
	(3, 3, 'Brandmeister/-in', 'Brandmeister', 'Brandmeisterin', '/assets/img/dienstgrade/bf/2.png', 0, '2025-03-20 00:53:27'),
	(4, 4, 'Oberbrandmeister/-in', 'Oberbrandmeister', 'Oberbrandmeisterin', '/assets/img/dienstgrade/bf/3.png', 0, '2025-03-20 00:54:22'),
	(5, 5, 'Hauptbrandmeister/-in', 'Hauptbrandmeister', 'Hauptbrandmeisterin', '/assets/img/dienstgrade/bf/4.png', 0, '2025-03-20 00:54:49'),
	(6, 6, 'Hauptbrandmeister/-in mit AZ', 'Hauptbrandmeister mit AZ', 'Hauptbrandmesiterin mit AZ', '/assets/img/dienstgrade/bf/5.png', 0, '2025-03-20 00:55:17'),
	(7, 8, 'Brandinspektor/-in', 'Brandinspektor', 'Brandinspektorin', '/assets/img/dienstgrade/bf/6.png', 0, '2025-03-20 00:55:46'),
	(8, 9, 'Oberbrandinspektor/-in', 'Oberbrandinspektor', 'Oberbrandinspektorin', '/assets/img/dienstgrade/bf/7.png', 0, '2025-03-20 00:56:02'),
	(9, 10, 'Brandamtmann/frau', 'Brandamtmann', 'Brandamtfrau', '/assets/img/dienstgrade/bf/8.png', 0, '2025-03-20 00:56:30'),
	(10, 11, 'Brandamtsrat/rätin', 'Brandamtsrat', 'Brandamtsrätin', '/assets/img/dienstgrade/bf/9.png', 0, '2025-03-20 00:56:57'),
	(11, 12, 'Brandoberamtsrat/rätin', 'Brandoberamtsrat', 'Brandoberamtsrätin', '/assets/img/dienstgrade/bf/10.png', 0, '2025-03-20 00:57:18'),
	(12, 13, 'Brandreferendar/-in', 'Brandreferendar', 'Brandreferendarin', '/assets/img/dienstgrade/bf/15.png', 0, '2025-03-20 00:57:48'),
	(13, 14, 'Brandrat/rätin', 'Brandrat', 'Brandrätin', '/assets/img/dienstgrade/bf/11.png', 0, '2025-03-20 00:58:33'),
	(14, 15, 'Oberbrandrat/rätin', 'Oberbrandrat', 'Oberbrandrätin', '/assets/img/dienstgrade/bf/12.png', 0, '2025-03-20 00:58:35'),
	(15, 7, 'Brandinspektoranwärter/-in', 'Brandinspektoranwärter', 'Brandinspektoranwärterin', '/assets/img/dienstgrade/bf/17_2.png', 0, '2025-03-20 00:59:35'),
	(16, 0, 'Ehrenamtliche/-r', 'Ehrenamtlicher', 'Ehrenamtliche', NULL, 0, '2025-03-20 01:02:58'),
	(17, 16, 'Branddirektor/-in', 'Branddirektor', 'Branddirektorin', '/assets/img/dienstgrade/bf/13.png', 0, '2025-03-20 01:03:56'),
	(18, 17, 'Leitende/-r Branddirektor/-in', 'Leitender Branddirektor', 'Leitende Branddirektorin', '/assets/img/dienstgrade/bf/14.png', 0, '2025-03-20 01:04:28'),
	(19, 0, 'Entlassen/Archiv', 'Entlassen/Archiv', 'Entlassen/Archiv', NULL, 1, '2025-03-20 02:10:36');

-- Exportiere Struktur von Tabelle intradev.intra_mitarbeiter_fwquali
CREATE TABLE IF NOT EXISTS `intra_mitarbeiter_fwquali` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priority` int(11) NOT NULL,
  `shortname` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_m` varchar(255) NOT NULL,
  `name_w` varchar(255) NOT NULL,
  `none` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `intra_mitarbeiter_fwquali` (`id`, `priority`, `shortname`, `name`, `name_m`, `name_w`, `none`, `created_at`) VALUES
	(2, 0, '-', 'Keine', 'Keine', 'Keine', 1, '2025-03-20 01:11:16'),
	(3, 1, 'B1', 'Grundausbildung', 'Grundausbildung', 'Grundausbildung', 0, '2025-03-20 01:11:32'),
	(4, 2, 'B2', 'Maschinist/-in', 'Maschinist', 'Maschinistin', 0, '2025-03-20 01:11:46'),
	(5, 3, 'B3', 'Gruppenführer/-in', 'Gruppenführer', 'Gruppenführerin', 0, '2025-03-20 01:12:06'),
	(6, 4, 'B4', 'Zugführer/-in', 'Zugführer', 'Zugführerin', 0, '2025-03-20 01:12:23'),
	(7, 5, 'B5', 'B-Dienst', 'B-Dienst', 'B-Dienst', 0, '2025-03-20 01:12:31'),
	(8, 6, 'B6', 'A-Dienst', 'A-Dienst', 'A-Dienst', 0, '2025-03-20 01:12:41');

-- Exportiere Struktur von Tabelle intradev.intra_mitarbeiter_log
CREATE TABLE IF NOT EXISTS `intra_mitarbeiter_log` (
  `logid` int(11) NOT NULL AUTO_INCREMENT,
  `profilid` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 0,
  `content` longtext NOT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `paneluser` varchar(255) NOT NULL,
  PRIMARY KEY (`logid`)
) ENGINE=InnoDB AUTO_INCREMENT=6412 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Daten-Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle intradev.intra_mitarbeiter_rdquali
CREATE TABLE IF NOT EXISTS `intra_mitarbeiter_rdquali` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priority` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `name_m` varchar(255) NOT NULL,
  `name_w` varchar(255) NOT NULL,
  `none` tinyint(1) NOT NULL DEFAULT 0,
  `trainable` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=DYNAMIC;

INSERT INTO `intra_mitarbeiter_rdquali` (`id`, `priority`, `name`, `name_m`, `name_w`, `none`, `trainable`, `created_at`) VALUES
	(2, 1, 'Rettungssanitäter/-in i. A.', 'Rettungssanitäter i. A.', 'Rettungssanitäterin i. A.', 0, 0, '2025-03-20 01:07:47'),
	(3, 0, 'Keine', 'Keine', 'Keine', 1, 0, '2025-03-20 01:08:48'),
	(4, 2, 'Rettungssanitäter/-in', 'Rettungssanitäter', 'Rettungssanitäterin', 0, 1, '2025-03-20 01:09:04'),
	(5, 3, 'Notfallsanitäter/-in i. A.', 'Notfallsanitäter i. A.', 'Notfallsanitäterin i. A.', 0, 0, '2025-03-20 01:09:31'),
	(6, 4, 'Notfallsanitäter/-in', 'Notfallsanitäter', 'Notfallsanitäterin', 0, 1, '2025-03-20 01:09:46'),
	(7, 5, 'Notarzt/ärztin', 'Notarzt', 'Notärztin', 0, 0, '2025-03-20 01:10:00'),
	(8, 6, 'Ärztliche/-r Leiter/-in RD', 'Ärztlicher Leiter RD', 'Ärztliche Leiterin RD', 0, 0, '2025-03-20 01:10:25');

-- Exportiere Struktur von Tabelle intradev.intra_mitarbeiter
CREATE TABLE IF NOT EXISTS `intra_mitarbeiter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL,
  `gebdatum` date NOT NULL,
  `charakterid` varchar(255) NOT NULL,
  `geschlecht` tinyint(1) NOT NULL,
  `forumprofil` int(5) DEFAULT NULL,
  `discordtag` varchar(255) DEFAULT NULL,
  `telefonnr` varchar(255) DEFAULT NULL,
  `dienstnr` varchar(255) NOT NULL,
  `einstdatum` date NOT NULL,
  `dienstgrad` int(11) NOT NULL DEFAULT 0,
  `qualifw2` int(11) NOT NULL DEFAULT 0,
  `qualird` int(11) NOT NULL DEFAULT 0,
  `zusatz` varchar(255) DEFAULT NULL,
  `fachdienste` longtext NOT NULL DEFAULT '[]',
  `createdate` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dienstnr` (`dienstnr`),
  KEY `FK_intra_mitarbeiter_intra_mitarbeiter_dienstgrade` (`dienstgrad`),
  KEY `FK_intra_mitarbeiter_intra_mitarbeiter_fwquali` (`qualifw2`),
  KEY `FK_intra_mitarbeiter_intra_mitarbeiter_rdquali` (`qualird`),
  CONSTRAINT `FK_intra_mitarbeiter_intra_mitarbeiter_dienstgrade` FOREIGN KEY (`dienstgrad`) REFERENCES `intra_mitarbeiter_dienstgrade` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `FK_intra_mitarbeiter_intra_mitarbeiter_fwquali` FOREIGN KEY (`qualifw2`) REFERENCES `intra_mitarbeiter_fwquali` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `FK_intra_mitarbeiter_intra_mitarbeiter_rdquali` FOREIGN KEY (`qualird`) REFERENCES `intra_mitarbeiter_rdquali` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1038 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Daten-Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle intradev.intra_mitarbeiter_dokumente
CREATE TABLE IF NOT EXISTS `intra_mitarbeiter_dokumente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `docid` int(11) NOT NULL,
  `type` tinyint(2) NOT NULL DEFAULT 0,
  `anrede` tinyint(1) NOT NULL DEFAULT 0,
  `erhalter` varchar(255) DEFAULT NULL,
  `inhalt` longtext DEFAULT NULL,
  `suspendtime` date DEFAULT NULL,
  `erhalter_gebdat` date DEFAULT NULL,
  `erhalter_rang` tinyint(2) DEFAULT NULL,
  `erhalter_rang_rd` tinyint(2) DEFAULT NULL,
  `erhalter_quali` tinyint(2) DEFAULT NULL,
  `ausstellungsdatum` date DEFAULT NULL,
  `ausstellerid` int(11) NOT NULL,
  `aussteller_name` varchar(255) DEFAULT NULL,
  `aussteller_rang` tinyint(2) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `profileid` int(11) DEFAULT NULL,
  `discordid` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `docid` (`docid`),
  KEY `FK_intra_mitarbeiter_dokumente_intra_mitarbeiter` (`profileid`),
  CONSTRAINT `FK_intra_mitarbeiter_dokumente_intra_mitarbeiter` FOREIGN KEY (`profileid`) REFERENCES `intra_mitarbeiter` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2291 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Daten-Export vom Benutzer nicht ausgewählt

-- Exportiere Struktur von Tabelle intradev.intra_uploads
CREATE TABLE IF NOT EXISTS `intra_uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(255) NOT NULL,
  `file_size` varchar(255) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `upload_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- Daten-Export vom Benutzer nicht ausgewählt

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
