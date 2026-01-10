-- Crime Management System Database Schema

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `crime_management`
--
CREATE DATABASE IF NOT EXISTS `crime_management`;
USE `crime_management`;

-- --------------------------------------------------------

--
-- Drop existing tables to avoid conflicts (Order matters due to Foreign Keys)
--
DROP TABLE IF EXISTS `investigationnotes`;
DROP TABLE IF EXISTS `caseassignments`;
DROP TABLE IF EXISTS `evidencefiles`;
DROP TABLE IF EXISTS `vehiclereports`;
DROP TABLE IF EXISTS `crimereports`;
DROP TABLE IF EXISTS `policestations`;
DROP TABLE IF EXISTS `crimecategory`;
DROP TABLE IF EXISTS `users`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `Role` enum('USER','OFFICER','POLICE','ADMIN') DEFAULT 'USER',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`UserID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users` (Default Admin)
--
INSERT INTO `users` (`Name`, `Email`, `Phone`, `PasswordHash`, `Role`) VALUES
('Admin User', 'admin@cms.com', '0000000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN'); 
-- Password is 'password' (default hash)

-- --------------------------------------------------------

--
-- Table structure for table `crimecategory`
--

CREATE TABLE `crimecategory` (
  `CategoryID` int(11) NOT NULL AUTO_INCREMENT,
  `CategoryName` varchar(50) NOT NULL,
  PRIMARY KEY (`CategoryID`),
  UNIQUE KEY `CategoryName` (`CategoryName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `crimecategory`
--

INSERT INTO `crimecategory` (`CategoryName`) VALUES
('Assault'),
('Burglary'),
('Fraud'),
('Harassment'),
('Homicide'),
('Kidnapping'),
('Robbery'),
('Theft'),
('Traffic Accident'),
('Vandalism');

-- --------------------------------------------------------

--
-- Table structure for table `policestations`
--

CREATE TABLE `policestations` (
  `StationID` int(11) NOT NULL AUTO_INCREMENT,
  `StationName` varchar(100) NOT NULL,
  `Latitude` decimal(10,8) NOT NULL,
  `Longitude` decimal(11,8) NOT NULL,
  `Address` text DEFAULT NULL,
  PRIMARY KEY (`StationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `policestations`
--

INSERT INTO `policestations` (`StationName`, `Latitude`, `Longitude`, `Address`) VALUES
('Central Police HQ', 28.61390000, 77.20900000, 'Connaught Place, New Delhi');

-- --------------------------------------------------------

--
-- Table structure for table `crimereports`
--

CREATE TABLE `crimereports` (
  `ReportID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `StationID` int(11) DEFAULT NULL,
  `CategoryID` int(11) DEFAULT NULL,
  `Title` varchar(255) NOT NULL,
  `Description` text NOT NULL,
  `LocationText` varchar(255) DEFAULT NULL,
  `Latitude` decimal(10,8) DEFAULT NULL,
  `Longitude` decimal(11,8) DEFAULT NULL,
  `Status` enum('Pending','Assigned','Investigating','Closed','Resolved') DEFAULT 'Pending',
  `ReportDate` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ReportID`),
  KEY `UserID` (`UserID`),
  KEY `StationID` (`StationID`),
  KEY `CategoryID` (`CategoryID`),
  CONSTRAINT `crimereports_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE,
  CONSTRAINT `crimereports_ibfk_2` FOREIGN KEY (`StationID`) REFERENCES `policestations` (`StationID`) ON DELETE SET NULL,
  CONSTRAINT `crimereports_ibfk_3` FOREIGN KEY (`CategoryID`) REFERENCES `crimecategory` (`CategoryID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `vehiclereports`
--

CREATE TABLE `vehiclereports` (
  `VehicleReportID` int(11) NOT NULL AUTO_INCREMENT,
  `ReportID` int(11) NOT NULL,
  `VehicleNumber` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`VehicleReportID`),
  KEY `ReportID` (`ReportID`),
  CONSTRAINT `vehiclereports_ibfk_1` FOREIGN KEY (`ReportID`) REFERENCES `crimereports` (`ReportID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `evidencefiles`
--

CREATE TABLE `evidencefiles` (
  `EvidenceID` int(11) NOT NULL AUTO_INCREMENT,
  `ReportID` int(11) NOT NULL,
  `FileURL` varchar(255) NOT NULL,
  `FileType` enum('IMAGE','VIDEO') NOT NULL,
  `UploadedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`EvidenceID`),
  KEY `ReportID` (`ReportID`),
  CONSTRAINT `evidencefiles_ibfk_1` FOREIGN KEY (`ReportID`) REFERENCES `crimereports` (`ReportID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `caseassignments`
--

CREATE TABLE `caseassignments` (
  `AssignmentID` int(11) NOT NULL AUTO_INCREMENT,
  `ReportID` int(11) NOT NULL,
  `PoliceID` int(11) NOT NULL,
  `AssignedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`AssignmentID`),
  KEY `ReportID` (`ReportID`),
  KEY `PoliceID` (`PoliceID`),
  CONSTRAINT `caseassignments_ibfk_1` FOREIGN KEY (`ReportID`) REFERENCES `crimereports` (`ReportID`) ON DELETE CASCADE,
  CONSTRAINT `caseassignments_ibfk_2` FOREIGN KEY (`PoliceID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `investigationnotes`
--

CREATE TABLE `investigationnotes` (
  `NoteID` int(11) NOT NULL AUTO_INCREMENT,
  `ReportID` int(11) NOT NULL,
  `PoliceID` int(11) NOT NULL,
  `NoteText` text NOT NULL,
  `AddedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`NoteID`),
  KEY `ReportID` (`ReportID`),
  KEY `PoliceID` (`PoliceID`),
  CONSTRAINT `investigationnotes_ibfk_1` FOREIGN KEY (`ReportID`) REFERENCES `crimereports` (`ReportID`) ON DELETE CASCADE,
  CONSTRAINT `investigationnotes_ibfk_2` FOREIGN KEY (`PoliceID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;