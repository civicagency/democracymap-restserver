-- phpMyAdmin SQL Dump
-- version 3.5.4
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Aug 08, 2013 at 10:39 PM
-- Server version: 5.5.28
-- PHP Version: 5.3.15

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `democracymap`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(40) NOT NULL,
  `level` int(2) NOT NULL,
  `ignore_limits` tinyint(1) NOT NULL DEFAULT '0',
  `date_created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `api_limits`
--

CREATE TABLE IF NOT EXISTS `api_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) NOT NULL,
  `count` int(10) NOT NULL,
  `hour_started` int(11) NOT NULL,
  `api_key` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `api_logs`
--

CREATE TABLE IF NOT EXISTS `api_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) NOT NULL,
  `method` varchar(6) NOT NULL,
  `params` text,
  `api_key` varchar(40) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `time` int(11) NOT NULL,
  `authorized` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2335 ;

-- --------------------------------------------------------

--
-- Table structure for table `community_boards`
--

CREATE TABLE IF NOT EXISTS `community_boards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `community_board` text NOT NULL,
  `city_id` int(3) NOT NULL,
  `address` text NOT NULL,
  `borough` text NOT NULL,
  `borough_id` int(2) NOT NULL,
  `board_meeting` text NOT NULL,
  `cabinet_meeting` text NOT NULL,
  `chair` text NOT NULL,
  `district_manager` text NOT NULL,
  `website` text NOT NULL,
  `email` text NOT NULL,
  `phone` text NOT NULL,
  `fax` text NOT NULL,
  `neighborhoods` text NOT NULL,
  `precinct1` text NOT NULL,
  `precinct1_phone` text NOT NULL,
  `precinct2` text NOT NULL,
  `precinct2_phone` text NOT NULL,
  `precinct3` text NOT NULL,
  `precinct3_phone` text NOT NULL,
  `precinct4` text NOT NULL,
  `precinct4_phone` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=60 ;

-- --------------------------------------------------------

--
-- Table structure for table `council_districts`
--

CREATE TABLE IF NOT EXISTS `council_districts` (
  `address` text NOT NULL,
  `committees` text NOT NULL,
  `term_expiration` text NOT NULL,
  `district` int(4) NOT NULL,
  `district_fax` varchar(255) NOT NULL,
  `district_phone` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `council_member_since` text NOT NULL,
  `headshot_photo` text NOT NULL,
  `legislative_fax` varchar(255) NOT NULL,
  `legislative_address` text NOT NULL,
  `legislative_phone` varchar(255) NOT NULL,
  `name` text NOT NULL,
  `twitter_user` text,
  `facebook_url` text,
  KEY `district` (`district`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `counties`
--

CREATE TABLE IF NOT EXISTS `counties` (
  `state_id` int(2) NOT NULL,
  `type_id` int(1) NOT NULL,
  `county_id` int(3) NOT NULL,
  `unit_id` int(3) NOT NULL,
  `supplement` int(3) NOT NULL,
  `sub_code` int(2) NOT NULL,
  `name` varchar(255) NOT NULL,
  `political_description` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `address1` varchar(255) NOT NULL,
  `address2` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(2) NOT NULL,
  `zip` int(5) NOT NULL,
  `zip4` int(4) NOT NULL,
  `website_url` varchar(255) NOT NULL,
  `population_2006` int(10) NOT NULL,
  `fips_state` int(2) NOT NULL,
  `fips_county` int(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `edited_jurisdictions`
--

CREATE TABLE IF NOT EXISTS `edited_jurisdictions` (
  `meta_internal_id` int(10) NOT NULL AUTO_INCREMENT,
  `meta_last_author_id` varchar(255) DEFAULT NULL,
  `meta_validated_source` varchar(255) NOT NULL,
  `ocd_id` varchar(256) NOT NULL,
  `uid` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `type_name` varchar(255) DEFAULT NULL,
  `level` varchar(255) NOT NULL,
  `level_name` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `url_contact` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address_name` varchar(255) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(2) DEFAULT NULL,
  `address_locality` varchar(255) DEFAULT NULL,
  `address_region` varchar(255) DEFAULT NULL,
  `address_postcode` varchar(255) DEFAULT NULL,
  `address_country` varchar(255) DEFAULT NULL,
  `service_discovery` varchar(255) DEFAULT NULL,
  `last_updated` datetime NOT NULL,
  `other_data` text,
  PRIMARY KEY (`meta_internal_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `edited_officials`
--

CREATE TABLE IF NOT EXISTS `edited_officials` (
  `meta_internal_id` int(10) NOT NULL AUTO_INCREMENT,
  `meta_ocd_id` varchar(255) NOT NULL,
  `meta_last_author_id` varchar(255) DEFAULT NULL,
  `meta_validated_source` varchar(255) NOT NULL,
  `uid` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `name_given` varchar(255) DEFAULT NULL,
  `name_family` varchar(255) DEFAULT NULL,
  `name_full` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `url_photo` varchar(255) DEFAULT NULL,
  `url_schedule` varchar(255) DEFAULT NULL,
  `url_contact` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address_name` varchar(255) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `address_locality` varchar(255) DEFAULT NULL,
  `address_region` varchar(255) DEFAULT NULL,
  `address_postcode` varchar(255) DEFAULT NULL,
  `address_country` varchar(255) DEFAULT NULL,
  `current_term_enddate` datetime DEFAULT NULL,
  `last_updated` datetime NOT NULL,
  `social_media` text,
  `other_data` text,
  PRIMARY KEY (`meta_internal_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gnis`
--

CREATE TABLE IF NOT EXISTS `gnis` (
  `FEATURE_ID` varchar(255) NOT NULL,
  `FEATURE_NAME` varchar(255) NOT NULL,
  `FEATURE_CLASS` varchar(255) NOT NULL,
  `CENSUS_CODE` varchar(255) NOT NULL,
  `CENSUS_CLASS_CODE` varchar(255) NOT NULL,
  `GSA_CODE` varchar(255) NOT NULL,
  `OPM_CODE` varchar(255) NOT NULL,
  `STATE_NUMERIC` varchar(255) NOT NULL,
  `STATE_ALPHA` varchar(255) NOT NULL,
  `COUNTY_SEQUENCE` varchar(255) NOT NULL,
  `COUNTY_NUMERIC` varchar(255) NOT NULL,
  `COUNTY_NAME` varchar(255) NOT NULL,
  `PRIMARY_LATITUDE` varchar(255) NOT NULL,
  `PRIMARY_LONGITUDE` varchar(255) NOT NULL,
  `DATE_CREATED` varchar(255) NOT NULL,
  `DATE_EDITED` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `municipalities`
--

CREATE TABLE IF NOT EXISTS `municipalities` (
  `STATE` int(2) NOT NULL,
  `TYPE` int(1) NOT NULL,
  `COUNTY` int(3) NOT NULL,
  `UNIT` int(3) NOT NULL,
  `SUPPLEMENT` int(3) NOT NULL,
  `SUB_CODE` int(2) NOT NULL,
  `GOVERNMENT_NAME` varchar(255) NOT NULL,
  `POLITICAL_DESCRIPTION` varchar(255) NOT NULL,
  `TITLE` varchar(255) NOT NULL,
  `ADDRESS1` varchar(255) NOT NULL,
  `ADDRESS2` varchar(255) NOT NULL,
  `CITY` varchar(255) NOT NULL,
  `STATE_ABBR` varchar(2) NOT NULL,
  `ZIP` varchar(255) NOT NULL,
  `ZIP4` varchar(255) NOT NULL,
  `WEB_ADDRESS` varchar(255) NOT NULL,
  `POPULATION_2005` int(10) NOT NULL,
  `FIPS_STATE` int(2) NOT NULL,
  `FIPS_COUNTY` int(3) NOT NULL,
  `FIPS_PLACE` int(5) NOT NULL,
  `COUNTY_AREA_NAME` varchar(255) NOT NULL,
  `COUNTY_AREA_TYPE` varchar(255) NOT NULL,
  `SERVICE_DISCOVERY` varchar(255) NOT NULL,
  `GEOID` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ocd`
--

CREATE TABLE IF NOT EXISTS `ocd` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `OCDID` varchar(255) DEFAULT NULL,
  `GEOID` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=65538 ;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(12) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `scraped_jurisdictions`
--

CREATE TABLE IF NOT EXISTS `scraped_jurisdictions` (
  `meta_internal_id` int(10) NOT NULL AUTO_INCREMENT,
  `ocd_id` varchar(256) NOT NULL,
  `uid` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `type_name` varchar(255) DEFAULT NULL,
  `level` varchar(255) NOT NULL,
  `level_name` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `url_contact` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address_name` varchar(255) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(2) DEFAULT NULL,
  `address_locality` varchar(255) DEFAULT NULL,
  `address_region` varchar(255) DEFAULT NULL,
  `address_postcode` varchar(255) DEFAULT NULL,
  `address_country` varchar(255) DEFAULT NULL,
  `service_discovery` varchar(255) NOT NULL,
  `last_updated` datetime NOT NULL,
  `other_data` text,
  `conflicting_data` text NOT NULL,
  `sources` text NOT NULL,
  PRIMARY KEY (`meta_internal_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19441 ;

-- --------------------------------------------------------

--
-- Table structure for table `scraped_officials`
--

CREATE TABLE IF NOT EXISTS `scraped_officials` (
  `meta_internal_id` int(10) NOT NULL AUTO_INCREMENT,
  `meta_ocd_id` varchar(255) NOT NULL,
  `uid` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `name_given` varchar(255) DEFAULT NULL,
  `name_family` varchar(255) DEFAULT NULL,
  `name_full` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `url_photo` varchar(255) DEFAULT NULL,
  `url_schedule` varchar(255) DEFAULT NULL,
  `url_contact` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address_name` varchar(255) DEFAULT NULL,
  `address_1` varchar(255) DEFAULT NULL,
  `address_2` varchar(255) DEFAULT NULL,
  `address_locality` varchar(255) DEFAULT NULL,
  `address_region` varchar(255) DEFAULT NULL,
  `address_postcode` varchar(255) DEFAULT NULL,
  `address_country` varchar(255) DEFAULT NULL,
  `current_term_enddate` datetime DEFAULT NULL,
  `last_updated` datetime NOT NULL,
  `social_media` text,
  `other_data` text,
  PRIMARY KEY (`meta_internal_id`),
  KEY `meta_ocd_id` (`meta_ocd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `scraped_socialmedia`
--

CREATE TABLE IF NOT EXISTS `scraped_socialmedia` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `source_type` varchar(256) DEFAULT NULL COMMENT 'scraped, edited, etc',
  `ocd_id` varchar(256) DEFAULT NULL,
  `network_type` varchar(256) DEFAULT NULL,
  `description` text,
  `username` varchar(256) DEFAULT NULL,
  `url` varchar(256) DEFAULT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sync_log`
--

CREATE TABLE IF NOT EXISTS `sync_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `source` varchar(256) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `description` text,
  `timestamp` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=145 ;

-- --------------------------------------------------------

--
-- Table structure for table `sync_scheduler`
--

CREATE TABLE IF NOT EXISTS `sync_scheduler` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `scraper_name` varchar(255) DEFAULT NULL,
  `description` text,
  `url` varchar(255) DEFAULT NULL,
  `last_run` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `email` varchar(30) DEFAULT NULL,
  `login` varchar(18) NOT NULL,
  `password` varchar(60) NOT NULL,
  `last_login` date DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `users_permissions`
--

CREATE TABLE IF NOT EXISTS `users_permissions` (
  `user_id` mediumint(8) NOT NULL,
  `permission_id` mediumint(8) NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users_permissions`
--
ALTER TABLE `users_permissions`
  ADD CONSTRAINT `users_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `users_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
