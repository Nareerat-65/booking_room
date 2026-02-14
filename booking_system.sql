-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 14, 2026 at 08:47 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `booking_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `full_name`) VALUES
(1, 'admin', '$2y$12$YamTIz4YXM.bmTlcAfTtrOWEuOpARBFTq8mUN2a.khFqoQxRJvke6', 'ผู้ดูแลระบบ');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `line_id` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `position` enum('student','intern','resident','staff','other') NOT NULL,
  `student_year` tinyint(4) DEFAULT NULL,
  `position_other` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `purpose` enum('study','elective') DEFAULT NULL,
  `study_course` varchar(255) DEFAULT NULL,
  `study_dept` varchar(255) DEFAULT NULL,
  `elective_dept` varchar(255) DEFAULT NULL,
  `check_in_date` date DEFAULT NULL,
  `check_out_date` date DEFAULT NULL,
  `woman_count` int(11) DEFAULT 0,
  `man_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pending',
  `reject_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `full_name`, `phone`, `line_id`, `email`, `position`, `student_year`, `position_other`, `department`, `purpose`, `study_course`, `study_dept`, `elective_dept`, `check_in_date`, `check_out_date`, `woman_count`, `man_count`, `created_at`, `status`, `reject_reason`) VALUES
(52, 'ธันยวีร์ เทพสี', '0856322142', 'test1', 'khawktk@gmail.com', 'other', NULL, 'อาจารย์แพทย์', 'โรงพยาบาลจุฬาลงกรณ์', 'elective', '', '', 'ศัลยศาสตร์', '2025-12-15', '2025-12-19', 2, 2, '2025-12-01 13:25:53', 'rejected', 'ห้องไม่เพียงพอ'),
(64, 'นรีรัตน์ ศรีแก้วอินทร์', '0992539286', 'test1', 'khawktk@gmail.com', 'student', 4, NULL, 'โรงพยาบาลรามาธิบดี', 'elective', '', '', 'จิตเวชศาสตร์', '2025-12-23', '2025-12-26', 4, 0, '2025-12-09 14:34:59', 'approved', NULL),
(65, 'Nareerat', '0992539286', 'g1241', 'khawktk@gmail.com', 'intern', NULL, '', 'คณะแพทยศาสตร์', 'elective', '', '', 'กุมารเวชศาสตร์', '2025-12-29', '2025-12-31', 1, 0, '2025-12-11 16:04:03', 'rejected', '1234'),
(74, 'Nareerat', '0992539286', 'g1241', 'khawktk@gmail.com', 'intern', NULL, NULL, 'คณะแพทยศาสตร์', 'elective', '', '', 'เวชศาสตร์ฟื้นฟู', '2026-01-07', '2026-01-09', 2, 1, '2025-12-12 11:23:42', 'approved', NULL),
(78, 'วราภร ไตรมี', '0981235673', 'test1', 'khawktk@gmail.com', 'resident', NULL, NULL, 'โรงพยาบาลจุฬาลงกรณ์', 'elective', '', '', 'เวชศาสตร์ชุมชน', '2026-01-07', '2026-01-09', 2, 2, '2025-12-17 14:24:35', 'approved', NULL),
(79, 'อัชรี วันธา', '0981235673', 'test1', 'khawktk@gmail.com', 'student', 2, NULL, 'คณะแพทยศาสตร์', 'elective', '', '', 'เวชศาสตร์ฟื้นฟู', '2026-01-05', '2026-01-09', 1, 0, '2025-12-19 15:49:56', 'rejected', 'ห้องไม่พอ'),
(80, 'เจตนิพัทธ์ เรืองทอง', '0826781983', 'chayanon12', 'khawktk@gmail.com', 'other', NULL, '1234', 'โรงพยาบาลรามาธิบดี', 'elective', '', '', 'ออร์โธปิดิกส์', '2026-01-07', '2026-01-09', 2, 0, '2025-12-19 15:50:58', 'approved', NULL),
(82, 'นรริน กกก', '0866142131', 'test1', 'khawktk@gmail.com', 'staff', 0, NULL, 'ศูนย์แพทยศาสตรศึกษาชั้นคลินิก โรงพยาบาลขอนแก่น', 'study', 'แนวทางการปฏิบัติต่างๆ ภาคกุมารเวชศาสตร์', 'กุมารเวชศาสตร์', '', '2026-01-19', '2026-01-23', 2, 1, '2026-01-05 08:49:51', 'rejected', 'ห้องไม่พอ'),
(83, 'อัมริน สีสังวาฬ', '0992539286', 'test1', 'khawktk@gmail.com', 'resident', NULL, NULL, 'ศูนย์แพทยศาสตรศึกษาชั้นคลินิก โรงพยาบาลสระบุรี', 'elective', '', '', 'พยาธิวิทยา', '2026-01-19', '2026-01-23', 3, 0, '2026-01-05 09:01:52', 'approved', NULL),
(84, 'วราภร ไตรมี', '0981235673', 'test1', 'khawktk@gmail.com', 'resident', NULL, NULL, 'โรงพยาบาลจุฬาลงกรณ์', 'elective', '', '', 'เวชศาสตร์ครอบครัว', '2026-01-26', '2026-01-29', 2, 1, '2026-01-05 10:22:38', 'approved', NULL),
(85, 'อัชรี วันธา', '0981235673', 'test1', 'khawktk@gmail.com', 'intern', 0, NULL, 'โรงพยาบาลจุฬาลงกรณ์', 'elective', '', '', 'ศัลยศาสตร์', '2026-01-26', '2026-01-27', 0, 2, '2026-01-05 11:05:08', 'approved', NULL),
(86, 'เจตนิพัทธ์ เรืองทอง', '0981235673', 'test1', 'khawktk@gmail.com', 'intern', 0, NULL, 'โรงพยาบาลรามาธิบดี', 'elective', '', '', 'ออร์โธปิดิกส์', '2026-01-26', '2026-01-28', 1, 0, '2026-01-05 11:08:43', 'rejected', 'ห้องไม่พอ'),
(87, 'ณัฐวุฒิ เทพคำ', '0981235673', 'test1', 'khawktk@gmail.com', 'intern', 0, NULL, 'ศูนย์แพทยศาสตรศึกษาชั้นคลินิก โรงพยาบาลขอนแก่น', 'study', 'แนวทางการปฏิบัติต่างๆ ภาคกุมารเวชศาสตร์', 'เวชศาสตร์ฟื้นฟู', '', '2026-01-26', '2026-01-30', 1, 0, '2026-01-05 11:16:55', 'pending', NULL),
(88, 'อัชรี วันธา', '0992539286', 'test1', 'khawktk@gmail.com', 'intern', 0, NULL, 'คณะแพทยศาสตร์', 'elective', '', '', 'โสต ศอ นาสิกวิทยา', '2026-01-26', '2026-01-29', 1, 0, '2026-01-05 11:20:41', 'rejected', '-'),
(89, 'ปราวิช ดี', '0981235673', 'test1', 'khawktk@gmail.com', 'student', 3, NULL, 'โรงพยาบาลจุฬาลงกรณ์', 'elective', '', '', 'วิสัญญีวิทยา', '2026-01-26', '2026-01-30', 2, 1, '2026-01-09 13:38:59', 'approved', NULL),
(90, 'ณัฐวุฒิ เทพคำ', '0981235673', 'Bussaba', 'khawktk@gmail.com', 'student', 4, NULL, 'ศูนย์แพทยศาสตรศึกษาชั้นคลินิก โรงพยาบาลสระบุรี', 'elective', '', '', 'ออร์โธปิดิกส์', '2026-02-09', '2026-02-13', 2, 0, '2026-01-26 15:54:22', 'approved', NULL),
(91, 'sadadasd', '5656156656', 'asdassdasdasdsad', 'khawktk@gmail.com', 'intern', 0, NULL, 'โรงพยาบาลรามาธิบดี', 'elective', '', '', 'โสต ศอ นาสิกวิทยา', '2026-02-19', '2026-02-23', 2, 0, '2026-02-05 16:08:28', 'approved', NULL),
(92, 'วราภร ไตรมี', '0981235673', 'test1', 'khawktk@gmail.com', 'student', 4, NULL, 'โรงพยาบาลรามาธิบดี', 'study', 'แนวทางการปฏิบัติต่างๆ ภาคกุมารเวชศาสตร์', 'กุมารเวชศาสตร์', '', '2026-02-20', '2026-02-27', 2, 1, '2026-02-06 10:10:01', 'approved', NULL),
(93, 'นรริน กกก', '0000000000', 'test1', 'khawktk@gmail.com', 'student', 3, NULL, 'คณะแพทยศาสตร์', 'elective', '', '', 'เวชศาสตร์ฟื้นฟู', '2026-02-24', '2026-02-27', 2, 0, '2026-02-10 11:34:33', 'rejected', 'ห้องไม่พอ'),
(94, 'กกกกก ขขขขขข', '0000000000', 'test1', 'khawktk@gmail.com', 'intern', 0, NULL, 'โรงพยาบาลจุฬาลงกรณ์', 'elective', '', '', 'เวชศาสตร์ฟื้นฟู', '2026-03-02', '2026-03-06', 1, 1, '2026-02-10 11:44:40', 'pending', NULL),
(95, 'คคคค งงงงง', '0000000000', 'ทดสอบ1', 'khawktk@gmail.com', 'resident', 0, NULL, 'ศูนย์แพทยศาสตรศึกษาชั้นคลินิก โรงพยาบาลสระบุรี', 'elective', '', '', 'เวชศาสตร์ฟื้นฟู', '2026-03-01', '2026-03-06', 1, 1, '2026-02-10 11:48:15', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `booking_documents`
--

CREATE TABLE `booking_documents` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `uploaded_by` enum('user','admin') NOT NULL,
  `uploader_id` int(11) NOT NULL,
  `doc_type` varchar(50) DEFAULT NULL,
  `original_name` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(10) UNSIGNED NOT NULL,
  `is_visible_to_user` tinyint(1) NOT NULL DEFAULT 1,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_documents`
--

INSERT INTO `booking_documents` (`id`, `booking_id`, `uploaded_by`, `uploader_id`, `doc_type`, `original_name`, `stored_name`, `file_path`, `mime_type`, `file_size`, `is_visible_to_user`, `uploaded_at`) VALUES
(1, 56, 'admin', 1, 'admin_reply', 'nu-coop-05.pdf', 'booking_56_1764661061.pdf', 'uploads/documents/booking_56_1764661061.pdf', 'application/pdf', 137981, 1, '2025-12-02 14:37:41'),
(2, 57, 'user', 0, 'user_attachment', 'nu-coop-04.pdf', 'booking_57_user_1764664057_31e85f.pdf', 'uploads/documents/booking_57_user_1764664057_31e85f.pdf', 'application/pdf', 180133, 1, '2025-12-02 15:27:37'),
(3, 57, 'user', 0, 'user_attachment', 'nu-coop-05.pdf', 'booking_57_user_1764664057_1cb464.pdf', 'uploads/documents/booking_57_user_1764664057_1cb464.pdf', 'application/pdf', 137981, 1, '2025-12-02 15:27:37'),
(4, 57, 'user', 0, 'user_attachment', 'sc_coop_01.pdf', 'booking_57_user_1764664696_d4e209.pdf', 'uploads/documents/booking_57_user_1764664696_d4e209.pdf', 'application/pdf', 546610, 1, '2025-12-02 15:38:16'),
(5, 57, 'user', 0, 'user_attachment', 'sc-coop-13.pdf', 'booking_57_user_1764664696_054b29.pdf', 'uploads/documents/booking_57_user_1764664696_054b29.pdf', 'application/pdf', 248046, 1, '2025-12-02 15:38:16'),
(6, 57, 'user', 0, 'user_attachment', 'รูปนิสิต.jpg', 'booking_57_user_1764664715_8adf2d.jpg', 'uploads/documents/booking_57_user_1764664715_8adf2d.jpg', 'image/jpeg', 393418, 1, '2025-12-02 15:38:35'),
(12, 61, 'user', 0, 'user_attachment', 'เอกสารขอความอนุเคราะห์.pdf', 'booking_61_user_1765182855_843042.pdf', 'uploads/documents/booking_61_user_1765182855_843042.pdf', 'application/pdf', 8030, 1, '2025-12-08 15:34:15'),
(13, 61, 'admin', 1, 'หนังสืออนุมัติ', 'เอกสารอนุมัติ.pdf', 'doc_69368dc3686c0.pdf', 'uploads/documents/doc_69368dc3686c0.pdf', 'application/pdf', 7451, 1, '2025-12-08 15:35:15'),
(14, 62, 'user', 0, 'user_attachment', 'เอกสารขอความอนุเคราะห์.pdf', 'booking_62_user_1765254408_1a7a78.pdf', 'uploads/documents/booking_62_user_1765254408_1a7a78.pdf', 'application/pdf', 8030, 1, '2025-12-09 11:26:48'),
(16, 63, 'user', 0, 'user_attachment', 'เอกสารขอความอนุเคราะห์.pdf', 'booking_63_user_1765262623_c957e2.pdf', 'uploads/documents/booking_63_user_1765262623_c957e2.pdf', 'application/pdf', 8030, 1, '2025-12-09 13:43:43'),
(17, 63, 'admin', 1, 'หนังสืออนุมัติ', 'เอกสารอนุมัติ.pdf', 'doc_6937c54eae9db.pdf', 'uploads/documents/doc_6937c54eae9db.pdf', 'application/pdf', 7451, 1, '2025-12-09 13:44:30'),
(21, 64, 'user', 0, 'user_attachment', 'เอกสารขอความอนุเคราะห์.pdf', 'booking_64_user_1765265935_0f8bea.pdf', 'uploads/documents/booking_64_user_1765265935_0f8bea.pdf', 'application/pdf', 8030, 1, '2025-12-09 14:38:55'),
(22, 64, 'admin', 1, 'หนังสืออนุมัติ', 'เอกสารแจ้งค่าใช้จ่าย.pdf', 'doc_6937d2437effc.pdf', 'uploads/documents/doc_6937d2437effc.pdf', 'application/pdf', 8010, 1, '2025-12-09 14:39:47'),
(24, 64, 'user', 0, 'user_attachment', 'เอกสารขอความอนุเคราะห์.pdf', 'booking_64_user_1765437487_88c6b4.pdf', 'uploads/documents/booking_64_user_1765437487_88c6b4.pdf', 'application/pdf', 8030, 1, '2025-12-11 14:18:07'),
(32, 78, 'user', 0, 'user_attachment', 'เอกสารขอความอนุเคราะห์.pdf', 'booking_78_user_1765958801_c494ce.pdf', 'uploads/documents/booking_78_user_1765958801_c494ce.pdf', 'application/pdf', 8030, 1, '2025-12-17 15:06:41'),
(33, 74, 'admin', 1, 'ใบแจ้งค่าใช้จ่าย', 'รายงานประจำเดือนธันวาคม2025.pdf', 'doc_694266129d709.pdf', 'uploads/documents/doc_694266129d709.pdf', 'application/pdf', 144761, 1, '2025-12-17 15:13:06'),
(38, 83, 'user', 0, 'user_attachment', 'เอกสารขอความอนุเคราะห์.pdf', 'booking_83_user_1767578781_8a1817.pdf', 'uploads/documents/booking_83_user_1767578781_8a1817.pdf', 'application/pdf', 8030, 1, '2026-01-05 09:06:21'),
(39, 83, 'admin', 1, 'หนังสืออนุมัติ', 'เอกสารอนุมัติ.pdf', 'doc_695b1ca900e8c.pdf', 'uploads/documents/doc_695b1ca900e8c.pdf', 'application/pdf', 7451, 1, '2026-01-05 09:06:33'),
(40, 83, 'admin', 1, 'หนังสืออนุมัติ', 'เอกสารอนุมัติ.pdf', 'doc_695b1cd569998.pdf', 'uploads/documents/doc_695b1cd569998.pdf', 'application/pdf', 7451, 0, '2026-01-05 09:07:17'),
(41, 84, 'admin', 1, 'หนังสืออนุมัติ', 'เอกสารอนุมัติ.pdf', 'doc_695b3772551cf.pdf', 'uploads/documents/doc_695b3772551cf.pdf', 'application/pdf', 7451, 1, '2026-01-05 11:00:50'),
(42, 89, 'user', 0, 'user_attachment', 'เอกสารขอความอนุเคราะห์.pdf', 'booking_89_user_1767940889_7e48df.pdf', 'uploads/documents/booking_89_user_1767940889_7e48df.pdf', 'application/pdf', 8030, 1, '2026-01-09 13:41:29'),
(43, 89, 'admin', 1, 'หนังสืออนุมัติ', 'เอกสารอนุมัติ.pdf', 'doc_6960a36f874cc.pdf', 'uploads/documents/doc_6960a36f874cc.pdf', 'application/pdf', 7451, 1, '2026-01-09 13:42:55'),
(46, 92, 'user', 0, 'user_attachment', 'เอกสารขอความอนุเคราะห์.pdf', 'booking_92_user_1770347540_868dd0.pdf', 'uploads/documents/booking_92_user_1770347540_868dd0.pdf', 'application/pdf', 8030, 1, '2026-02-06 10:12:20'),
(48, 92, 'admin', 1, 'ใบแจ้งค่าใช้จ่าย', 'เอกสารแจ้งค่าใช้จ่าย.pdf', 'doc_69855c5f63977.pdf', 'uploads/documents/doc_69855c5f63977.pdf', 'application/pdf', 8010, 1, '2026-02-06 10:13:35'),
(49, 92, 'admin', 1, 'หนังสืออนุมัติ', 'เอกสารอนุมัติ.pdf', 'doc_69855caf658ba.pdf', 'uploads/documents/doc_69855caf658ba.pdf', 'application/pdf', 7451, 1, '2026-02-06 10:14:55');

-- --------------------------------------------------------

--
-- Table structure for table `booking_guest_requests`
--

CREATE TABLE `booking_guest_requests` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `gender` enum('F','M') DEFAULT NULL,
  `guest_phone` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_guest_requests`
--

INSERT INTO `booking_guest_requests` (`id`, `booking_id`, `guest_name`, `gender`, `guest_phone`, `created_at`) VALUES
(1, 65, '1234', 'F', '0855541412', '2025-12-11 16:04:03'),
(30, 79, 'กชนิภา ดอกบัว', 'F', '0855541412', '2025-12-19 15:49:56'),
(66, 80, '1234', 'F', '0855541412', '2025-12-24 10:53:30'),
(67, 80, '1234', 'F', '0855541412', '2025-12-24 10:53:30'),
(70, 78, 'กชนิภา ดอกบัวบาน', 'F', '0855541412', '2025-12-24 10:54:02'),
(71, 78, 'วราภร ไตรมี', 'F', '', '2025-12-24 10:54:02'),
(72, 74, 'ากหวาอวรทฆศซ?ศฮ๋ฏ', 'F', '0855541412', '2025-12-24 10:54:24'),
(73, 74, 'ญฤฯษโซศซฆสาวกฟดสาวงหวงดสกา', 'F', '0855541412', '2025-12-24 10:54:24'),
(74, 82, 'กชนิภา ดอกบัว', 'F', '0855541412', '2026-01-05 08:49:51'),
(75, 82, 'กชนิภา ดอกบัวบาน', 'F', '', '2026-01-05 08:49:51'),
(76, 82, 'ปราวิช ดี', 'M', '0855541412', '2026-01-05 08:49:51'),
(81, 83, 'กรกฏ พลอยใส', 'F', '0855541412', '2026-01-05 09:04:34'),
(82, 83, '1234', 'F', '', '2026-01-05 09:04:34'),
(83, 83, 'กชนิภา ดอกบัว', 'F', '', '2026-01-05 09:04:34'),
(92, 84, 'กชนิภา ดอกบัว', 'F', '0855541412', '2026-01-05 11:00:08'),
(93, 84, 'กรกฏ พลอยใส', 'F', '', '2026-01-05 11:00:08'),
(94, 84, 'ปราวิช ดี', 'M', '0855541412', '2026-01-05 11:00:08'),
(95, 85, 'ปราวิช ดี', 'M', '0855541412', '2026-01-05 11:05:08'),
(96, 85, 'ปราวิช ดี', 'M', '', '2026-01-05 11:05:08'),
(97, 86, '1234', 'F', '0855541412', '2026-01-05 11:08:43'),
(98, 87, 'กชนิภา ดอกบัว', 'F', '0855541412', '2026-01-05 11:16:55'),
(99, 88, 'กรกฏ พลอยใส', 'F', '0855541412', '2026-01-05 11:20:41'),
(102, 89, 'กรกฏ พลอยใส', 'F', '0855541412', '2026-01-09 13:45:13'),
(103, 89, 'กชนิภา ดอกบัวบาน', 'F', '', '2026-01-09 13:45:13'),
(104, 89, 'ปราวิช ดี', 'M', '0855541412', '2026-01-09 13:45:13'),
(107, 90, 'กรกฏ พลอยใส', 'F', '0855541412', '2026-02-05 15:51:24'),
(108, 90, 'กชนิภา ดอกบัว', 'F', '', '2026-02-05 15:51:24'),
(109, 91, 'กชนิภา ดอกบัว', 'F', '0855541412', '2026-02-05 16:08:28'),
(110, 91, 'กรกฏ พลอยใส', 'F', '', '2026-02-05 16:08:28'),
(111, 92, 'กชนิภา ดอกบัว', 'F', '0855541412', '2026-02-06 10:10:01'),
(112, 92, 'กรกฏ พลอยใส', 'F', '', '2026-02-06 10:10:01'),
(113, 92, 'ปราวิช ดี', 'M', '1111111111', '2026-02-06 10:10:01'),
(114, 93, 'กรกฏ พลอยใส', 'F', '0855541412', '2026-02-10 11:34:33'),
(115, 93, 'กชนิภา ดอกบัว', 'F', '', '2026-02-10 11:34:33'),
(116, 94, 'กชนิภา ดอกบัวบาน', 'F', '1111111111', '2026-02-10 11:44:40'),
(117, 94, 'ปราวิช ดี', 'M', '2222222222', '2026-02-10 11:44:40'),
(118, 95, 'กกกกก กกกกก', 'F', '1111111111', '2026-02-10 11:48:15'),
(119, 95, 'ขขขขข ขขขขข', 'M', '2222222222', '2026-02-10 11:48:15');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_name` varchar(50) NOT NULL,
  `location` varchar(255) NOT NULL,
  `capacity` tinyint(4) NOT NULL DEFAULT 4,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_name`, `location`, `capacity`, `is_active`, `created_at`) VALUES
(1, '001', 'อาคาร1', 2, 1, '2026-01-30 09:47:16'),
(2, '002', 'อาคาร1', 4, 1, '2026-01-30 09:47:16'),
(3, '003', 'อาคาร1', 4, 1, '2026-01-30 09:47:16'),
(4, '004', 'อาคาร1', 4, 1, '2026-01-30 09:47:16'),
(5, '005', 'อาคาร1', 4, 1, '2026-01-30 09:47:16'),
(6, '006', 'อาคาร1', 4, 1, '2026-01-30 09:47:16');

-- --------------------------------------------------------

--
-- Table structure for table `room_allocations`
--

CREATE TABLE `room_allocations` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `woman_count` int(11) NOT NULL DEFAULT 0,
  `man_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_allocations`
--

INSERT INTO `room_allocations` (`id`, `booking_id`, `room_id`, `start_date`, `end_date`, `woman_count`, `man_count`) VALUES
(142, 64, 1, '2025-12-23', '2025-12-26', 4, 0),
(160, 80, 2, '2026-01-07', '2026-01-09', 2, 0),
(161, 78, 3, '2026-01-07', '2026-01-09', 2, 0),
(162, 78, 4, '2026-01-07', '2026-01-09', 0, 2),
(163, 74, 5, '2026-01-07', '2026-01-09', 2, 0),
(164, 74, 6, '2026-01-07', '2026-01-09', 0, 1),
(166, 83, 1, '2026-01-19', '2026-01-23', 3, 0),
(168, 84, 2, '2026-01-26', '2026-01-29', 2, 0),
(169, 84, 3, '2026-01-26', '2026-01-29', 0, 1),
(171, 89, 4, '2026-01-26', '2026-01-30', 2, 0),
(172, 89, 5, '2026-01-26', '2026-01-30', 0, 1),
(174, 90, 1, '2026-02-09', '2026-02-13', 2, 0),
(175, 91, 1, '2026-02-19', '2026-02-23', 2, 0),
(176, 92, 2, '2026-02-20', '2026-02-27', 2, 0),
(177, 92, 3, '2026-02-20', '2026-02-27', 0, 1),
(178, 85, 6, '2026-01-26', '2026-01-27', 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table `room_guests`
--

CREATE TABLE `room_guests` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `allocation_id` int(11) NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_phone` varchar(20) DEFAULT NULL,
  `gender` enum('F','M') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_guests`
--

INSERT INTO `room_guests` (`id`, `booking_id`, `allocation_id`, `guest_name`, `guest_phone`, `gender`, `created_at`) VALUES
(188, 80, 160, '1234', '0855541412', 'F', '2025-12-24 03:53:32'),
(189, 80, 160, '1234', '0855541412', 'F', '2025-12-24 03:53:32'),
(190, 78, 161, 'กชนิภา ดอกบัวบาน', '0855541412', 'F', '2025-12-24 03:54:04'),
(191, 78, 161, 'วราภร ไตรมี', '', 'F', '2025-12-24 03:54:04'),
(192, 74, 163, 'ากหวาอวรทฆศซ?ศฮ๋ฏ', '0855541412', 'F', '2025-12-24 03:54:26'),
(193, 74, 163, 'ญฤฯษโซศซฆสาวกฟดสาวงหวงดสกา', '0855541412', 'F', '2025-12-24 03:54:26'),
(196, 83, 166, 'กรกฏ พลอยใส', '0855541412', 'F', '2026-01-05 02:04:37'),
(197, 83, 166, '1234', '', 'F', '2026-01-05 02:04:37'),
(198, 83, 166, 'กชนิภา ดอกบัว', '', 'F', '2026-01-05 02:04:37'),
(201, 84, 168, 'กชนิภา ดอกบัว', '0855541412', 'F', '2026-01-05 04:00:10'),
(202, 84, 168, 'กรกฏ พลอยใส', '', 'F', '2026-01-05 04:00:10'),
(203, 84, 169, 'ปราวิช ดี', '0855541412', 'M', '2026-01-05 04:00:10'),
(206, 89, 171, 'กรกฏ พลอยใส', '0855541412', 'F', '2026-01-09 06:45:16'),
(207, 89, 171, 'กชนิภา ดอกบัวบาน', '', 'F', '2026-01-09 06:45:16'),
(208, 89, 172, 'ปราวิช ดี', '0855541412', 'M', '2026-01-09 06:45:16'),
(211, 90, 174, 'กรกฏ พลอยใส', '0855541412', 'F', '2026-02-05 08:51:28'),
(212, 90, 174, 'กชนิภา ดอกบัว', '', 'F', '2026-02-05 08:51:28'),
(213, 91, 175, 'กชนิภา ดอกบัว', '0855541412', 'F', '2026-02-05 09:11:05'),
(214, 91, 175, 'กรกฏ พลอยใส', '', 'F', '2026-02-05 09:11:05'),
(215, 92, 176, 'กชนิภา ดอกบัว', '0855541412', 'F', '2026-02-06 03:11:14'),
(216, 92, 176, 'กรกฏ พลอยใส', '', 'F', '2026-02-06 03:11:14'),
(217, 92, 177, 'ปราวิช ดี', '1111111111', 'M', '2026-02-06 03:11:14'),
(218, 85, 178, 'ปราวิช ดี', '0855541412', 'M', '2026-02-10 04:37:05'),
(219, 85, 178, 'ปราวิช ดี', '', 'M', '2026-02-10 04:37:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_documents`
--
ALTER TABLE `booking_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_guest_requests`
--
ALTER TABLE `booking_guest_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_allocations`
--
ALTER TABLE `room_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_alloc_booking` (`booking_id`),
  ADD KEY `fk_alloc_room` (`room_id`);

--
-- Indexes for table `room_guests`
--
ALTER TABLE `room_guests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `allocation_id` (`allocation_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `booking_documents`
--
ALTER TABLE `booking_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `booking_guest_requests`
--
ALTER TABLE `booking_guest_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `room_allocations`
--
ALTER TABLE `room_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=179;

--
-- AUTO_INCREMENT for table `room_guests`
--
ALTER TABLE `room_guests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking_guest_requests`
--
ALTER TABLE `booking_guest_requests`
  ADD CONSTRAINT `booking_guest_requests_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_allocations`
--
ALTER TABLE `room_allocations`
  ADD CONSTRAINT `fk_alloc_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_alloc_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);

--
-- Constraints for table `room_guests`
--
ALTER TABLE `room_guests`
  ADD CONSTRAINT `room_guests_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_guests_ibfk_2` FOREIGN KEY (`allocation_id`) REFERENCES `room_allocations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
