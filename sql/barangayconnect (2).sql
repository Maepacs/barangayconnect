-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 29, 2025 at 09:03 AM
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
-- Database: `barangayconnect`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `action` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `created_at`) VALUES
('LOG000001', 'UM000001', 'Created account for Mae Pacquiao (Admin)', '2025-10-02 04:25:47'),
('LOG000002', 'UI000002', 'Resident Ismael Saripada registered an account', '2025-10-08 04:01:13'),
('LOG000003', 'UI000002', 'Resident profile created for Ismael Saripada (ID: R000001)', '2025-10-08 04:24:07'),
('LOG000004', 'UI000002', 'Resident Ismael Saripada updated personal information (Changed: Address)', '2025-10-13 08:49:18'),
('LOG000005', 'UA000003', 'Created account for Armando Vito (Official)', '2025-10-13 11:34:03'),
('LOG000006', 'UI000002', 'Resident Ismael Saripada requested a document (ID: DOC000001)', '2025-10-13 12:38:20'),
('LOG000007', 'UI000002', 'Resident Ismael Saripada filed a complaint (ID: CMP000001, Tracking No: CMP-20251014-92F58A04)', '2025-10-13 23:16:42'),
('LOG000008', 'UM000001', 'Admin Mae Pacquiao added a new Barangay Official (BO000001)', '2025-10-26 17:52:47'),
('LOG000009', 'UJ000002', 'Resident Jerwell Amar registered an account', '2025-10-27 05:46:22'),
('LOG000010', 'UJ000002', 'Resident profile created for Jerwell Amar (ID: R000002)', '2025-10-27 05:49:25');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_officials`
--

CREATE TABLE `barangay_officials` (
  `official_id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `position` varchar(100) NOT NULL,
  `term_start` date NOT NULL,
  `term_end` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_officials`
--

INSERT INTO `barangay_officials` (`official_id`, `user_id`, `position`, `term_start`, `term_end`) VALUES
('BO000001', 'UA000003', 'Barangay Captain', '2025-01-01', '2028-01-01');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `complaint_title` varchar(100) NOT NULL,
  `complaint_type` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `image_file` varchar(100) NOT NULL,
  `date_filed` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('pending','in progress','resolved') NOT NULL,
  `tracking_number` varchar(100) NOT NULL,
  `handled_by` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `user_id`, `complaint_title`, `complaint_type`, `description`, `image_file`, `date_filed`, `status`, `tracking_number`, `handled_by`) VALUES
('CMP000001', 'UI000002', 'Noise', 'Noise', 'sndsnhhnsuhc', '1760419002_13.jpg', '2025-10-14 05:16:42', 'pending', 'CMP-20251014-92F58A04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `document_request`
--

CREATE TABLE `document_request` (
  `request_id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `document_type` varchar(100) NOT NULL,
  `purpose` varchar(100) NOT NULL,
  `supporting_file` varchar(100) NOT NULL,
  `status` enum('Approved','Rejected','Released','Pending') NOT NULL,
  `processed_by` varchar(20) DEFAULT NULL,
  `date_requested` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tracking_number` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_request`
--

INSERT INTO `document_request` (`request_id`, `user_id`, `document_type`, `purpose`, `supporting_file`, `status`, `processed_by`, `date_requested`, `tracking_number`) VALUES
('DOC000001', 'UI000002', 'Barangay Clearance', 'For Educational Purposes Only', '1760380700_14.jpg', 'Pending', NULL, '2025-10-13 18:38:20', 'DOC-20251013-9283-UI000002');

-- --------------------------------------------------------

--
-- Table structure for table `household`
--

CREATE TABLE `household` (
  `household_id` varchar(20) NOT NULL,
  `head_id` varchar(20) NOT NULL,
  `household_type` enum('owned','rented','government-provided','shared') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `household_members`
--

CREATE TABLE `household_members` (
  `house_member_id` varchar(20) NOT NULL,
  `household_id` varchar(20) NOT NULL,
  `resident_id` varchar(20) NOT NULL,
  `relation_to_head` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `otp_type` enum('SMS','Email') NOT NULL,
  `expiry_time` datetime NOT NULL,
  `is_used` tinyint(1) NOT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `residents_profile`
--

CREATE TABLE `residents_profile` (
  `resident_id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `sex` enum('Male','Female') DEFAULT NULL,
  `birthdate` date NOT NULL,
  `age` int(11) GENERATED ALWAYS AS (timestampdiff(YEAR,`birthdate`,curdate())) VIRTUAL,
  `civil_status` enum('Single','Married','Widowed','Separated') DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `livelihood_status` varchar(50) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `educational_attainment` varchar(100) DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `medical_conditions` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email_address` varchar(100) DEFAULT NULL,
  `voter_status` enum('Registered','Not Registered') DEFAULT NULL,
  `pwd_status` tinyint(1) DEFAULT 0,
  `senior_citizen_status` tinyint(1) DEFAULT 0,
  `solo_parent_status` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `residents_profile`
--

INSERT INTO `residents_profile` (`resident_id`, `user_id`, `first_name`, `middle_name`, `last_name`, `suffix`, `sex`, `birthdate`, `civil_status`, `nationality`, `religion`, `address`, `livelihood_status`, `occupation`, `educational_attainment`, `blood_type`, `medical_conditions`, `allergies`, `contact_number`, `email_address`, `voter_status`, `pwd_status`, `senior_citizen_status`, `solo_parent_status`, `created_at`, `updated_at`) VALUES
('R000001', 'UI000002', 'Ismael', 'Saludes', 'Saripada', '', 'Male', '2003-06-06', 'Single', 'Filipino', 'Roman Catholic', 'Barangay Eroreco, Bacolod City', 'Employed', 'Full-Stack Developer', 'College Graduate', 'O-', '', '', '', '', 'Not Registered', 0, 0, 0, '2025-10-08 10:24:07', '2025-10-13 14:49:18'),
('R000002', 'UJ000002', 'Jerwell', 'Martinez', 'Amar', '', 'Male', '2003-11-25', 'Single', 'Filipino', 'Roman Catholic', 'Himamaylan City, Negros Occidental', 'Employed', 'Network Engineer', 'College Graduate', 'AB+', '', '', '', '', 'Registered', 0, 0, 0, '2025-10-27 12:49:25', '2025-10-27 12:49:25');

-- --------------------------------------------------------

--
-- Table structure for table `sms_logs`
--

CREATE TABLE `sms_logs` (
  `sms_id` varchar(20) NOT NULL,
  `user_id` varchar(20) NOT NULL,
  `resident_id` varchar(20) NOT NULL,
  `direction` enum('incoming','outgoing') NOT NULL,
  `sender_number` varchar(20) NOT NULL,
  `recipient_number` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `status` enum('Sent','Failed','Pending') DEFAULT 'Pending',
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(20) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(100) NOT NULL,
  `role` enum('Admin','Official','Resident') NOT NULL,
  `status` enum('active','inactive') NOT NULL,
  `date_registered` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `username`, `password_hash`, `role`, `status`, `date_registered`) VALUES
('UA000003', 'Armando Vito', 'Cap_armando', '$2y$10$6JUBUOH5vrf0RYc/Ghc5jeiF4g9.wzHH.i59vKmS5rUI1kwYgpcqO', 'Official', 'active', '2025-10-13 11:34:03'),
('UI000002', 'Ismael Saripada', 'Smile', '$2y$10$HKC3nNluTRz.W7iEWWrl9egkOlVnONau5/8NMtC1reMnPWO7aG7LK', 'Resident', 'active', '2025-10-08 04:01:13'),
('UJ000002', 'Jerwell Amar', 'amarzkie', '$2y$10$bygYNCl.hpaNnqAtMwNhJOLTR5gfBuMxYkMaF0H1oByxTBS2Qzx0a', 'Resident', 'active', '2025-10-27 05:46:22'),
('UM000001', 'Mae Pacquiao', 'meipacs', '$2y$10$VAhsxDQeRHjlluY2w9Uktu2BkOFEqPtt1FpSPahK5E8qUS61fsaSK', 'Admin', 'active', '2025-10-02 04:25:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`) USING BTREE;

--
-- Indexes for table `barangay_officials`
--
ALTER TABLE `barangay_officials`
  ADD PRIMARY KEY (`official_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `complaints_ibfk_1` (`user_id`),
  ADD KEY `handled_by` (`handled_by`);

--
-- Indexes for table `document_request`
--
ALTER TABLE `document_request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `processed_by` (`processed_by`) USING BTREE,
  ADD KEY `user_id` (`user_id`) USING BTREE;

--
-- Indexes for table `household`
--
ALTER TABLE `household`
  ADD PRIMARY KEY (`household_id`),
  ADD UNIQUE KEY `resident_id` (`head_id`);

--
-- Indexes for table `household_members`
--
ALTER TABLE `household_members`
  ADD PRIMARY KEY (`house_member_id`),
  ADD KEY `house_member_ibfk_1` (`household_id`),
  ADD KEY `house_member_ibfk_2` (`resident_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `residents_profile`
--
ALTER TABLE `residents_profile`
  ADD PRIMARY KEY (`resident_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`sms_id`),
  ADD UNIQUE KEY `resident_id` (`resident_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `barangay_officials`
--
ALTER TABLE `barangay_officials`
  ADD CONSTRAINT `barangay_officials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`handled_by`) REFERENCES `barangay_officials` (`official_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `document_request`
--
ALTER TABLE `document_request`
  ADD CONSTRAINT `doc_req_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `doc_req_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `barangay_officials` (`official_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `household`
--
ALTER TABLE `household`
  ADD CONSTRAINT `household_ibfk_1` FOREIGN KEY (`head_id`) REFERENCES `residents_profile` (`resident_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `household_members`
--
ALTER TABLE `household_members`
  ADD CONSTRAINT `house_member_ibfk_1` FOREIGN KEY (`household_id`) REFERENCES `household` (`household_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `house_member_ibfk_2` FOREIGN KEY (`resident_id`) REFERENCES `residents_profile` (`resident_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `pass_res_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `residents_profile`
--
ALTER TABLE `residents_profile`
  ADD CONSTRAINT `res_prof_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD CONSTRAINT `sms_logs_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `residents_profile` (`resident_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sms_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
