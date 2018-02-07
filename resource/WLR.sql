-- phpMyAdmin SQL Dump
-- version 4.5.2
-- http://www.phpmyadmin.net
--
-- 主機: localhost
-- 產生時間： 2018 年 02 月 01 日 09:37
-- 伺服器版本: 10.1.13-MariaDB
-- PHP 版本： 5.6.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `WLR`
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
  `STATE` varchar(1) COLLATE utf8_bin NOT NULL DEFAULT 'A',
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
-- 資料表結構 `system`
--

CREATE TABLE `system` (
  `NEXT_LOGISTICNO` varchar(8) COLLATE utf8_bin NOT NULL,
  `NEXT_REQUESTNO` varchar(8) COLLATE utf8_bin NOT NULL,
  `NUM_USER` int(11) NOT NULL DEFAULT '0',
  `NUM_WHOUSE` int(11) NOT NULL DEFAULT '0',
  `NUM_ITEM` int(11) NOT NULL DEFAULT '0',
  `NUM_LOGISTIC` int(11) NOT NULL DEFAULT '0',
  `NUM_REQUEST` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- 資料表的匯出資料 `system`
--

INSERT INTO `system` (`NEXT_LOGISTICNO`, `NEXT_REQUESTNO`, `NUM_USER`, `NUM_WHOUSE`, `NUM_ITEM`, `NUM_LOGISTIC`, `NUM_REQUEST`) VALUES
('L1000001', 'R1000001', 1, 0, 0, 0, 0);

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
('tim841206', '900a5367f5e1', 'XSPkU3sAC5gO', '冷俊瑩', '0922825881', 'a0922825881@gmail.com', 'A', '2018-01-30 00:00:00', '2018-01-31 21:55:08', 1);

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

--
-- 已匯出資料表的索引
--

--
-- 資料表索引 `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`ITEMNO`);

--
-- 資料表索引 `logistic`
--
ALTER TABLE `logistic`
  ADD PRIMARY KEY (`LOGISTICNO`);

--
-- 資料表索引 `logisticitem`
--
ALTER TABLE `logisticitem`
  ADD PRIMARY KEY (`LOGISTICNO`,`ITEMNO`);

--
-- 資料表索引 `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`REQUESTNO`);

--
-- 資料表索引 `requestitem`
--
ALTER TABLE `requestitem`
  ADD PRIMARY KEY (`REQUESTNO`,`ITEMNO`);

--
-- 資料表索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`ACCOUNT`);

--
-- 資料表索引 `userwhouse`
--
ALTER TABLE `userwhouse`
  ADD PRIMARY KEY (`ACCOUNT`,`WHOUSENO`);

--
-- 資料表索引 `whouse`
--
ALTER TABLE `whouse`
  ADD PRIMARY KEY (`WHOUSENO`);

--
-- 資料表索引 `whouseitem`
--
ALTER TABLE `whouseitem`
  ADD PRIMARY KEY (`WHOUSENO`,`ITEMNO`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
