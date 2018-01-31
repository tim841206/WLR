-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- 主機: 127.0.0.1
-- 產生時間： 2018-01-31 07:12:34
-- 伺服器版本: 10.1.28-MariaDB
-- PHP 版本： 5.6.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `wlr`
--

-- --------------------------------------------------------

--
-- 資料表結構 `item`
--

CREATE TABLE `item` (
  `ITEMNO` varchar(20) COLLATE utf8_bin NOT NULL,
  `ITEMNM` varchar(50) COLLATE utf8_bin NOT NULL,
  `ITEMAMT` int(11) NOT NULL DEFAULT '0',
  `DESCRIPTION` varchar(200) COLLATE utf8_bin NOT NULL,
  `MEMO` varchar(200) COLLATE utf8_bin NOT NULL,
  `CREATETIME` datetime NOT NULL,
  `UPDATETIME` datetime NOT NULL,
  `ACTCODE` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 資料表結構 `logistic`
--

CREATE TABLE `logistic` (
  `LOGISTICNO` varchar(20) COLLATE utf8_bin NOT NULL,
  `SENDER` varchar(20) COLLATE utf8_bin NOT NULL,
  `RECEIVER` varchar(20) COLLATE utf8_bin NOT NULL,
  `STATE` varchar(1) COLLATE utf8_bin NOT NULL,
  `MEMO` varchar(200) COLLATE utf8_bin NOT NULL,
  `CREATETIME` datetime NOT NULL,
  `UPDATETIME` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 資料表結構 `logisticitem`
--

CREATE TABLE `logisticitem` (
  `LOGISTICNO` varchar(20) COLLATE utf8_bin NOT NULL,
  `ITEMNO` varchar(20) COLLATE utf8_bin NOT NULL,
  `AMT` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 資料表結構 `request`
--

CREATE TABLE `request` (
  `REQUESTNO` varchar(20) COLLATE utf8_bin NOT NULL,
  `REQUESTER` varchar(20) COLLATE utf8_bin NOT NULL,
  `TARGET` varchar(20) COLLATE utf8_bin NOT NULL,
  `STATE` varchar(1) COLLATE utf8_bin NOT NULL,
  `MEMO` varchar(200) COLLATE utf8_bin NOT NULL,
  `CREATETIME` datetime NOT NULL,
  `UPDATETIME` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 資料表結構 `requestitem`
--

CREATE TABLE `requestitem` (
  `REQUESTNO` varchar(20) COLLATE utf8_bin NOT NULL,
  `ITEMNO` varchar(20) COLLATE utf8_bin NOT NULL,
  `AMT` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 資料表結構 `user`
--

CREATE TABLE `user` (
  `ACCOUNT` varchar(20) COLLATE utf8_bin NOT NULL,
  `PASSWORD` varchar(20) COLLATE utf8_bin NOT NULL,
  `TOKEN` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  `USERNM` varchar(50) COLLATE utf8_bin NOT NULL,
  `PHONE` varchar(20) COLLATE utf8_bin DEFAULT NULL,
  `EMAIL` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `AUTHORITY` varchar(1) COLLATE utf8_bin NOT NULL DEFAULT 'C',
  `CREATEDATE` datetime NOT NULL,
  `LASTLOGINDATE` datetime NOT NULL,
  `ACTCODE` tinyint(2) NOT NULL DEFAULT '2'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- 資料表的匯出資料 `user`
--

INSERT INTO `user` (`ACCOUNT`, `PASSWORD`, `TOKEN`, `USERNM`, `PHONE`, `EMAIL`, `AUTHORITY`, `CREATEDATE`, `LASTLOGINDATE`, `ACTCODE`) VALUES
('tim841206', '900a5367f5e1', NULL, '冷俊瑩', '0922825881', 'a0922825881@gmail.com', 'A', '2018-01-30 00:00:00', '2018-01-30 00:00:00', 1);

-- --------------------------------------------------------

--
-- 資料表結構 `userwhouse`
--

CREATE TABLE `userwhouse` (
  `ACCOUNT` varchar(20) COLLATE utf8_bin NOT NULL,
  `WHOUSENO` varchar(20) COLLATE utf8_bin NOT NULL,
  `AUTHORITY` varchar(1) COLLATE utf8_bin NOT NULL DEFAULT 'C',
  `LASTUSEDATE` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 資料表結構 `whouse`
--

CREATE TABLE `whouse` (
  `WHOUSENO` varchar(20) COLLATE utf8_bin NOT NULL,
  `WHOUSENM` varchar(50) COLLATE utf8_bin NOT NULL,
  `WHOUSEAMT` int(11) NOT NULL DEFAULT '0',
  `DESCRIPTION` varchar(200) COLLATE utf8_bin NOT NULL,
  `MEMO` varchar(200) COLLATE utf8_bin NOT NULL,
  `CREATETIME` datetime NOT NULL,
  `UPDATETIME` datetime NOT NULL,
  `ACTCODE` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- 資料表結構 `whouseitem`
--

CREATE TABLE `whouseitem` (
  `WHOUSENO` varchar(20) COLLATE utf8_bin NOT NULL,
  `ITEMNO` varchar(20) COLLATE utf8_bin NOT NULL,
  `AMT` int(11) NOT NULL DEFAULT '0',
  `LOGISTIC` tinyint(1) NOT NULL,
  `REQUEST` tinyint(1) NOT NULL,
  `MEMO` varchar(200) COLLATE utf8_bin NOT NULL,
  `CREATETIME` datetime NOT NULL,
  `UPDATETIME` datetime NOT NULL,
  `ACTCODE` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
