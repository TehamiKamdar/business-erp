-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2026 at 01:07 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.3.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `business_erp`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounting_periods`
--

CREATE TABLE `accounting_periods` (
  `id` char(36) NOT NULL,
  `fiscal_year_id` char(36) NOT NULL,
  `period_number` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` char(36) NOT NULL,
  `company_id` char(36) DEFAULT NULL,
  `branch_id` char(36) DEFAULT NULL,
  `actor_type` varchar(255) DEFAULT NULL,
  `actor_id` char(36) DEFAULT NULL,
  `auditable_type` varchar(255) DEFAULT NULL,
  `auditable_id` char(36) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `company_id`, `branch_id`, `actor_type`, `actor_id`, `auditable_type`, `auditable_id`, `action`, `old_values`, `new_values`, `description`, `ip_address`, `user_agent`, `metadata`, `created_at`) VALUES
('01a1abb9-eb7d-7280-8727-6e6824ba5f4a', '01a1a009-5ccb-7270-814b-95288da5b942', '01a1a009-5ce4-7307-b010-33d1406552a6', 'App\\Models\\User', '01a1a00d-2379-720c-b2e7-38fb42cc1dd8', 'App\\Models\\GoodsReceiptItem', '01a1abb9-eb77-7223-b9c3-c550dbfd8d1f', 'created', NULL, '{\"purchase_order_item_id\":\"01a1ab9c-b58b-7097-851d-3c9439f8c09f\",\"product_id\":\"01a1a167-6cb0-7180-a2fd-bacd940de08c\",\"unit_id\":\"01a1a168-03ca-7336-a4fa-8929c05cb80b\",\"received_quantity\":5,\"conversion_factor\":5,\"base_quantity\":25,\"unit_cost\":\"2200.00\",\"notes\":\"Received in good condition\"}', 'Goods receipt item created.', '127.0.0.1', 'Thunder Client (https://www.thunderclient.com)', '[]', '2026-11-05 07:40:50'),
('01a1abb9-eb81-72d7-9828-308b1ba1f1ee', '01a1a009-5ccb-7270-814b-95288da5b942', '01a1a009-5ce4-7307-b010-33d1406552a6', 'App\\Models\\User', '01a1a00d-2379-720c-b2e7-38fb42cc1dd8', 'App\\Models\\GoodsReceipt', '01a1abb9-eb70-7212-9d63-d8a4da734c46', 'created', NULL, '{\"purchase_order_id\":\"01a1ab93-207e-732d-9e12-3696e93b683a\",\"warehouse_id\":\"01a1a009-5ce8-73c5-89dd-881e9a53222e\",\"receipt_number\":\"GRN-0001\",\"receipt_date\":\"2026-11-05T00:00:00.000000Z\",\"status\":\"draft\",\"items_count\":1}', 'Goods receipt draft created.', '127.0.0.1', 'Thunder Client (https://www.thunderclient.com)', '[]', '2026-11-05 07:40:50');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `is_main_branch` tinyint(1) NOT NULL DEFAULT 0,
  `address` text DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `company_id`, `name`, `code`, `is_main_branch`, `address`, `phone`, `status`, `created_at`, `updated_at`) VALUES
('01a1a009-5ce4-7307-b010-33d1406552a6', '01a1a009-5ccb-7270-814b-95288da5b942', 'Main Branch', 'MAIN', 1, 'Karachi, Pakistan', '+923001234567', 'active', '2026-11-03 01:12:10', '2026-11-03 01:12:10');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cash_bank_accounts`
--

CREATE TABLE `cash_bank_accounts` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `branch_id` char(36) DEFAULT NULL,
  `account_name` varchar(255) NOT NULL,
  `account_type` varchar(255) NOT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `account_title` varchar(255) DEFAULT NULL,
  `iban` varchar(255) DEFAULT NULL,
  `currency` char(3) NOT NULL DEFAULT 'PKR',
  `opening_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `current_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cash_bank_transactions`
--

CREATE TABLE `cash_bank_transactions` (
  `id` char(36) NOT NULL,
  `cash_bank_account_id` char(36) NOT NULL,
  `branch_id` char(36) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `transaction_type` varchar(255) NOT NULL,
  `direction` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'PKR',
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` char(36) DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `balance_after` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_by` char(36) DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `parent_id` char(36) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `company_id`, `parent_id`, `name`, `slug`, `description`, `status`, `created_at`, `updated_at`) VALUES
('01a1a164-e85f-70a7-b49e-72deaab8050f', '01a1a009-5ccb-7270-814b-95288da5b942', NULL, 'Oil', 'oil', NULL, 'active', '2026-11-03 07:31:47', '2026-11-03 07:31:47'),
('01a1a165-4fc2-73f0-a5ba-0d24b62b0b93', '01a1a009-5ccb-7270-814b-95288da5b942', NULL, 'Atta', 'atta', NULL, 'active', '2026-11-03 07:32:13', '2026-11-03 07:32:13');

-- --------------------------------------------------------

--
-- Table structure for table `chart_of_accounts`
--

CREATE TABLE `chart_of_accounts` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `parent_id` char(36) DEFAULT NULL,
  `account_code` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `account_type` varchar(255) NOT NULL,
  `account_subtype` varchar(255) DEFAULT NULL,
  `normal_balance` varchar(255) NOT NULL,
  `is_control_account` tinyint(1) NOT NULL DEFAULT 0,
  `allow_manual_entries` tinyint(1) NOT NULL DEFAULT 1,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `legal_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `currency` varchar(3) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `legal_name`, `email`, `phone`, `address`, `currency`, `status`, `created_at`, `updated_at`) VALUES
('01a1a009-5ccb-7270-814b-95288da5b942', 'Demo TradeFlow', 'Demo TradeFlow Private Limited', 'admin@demo-tradeflow.com', '+923001234567', 'Karachi, Pakistan', 'PKR', 'active', '2026-11-03 01:12:10', '2026-11-03 01:12:10');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `customer_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `tax_number` varchar(255) DEFAULT NULL,
  `opening_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `credit_limit` decimal(15,2) DEFAULT NULL,
  `payment_terms_days` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_addresses`
--

CREATE TABLE `customer_addresses` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'billing',
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address_line_1` varchar(255) NOT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_payments`
--

CREATE TABLE `customer_payments` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `branch_id` char(36) NOT NULL,
  `cash_bank_account_id` char(36) DEFAULT NULL,
  `payment_method_id` char(36) DEFAULT NULL,
  `receipt_number` varchar(255) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'PKR',
  `reference_number` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_payment_allocations`
--

CREATE TABLE `customer_payment_allocations` (
  `id` char(36) NOT NULL,
  `customer_payment_id` char(36) NOT NULL,
  `sales_invoice_id` char(36) NOT NULL,
  `allocated_amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_prices`
--

CREATE TABLE `customer_prices` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `product_unit_id` char(36) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `min_quantity` decimal(20,8) NOT NULL DEFAULT 1.00000000,
  `currency` char(3) NOT NULL DEFAULT 'PKR',
  `effective_from` date DEFAULT NULL,
  `effective_until` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` char(36) NOT NULL,
  `sales_order_id` char(36) NOT NULL,
  `warehouse_id` char(36) NOT NULL,
  `delivery_number` varchar(255) NOT NULL,
  `delivery_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `dispatched_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `received_by_name` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_invoice`
--

CREATE TABLE `delivery_invoice` (
  `delivery_id` char(36) NOT NULL,
  `sales_invoice_id` char(36) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_items`
--

CREATE TABLE `delivery_items` (
  `id` char(36) NOT NULL,
  `delivery_id` char(36) NOT NULL,
  `sales_order_item_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `unit_id` char(36) NOT NULL,
  `quantity` decimal(20,8) NOT NULL,
  `conversion_factor` decimal(20,8) NOT NULL DEFAULT 1.00000000,
  `base_quantity` decimal(20,8) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_status_histories`
--

CREATE TABLE `delivery_status_histories` (
  `id` char(36) NOT NULL,
  `delivery_id` char(36) NOT NULL,
  `old_status` varchar(255) DEFAULT NULL,
  `new_status` varchar(255) NOT NULL,
  `changed_by` char(36) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(30) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `designations`
--

CREATE TABLE `designations` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(30) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `employee_code` varchar(50) NOT NULL,
  `department_id` char(36) DEFAULT NULL,
  `designation_id` char(36) DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `gender` varchar(30) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `national_id` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(30) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `employment_type` varchar(50) NOT NULL DEFAULT 'full_time',
  `employment_status` varchar(50) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_branch_histories`
--

CREATE TABLE `employee_branch_histories` (
  `id` char(36) NOT NULL,
  `employee_id` char(36) NOT NULL,
  `branch_id` char(36) NOT NULL,
  `action` varchar(30) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_until` date DEFAULT NULL,
  `changed_by` char(36) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_documents`
--

CREATE TABLE `employee_documents` (
  `id` char(36) NOT NULL,
  `employee_id` char(36) NOT NULL,
  `document_type` varchar(100) NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `document_number` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `issued_on` date DEFAULT NULL,
  `expires_on` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `uploaded_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_emergency_contacts`
--

CREATE TABLE `employee_emergency_contacts` (
  `id` char(36) NOT NULL,
  `employee_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `relationship` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_employment_histories`
--

CREATE TABLE `employee_employment_histories` (
  `id` char(36) NOT NULL,
  `employee_id` char(36) NOT NULL,
  `department_id` char(36) DEFAULT NULL,
  `designation_id` char(36) DEFAULT NULL,
  `employment_type` varchar(50) DEFAULT NULL,
  `effective_from` date NOT NULL,
  `effective_until` date DEFAULT NULL,
  `change_type` varchar(50) NOT NULL,
  `reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `changed_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_invitations`
--

CREATE TABLE `employee_invitations` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `email` varchar(255) NOT NULL,
  `invited_by` char(36) NOT NULL,
  `accepted_user_id` char(36) DEFAULT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `accepted_at` timestamp NULL DEFAULT NULL,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `resend_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_status_histories`
--

CREATE TABLE `employee_status_histories` (
  `id` char(36) NOT NULL,
  `employee_id` char(36) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `effective_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `changed_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` char(36) NOT NULL,
  `branch_id` char(36) NOT NULL,
  `expense_category_id` char(36) NOT NULL,
  `cash_bank_account_id` char(36) DEFAULT NULL,
  `payment_method_id` char(36) DEFAULT NULL,
  `expense_number` varchar(255) NOT NULL,
  `expense_date` date NOT NULL,
  `title` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'PKR',
  `reference_number` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `description` text DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fiscal_years`
--

CREATE TABLE `fiscal_years` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `goods_receipts`
--

CREATE TABLE `goods_receipts` (
  `id` char(36) NOT NULL,
  `purchase_order_id` char(36) NOT NULL,
  `warehouse_id` char(36) NOT NULL,
  `receipt_number` varchar(255) NOT NULL,
  `receipt_date` date NOT NULL,
  `supplier_delivery_number` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `received_by` char(36) DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `goods_receipts`
--

INSERT INTO `goods_receipts` (`id`, `purchase_order_id`, `warehouse_id`, `receipt_number`, `receipt_date`, `supplier_delivery_number`, `status`, `notes`, `received_by`, `posted_at`, `created_at`, `updated_at`) VALUES
('01a1abb9-eb70-7212-9d63-d8a4da734c46', '01a1ab93-207e-732d-9e12-3696e93b683a', '01a1a009-5ce8-73c5-89dd-881e9a53222e', 'GRN-0001', '2026-11-05', 'SUP-DEL-001', 'draft', 'Initial goods receipt', '01a1a00d-2379-720c-b2e7-38fb42cc1dd8', NULL, '2026-11-05 07:40:50', '2026-11-05 07:40:50');

-- --------------------------------------------------------

--
-- Table structure for table `goods_receipt_items`
--

CREATE TABLE `goods_receipt_items` (
  `id` char(36) NOT NULL,
  `goods_receipt_id` char(36) NOT NULL,
  `purchase_order_item_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `unit_id` char(36) NOT NULL,
  `received_quantity` decimal(20,8) NOT NULL,
  `conversion_factor` decimal(20,8) NOT NULL,
  `base_quantity` decimal(20,8) NOT NULL,
  `unit_cost` decimal(15,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `goods_receipt_items`
--

INSERT INTO `goods_receipt_items` (`id`, `goods_receipt_id`, `purchase_order_item_id`, `product_id`, `unit_id`, `received_quantity`, `conversion_factor`, `base_quantity`, `unit_cost`, `notes`, `created_at`, `updated_at`) VALUES
('01a1abb9-eb77-7223-b9c3-c550dbfd8d1f', '01a1abb9-eb70-7212-9d63-d8a4da734c46', '01a1ab9c-b58b-7097-851d-3c9439f8c09f', '01a1a167-6cb0-7180-a2fd-bacd940de08c', '01a1a168-03ca-7336-a4fa-8929c05cb80b', 5.00000000, 5.00000000, 25.00000000, 2200.00, 'Received in good condition', '2026-11-05 07:40:50', '2026-11-05 07:40:50');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `journal_entries`
--

CREATE TABLE `journal_entries` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `branch_id` char(36) DEFAULT NULL,
  `fiscal_year_id` char(36) NOT NULL,
  `accounting_period_id` char(36) NOT NULL,
  `entry_number` varchar(255) NOT NULL,
  `entry_date` date NOT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `source_type` varchar(255) DEFAULT NULL,
  `source_id` char(36) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `total_debit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_credit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_by` char(36) DEFAULT NULL,
  `posted_by` char(36) DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `journal_entry_lines`
--

CREATE TABLE `journal_entry_lines` (
  `id` char(36) NOT NULL,
  `journal_entry_id` char(36) NOT NULL,
  `chart_of_account_id` char(36) NOT NULL,
  `line_number` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `description` varchar(255) DEFAULT NULL,
  `debit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_10_30_125405_create_personal_access_tokens_table', 1),
(5, '2026_11_02_062359_create_companies_table', 1),
(6, '2026_11_02_062412_create_branches_table', 1),
(7, '2026_11_02_062429_create_warehouses_table', 1),
(8, '2026_11_02_063921_add_status_to_user_table', 1),
(9, '2026_11_02_064932_create_roles_table', 2),
(10, '2026_11_02_064942_create_permissions_table', 2),
(11, '2026_11_02_065953_create_role_user_table', 2),
(12, '2026_11_02_070057_create_permission_role_table', 2),
(13, '2026_11_02_070111_create_user_branch_table', 2),
(14, '2026_11_02_071648_create_categories_table', 3),
(15, '2026_11_02_071655_create_units_table', 3),
(16, '2026_11_02_072448_create_products_table', 3),
(17, '2026_11_02_072452_create_product_units_table', 3),
(18, '2026_11_02_072458_create_product_prices_table', 4),
(19, '2026_11_02_101126_create_stock_balances_table', 5),
(20, '2026_11_02_101135_create_stock_movements_table', 5),
(21, '2026_11_02_101156_create_stock_adjustments_table', 5),
(22, '2026_11_02_101203_create_stock_adjustment_items_table', 5),
(23, '2026_11_02_101221_create_stock_transfers_table', 5),
(24, '2026_11_02_101225_create_stock_transfer_items_table', 5),
(25, '2026_11_02_101235_create_stock_reservations_table', 5),
(26, '2026_11_02_103343_create_suppliers_table', 6),
(27, '2026_11_02_103401_create_supplier_addresses_table', 6),
(28, '2026_11_02_103448_create_purchase_requests_table', 6),
(29, '2026_11_02_103455_create_purchase_request_items_table', 6),
(30, '2026_11_02_103522_create_purchase_orders_table', 6),
(31, '2026_11_02_103526_create_purchase_order_items_table', 6),
(32, '2026_11_02_103538_create_goods_receipts_table', 6),
(33, '2026_11_02_103545_create_goods_receipt_items_table', 6),
(34, '2026_11_02_103555_create_purchase_returns_table', 6),
(35, '2026_11_02_103559_create_purchase_return_items_table', 6),
(36, '2026_11_02_103612_create_supplier_invoices_table', 6),
(37, '2026_11_02_103619_create_supplier_invoice_items_table', 6),
(38, '2026_11_02_103707_create_supplier_product_prices_table', 6),
(39, '2026_11_02_111955_create_delivery_invoice_table', 7),
(40, '2026_11_02_113515_create_payments_and_financial_management_tables', 8),
(41, '2026_11_02_114932_create_accounting_tables', 9),
(42, '2026_11_03_045543_create_departments_table', 10),
(43, '2026_11_03_045615_create_designations_table', 10),
(44, '2026_11_03_045638_create_employees_table', 10),
(45, '2026_11_03_045646_create_employee_documents_table', 10),
(46, '2026_11_03_045710_create_employee_emergency_contacts_table', 10),
(47, '2026_11_03_045735_create_employee_employment_histories_table', 10),
(48, '2026_11_03_045754_create_employee_status_histories_table', 10),
(49, '2026_11_03_045800_create_employee_branch_histories_table', 10),
(50, '2026_11_03_045815_create_employee_invitations_table', 10),
(51, '2026_11_03_053659_update_branches_table_for_main_branch', 11),
(52, '2026_11_03_053704_update_warehouses_table_indexes', 11),
(53, '2026_11_03_053709_update_roles_table_for_company_roles', 12),
(54, '2026_11_03_063901_change_tokenable_id_to_uuid_in_personal_access_tokens_table', 13),
(55, '2026_11_03_095226_create_brands_table', 14),
(56, '2026_11_03_101116_add_brand_id_to_products_table', 15),
(57, '2026_11_03_103025_update_product_prices_unique_constraint', 16),
(58, '2026_11_03_120001_add_is_base_unit_to_product_units', 17),
(59, '2026_11_03_120135_migrate_existing_product_base_units', 18),
(60, '2026_11_03_120654_remove_base_unit_id_from_products_table', 19),
(61, '2026_11_04_055344_remove_columns_from_suppliers_table', 20),
(62, '2026_11_04_072309_add_rejection_fields_to_purchase_requests_table', 21),
(64, '2026_11_04_074915_add_cancelation_fields_to_purchase_requests_table', 22),
(66, '2026_11_04_095705_create_audit_logs_table', 23),
(67, '2026_11_05_110108_add_purchase_request_item_id_to_purchase_order_items_table', 24);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `requires_reference` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `module`, `description`, `created_at`, `updated_at`) VALUES
('01a19ff9-1c9a-73b2-8494-ca7548558865', 'View Dashboard', 'dashboard.view', 'Dashboard', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1d1f-73a6-9cc2-912560c0a280', 'View Company', 'company.view', 'Company Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1d3e-708c-995b-1386a1a63e67', 'Edit Company', 'company.edit', 'Company Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1d4b-7139-bc39-29801a25de13', 'View Branches', 'branches.view', 'Branch Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1d58-72c4-87ce-b035119e9add', 'View All Branches', 'branches.view_all', 'Branch Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1d67-7344-b14b-60de553a26ce', 'Create Branches', 'branches.create', 'Branch Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1d77-7381-b784-ae8670a0b9a6', 'Edit Branches', 'branches.edit', 'Branch Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1d87-706a-9eb7-b450e0bb3d15', 'Deactivate Branches', 'branches.deactivate', 'Branch Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1d98-700e-a0b7-ccbab6862cc1', 'Activate Branches', 'branches.activate', 'Branch Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1da9-70b2-b53c-5db1f28cb4db', 'Archive Branches', 'branches.archive', 'Branch Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1dbf-729f-9d1b-b020b5bde567', 'Restore Branches', 'branches.restore', 'Branch Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1dd1-71c2-b377-1c3ceee4339c', 'Export Branches', 'branches.export', 'Branch Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1de2-7197-b5f8-47d442b37627', 'View Warehouses', 'warehouses.view', 'Warehouse Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1df4-7304-93fb-a57404474ff9', 'Create Warehouses', 'warehouses.create', 'Warehouse Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1e04-733d-b83d-78252b032974', 'Edit Warehouses', 'warehouses.edit', 'Warehouse Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1e14-73bc-bd5f-0908ba4137f3', 'Deactivate Warehouses', 'warehouses.deactivate', 'Warehouse Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1e25-72bc-af82-b01265930300', 'Activate Warehouses', 'warehouses.activate', 'Warehouse Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1e35-7158-aec1-a829ab245082', 'Archive Warehouses', 'warehouses.archive', 'Warehouse Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1e43-7035-b0a5-392ca27fd8d0', 'Restore Warehouses', 'warehouses.restore', 'Warehouse Management', NULL, '2026-11-03 00:54:25', '2026-11-03 00:54:25'),
('01a19ff9-1e52-72f2-9c4f-9eb50728c31a', 'Export Warehouses', 'warehouses.export', 'Warehouse Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1e60-71c8-beb8-5869a560f6af', 'View Users', 'users.view', 'User Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1e6e-70e0-9dcb-96061619440d', 'Create Users', 'users.create', 'User Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1e7d-7196-b9eb-1eab0b580be7', 'Edit Users', 'users.edit', 'User Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1e8d-71b5-b446-589aee9c8389', 'Deactivate Users', 'users.deactivate', 'User Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1e9e-717d-bd57-d6938ce62c2d', 'Activate Users', 'users.activate', 'User Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1eaf-707f-a970-61b5a145029f', 'Assign Users', 'users.assign', 'User Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1ec5-7393-aed6-e79badd5ee47', 'Export Users', 'users.export', 'User Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1ed6-71d2-b58e-f95fd27d47c7', 'View User Invitations', 'user_invitations.view', 'User Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1ee7-7001-bf63-6b00c1846761', 'Create User Invitations', 'user_invitations.create', 'User Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1ef7-702b-b4aa-b115ac2b7254', 'Cancel User Invitations', 'user_invitations.cancel', 'User Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1f08-7327-a766-ad30c9d7f049', 'Manage User Invitations', 'user_invitations.manage', 'User Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1f18-7301-a67c-61f93dddb0f2', 'View Roles', 'roles.view', 'Access Control', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1f28-72f0-960d-82b5b170b4e8', 'Create Roles', 'roles.create', 'Access Control', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1f3a-7050-9e7d-d593eec4db0c', 'Edit Roles', 'roles.edit', 'Access Control', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1f4b-706e-955c-77f1a3131938', 'Deactivate Roles', 'roles.deactivate', 'Access Control', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1f7c-71a7-b3bd-7d851051190e', 'Activate Roles', 'roles.activate', 'Access Control', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1fb9-72d1-846c-1eccbf8ec2a6', 'Assign Roles', 'roles.assign', 'Access Control', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1fce-70ca-9e67-df1c228c7084', 'View Permissions', 'permissions.view', 'Access Control', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1fdd-7190-96a3-0ea853832ecf', 'Assign Permissions', 'permissions.assign', 'Access Control', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1feb-72c8-8fa2-a8a0d3a3291e', 'Manage Permissions', 'permissions.manage', 'Access Control', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-1ffa-7281-a645-7654bc23a9ca', 'View User Branches', 'user_branches.view', 'Access Control', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2009-7022-aa10-1aba6268949b', 'Assign User Branches', 'user_branches.assign', 'Access Control', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2018-7370-9fe6-a41e4edb7352', 'Manage User Branches', 'user_branches.manage', 'Access Control', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2027-738f-8767-ab0a7dd7a425', 'View Employees', 'employees.view', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2036-73ad-b3c3-e7ff230ea2eb', 'Create Employees', 'employees.create', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2046-7194-88c4-1f0070e86af3', 'Edit Employees', 'employees.edit', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2055-732e-8cf0-a06168533a27', 'Deactivate Employees', 'employees.deactivate', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2064-717c-b7fa-0324d9f3ff2d', 'Activate Employees', 'employees.activate', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2072-7175-b67c-4b52d1b0a781', 'Export Employees', 'employees.export', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2080-73e6-992f-901bdcc2b3c0', 'Print Employees', 'employees.print', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-208e-7390-957f-c470db8453a9', 'View Departments', 'departments.view', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-209c-72ce-b878-ee9b2bbc7bb2', 'Create Departments', 'departments.create', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-20ab-70fc-96a1-ad224bf654ad', 'Edit Departments', 'departments.edit', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-20b9-73d3-8f3d-19c982f8da80', 'Deactivate Departments', 'departments.deactivate', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-20c7-73dd-8c47-ee8f1182160b', 'Activate Departments', 'departments.activate', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-20d5-719d-a83d-4a1d6d14844d', 'Export Departments', 'departments.export', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-20e4-7133-ab9c-486c39015f28', 'Print Departments', 'departments.print', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-20f4-73e1-8a45-2bee415008a7', 'View Designations', 'designations.view', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2103-7199-9b3f-e137d6a31be5', 'Create Designations', 'designations.create', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2112-73df-8d11-95748b84e6f8', 'Edit Designations', 'designations.edit', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2121-725f-a086-15a5a956fd1d', 'Deactivate Designations', 'designations.deactivate', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2130-721d-8a50-d54d6f0a5442', 'Activate Designations', 'designations.activate', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-213e-722f-aa16-13f10b431b86', 'Export Designations', 'designations.export', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-214d-7253-a431-b6174e1f307f', 'Print Designations', 'designations.print', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-215b-70dd-bed0-fe22d3a1928e', 'View Employee Documents', 'employee_documents.view', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2168-71c1-8873-b85991cdc097', 'Upload Employee Documents', 'employee_documents.upload', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2177-7393-ad8f-462a3acb00b6', 'Edit Employee Documents', 'employee_documents.edit', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2185-7136-8f14-29fa2f484974', 'Delete Employee Documents', 'employee_documents.delete', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2193-714e-b8a9-1f51877cbd71', 'Export Employee Documents', 'employee_documents.export', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-21a1-722c-875a-dfe0ea1cf5d9', 'View Employee Invitations', 'employee_invitations.view', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-21af-7220-989e-549b63eb3d78', 'Create Employee Invitations', 'employee_invitations.create', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-21bd-724a-a4cd-0568e4e61238', 'Cancel Employee Invitations', 'employee_invitations.cancel', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-21cc-7129-95dd-266761213a33', 'Manage Employee Invitations', 'employee_invitations.manage', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-21db-72e5-884b-0e8458d58748', 'View Employee Emergency Contacts', 'employee_emergency_contacts.view', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-21ea-7079-9601-ad057a805b11', 'Create Employee Emergency Contacts', 'employee_emergency_contacts.create', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-21f9-717e-b552-06e5b754762b', 'Edit Employee Emergency Contacts', 'employee_emergency_contacts.edit', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2209-737e-98a8-8a36a5253129', 'Delete Employee Emergency Contacts', 'employee_emergency_contacts.delete', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2217-72b3-857b-53ce2384c253', 'View Employee Employment Histories', 'employee_employment_histories.view', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2226-733f-8e77-ba0e5362d3e7', 'Create Employee Employment Histories', 'employee_employment_histories.create', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2235-71b2-b46b-b8c53fe2e866', 'Edit Employee Employment Histories', 'employee_employment_histories.edit', 'Employee Management', NULL, '2026-11-03 00:54:26', '2026-11-03 00:54:26'),
('01a19ff9-2242-72b5-a618-15cdcba9424b', 'Export Employee Employment Histories', 'employee_employment_histories.export', 'Employee Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2250-714c-b10d-e100e937e6d0', 'View Employee Status Histories', 'employee_status_histories.view', 'Employee Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-225e-72b8-a00f-260501bd2d82', 'Export Employee Status Histories', 'employee_status_histories.export', 'Employee Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-226c-7022-87b4-41156b77c8ad', 'View Employee Branch Histories', 'employee_branch_histories.view', 'Employee Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-227a-73cc-b568-defd762b1916', 'Create Employee Branch Histories', 'employee_branch_histories.create', 'Employee Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2288-736f-a9f2-68e31a4ccfc4', 'Export Employee Branch Histories', 'employee_branch_histories.export', 'Employee Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2296-731c-8cfa-8ab71f5e64de', 'View Categories', 'categories.view', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-22a4-71e1-ae72-e7dced0e03e3', 'Create Categories', 'categories.create', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-22b3-720d-9274-f4b07a7c691c', 'Edit Categories', 'categories.edit', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-22c0-737b-9e4f-97c79491142d', 'Deactivate Categories', 'categories.deactivate', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-22cd-71f8-93ad-1ddee63ece6b', 'Activate Categories', 'categories.activate', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-22da-71e5-89d1-b83a84dce1b3', 'Archive Categories', 'categories.archive', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-22e9-7197-842a-c9cb4c8b74c8', 'Restore Categories', 'categories.restore', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-22f7-70e8-aa18-5eb774d68e73', 'Export Categories', 'categories.export', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2306-7089-b757-1073d2012a21', 'Print Categories', 'categories.print', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2315-72cb-ac13-39f7aa34779c', 'View Products', 'products.view', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2323-7278-9d72-ddaf1048aade', 'Create Products', 'products.create', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2331-72cf-bed7-f3e56046101d', 'Edit Products', 'products.edit', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-233e-709e-8bf8-4eba27998827', 'Deactivate Products', 'products.deactivate', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-234c-70e1-b64b-cc2b0e89f732', 'Activate Products', 'products.activate', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2360-708e-b3cd-963f3f4f0d47', 'Archive Products', 'products.archive', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-236d-7214-b197-d6d33eabaa1c', 'Restore Products', 'products.restore', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-237a-70bb-8dfc-090a1e4e5d36', 'Export Products', 'products.export', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2388-70ad-832e-62b0963d3a08', 'Print Products', 'products.print', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2397-7132-a8bb-a4929570e2d3', 'View Units', 'units.view', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-23a5-7170-ace6-a014413d1a72', 'Create Units', 'units.create', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-23b3-723b-b665-3d2ea8771c40', 'Edit Units', 'units.edit', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-23c0-7316-8a8a-e1739aab51c8', 'Deactivate Units', 'units.deactivate', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-23ce-709b-adeb-0a6534bb1b47', 'Activate Units', 'units.activate', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-23db-71c6-80f1-e4b400a386cd', 'Archive Units', 'units.archive', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-23e8-729e-8610-99cf57543e22', 'Restore Units', 'units.restore', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-23f5-73a0-842d-8eaa2e7e8602', 'Export Units', 'units.export', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2401-7215-83b0-3c148f1a4be4', 'Print Units', 'units.print', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-240e-73fc-acf5-2f07455dd82a', 'View Brands', 'brands.view', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-241b-7252-b3ee-7002cc6a2b4f', 'Create Brands', 'brands.create', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2427-724d-a89e-fd80141d6d2f', 'Edit Brands', 'brands.edit', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2433-7236-83d1-83057274d7ec', 'Deactivate Brands', 'brands.deactivate', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-243e-72e8-858c-7ad5d94755be', 'Activate Brands', 'brands.activate', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-244a-71a4-bc30-e642f2943ec5', 'Archive Brands', 'brands.archive', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2458-70a8-90bc-5fbef55e89bb', 'Restore Brands', 'brands.restore', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2466-70e3-b754-fd4d06b5fda6', 'Export Brands', 'brands.export', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2473-736e-be9e-cbc24930d28e', 'Print Brands', 'brands.print', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2480-718e-926b-2b76a538cfc2', 'View Product Pricing', 'product_pricing.view', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-248d-7000-911e-b9bda3d68cbb', 'Create Product Pricing', 'product_pricing.create', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-249b-72fd-b575-9635a15494c0', 'Edit Product Pricing', 'product_pricing.edit', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-24a8-7286-b811-d453ad8c4f90', 'Export Product Pricing', 'product_pricing.export', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-24c0-71fa-ab4f-4c905b1aa7ab', 'View Product Attributes', 'product_attributes.view', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-24cc-7323-9d68-0fc264dded87', 'Create Product Attributes', 'product_attributes.create', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-24d9-73b9-8eb2-948a52e0b84c', 'Edit Product Attributes', 'product_attributes.edit', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-24e5-72e1-9915-c8db2ee3681e', 'Deactivate Product Attributes', 'product_attributes.deactivate', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-24f1-7038-9f70-da88ef88b48d', 'View Product Variants', 'product_variants.view', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-24fe-716c-ac6c-4ec11febc700', 'Create Product Variants', 'product_variants.create', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-250a-73f6-9da0-0a9c852d26e8', 'Edit Product Variants', 'product_variants.edit', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2517-7298-949b-8d7205e3b716', 'Deactivate Product Variants', 'product_variants.deactivate', 'Product Catalog', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2525-70d3-96da-f9f1a5a75612', 'View Stock Balances', 'stock_balances.view', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2538-73ea-9002-de27a9139090', 'Export Stock Balances', 'stock_balances.export', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2545-7277-bfaf-2a74ef938cb4', 'Print Stock Balances', 'stock_balances.print', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2552-709f-bcf1-ce1bb1ac1703', 'View Stock Movements', 'stock_movements.view', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2560-722c-ae0a-3638e66545ae', 'Export Stock Movements', 'stock_movements.export', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-256d-70f0-a9ff-289c1fc4dd58', 'Print Stock Movements', 'stock_movements.print', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2579-724f-96e2-10025a47a3b3', 'View Stock Adjustments', 'stock_adjustments.view', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2585-7048-8ea9-ce70ee95bac5', 'Create Stock Adjustments', 'stock_adjustments.create', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2591-7205-97eb-29e6855bb57c', 'Edit Stock Adjustments', 'stock_adjustments.edit', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-259d-707d-b76d-ab6604f02c1d', 'Submit Stock Adjustments', 'stock_adjustments.submit', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-25ab-7301-8c09-5c691a9a9d61', 'Approve Stock Adjustments', 'stock_adjustments.approve', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-25b7-7030-8dd6-8a57000700a5', 'Reject Stock Adjustments', 'stock_adjustments.reject', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-25c4-71bb-867b-44c83aeed77b', 'Cancel Stock Adjustments', 'stock_adjustments.cancel', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-25d0-7056-b672-8755b0cfea78', 'Export Stock Adjustments', 'stock_adjustments.export', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-25dd-7210-a39d-a9ae791d1dbe', 'Print Stock Adjustments', 'stock_adjustments.print', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-25eb-72cd-a642-110c287edf19', 'View Stock Transfers', 'stock_transfers.view', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-25f8-7316-a4b3-cd5845a99d31', 'Create Stock Transfers', 'stock_transfers.create', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-260b-73c2-a2a7-67d2ca8c598d', 'Edit Stock Transfers', 'stock_transfers.edit', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2618-70a6-a289-ce4d3e3dd1ff', 'Submit Stock Transfers', 'stock_transfers.submit', 'Inventory Management', NULL, '2026-11-03 00:54:27', '2026-11-03 00:54:27'),
('01a19ff9-2626-7235-8dbb-8026c0dde36f', 'Approve Stock Transfers', 'stock_transfers.approve', 'Inventory Management', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2631-7086-af7a-6addc3b24507', 'Reject Stock Transfers', 'stock_transfers.reject', 'Inventory Management', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-263d-705b-9e68-ddec926f42a2', 'Receive Stock Transfers', 'stock_transfers.receive', 'Inventory Management', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-264a-72d2-bea2-e4dfecbd8a7f', 'Cancel Stock Transfers', 'stock_transfers.cancel', 'Inventory Management', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2657-70bd-9484-d3d7afbe4e02', 'Export Stock Transfers', 'stock_transfers.export', 'Inventory Management', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2663-731a-82ec-360f055acb0a', 'Print Stock Transfers', 'stock_transfers.print', 'Inventory Management', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-266f-7266-b43b-de5fe90d49d4', 'View Stock Reservations', 'stock_reservations.view', 'Inventory Management', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-267b-70d3-affb-f66caed0e7b1', 'Create Stock Reservations', 'stock_reservations.create', 'Inventory Management', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2688-7213-be0a-b41d23952ccb', 'Edit Stock Reservations', 'stock_reservations.edit', 'Inventory Management', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2695-704f-87b1-9f03c929cdec', 'Cancel Stock Reservations', 'stock_reservations.cancel', 'Inventory Management', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-26a3-730a-bf35-53dd385aa12c', 'View Stock Alerts', 'stock_alerts.view', 'Inventory Management', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-26b0-7120-814b-504754764825', 'Manage Stock Alerts', 'stock_alerts.manage', 'Inventory Management', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-26bf-7334-91ec-ad1383b5d04a', 'Export Stock Alerts', 'stock_alerts.export', 'Inventory Management', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-26cc-7289-b363-2652636018c9', 'View Suppliers', 'suppliers.view', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-26d9-71cc-a4c8-323cda904ab1', 'Create Suppliers', 'suppliers.create', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-26e7-72e3-8e60-4aad89d374ec', 'Edit Suppliers', 'suppliers.edit', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-26f4-732b-9243-033c157365ba', 'Deactivate Suppliers', 'suppliers.deactivate', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2700-70be-9d3e-a63a7edfa0c5', 'Activate Suppliers', 'suppliers.activate', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-270c-7278-a641-ac3235ac1682', 'Archive Suppliers', 'suppliers.archive', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2718-730c-bf92-57d541938568', 'Restore Suppliers', 'suppliers.restore', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2724-7101-a184-6ba51b7890a0', 'Export Suppliers', 'suppliers.export', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2731-70bf-bcc5-5b234966b091', 'Print Suppliers', 'suppliers.print', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2767-712c-97a7-e4acaed15dab', 'View Purchase Requests', 'purchase_requests.view', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-277c-724e-80f3-315f65bd0c80', 'Create Purchase Requests', 'purchase_requests.create', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2788-7314-9b5b-77e4542b07a4', 'Edit Purchase Requests', 'purchase_requests.edit', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2795-70e0-b149-366b40b03e36', 'Submit Purchase Requests', 'purchase_requests.submit', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-27a1-733a-86c2-fabeb8af5014', 'Approve Purchase Requests', 'purchase_requests.approve', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-27ae-739a-b55c-0d1e768489d2', 'Reject Purchase Requests', 'purchase_requests.reject', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-27bb-72e9-8081-fe9746b17a46', 'Cancel Purchase Requests', 'purchase_requests.cancel', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-27c8-7179-95c3-fdf89d862da8', 'Export Purchase Requests', 'purchase_requests.export', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-27d5-7380-86e9-616546173c02', 'Print Purchase Requests', 'purchase_requests.print', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-27ee-72e3-a730-8d804761075e', 'View Supplier Quotations', 'supplier_quotations.view', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-27fc-7355-835d-9a933e381c27', 'Create Supplier Quotations', 'supplier_quotations.create', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-280a-7396-b38e-a3c99a1fee27', 'Edit Supplier Quotations', 'supplier_quotations.edit', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2815-7388-9ca7-0b746e5dd658', 'Submit Supplier Quotations', 'supplier_quotations.submit', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2821-72a5-9719-3dd974ba0b00', 'Approve Supplier Quotations', 'supplier_quotations.approve', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-282c-7017-b523-0dcfa79ccd7f', 'Reject Supplier Quotations', 'supplier_quotations.reject', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2837-720e-9f9a-4aabf2a808f5', 'Cancel Supplier Quotations', 'supplier_quotations.cancel', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2843-7348-845a-94d4b27f39c8', 'Export Supplier Quotations', 'supplier_quotations.export', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-284e-7398-80f9-66e5bbb1bba9', 'Print Supplier Quotations', 'supplier_quotations.print', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2858-7267-aa7d-133492745876', 'View Purchase Orders', 'purchase_orders.view', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2862-71d4-af5b-ff5d637ff774', 'Create Purchase Orders', 'purchase_orders.create', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-286c-7001-a313-fadea07566f5', 'Edit Purchase Orders', 'purchase_orders.edit', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2879-718d-9998-4faab4f59d72', 'Submit Purchase Orders', 'purchase_orders.submit', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2885-7276-9833-1dda7c22e4ad', 'Approve Purchase Orders', 'purchase_orders.approve', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2892-7341-b97a-4069c03e7bd0', 'Reject Purchase Orders', 'purchase_orders.reject', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-28a0-704b-8956-ea1e9095c5ab', 'Cancel Purchase Orders', 'purchase_orders.cancel', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-28ad-728c-a393-97038622bc56', 'Export Purchase Orders', 'purchase_orders.export', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-28bb-706a-9012-6b4472481f42', 'Print Purchase Orders', 'purchase_orders.print', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-28ca-7119-9d78-571ba193425a', 'View Goods Receipts', 'goods_receipts.view', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-28d7-70f3-a31a-71fc7357ab54', 'Create Goods Receipts', 'goods_receipts.create', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-28e5-717d-9db2-defd15e93c83', 'Edit Goods Receipts', 'goods_receipts.edit', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-28f2-718f-b570-bfb3504b4cee', 'Submit Goods Receipts', 'goods_receipts.submit', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-28ff-70b2-aaaa-baa8cbeea35f', 'Approve Goods Receipts', 'goods_receipts.approve', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-290c-7344-8d8e-975b65fd04f4', 'Reject Goods Receipts', 'goods_receipts.reject', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2918-7170-913a-6221e2bf925f', 'Cancel Goods Receipts', 'goods_receipts.cancel', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2925-7213-a8b6-5e14f803ec06', 'Export Goods Receipts', 'goods_receipts.export', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2932-707f-b4d6-7f65db38671a', 'Print Goods Receipts', 'goods_receipts.print', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2941-73ee-93f7-fbe648d2d8b2', 'View Supplier Invoices', 'supplier_invoices.view', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2950-722f-b564-46d3d4901c57', 'Create Supplier Invoices', 'supplier_invoices.create', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-295f-72e5-8466-e0df12e3615f', 'Edit Supplier Invoices', 'supplier_invoices.edit', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-296f-739a-8575-33796c63cba4', 'Submit Supplier Invoices', 'supplier_invoices.submit', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-297f-7304-b3c1-9c8f0ef0a77e', 'Approve Supplier Invoices', 'supplier_invoices.approve', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-298f-729b-966f-bae63342b567', 'Reject Supplier Invoices', 'supplier_invoices.reject', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-299e-720b-aecf-b4365af617a1', 'Cancel Supplier Invoices', 'supplier_invoices.cancel', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-29ae-7115-b1b9-29e16dd4683e', 'Export Supplier Invoices', 'supplier_invoices.export', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-29bd-7329-a628-1a6bb17fd7a8', 'Print Supplier Invoices', 'supplier_invoices.print', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-29cd-731b-8aa9-063bc087bfb1', 'View Purchase Returns', 'purchase_returns.view', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-29dd-70a0-9ee6-eb41dd3b9adb', 'Create Purchase Returns', 'purchase_returns.create', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-29ec-7399-9301-a1820de6d940', 'Edit Purchase Returns', 'purchase_returns.edit', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-29fb-70c2-9c63-0c5c4108137d', 'Submit Purchase Returns', 'purchase_returns.submit', 'Purchasing', NULL, '2026-11-03 00:54:28', '2026-11-03 00:54:28'),
('01a19ff9-2a09-73ed-beb3-660571445b3d', 'Approve Purchase Returns', 'purchase_returns.approve', 'Purchasing', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2a17-739f-ad4f-e551e2ce6a44', 'Reject Purchase Returns', 'purchase_returns.reject', 'Purchasing', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2a26-738b-beb4-ea7445790082', 'Cancel Purchase Returns', 'purchase_returns.cancel', 'Purchasing', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2a35-72ba-a967-2d85da548590', 'Export Purchase Returns', 'purchase_returns.export', 'Purchasing', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2a44-731b-9d41-8c45023c970a', 'Print Purchase Returns', 'purchase_returns.print', 'Purchasing', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2a52-7163-b4a7-b3a72a801ab0', 'View Supplier Payments', 'supplier_payments.view', 'Purchasing', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2a61-71ce-93e0-9026e866eec6', 'Create Supplier Payments', 'supplier_payments.create', 'Purchasing', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2a72-7379-aea7-40ce7f291a38', 'Edit Supplier Payments', 'supplier_payments.edit', 'Purchasing', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2a82-725c-b0b6-31ded199091d', 'Submit Supplier Payments', 'supplier_payments.submit', 'Purchasing', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2a91-7331-94c8-5ff3254d0d24', 'Approve Supplier Payments', 'supplier_payments.approve', 'Purchasing', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2aa1-7305-a432-5dd933ee4f03', 'Reject Supplier Payments', 'supplier_payments.reject', 'Purchasing', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2ab1-731e-9b7b-31736ae3940a', 'Cancel Supplier Payments', 'supplier_payments.cancel', 'Purchasing', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2ac0-70f2-9a8c-393370370dac', 'Export Supplier Payments', 'supplier_payments.export', 'Purchasing', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2acf-72f0-976a-bdf968634efd', 'Print Supplier Payments', 'supplier_payments.print', 'Purchasing', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2adf-7268-a6f9-1cff32b4df34', 'Receive Goods Receipts', 'goods_receipts.receive', 'Purchasing', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2aed-709b-ade9-41458cfac1da', 'View Customers', 'customers.view', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2afd-7382-8c13-d9f16e365c5a', 'Create Customers', 'customers.create', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2b0c-7243-b735-aa24f151e1df', 'Edit Customers', 'customers.edit', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2b1b-713f-8cc1-37b18c1f48c4', 'Deactivate Customers', 'customers.deactivate', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2b29-7294-b6c4-260bffd04479', 'Activate Customers', 'customers.activate', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2b38-72dd-ba6e-ab9750d7fbb1', 'Archive Customers', 'customers.archive', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2b44-7033-840a-0ff86740a718', 'Restore Customers', 'customers.restore', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2b5e-70f7-b919-4b2598bebd9e', 'Export Customers', 'customers.export', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2b6a-726a-82bc-8a5130127a93', 'Print Customers', 'customers.print', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2b89-7010-9295-3560e82255ae', 'View Quotations', 'quotations.view', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2bbc-7157-a6eb-019cfbebcf8b', 'Create Quotations', 'quotations.create', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2bcb-7380-a355-c50e8657b3d3', 'Edit Quotations', 'quotations.edit', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2bdb-7382-b230-1edda57d1032', 'Submit Quotations', 'quotations.submit', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2beb-70e8-b44e-7fce6c1fffac', 'Approve Quotations', 'quotations.approve', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2bfa-722f-807c-34e823a69f93', 'Reject Quotations', 'quotations.reject', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2c0a-727d-94bd-513e61b56cfd', 'Cancel Quotations', 'quotations.cancel', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2c1a-70dd-8016-b2df27453c7f', 'Export Quotations', 'quotations.export', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2c2a-704c-a12b-5f9092d8c959', 'Print Quotations', 'quotations.print', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2c4a-70b7-a9c2-a043cb762665', 'View Sales Orders', 'sales_orders.view', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2c58-727a-93a5-312fdb50e29b', 'Create Sales Orders', 'sales_orders.create', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2c65-7324-b5f2-4d41050ac8c6', 'Edit Sales Orders', 'sales_orders.edit', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2c72-72b9-8d98-0652d5b5b06d', 'Submit Sales Orders', 'sales_orders.submit', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2c7f-70a4-b40d-059c2c8596ca', 'Approve Sales Orders', 'sales_orders.approve', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2c8d-70d4-b7cb-2c09e3661aa2', 'Reject Sales Orders', 'sales_orders.reject', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2c99-736f-9820-4dd217ff65d1', 'Cancel Sales Orders', 'sales_orders.cancel', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2ca7-7343-a44b-0f2599bdf22c', 'Export Sales Orders', 'sales_orders.export', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2cb4-715f-8c24-90c610c05d55', 'Print Sales Orders', 'sales_orders.print', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2cc1-73c3-9333-7e26451152ec', 'View Deliveries', 'deliveries.view', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2ccf-701c-8f4b-38eaba41e22e', 'Create Deliveries', 'deliveries.create', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2cde-73b2-b476-821a728b1149', 'Edit Deliveries', 'deliveries.edit', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2ced-72f3-86ed-f1e09419c1e2', 'Submit Deliveries', 'deliveries.submit', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2cfb-724f-bd60-fb5efaca33b2', 'Approve Deliveries', 'deliveries.approve', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2d0a-7199-b238-14adccc65191', 'Reject Deliveries', 'deliveries.reject', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2d19-73a9-b88d-94a63b7d61b4', 'Cancel Deliveries', 'deliveries.cancel', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2d27-7227-ad4c-56a9d7a35d5f', 'Export Deliveries', 'deliveries.export', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2d36-713b-849d-10cffef561b4', 'Print Deliveries', 'deliveries.print', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2d44-7354-8879-b7d6e1e4ef62', 'View Sales Invoices', 'sales_invoices.view', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2d53-7380-93b8-8b6aa52feb87', 'Create Sales Invoices', 'sales_invoices.create', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2d61-7327-884a-22127b725cec', 'Edit Sales Invoices', 'sales_invoices.edit', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2d70-71cb-86bb-8e4b8c9d051c', 'Submit Sales Invoices', 'sales_invoices.submit', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2d7e-7375-b83a-3e4bfb894708', 'Approve Sales Invoices', 'sales_invoices.approve', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2d8d-7304-a1bd-754d3ae88375', 'Reject Sales Invoices', 'sales_invoices.reject', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2d9d-73ae-828e-59256b1ab647', 'Cancel Sales Invoices', 'sales_invoices.cancel', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2dac-73ec-8f36-a76f4543a436', 'Export Sales Invoices', 'sales_invoices.export', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2dbc-7270-b8a6-7c8c6ca70c19', 'Print Sales Invoices', 'sales_invoices.print', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2dcb-73d1-8f3c-b07dc6cb2396', 'View Customer Payments', 'customer_payments.view', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2dda-712e-94d9-36a1a8d15187', 'Create Customer Payments', 'customer_payments.create', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2de9-73b5-8b66-493e1ca48b08', 'Edit Customer Payments', 'customer_payments.edit', 'Sales', NULL, '2026-11-03 00:54:29', '2026-11-03 00:54:29'),
('01a19ff9-2df8-7325-ae6e-a67df0e0da00', 'Submit Customer Payments', 'customer_payments.submit', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2e06-7090-afcb-0a395b330e9c', 'Approve Customer Payments', 'customer_payments.approve', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2e14-73b7-8ace-727cb5f77ce4', 'Reject Customer Payments', 'customer_payments.reject', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2e23-7343-bf35-5a76c1171d89', 'Cancel Customer Payments', 'customer_payments.cancel', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2e31-72ae-8396-fab1fd4df645', 'Export Customer Payments', 'customer_payments.export', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2e57-71e7-b72c-0218778646f4', 'Print Customer Payments', 'customer_payments.print', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2e65-70c7-ab8d-38934c0a712a', 'View Sales Returns', 'sales_returns.view', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2e74-7343-a03b-0d4b4a523635', 'Create Sales Returns', 'sales_returns.create', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2e87-71ab-a857-a25e3d69831e', 'Edit Sales Returns', 'sales_returns.edit', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2e98-7254-b1c5-c81096963e71', 'Submit Sales Returns', 'sales_returns.submit', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2ea5-7014-a635-ac3ef3c641f6', 'Approve Sales Returns', 'sales_returns.approve', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2eb3-72cb-9620-4c6771f5dde0', 'Reject Sales Returns', 'sales_returns.reject', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2ec0-72dd-b4d8-59ce9dd4f434', 'Cancel Sales Returns', 'sales_returns.cancel', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2ecd-71d5-86cc-37ded46ab7a9', 'Export Sales Returns', 'sales_returns.export', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2eda-7239-ab12-effbde99f362', 'Print Sales Returns', 'sales_returns.print', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2ee7-7290-adae-6be799030e14', 'View Sales Discounts', 'sales_discounts.view', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2ef8-7047-b7db-0a65d5259260', 'Create Sales Discounts', 'sales_discounts.create', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2f07-7121-a26c-c81acd914f3a', 'Edit Sales Discounts', 'sales_discounts.edit', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2f18-70e1-bb8e-570487839334', 'Apply Discount Sales Discounts', 'sales_discounts.apply_discount', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2f27-71d0-9dd5-9e5a764fa288', 'Approve Sales Discounts', 'sales_discounts.approve', 'Sales', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2f34-72a5-a07b-e2bf8c4d1840', 'View Cash Bank Accounts', 'cash_bank_accounts.view', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2f40-718f-b45a-3fd7be025c5e', 'Create Cash Bank Accounts', 'cash_bank_accounts.create', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2f55-7021-919e-8f2e29675389', 'Edit Cash Bank Accounts', 'cash_bank_accounts.edit', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2f64-7322-acf1-b3f60ddb324b', 'Submit Cash Bank Accounts', 'cash_bank_accounts.submit', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2f75-71a2-830a-c740637a7fec', 'Approve Cash Bank Accounts', 'cash_bank_accounts.approve', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2f86-7063-b9f6-59fa39f46814', 'Reject Cash Bank Accounts', 'cash_bank_accounts.reject', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2fd3-7240-9565-bf2aaae625d1', 'Cancel Cash Bank Accounts', 'cash_bank_accounts.cancel', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-2ffc-704b-bb62-b9ecaeeeb5a4', 'Export Cash Bank Accounts', 'cash_bank_accounts.export', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-3014-72f3-bff3-957e590cb215', 'Print Cash Bank Accounts', 'cash_bank_accounts.print', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-3026-7192-a470-3c1f87c866cb', 'View Payment Methods', 'payment_methods.view', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30');
INSERT INTO `permissions` (`id`, `name`, `slug`, `module`, `description`, `created_at`, `updated_at`) VALUES
('01a19ff9-3037-71f0-845d-0e54603b5cfd', 'Create Payment Methods', 'payment_methods.create', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-3048-7024-8897-b3fcf3387e6b', 'Edit Payment Methods', 'payment_methods.edit', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-3059-73b8-9623-260ebc6ebb0e', 'Submit Payment Methods', 'payment_methods.submit', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-306a-7278-b9e0-e2753275a3e4', 'Approve Payment Methods', 'payment_methods.approve', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-307a-7198-833b-bd3ab7d03c45', 'Reject Payment Methods', 'payment_methods.reject', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-308a-70cd-b13f-ec720deab638', 'Cancel Payment Methods', 'payment_methods.cancel', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-309a-729f-aa7f-4dff2852ca50', 'Export Payment Methods', 'payment_methods.export', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-30aa-7333-9f18-373385f5f732', 'Print Payment Methods', 'payment_methods.print', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-30bb-71c1-9726-0a3feed58f37', 'View Expense Categories', 'expense_categories.view', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-30cb-70f1-a299-24eaed2774c5', 'Create Expense Categories', 'expense_categories.create', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-30db-7311-ba99-80aa65ed8299', 'Edit Expense Categories', 'expense_categories.edit', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-30eb-712c-b41c-effbaaf953bf', 'Submit Expense Categories', 'expense_categories.submit', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-30fc-7290-aeec-a3648bcd022d', 'Approve Expense Categories', 'expense_categories.approve', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-310e-73c9-bb5c-89fe0c127078', 'Reject Expense Categories', 'expense_categories.reject', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-311f-7060-b427-5baf51863c30', 'Cancel Expense Categories', 'expense_categories.cancel', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-312e-72a3-9d3a-90ebfeb2c7d1', 'Export Expense Categories', 'expense_categories.export', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-313d-7229-8d3b-6c7dfc7d6f40', 'Print Expense Categories', 'expense_categories.print', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-314c-705f-829c-905d4961602f', 'View Expenses', 'expenses.view', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-315c-727d-a0b2-508d9adb6e3b', 'Create Expenses', 'expenses.create', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-316b-7128-b3f3-1ad907b1257f', 'Edit Expenses', 'expenses.edit', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-3178-70ba-84da-26c937878806', 'Submit Expenses', 'expenses.submit', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-3186-725a-a1e5-254be2b7bfc3', 'Approve Expenses', 'expenses.approve', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-3194-701b-bfb6-c9eae1eb6c1e', 'Reject Expenses', 'expenses.reject', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-31a2-7144-8b9d-a8313b3388fe', 'Cancel Expenses', 'expenses.cancel', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-31b0-71b9-b483-45255b6ddbd5', 'Export Expenses', 'expenses.export', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-31be-72b7-a154-933a3e5d53fd', 'Print Expenses', 'expenses.print', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-31cc-70bc-ada4-6896263ff54a', 'View Cash Bank Transactions', 'cash_bank_transactions.view', 'Financial Management', NULL, '2026-11-03 00:54:30', '2026-11-03 00:54:30'),
('01a19ff9-31db-7183-b76f-11b367b314a3', 'Create Cash Bank Transactions', 'cash_bank_transactions.create', 'Financial Management', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-31f0-7380-867a-43ab0cb2f8c6', 'Edit Cash Bank Transactions', 'cash_bank_transactions.edit', 'Financial Management', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-31ff-73e9-b3bd-790d16225365', 'Submit Cash Bank Transactions', 'cash_bank_transactions.submit', 'Financial Management', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-320e-71da-9f14-f99990f88607', 'Approve Cash Bank Transactions', 'cash_bank_transactions.approve', 'Financial Management', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-321d-7243-b6fb-0b93c439f2a8', 'Reject Cash Bank Transactions', 'cash_bank_transactions.reject', 'Financial Management', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-322c-71de-9a2b-12bf0204d950', 'Cancel Cash Bank Transactions', 'cash_bank_transactions.cancel', 'Financial Management', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-323b-7313-9e3f-17d2abfe6b5f', 'Export Cash Bank Transactions', 'cash_bank_transactions.export', 'Financial Management', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3249-710a-bb3d-9abbffbf481e', 'Print Cash Bank Transactions', 'cash_bank_transactions.print', 'Financial Management', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3257-73df-b48b-64d0e0fa6134', 'View Accounts Receivable', 'accounts_receivable.view', 'Financial Management', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3265-7152-8b0a-65644a6795fb', 'Export Accounts Receivable', 'accounts_receivable.export', 'Financial Management', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3274-72c0-a0f4-ce656c5ed6b5', 'Print Accounts Receivable', 'accounts_receivable.print', 'Financial Management', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3282-72ae-a846-0ef17c363a6f', 'View Accounts Payable', 'accounts_payable.view', 'Financial Management', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3290-700a-9381-6c63247aaecc', 'Export Accounts Payable', 'accounts_payable.export', 'Financial Management', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-329e-73cc-91e8-66e2db82f1a6', 'Print Accounts Payable', 'accounts_payable.print', 'Financial Management', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-32b0-73f0-b1b7-b450d8dfaaf0', 'View Chart Of Accounts', 'chart_of_accounts.view', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-32bf-72e2-968e-99851087c53b', 'Create Chart Of Accounts', 'chart_of_accounts.create', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-32ce-73d1-9c92-418e856a70e2', 'Edit Chart Of Accounts', 'chart_of_accounts.edit', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-32e3-73de-b051-21dc3c1c6742', 'Deactivate Chart Of Accounts', 'chart_of_accounts.deactivate', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-32f2-7190-9a39-f3d51739db46', 'Export Chart Of Accounts', 'chart_of_accounts.export', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3303-7382-8269-63933b5a201c', 'Print Chart Of Accounts', 'chart_of_accounts.print', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3310-707a-a481-310620dd2d65', 'View Fiscal Years', 'fiscal_years.view', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-331d-73d5-ad79-91dcd003970f', 'Create Fiscal Years', 'fiscal_years.create', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-332a-713b-9ced-401e8fbd8f2b', 'Edit Fiscal Years', 'fiscal_years.edit', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3336-72c0-82ad-da88e3d21576', 'Activate Fiscal Years', 'fiscal_years.activate', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-334c-7398-bfac-9ad156674e1a', 'Archive Fiscal Years', 'fiscal_years.archive', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3362-7246-8181-ea67b0b94ef2', 'View Journal Entries', 'journal_entries.view', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3372-7307-b40c-640c8c142d49', 'Create Journal Entries', 'journal_entries.create', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3384-70a8-ad3a-d908f2db6483', 'Edit Journal Entries', 'journal_entries.edit', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3395-7151-a123-c339039c970d', 'Submit Journal Entries', 'journal_entries.submit', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-33bf-721f-987a-9daf130183fd', 'Approve Journal Entries', 'journal_entries.approve', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-33fb-7226-890a-9eb7762c4672', 'Reject Journal Entries', 'journal_entries.reject', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-342c-710e-a79c-d103d1557ae8', 'Post Journal Entries', 'journal_entries.post', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-344d-7361-959a-75264378474e', 'Reverse Journal Entries', 'journal_entries.reverse', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3462-7084-b2cd-b2202573b5d5', 'Cancel Journal Entries', 'journal_entries.cancel', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3470-700d-be95-d8dc58150d50', 'Export Journal Entries', 'journal_entries.export', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3481-733d-b7b5-e0877d25ac2b', 'Print Journal Entries', 'journal_entries.print', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-348f-703a-ad2c-96c3d5aa6f29', 'View General Ledger', 'general_ledger.view', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-349d-733d-b15e-72fb07df8701', 'Export General Ledger', 'general_ledger.export', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-34ad-71b3-b6c6-18457cb2bfb6', 'Print General Ledger', 'general_ledger.print', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-34bf-72af-ad72-1b42c3124c7d', 'View Trial Balance', 'trial_balance.view', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-34cc-72bf-b9f7-db1b6d373dbf', 'Export Trial Balance', 'trial_balance.export', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-34db-73c0-a6b7-13f427feef67', 'Print Trial Balance', 'trial_balance.print', 'Accounting', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-34ea-7277-a60d-2befc55ef6fc', 'View Approval Requests', 'approval_requests.view', 'Approval Workflow', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-34fa-70b7-97bc-ac94c57d9b27', 'Approve Approval Requests', 'approval_requests.approve', 'Approval Workflow', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3509-70d6-8b5b-0b8df5bf2e11', 'Reject Approval Requests', 'approval_requests.reject', 'Approval Workflow', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3519-71fa-ac80-5ee5d4736840', 'Withdraw Approval Requests', 'approval_requests.withdraw', 'Approval Workflow', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3527-7124-9ed6-c37e202a6cd5', 'Delegate Approval Requests', 'approval_requests.delegate', 'Approval Workflow', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3535-70f2-8a68-c0cdd83d14a7', 'Export Approval Requests', 'approval_requests.export', 'Approval Workflow', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3543-7268-aa0d-534507a80107', 'View Approval Settings', 'approval_settings.view', 'Approval Workflow', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-3551-707a-86a0-939378e4d28e', 'Create Approval Settings', 'approval_settings.create', 'Approval Workflow', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-355f-73a1-bc7a-17f9f76e8a73', 'Edit Approval Settings', 'approval_settings.edit', 'Approval Workflow', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-356d-72c7-bb1f-94756c1a2383', 'Configure Approval Settings', 'approval_settings.configure', 'Approval Workflow', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-357c-7333-a9a0-83b486d6c1d7', 'View Audit Logs', 'audit_logs.view', 'Audit Trail', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-35a0-7296-904e-152f4b4f82d6', 'Export Audit Logs', 'audit_logs.export', 'Audit Trail', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-35be-73ca-bd8e-5458c0d738f3', 'Print Audit Logs', 'audit_logs.print', 'Audit Trail', NULL, '2026-11-03 00:54:31', '2026-11-03 00:54:31'),
('01a19ff9-35cc-71ec-b5cc-161be06bedf8', 'View Financial Reports', 'financial_reports.view', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-35e4-7161-9db7-5a8545a05b89', 'Export Financial Reports', 'financial_reports.export', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-35f1-7170-81dd-a9a5608b51ea', 'Print Financial Reports', 'financial_reports.print', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-35fe-721e-b5ee-13eb9794e16c', 'View Inventory Reports', 'inventory_reports.view', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-360c-7096-bf05-c419ce1747b8', 'Export Inventory Reports', 'inventory_reports.export', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-361a-715c-903a-7332bc5eb08d', 'Print Inventory Reports', 'inventory_reports.print', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-3624-7236-8854-8e18aa9a5143', 'View Purchasing Reports', 'purchasing_reports.view', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-363e-70bc-8525-f6d4fc061b70', 'Export Purchasing Reports', 'purchasing_reports.export', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-3657-707d-bff7-6ecdc6ebe6b9', 'Print Purchasing Reports', 'purchasing_reports.print', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-3663-7084-ac13-bc4d89cc8342', 'View Sales Reports', 'sales_reports.view', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-366e-7206-a0a7-a4c836d62010', 'Export Sales Reports', 'sales_reports.export', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-367a-70fe-9b10-2b78d632f1f2', 'Print Sales Reports', 'sales_reports.print', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-3685-71f4-a328-7c91ab58e3f3', 'View Customer Reports', 'customer_reports.view', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-3691-72d8-845a-f1a33fac0bec', 'Export Customer Reports', 'customer_reports.export', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-369d-729a-8ba5-cfaab4b42514', 'Print Customer Reports', 'customer_reports.print', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-36ab-71c4-ae81-88115f8d3ead', 'View Supplier Reports', 'supplier_reports.view', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-36b9-7225-bdb0-cd422d500b85', 'Export Supplier Reports', 'supplier_reports.export', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-36c7-726b-b79e-d81953f865d1', 'Print Supplier Reports', 'supplier_reports.print', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-36d4-7260-8f5a-e3b0cc19f5df', 'View Branch Reports', 'branch_reports.view', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-36e1-71e8-ac43-977e7669bb50', 'Export Branch Reports', 'branch_reports.export', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-36ee-7365-9df6-ed395c10c7a4', 'Print Branch Reports', 'branch_reports.print', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-36fe-7222-96ee-4c28160951b0', 'View Employee Reports', 'employee_reports.view', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-370d-701b-a917-e66515bddcaf', 'Export Employee Reports', 'employee_reports.export', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-371c-71e3-b0e7-fb0efad5cc49', 'Print Employee Reports', 'employee_reports.print', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-372d-72d8-b127-45940f52f30a', 'View Profit Loss Report', 'profit_loss_report.view', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-3739-72b6-8b0a-f8ab0b8beaf7', 'Export Profit Loss Report', 'profit_loss_report.export', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-375d-709d-8faa-24a524a3c00f', 'Print Profit Loss Report', 'profit_loss_report.print', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-3769-7026-b1b1-d25198d518b0', 'View Balance Sheet Report', 'balance_sheet_report.view', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-3779-71c9-8041-993fbb36c3ef', 'Export Balance Sheet Report', 'balance_sheet_report.export', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-3790-7316-871b-13a45a78e291', 'Print Balance Sheet Report', 'balance_sheet_report.print', 'Reports & Analytics', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-37a1-711d-9f98-b5f52aa04ff0', 'View Data Imports', 'data_imports.view', 'Import & Bulk Data', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-37b1-73fd-a2f2-3b03fd2301dd', 'Upload Data Imports', 'data_imports.upload', 'Import & Bulk Data', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-37cd-725a-b011-1ab241ed546e', 'Validate Data Imports', 'data_imports.validate', 'Import & Bulk Data', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-37dd-72fe-9d97-831c569e2b08', 'Import Data Imports', 'data_imports.import', 'Import & Bulk Data', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-37ed-7002-9e15-de0e4ea3ab1a', 'View Company Settings', 'company_settings.view', 'Company Settings', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-37fd-71c2-b234-981d0c56ff99', 'Edit Company Settings', 'company_settings.edit', 'Company Settings', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-380d-72fa-981c-dbb5a57d2699', 'View Financial Settings', 'financial_settings.view', 'Company Settings', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-382f-71ad-95cd-b8a507af52cd', 'Edit Financial Settings', 'financial_settings.edit', 'Company Settings', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-3875-717f-86e6-0c2f1aa09134', 'View Document Settings', 'document_settings.view', 'Company Settings', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-388b-72f8-9f0a-f6001369114c', 'Edit Document Settings', 'document_settings.edit', 'Company Settings', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-3899-711e-a3a1-3908a4134940', 'View Notifications', 'notifications.view', 'Notifications', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-38a7-73c4-9db5-e06dd3760d8d', 'Mark as Read Notifications', 'notifications.mark_read', 'Notifications', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-38c0-72a5-89ae-ad9c18645d2e', 'View Notification Preferences', 'notification_preferences.view', 'Notifications', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32'),
('01a19ff9-38d0-7021-8f43-39c4774541af', 'Edit Notification Preferences', 'notification_preferences.edit', 'Notifications', NULL, '2026-11-03 00:54:32', '2026-11-03 00:54:32');

-- --------------------------------------------------------

--
-- Table structure for table `permission_role`
--

CREATE TABLE `permission_role` (
  `permission_id` char(36) NOT NULL,
  `role_id` char(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permission_role`
--

INSERT INTO `permission_role` (`permission_id`, `role_id`, `created_at`, `updated_at`) VALUES
('01a19ff9-1c9a-73b2-8494-ca7548558865', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1d1f-73a6-9cc2-912560c0a280', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1d3e-708c-995b-1386a1a63e67', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1d4b-7139-bc39-29801a25de13', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1d58-72c4-87ce-b035119e9add', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1d67-7344-b14b-60de553a26ce', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1d77-7381-b784-ae8670a0b9a6', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1d87-706a-9eb7-b450e0bb3d15', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1d98-700e-a0b7-ccbab6862cc1', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1da9-70b2-b53c-5db1f28cb4db', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1dbf-729f-9d1b-b020b5bde567', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1dd1-71c2-b377-1c3ceee4339c', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1de2-7197-b5f8-47d442b37627', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1df4-7304-93fb-a57404474ff9', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1e04-733d-b83d-78252b032974', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1e14-73bc-bd5f-0908ba4137f3', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1e25-72bc-af82-b01265930300', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1e35-7158-aec1-a829ab245082', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1e43-7035-b0a5-392ca27fd8d0', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1e52-72f2-9c4f-9eb50728c31a', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1e60-71c8-beb8-5869a560f6af', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1e6e-70e0-9dcb-96061619440d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1e7d-7196-b9eb-1eab0b580be7', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1e8d-71b5-b446-589aee9c8389', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1e9e-717d-bd57-d6938ce62c2d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1eaf-707f-a970-61b5a145029f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1ec5-7393-aed6-e79badd5ee47', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1ed6-71d2-b58e-f95fd27d47c7', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1ee7-7001-bf63-6b00c1846761', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1ef7-702b-b4aa-b115ac2b7254', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1f08-7327-a766-ad30c9d7f049', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1f18-7301-a67c-61f93dddb0f2', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1f28-72f0-960d-82b5b170b4e8', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1f3a-7050-9e7d-d593eec4db0c', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1f4b-706e-955c-77f1a3131938', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1f7c-71a7-b3bd-7d851051190e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1fb9-72d1-846c-1eccbf8ec2a6', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1fce-70ca-9e67-df1c228c7084', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1fdd-7190-96a3-0ea853832ecf', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1feb-72c8-8fa2-a8a0d3a3291e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-1ffa-7281-a645-7654bc23a9ca', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2009-7022-aa10-1aba6268949b', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2018-7370-9fe6-a41e4edb7352', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2027-738f-8767-ab0a7dd7a425', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2036-73ad-b3c3-e7ff230ea2eb', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2046-7194-88c4-1f0070e86af3', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2055-732e-8cf0-a06168533a27', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2064-717c-b7fa-0324d9f3ff2d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2072-7175-b67c-4b52d1b0a781', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2080-73e6-992f-901bdcc2b3c0', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-208e-7390-957f-c470db8453a9', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-209c-72ce-b878-ee9b2bbc7bb2', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-20ab-70fc-96a1-ad224bf654ad', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-20b9-73d3-8f3d-19c982f8da80', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-20c7-73dd-8c47-ee8f1182160b', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-20d5-719d-a83d-4a1d6d14844d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-20e4-7133-ab9c-486c39015f28', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-20f4-73e1-8a45-2bee415008a7', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2103-7199-9b3f-e137d6a31be5', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2112-73df-8d11-95748b84e6f8', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2121-725f-a086-15a5a956fd1d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2130-721d-8a50-d54d6f0a5442', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-213e-722f-aa16-13f10b431b86', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-214d-7253-a431-b6174e1f307f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-215b-70dd-bed0-fe22d3a1928e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2168-71c1-8873-b85991cdc097', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2177-7393-ad8f-462a3acb00b6', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2185-7136-8f14-29fa2f484974', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2193-714e-b8a9-1f51877cbd71', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-21a1-722c-875a-dfe0ea1cf5d9', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-21af-7220-989e-549b63eb3d78', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-21bd-724a-a4cd-0568e4e61238', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-21cc-7129-95dd-266761213a33', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-21db-72e5-884b-0e8458d58748', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-21ea-7079-9601-ad057a805b11', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-21f9-717e-b552-06e5b754762b', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2209-737e-98a8-8a36a5253129', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2217-72b3-857b-53ce2384c253', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2226-733f-8e77-ba0e5362d3e7', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2235-71b2-b46b-b8c53fe2e866', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2242-72b5-a618-15cdcba9424b', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2250-714c-b10d-e100e937e6d0', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-225e-72b8-a00f-260501bd2d82', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-226c-7022-87b4-41156b77c8ad', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-227a-73cc-b568-defd762b1916', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2288-736f-a9f2-68e31a4ccfc4', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2296-731c-8cfa-8ab71f5e64de', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2296-731c-8cfa-8ab71f5e64de', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-22a4-71e1-ae72-e7dced0e03e3', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-22a4-71e1-ae72-e7dced0e03e3', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-22b3-720d-9274-f4b07a7c691c', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-22b3-720d-9274-f4b07a7c691c', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-22c0-737b-9e4f-97c79491142d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-22c0-737b-9e4f-97c79491142d', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-22cd-71f8-93ad-1ddee63ece6b', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-22cd-71f8-93ad-1ddee63ece6b', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-22da-71e5-89d1-b83a84dce1b3', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-22da-71e5-89d1-b83a84dce1b3', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-22e9-7197-842a-c9cb4c8b74c8', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-22e9-7197-842a-c9cb4c8b74c8', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-22f7-70e8-aa18-5eb774d68e73', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-22f7-70e8-aa18-5eb774d68e73', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2306-7089-b757-1073d2012a21', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2306-7089-b757-1073d2012a21', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2315-72cb-ac13-39f7aa34779c', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2315-72cb-ac13-39f7aa34779c', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2323-7278-9d72-ddaf1048aade', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2323-7278-9d72-ddaf1048aade', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2331-72cf-bed7-f3e56046101d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2331-72cf-bed7-f3e56046101d', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-233e-709e-8bf8-4eba27998827', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-233e-709e-8bf8-4eba27998827', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-234c-70e1-b64b-cc2b0e89f732', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-234c-70e1-b64b-cc2b0e89f732', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2360-708e-b3cd-963f3f4f0d47', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2360-708e-b3cd-963f3f4f0d47', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-236d-7214-b197-d6d33eabaa1c', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-236d-7214-b197-d6d33eabaa1c', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-237a-70bb-8dfc-090a1e4e5d36', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-237a-70bb-8dfc-090a1e4e5d36', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2388-70ad-832e-62b0963d3a08', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2388-70ad-832e-62b0963d3a08', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2397-7132-a8bb-a4929570e2d3', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2397-7132-a8bb-a4929570e2d3', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-23a5-7170-ace6-a014413d1a72', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-23a5-7170-ace6-a014413d1a72', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-23b3-723b-b665-3d2ea8771c40', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-23b3-723b-b665-3d2ea8771c40', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-23c0-7316-8a8a-e1739aab51c8', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-23c0-7316-8a8a-e1739aab51c8', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-23ce-709b-adeb-0a6534bb1b47', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-23ce-709b-adeb-0a6534bb1b47', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-23db-71c6-80f1-e4b400a386cd', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-23db-71c6-80f1-e4b400a386cd', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-23e8-729e-8610-99cf57543e22', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-23e8-729e-8610-99cf57543e22', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-23f5-73a0-842d-8eaa2e7e8602', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-23f5-73a0-842d-8eaa2e7e8602', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2401-7215-83b0-3c148f1a4be4', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2401-7215-83b0-3c148f1a4be4', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-240e-73fc-acf5-2f07455dd82a', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-240e-73fc-acf5-2f07455dd82a', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-241b-7252-b3ee-7002cc6a2b4f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-241b-7252-b3ee-7002cc6a2b4f', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2427-724d-a89e-fd80141d6d2f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2427-724d-a89e-fd80141d6d2f', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2433-7236-83d1-83057274d7ec', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2433-7236-83d1-83057274d7ec', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-243e-72e8-858c-7ad5d94755be', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-243e-72e8-858c-7ad5d94755be', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-244a-71a4-bc30-e642f2943ec5', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-244a-71a4-bc30-e642f2943ec5', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2458-70a8-90bc-5fbef55e89bb', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2458-70a8-90bc-5fbef55e89bb', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2466-70e3-b754-fd4d06b5fda6', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2466-70e3-b754-fd4d06b5fda6', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2473-736e-be9e-cbc24930d28e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2473-736e-be9e-cbc24930d28e', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2480-718e-926b-2b76a538cfc2', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2480-718e-926b-2b76a538cfc2', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-248d-7000-911e-b9bda3d68cbb', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-248d-7000-911e-b9bda3d68cbb', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-249b-72fd-b575-9635a15494c0', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-249b-72fd-b575-9635a15494c0', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-24a8-7286-b811-d453ad8c4f90', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-24a8-7286-b811-d453ad8c4f90', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-24c0-71fa-ab4f-4c905b1aa7ab', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-24c0-71fa-ab4f-4c905b1aa7ab', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-24cc-7323-9d68-0fc264dded87', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-24cc-7323-9d68-0fc264dded87', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-24d9-73b9-8eb2-948a52e0b84c', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-24d9-73b9-8eb2-948a52e0b84c', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-24e5-72e1-9915-c8db2ee3681e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-24e5-72e1-9915-c8db2ee3681e', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-24f1-7038-9f70-da88ef88b48d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-24f1-7038-9f70-da88ef88b48d', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-24fe-716c-ac6c-4ec11febc700', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-24fe-716c-ac6c-4ec11febc700', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-250a-73f6-9da0-0a9c852d26e8', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-250a-73f6-9da0-0a9c852d26e8', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2517-7298-949b-8d7205e3b716', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2517-7298-949b-8d7205e3b716', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2525-70d3-96da-f9f1a5a75612', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2525-70d3-96da-f9f1a5a75612', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2538-73ea-9002-de27a9139090', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2538-73ea-9002-de27a9139090', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2545-7277-bfaf-2a74ef938cb4', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2545-7277-bfaf-2a74ef938cb4', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2552-709f-bcf1-ce1bb1ac1703', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2552-709f-bcf1-ce1bb1ac1703', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2560-722c-ae0a-3638e66545ae', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2560-722c-ae0a-3638e66545ae', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-256d-70f0-a9ff-289c1fc4dd58', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-256d-70f0-a9ff-289c1fc4dd58', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2579-724f-96e2-10025a47a3b3', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2579-724f-96e2-10025a47a3b3', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2585-7048-8ea9-ce70ee95bac5', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2585-7048-8ea9-ce70ee95bac5', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2591-7205-97eb-29e6855bb57c', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2591-7205-97eb-29e6855bb57c', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-259d-707d-b76d-ab6604f02c1d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-259d-707d-b76d-ab6604f02c1d', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-25ab-7301-8c09-5c691a9a9d61', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-25ab-7301-8c09-5c691a9a9d61', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-25b7-7030-8dd6-8a57000700a5', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-25b7-7030-8dd6-8a57000700a5', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-25c4-71bb-867b-44c83aeed77b', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-25c4-71bb-867b-44c83aeed77b', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-25d0-7056-b672-8755b0cfea78', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-25d0-7056-b672-8755b0cfea78', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-25dd-7210-a39d-a9ae791d1dbe', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-25dd-7210-a39d-a9ae791d1dbe', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-25eb-72cd-a642-110c287edf19', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-25eb-72cd-a642-110c287edf19', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-25f8-7316-a4b3-cd5845a99d31', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-25f8-7316-a4b3-cd5845a99d31', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-260b-73c2-a2a7-67d2ca8c598d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-260b-73c2-a2a7-67d2ca8c598d', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2618-70a6-a289-ce4d3e3dd1ff', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2618-70a6-a289-ce4d3e3dd1ff', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2626-7235-8dbb-8026c0dde36f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2626-7235-8dbb-8026c0dde36f', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2631-7086-af7a-6addc3b24507', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2631-7086-af7a-6addc3b24507', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-263d-705b-9e68-ddec926f42a2', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-263d-705b-9e68-ddec926f42a2', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-264a-72d2-bea2-e4dfecbd8a7f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-264a-72d2-bea2-e4dfecbd8a7f', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2657-70bd-9484-d3d7afbe4e02', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2657-70bd-9484-d3d7afbe4e02', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2663-731a-82ec-360f055acb0a', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2663-731a-82ec-360f055acb0a', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-266f-7266-b43b-de5fe90d49d4', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-266f-7266-b43b-de5fe90d49d4', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-267b-70d3-affb-f66caed0e7b1', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-267b-70d3-affb-f66caed0e7b1', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2688-7213-be0a-b41d23952ccb', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2688-7213-be0a-b41d23952ccb', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-2695-704f-87b1-9f03c929cdec', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2695-704f-87b1-9f03c929cdec', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-26a3-730a-bf35-53dd385aa12c', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-26a3-730a-bf35-53dd385aa12c', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-26b0-7120-814b-504754764825', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-26b0-7120-814b-504754764825', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-26bf-7334-91ec-ad1383b5d04a', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-26bf-7334-91ec-ad1383b5d04a', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-26cc-7289-b363-2652636018c9', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-26cc-7289-b363-2652636018c9', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-26d9-71cc-a4c8-323cda904ab1', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-26d9-71cc-a4c8-323cda904ab1', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-26e7-72e3-8e60-4aad89d374ec', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-26e7-72e3-8e60-4aad89d374ec', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-26f4-732b-9243-033c157365ba', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-26f4-732b-9243-033c157365ba', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2700-70be-9d3e-a63a7edfa0c5', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2700-70be-9d3e-a63a7edfa0c5', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-270c-7278-a641-ac3235ac1682', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-270c-7278-a641-ac3235ac1682', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2718-730c-bf92-57d541938568', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2718-730c-bf92-57d541938568', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2724-7101-a184-6ba51b7890a0', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2724-7101-a184-6ba51b7890a0', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2731-70bf-bcc5-5b234966b091', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2731-70bf-bcc5-5b234966b091', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2767-712c-97a7-e4acaed15dab', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2767-712c-97a7-e4acaed15dab', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-277c-724e-80f3-315f65bd0c80', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-277c-724e-80f3-315f65bd0c80', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2788-7314-9b5b-77e4542b07a4', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2788-7314-9b5b-77e4542b07a4', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2795-70e0-b149-366b40b03e36', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2795-70e0-b149-366b40b03e36', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-27a1-733a-86c2-fabeb8af5014', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-27a1-733a-86c2-fabeb8af5014', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-27ae-739a-b55c-0d1e768489d2', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-27ae-739a-b55c-0d1e768489d2', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-27bb-72e9-8081-fe9746b17a46', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-27bb-72e9-8081-fe9746b17a46', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-27c8-7179-95c3-fdf89d862da8', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-27c8-7179-95c3-fdf89d862da8', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-27d5-7380-86e9-616546173c02', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-27d5-7380-86e9-616546173c02', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-27ee-72e3-a730-8d804761075e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-27ee-72e3-a730-8d804761075e', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-27fc-7355-835d-9a933e381c27', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-27fc-7355-835d-9a933e381c27', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-280a-7396-b38e-a3c99a1fee27', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-280a-7396-b38e-a3c99a1fee27', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2815-7388-9ca7-0b746e5dd658', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2815-7388-9ca7-0b746e5dd658', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2821-72a5-9719-3dd974ba0b00', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2821-72a5-9719-3dd974ba0b00', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-282c-7017-b523-0dcfa79ccd7f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-282c-7017-b523-0dcfa79ccd7f', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2837-720e-9f9a-4aabf2a808f5', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2837-720e-9f9a-4aabf2a808f5', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2843-7348-845a-94d4b27f39c8', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2843-7348-845a-94d4b27f39c8', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-284e-7398-80f9-66e5bbb1bba9', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-284e-7398-80f9-66e5bbb1bba9', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2858-7267-aa7d-133492745876', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2858-7267-aa7d-133492745876', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2862-71d4-af5b-ff5d637ff774', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2862-71d4-af5b-ff5d637ff774', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-286c-7001-a313-fadea07566f5', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-286c-7001-a313-fadea07566f5', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2879-718d-9998-4faab4f59d72', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2879-718d-9998-4faab4f59d72', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2885-7276-9833-1dda7c22e4ad', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2885-7276-9833-1dda7c22e4ad', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2892-7341-b97a-4069c03e7bd0', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2892-7341-b97a-4069c03e7bd0', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-28a0-704b-8956-ea1e9095c5ab', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-28a0-704b-8956-ea1e9095c5ab', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-28ad-728c-a393-97038622bc56', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-28ad-728c-a393-97038622bc56', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-28bb-706a-9012-6b4472481f42', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-28bb-706a-9012-6b4472481f42', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-28ca-7119-9d78-571ba193425a', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-28ca-7119-9d78-571ba193425a', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-28d7-70f3-a31a-71fc7357ab54', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-28d7-70f3-a31a-71fc7357ab54', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-28e5-717d-9db2-defd15e93c83', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-28e5-717d-9db2-defd15e93c83', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-28f2-718f-b570-bfb3504b4cee', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-28f2-718f-b570-bfb3504b4cee', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-28ff-70b2-aaaa-baa8cbeea35f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-28ff-70b2-aaaa-baa8cbeea35f', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-290c-7344-8d8e-975b65fd04f4', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-290c-7344-8d8e-975b65fd04f4', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2918-7170-913a-6221e2bf925f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2918-7170-913a-6221e2bf925f', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2925-7213-a8b6-5e14f803ec06', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2925-7213-a8b6-5e14f803ec06', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2932-707f-b4d6-7f65db38671a', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2932-707f-b4d6-7f65db38671a', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2941-73ee-93f7-fbe648d2d8b2', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2941-73ee-93f7-fbe648d2d8b2', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2950-722f-b564-46d3d4901c57', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2950-722f-b564-46d3d4901c57', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-295f-72e5-8466-e0df12e3615f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-295f-72e5-8466-e0df12e3615f', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-296f-739a-8575-33796c63cba4', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-296f-739a-8575-33796c63cba4', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-297f-7304-b3c1-9c8f0ef0a77e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-297f-7304-b3c1-9c8f0ef0a77e', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-298f-729b-966f-bae63342b567', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-298f-729b-966f-bae63342b567', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-299e-720b-aecf-b4365af617a1', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-299e-720b-aecf-b4365af617a1', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-29ae-7115-b1b9-29e16dd4683e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-29ae-7115-b1b9-29e16dd4683e', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-29bd-7329-a628-1a6bb17fd7a8', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-29bd-7329-a628-1a6bb17fd7a8', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-29cd-731b-8aa9-063bc087bfb1', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-29cd-731b-8aa9-063bc087bfb1', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-29dd-70a0-9ee6-eb41dd3b9adb', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-29dd-70a0-9ee6-eb41dd3b9adb', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-29ec-7399-9301-a1820de6d940', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-29ec-7399-9301-a1820de6d940', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-29fb-70c2-9c63-0c5c4108137d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-29fb-70c2-9c63-0c5c4108137d', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2a09-73ed-beb3-660571445b3d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2a09-73ed-beb3-660571445b3d', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2a17-739f-ad4f-e551e2ce6a44', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2a17-739f-ad4f-e551e2ce6a44', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2a26-738b-beb4-ea7445790082', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2a26-738b-beb4-ea7445790082', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2a35-72ba-a967-2d85da548590', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2a35-72ba-a967-2d85da548590', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2a44-731b-9d41-8c45023c970a', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2a44-731b-9d41-8c45023c970a', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2a52-7163-b4a7-b3a72a801ab0', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2a52-7163-b4a7-b3a72a801ab0', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2a61-71ce-93e0-9026e866eec6', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2a61-71ce-93e0-9026e866eec6', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2a72-7379-aea7-40ce7f291a38', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2a72-7379-aea7-40ce7f291a38', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2a82-725c-b0b6-31ded199091d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2a82-725c-b0b6-31ded199091d', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2a91-7331-94c8-5ff3254d0d24', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2a91-7331-94c8-5ff3254d0d24', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2aa1-7305-a432-5dd933ee4f03', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2aa1-7305-a432-5dd933ee4f03', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2ab1-731e-9b7b-31736ae3940a', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2ab1-731e-9b7b-31736ae3940a', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2ac0-70f2-9a8c-393370370dac', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2ac0-70f2-9a8c-393370370dac', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2acf-72f0-976a-bdf968634efd', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2acf-72f0-976a-bdf968634efd', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2adf-7268-a6f9-1cff32b4df34', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2adf-7268-a6f9-1cff32b4df34', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-2aed-709b-ade9-41458cfac1da', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2aed-709b-ade9-41458cfac1da', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2afd-7382-8c13-d9f16e365c5a', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2afd-7382-8c13-d9f16e365c5a', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2b0c-7243-b735-aa24f151e1df', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2b0c-7243-b735-aa24f151e1df', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2b1b-713f-8cc1-37b18c1f48c4', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2b1b-713f-8cc1-37b18c1f48c4', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2b29-7294-b6c4-260bffd04479', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2b29-7294-b6c4-260bffd04479', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2b38-72dd-ba6e-ab9750d7fbb1', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2b38-72dd-ba6e-ab9750d7fbb1', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2b44-7033-840a-0ff86740a718', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2b44-7033-840a-0ff86740a718', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2b5e-70f7-b919-4b2598bebd9e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2b5e-70f7-b919-4b2598bebd9e', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2b6a-726a-82bc-8a5130127a93', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2b6a-726a-82bc-8a5130127a93', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2b89-7010-9295-3560e82255ae', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2b89-7010-9295-3560e82255ae', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2bbc-7157-a6eb-019cfbebcf8b', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2bbc-7157-a6eb-019cfbebcf8b', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2bcb-7380-a355-c50e8657b3d3', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2bcb-7380-a355-c50e8657b3d3', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2bdb-7382-b230-1edda57d1032', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2bdb-7382-b230-1edda57d1032', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2beb-70e8-b44e-7fce6c1fffac', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2beb-70e8-b44e-7fce6c1fffac', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2bfa-722f-807c-34e823a69f93', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2bfa-722f-807c-34e823a69f93', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2c0a-727d-94bd-513e61b56cfd', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2c0a-727d-94bd-513e61b56cfd', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2c1a-70dd-8016-b2df27453c7f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2c1a-70dd-8016-b2df27453c7f', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2c2a-704c-a12b-5f9092d8c959', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2c2a-704c-a12b-5f9092d8c959', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2c4a-70b7-a9c2-a043cb762665', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2c4a-70b7-a9c2-a043cb762665', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2c58-727a-93a5-312fdb50e29b', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2c58-727a-93a5-312fdb50e29b', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2c65-7324-b5f2-4d41050ac8c6', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2c65-7324-b5f2-4d41050ac8c6', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2c72-72b9-8d98-0652d5b5b06d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2c72-72b9-8d98-0652d5b5b06d', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2c7f-70a4-b40d-059c2c8596ca', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2c7f-70a4-b40d-059c2c8596ca', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2c8d-70d4-b7cb-2c09e3661aa2', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2c8d-70d4-b7cb-2c09e3661aa2', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2c99-736f-9820-4dd217ff65d1', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2c99-736f-9820-4dd217ff65d1', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2ca7-7343-a44b-0f2599bdf22c', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2ca7-7343-a44b-0f2599bdf22c', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2cb4-715f-8c24-90c610c05d55', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2cb4-715f-8c24-90c610c05d55', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2cc1-73c3-9333-7e26451152ec', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2cc1-73c3-9333-7e26451152ec', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2ccf-701c-8f4b-38eaba41e22e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2ccf-701c-8f4b-38eaba41e22e', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2cde-73b2-b476-821a728b1149', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2cde-73b2-b476-821a728b1149', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2ced-72f3-86ed-f1e09419c1e2', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2ced-72f3-86ed-f1e09419c1e2', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2cfb-724f-bd60-fb5efaca33b2', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2cfb-724f-bd60-fb5efaca33b2', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2d0a-7199-b238-14adccc65191', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2d0a-7199-b238-14adccc65191', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2d19-73a9-b88d-94a63b7d61b4', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2d19-73a9-b88d-94a63b7d61b4', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2d27-7227-ad4c-56a9d7a35d5f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2d27-7227-ad4c-56a9d7a35d5f', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2d36-713b-849d-10cffef561b4', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2d36-713b-849d-10cffef561b4', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2d44-7354-8879-b7d6e1e4ef62', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2d44-7354-8879-b7d6e1e4ef62', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2d53-7380-93b8-8b6aa52feb87', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2d53-7380-93b8-8b6aa52feb87', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2d61-7327-884a-22127b725cec', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2d61-7327-884a-22127b725cec', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2d70-71cb-86bb-8e4b8c9d051c', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2d70-71cb-86bb-8e4b8c9d051c', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2d7e-7375-b83a-3e4bfb894708', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2d7e-7375-b83a-3e4bfb894708', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2d8d-7304-a1bd-754d3ae88375', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2d8d-7304-a1bd-754d3ae88375', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2d9d-73ae-828e-59256b1ab647', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2d9d-73ae-828e-59256b1ab647', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2dac-73ec-8f36-a76f4543a436', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2dac-73ec-8f36-a76f4543a436', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2dbc-7270-b8a6-7c8c6ca70c19', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2dbc-7270-b8a6-7c8c6ca70c19', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2dcb-73d1-8f3c-b07dc6cb2396', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2dcb-73d1-8f3c-b07dc6cb2396', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2dda-712e-94d9-36a1a8d15187', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2dda-712e-94d9-36a1a8d15187', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2de9-73b5-8b66-493e1ca48b08', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2de9-73b5-8b66-493e1ca48b08', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2df8-7325-ae6e-a67df0e0da00', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2df8-7325-ae6e-a67df0e0da00', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2e06-7090-afcb-0a395b330e9c', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2e06-7090-afcb-0a395b330e9c', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2e14-73b7-8ace-727cb5f77ce4', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2e14-73b7-8ace-727cb5f77ce4', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2e23-7343-bf35-5a76c1171d89', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2e23-7343-bf35-5a76c1171d89', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2e31-72ae-8396-fab1fd4df645', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2e31-72ae-8396-fab1fd4df645', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2e57-71e7-b72c-0218778646f4', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2e57-71e7-b72c-0218778646f4', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2e65-70c7-ab8d-38934c0a712a', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2e65-70c7-ab8d-38934c0a712a', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2e74-7343-a03b-0d4b4a523635', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2e74-7343-a03b-0d4b4a523635', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2e87-71ab-a857-a25e3d69831e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2e87-71ab-a857-a25e3d69831e', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2e98-7254-b1c5-c81096963e71', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2e98-7254-b1c5-c81096963e71', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2ea5-7014-a635-ac3ef3c641f6', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2ea5-7014-a635-ac3ef3c641f6', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2eb3-72cb-9620-4c6771f5dde0', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2eb3-72cb-9620-4c6771f5dde0', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2ec0-72dd-b4d8-59ce9dd4f434', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2ec0-72dd-b4d8-59ce9dd4f434', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2ecd-71d5-86cc-37ded46ab7a9', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2ecd-71d5-86cc-37ded46ab7a9', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2eda-7239-ab12-effbde99f362', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2eda-7239-ab12-effbde99f362', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2ee7-7290-adae-6be799030e14', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2ee7-7290-adae-6be799030e14', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2ef8-7047-b7db-0a65d5259260', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2ef8-7047-b7db-0a65d5259260', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2f07-7121-a26c-c81acd914f3a', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2f07-7121-a26c-c81acd914f3a', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2f18-70e1-bb8e-570487839334', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2f18-70e1-bb8e-570487839334', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2f27-71d0-9dd5-9e5a764fa288', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2f27-71d0-9dd5-9e5a764fa288', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-2f34-72a5-a07b-e2bf8c4d1840', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2f34-72a5-a07b-e2bf8c4d1840', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-2f40-718f-b45a-3fd7be025c5e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2f40-718f-b45a-3fd7be025c5e', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-2f55-7021-919e-8f2e29675389', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2f55-7021-919e-8f2e29675389', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-2f64-7322-acf1-b3f60ddb324b', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2f64-7322-acf1-b3f60ddb324b', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-2f75-71a2-830a-c740637a7fec', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2f75-71a2-830a-c740637a7fec', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-2f86-7063-b9f6-59fa39f46814', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2f86-7063-b9f6-59fa39f46814', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-2fd3-7240-9565-bf2aaae625d1', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2fd3-7240-9565-bf2aaae625d1', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL);
INSERT INTO `permission_role` (`permission_id`, `role_id`, `created_at`, `updated_at`) VALUES
('01a19ff9-2ffc-704b-bb62-b9ecaeeeb5a4', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-2ffc-704b-bb62-b9ecaeeeb5a4', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3014-72f3-bff3-957e590cb215', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3014-72f3-bff3-957e590cb215', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3026-7192-a470-3c1f87c866cb', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3026-7192-a470-3c1f87c866cb', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3037-71f0-845d-0e54603b5cfd', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3037-71f0-845d-0e54603b5cfd', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3048-7024-8897-b3fcf3387e6b', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3048-7024-8897-b3fcf3387e6b', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3059-73b8-9623-260ebc6ebb0e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3059-73b8-9623-260ebc6ebb0e', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-306a-7278-b9e0-e2753275a3e4', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-306a-7278-b9e0-e2753275a3e4', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-307a-7198-833b-bd3ab7d03c45', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-307a-7198-833b-bd3ab7d03c45', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-308a-70cd-b13f-ec720deab638', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-308a-70cd-b13f-ec720deab638', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-309a-729f-aa7f-4dff2852ca50', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-309a-729f-aa7f-4dff2852ca50', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-30aa-7333-9f18-373385f5f732', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-30aa-7333-9f18-373385f5f732', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-30bb-71c1-9726-0a3feed58f37', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-30bb-71c1-9726-0a3feed58f37', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-30cb-70f1-a299-24eaed2774c5', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-30cb-70f1-a299-24eaed2774c5', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-30db-7311-ba99-80aa65ed8299', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-30db-7311-ba99-80aa65ed8299', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-30eb-712c-b41c-effbaaf953bf', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-30eb-712c-b41c-effbaaf953bf', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-30fc-7290-aeec-a3648bcd022d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-30fc-7290-aeec-a3648bcd022d', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-310e-73c9-bb5c-89fe0c127078', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-310e-73c9-bb5c-89fe0c127078', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-311f-7060-b427-5baf51863c30', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-311f-7060-b427-5baf51863c30', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-312e-72a3-9d3a-90ebfeb2c7d1', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-312e-72a3-9d3a-90ebfeb2c7d1', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-313d-7229-8d3b-6c7dfc7d6f40', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-313d-7229-8d3b-6c7dfc7d6f40', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-314c-705f-829c-905d4961602f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-314c-705f-829c-905d4961602f', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-315c-727d-a0b2-508d9adb6e3b', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-315c-727d-a0b2-508d9adb6e3b', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-316b-7128-b3f3-1ad907b1257f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-316b-7128-b3f3-1ad907b1257f', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3178-70ba-84da-26c937878806', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3178-70ba-84da-26c937878806', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3186-725a-a1e5-254be2b7bfc3', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3186-725a-a1e5-254be2b7bfc3', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3194-701b-bfb6-c9eae1eb6c1e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3194-701b-bfb6-c9eae1eb6c1e', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-31a2-7144-8b9d-a8313b3388fe', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-31a2-7144-8b9d-a8313b3388fe', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-31b0-71b9-b483-45255b6ddbd5', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-31b0-71b9-b483-45255b6ddbd5', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-31be-72b7-a154-933a3e5d53fd', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-31be-72b7-a154-933a3e5d53fd', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-31cc-70bc-ada4-6896263ff54a', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-31cc-70bc-ada4-6896263ff54a', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-31db-7183-b76f-11b367b314a3', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-31db-7183-b76f-11b367b314a3', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-31f0-7380-867a-43ab0cb2f8c6', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-31f0-7380-867a-43ab0cb2f8c6', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-31ff-73e9-b3bd-790d16225365', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-31ff-73e9-b3bd-790d16225365', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-320e-71da-9f14-f99990f88607', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-320e-71da-9f14-f99990f88607', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-321d-7243-b6fb-0b93c439f2a8', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-321d-7243-b6fb-0b93c439f2a8', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-322c-71de-9a2b-12bf0204d950', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-322c-71de-9a2b-12bf0204d950', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-323b-7313-9e3f-17d2abfe6b5f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-323b-7313-9e3f-17d2abfe6b5f', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3249-710a-bb3d-9abbffbf481e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3249-710a-bb3d-9abbffbf481e', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3257-73df-b48b-64d0e0fa6134', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3257-73df-b48b-64d0e0fa6134', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3265-7152-8b0a-65644a6795fb', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3265-7152-8b0a-65644a6795fb', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3274-72c0-a0f4-ce656c5ed6b5', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3274-72c0-a0f4-ce656c5ed6b5', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3282-72ae-a846-0ef17c363a6f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3282-72ae-a846-0ef17c363a6f', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3290-700a-9381-6c63247aaecc', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3290-700a-9381-6c63247aaecc', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-329e-73cc-91e8-66e2db82f1a6', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-329e-73cc-91e8-66e2db82f1a6', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-32b0-73f0-b1b7-b450d8dfaaf0', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-32b0-73f0-b1b7-b450d8dfaaf0', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-32bf-72e2-968e-99851087c53b', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-32bf-72e2-968e-99851087c53b', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-32ce-73d1-9c92-418e856a70e2', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-32ce-73d1-9c92-418e856a70e2', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-32e3-73de-b051-21dc3c1c6742', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-32e3-73de-b051-21dc3c1c6742', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-32f2-7190-9a39-f3d51739db46', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-32f2-7190-9a39-f3d51739db46', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3303-7382-8269-63933b5a201c', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3303-7382-8269-63933b5a201c', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3310-707a-a481-310620dd2d65', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3310-707a-a481-310620dd2d65', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-331d-73d5-ad79-91dcd003970f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-331d-73d5-ad79-91dcd003970f', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-332a-713b-9ced-401e8fbd8f2b', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-332a-713b-9ced-401e8fbd8f2b', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3336-72c0-82ad-da88e3d21576', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3336-72c0-82ad-da88e3d21576', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-334c-7398-bfac-9ad156674e1a', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-334c-7398-bfac-9ad156674e1a', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3362-7246-8181-ea67b0b94ef2', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3362-7246-8181-ea67b0b94ef2', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3372-7307-b40c-640c8c142d49', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3372-7307-b40c-640c8c142d49', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3384-70a8-ad3a-d908f2db6483', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3384-70a8-ad3a-d908f2db6483', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3395-7151-a123-c339039c970d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3395-7151-a123-c339039c970d', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-33bf-721f-987a-9daf130183fd', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-33bf-721f-987a-9daf130183fd', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-33fb-7226-890a-9eb7762c4672', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-33fb-7226-890a-9eb7762c4672', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-342c-710e-a79c-d103d1557ae8', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-342c-710e-a79c-d103d1557ae8', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-344d-7361-959a-75264378474e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-344d-7361-959a-75264378474e', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3462-7084-b2cd-b2202573b5d5', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3462-7084-b2cd-b2202573b5d5', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3470-700d-be95-d8dc58150d50', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3470-700d-be95-d8dc58150d50', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3481-733d-b7b5-e0877d25ac2b', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3481-733d-b7b5-e0877d25ac2b', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-348f-703a-ad2c-96c3d5aa6f29', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-348f-703a-ad2c-96c3d5aa6f29', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-349d-733d-b15e-72fb07df8701', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-349d-733d-b15e-72fb07df8701', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-34ad-71b3-b6c6-18457cb2bfb6', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-34ad-71b3-b6c6-18457cb2bfb6', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-34bf-72af-ad72-1b42c3124c7d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-34bf-72af-ad72-1b42c3124c7d', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-34cc-72bf-b9f7-db1b6d373dbf', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-34cc-72bf-b9f7-db1b6d373dbf', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-34db-73c0-a6b7-13f427feef67', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-34db-73c0-a6b7-13f427feef67', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-34ea-7277-a60d-2befc55ef6fc', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-34fa-70b7-97bc-ac94c57d9b27', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3509-70d6-8b5b-0b8df5bf2e11', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3519-71fa-ac80-5ee5d4736840', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3527-7124-9ed6-c37e202a6cd5', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3535-70f2-8a68-c0cdd83d14a7', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3543-7268-aa0d-534507a80107', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3551-707a-86a0-939378e4d28e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-355f-73a1-bc7a-17f9f76e8a73', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-356d-72c7-bb1f-94756c1a2383', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-357c-7333-a9a0-83b486d6c1d7', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-35a0-7296-904e-152f4b4f82d6', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-35be-73ca-bd8e-5458c0d738f3', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-35cc-71ec-b5cc-161be06bedf8', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-35cc-71ec-b5cc-161be06bedf8', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-35e4-7161-9db7-5a8545a05b89', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-35e4-7161-9db7-5a8545a05b89', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-35f1-7170-81dd-a9a5608b51ea', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-35f1-7170-81dd-a9a5608b51ea', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-35fe-721e-b5ee-13eb9794e16c', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-35fe-721e-b5ee-13eb9794e16c', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-360c-7096-bf05-c419ce1747b8', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-360c-7096-bf05-c419ce1747b8', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-361a-715c-903a-7332bc5eb08d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-361a-715c-903a-7332bc5eb08d', '01a1a009-5f8d-722b-ba34-b0d8ccc8857d', NULL, NULL),
('01a19ff9-3624-7236-8854-8e18aa9a5143', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3624-7236-8854-8e18aa9a5143', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-363e-70bc-8525-f6d4fc061b70', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-363e-70bc-8525-f6d4fc061b70', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-3657-707d-bff7-6ecdc6ebe6b9', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3657-707d-bff7-6ecdc6ebe6b9', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-3663-7084-ac13-bc4d89cc8342', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3663-7084-ac13-bc4d89cc8342', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-366e-7206-a0a7-a4c836d62010', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-366e-7206-a0a7-a4c836d62010', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-367a-70fe-9b10-2b78d632f1f2', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-367a-70fe-9b10-2b78d632f1f2', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-3685-71f4-a328-7c91ab58e3f3', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3685-71f4-a328-7c91ab58e3f3', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-3691-72d8-845a-f1a33fac0bec', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3691-72d8-845a-f1a33fac0bec', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-369d-729a-8ba5-cfaab4b42514', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-369d-729a-8ba5-cfaab4b42514', '01a1a009-5f49-733c-bb3f-c6f1a2648e50', NULL, NULL),
('01a19ff9-36ab-71c4-ae81-88115f8d3ead', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-36ab-71c4-ae81-88115f8d3ead', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-36b9-7225-bdb0-cd422d500b85', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-36b9-7225-bdb0-cd422d500b85', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-36c7-726b-b79e-d81953f865d1', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-36c7-726b-b79e-d81953f865d1', '01a1a009-5f66-7051-a349-52b63ddccdfa', NULL, NULL),
('01a19ff9-36d4-7260-8f5a-e3b0cc19f5df', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-36e1-71e8-ac43-977e7669bb50', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-36ee-7365-9df6-ed395c10c7a4', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-36fe-7222-96ee-4c28160951b0', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-370d-701b-a917-e66515bddcaf', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-371c-71e3-b0e7-fb0efad5cc49', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-372d-72d8-b127-45940f52f30a', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-372d-72d8-b127-45940f52f30a', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3739-72b6-8b0a-f8ab0b8beaf7', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3739-72b6-8b0a-f8ab0b8beaf7', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-375d-709d-8faa-24a524a3c00f', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-375d-709d-8faa-24a524a3c00f', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3769-7026-b1b1-d25198d518b0', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3769-7026-b1b1-d25198d518b0', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3779-71c9-8041-993fbb36c3ef', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3779-71c9-8041-993fbb36c3ef', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-3790-7316-871b-13a45a78e291', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3790-7316-871b-13a45a78e291', '01a1a009-5f0f-73f1-91fe-278e66186b53', NULL, NULL),
('01a19ff9-37a1-711d-9f98-b5f52aa04ff0', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-37b1-73fd-a2f2-3b03fd2301dd', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-37cd-725a-b011-1ab241ed546e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-37dd-72fe-9d97-831c569e2b08', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-37ed-7002-9e15-de0e4ea3ab1a', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-37fd-71c2-b234-981d0c56ff99', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-380d-72fa-981c-dbb5a57d2699', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-382f-71ad-95cd-b8a507af52cd', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3875-717f-86e6-0c2f1aa09134', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-388b-72f8-9f0a-f6001369114c', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-3899-711e-a3a1-3908a4134940', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-38a7-73c4-9db5-e06dd3760d8d', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-38c0-72a5-89ae-ad9c18645d2e', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL),
('01a19ff9-38d0-7021-8f43-39c4774541af', '01a1a009-5d38-7151-8425-aceb7991950d', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` char(36) NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(2, 'App\\Models\\User', '01a1a00d-2379-720c-b2e7-38fb42cc1dd8', 'erp-auth-token', 'a0c9fd9fa36059b4c54f5d692b06e9c5c22d3b272a9f565a358b51bc2fb31956', '[\"*\"]', '2026-11-05 07:56:35', NULL, '2026-11-03 02:11:51', '2026-11-05 07:56:35');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `category_id` char(36) NOT NULL,
  `brand_id` char(36) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `reorder_level` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `company_id`, `category_id`, `brand_id`, `name`, `sku`, `barcode`, `description`, `reorder_level`, `status`, `created_at`, `updated_at`) VALUES
('01a1a167-6cb0-7180-a2fd-bacd940de08c', '01a1a009-5ccb-7270-814b-95288da5b942', '01a1a164-e85f-70a7-b49e-72deaab8050f', NULL, 'Dalda Cooking Oil', 'OIL-001', '882999104', 'Cooking oil 1 litre', 10.00, 'active', '2026-11-03 07:34:32', '2026-11-03 07:37:26');

-- --------------------------------------------------------

--
-- Table structure for table `product_prices`
--

CREATE TABLE `product_prices` (
  `id` char(36) NOT NULL,
  `product_unit_id` char(36) NOT NULL,
  `price_type` varchar(50) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `minimum_quantity` decimal(15,2) NOT NULL,
  `effective_from` date NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_prices`
--

INSERT INTO `product_prices` (`id`, `product_unit_id`, `price_type`, `price`, `minimum_quantity`, `effective_from`, `status`, `created_at`, `updated_at`) VALUES
('01a1a183-d57e-70b1-a685-6dff535ec1ac', '01a1a167-6cb6-72e0-b85c-aab21d99a8fa', 'retail', 550.00, 1.00, '2026-11-03', 'active', '2026-11-03 08:05:34', '2026-11-03 08:05:34'),
('01a1a184-ca1f-7183-8c7c-38771ae45284', '01a1a167-6cb6-72e0-b85c-aab21d99a8fa', 'retail', 535.00, 10.00, '2026-11-03', 'active', '2026-11-03 08:06:36', '2026-11-03 23:48:43');

-- --------------------------------------------------------

--
-- Table structure for table `product_units`
--

CREATE TABLE `product_units` (
  `id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `unit_id` char(36) NOT NULL,
  `conversion_factor` decimal(15,4) NOT NULL,
  `is_base_unit` tinyint(1) NOT NULL DEFAULT 0,
  `is_purchase_unit` tinyint(1) NOT NULL DEFAULT 0,
  `is_sale_unit` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_units`
--

INSERT INTO `product_units` (`id`, `product_id`, `unit_id`, `conversion_factor`, `is_base_unit`, `is_purchase_unit`, `is_sale_unit`, `status`, `created_at`, `updated_at`) VALUES
('01a1a167-6cb6-72e0-b85c-aab21d99a8fa', '01a1a167-6cb0-7180-a2fd-bacd940de08c', '01a1a0c7-4f89-7239-af4a-fe7550c79803', 1.0000, 1, 0, 1, 'active', '2026-11-03 07:34:32', '2026-11-03 07:34:32'),
('01a1a16f-f1a9-71b4-b639-6bc0ddcde1bc', '01a1a167-6cb0-7180-a2fd-bacd940de08c', '01a1a168-03ca-7336-a4fa-8929c05cb80b', 5.0000, 0, 1, 1, 'active', '2026-11-03 07:43:50', '2026-11-03 07:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` char(36) NOT NULL,
  `supplier_id` char(36) NOT NULL,
  `branch_id` char(36) NOT NULL,
  `warehouse_id` char(36) NOT NULL,
  `purchase_request_id` char(36) DEFAULT NULL,
  `order_number` varchar(255) NOT NULL,
  `order_date` date NOT NULL,
  `expected_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `currency` varchar(3) NOT NULL DEFAULT 'PKR',
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `approved_by` char(36) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `supplier_id`, `branch_id`, `warehouse_id`, `purchase_request_id`, `order_number`, `order_date`, `expected_date`, `status`, `currency`, `subtotal`, `discount_amount`, `tax_amount`, `total_amount`, `notes`, `created_by`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
('01a1ab93-207e-732d-9e12-3696e93b683a', '01a1a534-b1d6-727a-a179-8be41846384c', '01a1a009-5ce4-7307-b010-33d1406552a6', '01a1a009-5ce8-73c5-89dd-881e9a53222e', '01a1a55a-cad9-7186-ab55-e7e1c6f6ca6e', 'PO-0001', '2026-11-05', '2026-11-20', 'approved', 'PKR', 88000.00, 0.00, 180.00, 88180.00, 'Updated test purchase order', '01a1a00d-2379-720c-b2e7-38fb42cc1dd8', '01a1a00d-2379-720c-b2e7-38fb42cc1dd8', '2026-11-05 07:14:06', '2026-11-05 06:58:28', '2026-11-05 07:22:12');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` char(36) NOT NULL,
  `purchase_order_id` char(36) NOT NULL,
  `purchase_request_item_id` char(36) DEFAULT NULL,
  `product_id` char(36) NOT NULL,
  `unit_id` char(36) NOT NULL,
  `quantity` decimal(20,8) NOT NULL,
  `conversion_factor` decimal(20,8) NOT NULL,
  `base_quantity` decimal(20,8) NOT NULL,
  `received_quantity` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `unit_cost` decimal(15,2) NOT NULL,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`id`, `purchase_order_id`, `purchase_request_item_id`, `product_id`, `unit_id`, `quantity`, `conversion_factor`, `base_quantity`, `received_quantity`, `unit_cost`, `discount_amount`, `tax_amount`, `total_amount`, `notes`, `created_at`, `updated_at`) VALUES
('01a1ab9c-b58b-7097-851d-3c9439f8c09f', '01a1ab93-207e-732d-9e12-3696e93b683a', '01a1a55c-46bb-7299-b43e-f95bdbca2b79', '01a1a167-6cb0-7180-a2fd-bacd940de08c', '01a1a168-03ca-7336-a4fa-8929c05cb80b', 40.00000000, 5.00000000, 200.00000000, 0.00000000, 2200.00, 0.00, 180.00, 88180.00, 'Updated test item', '2026-11-05 07:08:56', '2026-11-05 07:08:56');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` char(36) NOT NULL,
  `branch_id` char(36) NOT NULL,
  `warehouse_id` char(36) DEFAULT NULL,
  `request_number` varchar(255) NOT NULL,
  `request_date` date NOT NULL,
  `required_date` date DEFAULT NULL,
  `priority` varchar(255) NOT NULL DEFAULT 'normal',
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `requested_by` char(36) DEFAULT NULL,
  `approved_by` char(36) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` char(36) DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `canceled_by` char(36) DEFAULT NULL,
  `canceled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_requests`
--

INSERT INTO `purchase_requests` (`id`, `branch_id`, `warehouse_id`, `request_number`, `request_date`, `required_date`, `priority`, `status`, `notes`, `requested_by`, `approved_by`, `approved_at`, `rejected_by`, `rejected_at`, `rejection_reason`, `canceled_by`, `canceled_at`, `cancellation_reason`, `created_at`, `updated_at`) VALUES
('01a1a55a-cad9-7186-ab55-e7e1c6f6ca6e', '01a1a009-5ce4-7307-b010-33d1406552a6', NULL, 'PR-001', '2026-11-04', '2026-11-10', 'normal', 'approved', 'Initial purchase request', '01a1a00d-2379-720c-b2e7-38fb42cc1dd8', '01a1a00d-2379-720c-b2e7-38fb42cc1dd8', '2026-11-05 06:39:59', NULL, NULL, NULL, NULL, NULL, NULL, '2026-11-04 01:59:13', '2026-11-05 06:39:59');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_request_items`
--

CREATE TABLE `purchase_request_items` (
  `id` char(36) NOT NULL,
  `purchase_request_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `unit_id` char(36) NOT NULL,
  `quantity` decimal(20,8) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_request_items`
--

INSERT INTO `purchase_request_items` (`id`, `purchase_request_id`, `product_id`, `unit_id`, `quantity`, `notes`, `created_at`, `updated_at`) VALUES
('01a1a55c-46bb-7299-b43e-f95bdbca2b79', '01a1a55a-cad9-7186-ab55-e7e1c6f6ca6e', '01a1a167-6cb0-7180-a2fd-bacd940de08c', '01a1a168-03ca-7336-a4fa-8929c05cb80b', 50.00000000, NULL, '2026-11-04 02:00:50', '2026-11-04 02:00:50');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_returns`
--

CREATE TABLE `purchase_returns` (
  `id` char(36) NOT NULL,
  `supplier_id` char(36) NOT NULL,
  `warehouse_id` char(36) NOT NULL,
  `goods_receipt_id` char(36) DEFAULT NULL,
  `return_number` varchar(255) NOT NULL,
  `return_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `reason` varchar(255) DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_return_items`
--

CREATE TABLE `purchase_return_items` (
  `id` char(36) NOT NULL,
  `purchase_return_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `unit_id` char(36) NOT NULL,
  `quantity` decimal(20,8) NOT NULL,
  `conversion_factor` decimal(20,8) NOT NULL,
  `base_quantity` decimal(20,8) NOT NULL,
  `unit_cost` decimal(15,2) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `branch_id` char(36) NOT NULL,
  `warehouse_id` char(36) DEFAULT NULL,
  `quotation_number` varchar(255) NOT NULL,
  `quotation_date` date NOT NULL,
  `valid_until` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `currency` char(3) NOT NULL DEFAULT 'PKR',
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `id` char(36) NOT NULL,
  `quotation_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `unit_id` char(36) NOT NULL,
  `quantity` decimal(20,8) NOT NULL,
  `conversion_factor` decimal(20,8) NOT NULL DEFAULT 1.00000000,
  `base_quantity` decimal(20,8) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `company_id`, `name`, `slug`, `description`, `status`, `created_at`, `updated_at`) VALUES
('01a1a009-5d38-7151-8425-aceb7991950d', '01a1a009-5ccb-7270-814b-95288da5b942', 'Company Super Admin', 'company-super-admin', 'Full access to all company modules and permissions.', 'active', '2026-11-03 01:12:10', '2026-11-03 01:12:10'),
('01a1a009-5f0f-73f1-91fe-278e66186b53', '01a1a009-5ccb-7270-814b-95288da5b942', 'Accountant', 'accountant', 'Manages accounting, financial transactions and financial reports.', 'active', '2026-11-03 01:12:11', '2026-11-03 01:12:11'),
('01a1a009-5f49-733c-bb3f-c6f1a2648e50', '01a1a009-5ccb-7270-814b-95288da5b942', 'Sales Manager', 'sales-manager', 'Manages customers, sales operations and sales reports.', 'active', '2026-11-03 01:12:11', '2026-11-03 01:12:11'),
('01a1a009-5f66-7051-a349-52b63ddccdfa', '01a1a009-5ccb-7270-814b-95288da5b942', 'Purchase Manager', 'purchase-manager', 'Manages suppliers, purchasing operations and purchase reports.', 'active', '2026-11-03 01:12:11', '2026-11-03 01:12:11'),
('01a1a009-5f8d-722b-ba34-b0d8ccc8857d', '01a1a009-5ccb-7270-814b-95288da5b942', 'Inventory Manager', 'inventory-manager', 'Manages inventory, stock operations and product catalog.', 'active', '2026-11-03 01:12:11', '2026-11-03 01:12:11');

-- --------------------------------------------------------

--
-- Table structure for table `role_user`
--

CREATE TABLE `role_user` (
  `role_id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_user`
--

INSERT INTO `role_user` (`role_id`, `user_id`, `created_at`, `updated_at`) VALUES
('01a1a009-5d38-7151-8425-aceb7991950d', '01a1a00d-2379-720c-b2e7-38fb42cc1dd8', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sales_invoices`
--

CREATE TABLE `sales_invoices` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `branch_id` char(36) NOT NULL,
  `sales_order_id` char(36) DEFAULT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `currency` char(3) NOT NULL DEFAULT 'PKR',
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_invoice_items`
--

CREATE TABLE `sales_invoice_items` (
  `id` char(36) NOT NULL,
  `sales_invoice_id` char(36) NOT NULL,
  `delivery_item_id` char(36) DEFAULT NULL,
  `product_id` char(36) NOT NULL,
  `unit_id` char(36) NOT NULL,
  `quantity` decimal(20,8) NOT NULL,
  `conversion_factor` decimal(20,8) NOT NULL DEFAULT 1.00000000,
  `base_quantity` decimal(20,8) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_orders`
--

CREATE TABLE `sales_orders` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `branch_id` char(36) NOT NULL,
  `warehouse_id` char(36) NOT NULL,
  `quotation_id` char(36) DEFAULT NULL,
  `order_number` varchar(255) NOT NULL,
  `order_date` date NOT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `currency` char(3) NOT NULL DEFAULT 'PKR',
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `approved_by` char(36) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_order_items`
--

CREATE TABLE `sales_order_items` (
  `id` char(36) NOT NULL,
  `sales_order_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `unit_id` char(36) NOT NULL,
  `quantity` decimal(20,8) NOT NULL,
  `conversion_factor` decimal(20,8) NOT NULL DEFAULT 1.00000000,
  `base_quantity` decimal(20,8) NOT NULL,
  `delivered_quantity` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `unit_price` decimal(15,2) NOT NULL,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_order_status_histories`
--

CREATE TABLE `sales_order_status_histories` (
  `id` char(36) NOT NULL,
  `sales_order_id` char(36) NOT NULL,
  `old_status` varchar(255) DEFAULT NULL,
  `new_status` varchar(255) NOT NULL,
  `changed_by` char(36) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_returns`
--

CREATE TABLE `sales_returns` (
  `id` char(36) NOT NULL,
  `customer_id` char(36) NOT NULL,
  `warehouse_id` char(36) NOT NULL,
  `sales_invoice_id` char(36) DEFAULT NULL,
  `return_number` varchar(255) NOT NULL,
  `return_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `reason` text DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_return_items`
--

CREATE TABLE `sales_return_items` (
  `id` char(36) NOT NULL,
  `sales_return_id` char(36) NOT NULL,
  `sales_invoice_item_id` char(36) DEFAULT NULL,
  `product_id` char(36) NOT NULL,
  `unit_id` char(36) NOT NULL,
  `quantity` decimal(20,8) NOT NULL,
  `conversion_factor` decimal(20,8) NOT NULL DEFAULT 1.00000000,
  `base_quantity` decimal(20,8) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustments`
--

CREATE TABLE `stock_adjustments` (
  `id` char(36) NOT NULL,
  `warehouse_id` char(36) NOT NULL,
  `adjustment_number` varchar(255) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `approved_by` char(36) DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustment_items`
--

CREATE TABLE `stock_adjustment_items` (
  `id` char(36) NOT NULL,
  `stock_adjustment_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `unit_id` char(36) NOT NULL,
  `system_quantity` decimal(20,8) NOT NULL,
  `counted_quantity` decimal(20,8) NOT NULL,
  `difference_quantity` decimal(20,8) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_balances`
--

CREATE TABLE `stock_balances` (
  `id` char(36) NOT NULL,
  `warehouse_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `on_hand_quantity` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `reserved_quantity` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` char(36) NOT NULL,
  `warehouse_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `unit_id` char(36) NOT NULL,
  `movement_type` varchar(255) NOT NULL,
  `direction` varchar(255) NOT NULL,
  `quantity` decimal(20,8) NOT NULL,
  `conversion_factor` decimal(20,8) NOT NULL,
  `base_quantity` decimal(20,8) NOT NULL,
  `balance_after` decimal(20,8) NOT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` char(36) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_reservations`
--

CREATE TABLE `stock_reservations` (
  `id` char(36) NOT NULL,
  `warehouse_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `reference_type` varchar(255) NOT NULL,
  `reference_id` char(36) NOT NULL,
  `quantity` decimal(20,8) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfers`
--

CREATE TABLE `stock_transfers` (
  `id` char(36) NOT NULL,
  `source_warehouse_id` char(36) NOT NULL,
  `destination_warehouse_id` char(36) NOT NULL,
  `transfer_number` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `dispatched_by` char(36) DEFAULT NULL,
  `received_by` char(36) DEFAULT NULL,
  `dispatched_at` timestamp NULL DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfer_items`
--

CREATE TABLE `stock_transfer_items` (
  `id` char(36) NOT NULL,
  `stock_transfer_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `unit_id` char(36) NOT NULL,
  `requested_quantity` decimal(20,8) NOT NULL,
  `dispatched_quantity` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `received_quantity` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `supplier_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `tax_number` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `opening_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `company_id`, `supplier_code`, `name`, `email`, `phone`, `tax_number`, `website`, `opening_balance`, `status`, `notes`, `created_at`, `updated_at`) VALUES
('01a1a534-b1d6-727a-a179-8be41846384c', '01a1a009-5ccb-7270-814b-95288da5b942', 'SUP-001', 'Michael Erassmus', NULL, '+13339876678', NULL, NULL, 0.00, 'active', NULL, '2026-11-04 01:17:36', '2026-11-04 01:17:36');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_addresses`
--

CREATE TABLE `supplier_addresses` (
  `id` char(36) NOT NULL,
  `supplier_id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'billing',
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address_line_1` varchar(255) NOT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `city` varchar(255) NOT NULL,
  `state` varchar(255) DEFAULT NULL,
  `postal_code` varchar(255) DEFAULT NULL,
  `country` varchar(255) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_addresses`
--

INSERT INTO `supplier_addresses` (`id`, `supplier_id`, `type`, `contact_person`, `phone`, `address_line_1`, `address_line_2`, `city`, `state`, `postal_code`, `country`, `is_default`, `created_at`, `updated_at`) VALUES
('01a1a534-b1de-7218-8dee-3c2fbdcd1ebf', '01a1a534-b1d6-727a-a179-8be41846384c', 'both', NULL, '+13339876678', '2043 Pleasant Valley Drive, Newark, NJ 07106', '192 Walnut Run, Newark, NJ 07103', 'Newark', 'New Jersey', '07106', 'USA', 1, '2026-11-04 01:17:36', '2026-11-04 01:24:14');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_invoices`
--

CREATE TABLE `supplier_invoices` (
  `id` char(36) NOT NULL,
  `supplier_id` char(36) NOT NULL,
  `purchase_order_id` char(36) DEFAULT NULL,
  `goods_receipt_id` char(36) DEFAULT NULL,
  `branch_id` char(36) NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `supplier_invoice_number` varchar(255) DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `currency` varchar(3) NOT NULL DEFAULT 'PKR',
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_invoice_items`
--

CREATE TABLE `supplier_invoice_items` (
  `id` char(36) NOT NULL,
  `supplier_invoice_id` char(36) NOT NULL,
  `product_id` char(36) NOT NULL,
  `unit_id` char(36) NOT NULL,
  `quantity` decimal(20,8) NOT NULL,
  `unit_cost` decimal(15,2) NOT NULL,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_payments`
--

CREATE TABLE `supplier_payments` (
  `id` char(36) NOT NULL,
  `supplier_id` char(36) NOT NULL,
  `branch_id` char(36) NOT NULL,
  `cash_bank_account_id` char(36) DEFAULT NULL,
  `payment_method_id` char(36) DEFAULT NULL,
  `payment_number` varchar(255) NOT NULL,
  `payment_date` date NOT NULL,
  `cash_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `trade_in_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency` char(3) NOT NULL DEFAULT 'PKR',
  `reference_number` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_payment_allocations`
--

CREATE TABLE `supplier_payment_allocations` (
  `id` char(36) NOT NULL,
  `supplier_payment_id` char(36) NOT NULL,
  `supplier_invoice_id` char(36) NOT NULL,
  `allocated_amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_payment_trade_ins`
--

CREATE TABLE `supplier_payment_trade_ins` (
  `id` char(36) NOT NULL,
  `supplier_payment_id` char(36) NOT NULL,
  `product_id` char(36) DEFAULT NULL,
  `warehouse_id` char(36) DEFAULT NULL,
  `unit_id` char(36) DEFAULT NULL,
  `item_type` varchar(255) NOT NULL DEFAULT 'stock',
  `description` varchar(255) DEFAULT NULL,
  `quantity` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `conversion_factor` decimal(20,8) NOT NULL DEFAULT 1.00000000,
  `base_quantity` decimal(20,8) NOT NULL DEFAULT 0.00000000,
  `book_unit_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `agreed_unit_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_book_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_agreed_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_product_prices`
--

CREATE TABLE `supplier_product_prices` (
  `id` char(36) NOT NULL,
  `supplier_id` char(36) NOT NULL,
  `product_unit_id` char(36) NOT NULL,
  `purchase_price` decimal(15,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'PKR',
  `min_quantity` decimal(20,8) NOT NULL DEFAULT 1.00000000,
  `effective_from` date DEFAULT NULL,
  `effective_until` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` char(36) NOT NULL,
  `company_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(20) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `company_id`, `name`, `code`, `status`, `created_at`, `updated_at`) VALUES
('01a1a0c7-4f89-7239-af4a-fe7550c79803', '01a1a009-5ccb-7270-814b-95288da5b942', 'Pieces', 'pcs', 'active', '2026-11-03 04:39:39', '2026-11-03 04:43:00'),
('01a1a168-03ca-7336-a4fa-8929c05cb80b', '01a1a009-5ccb-7270-814b-95288da5b942', 'Carton', 'ctn', 'active', '2026-11-03 07:35:10', '2026-11-03 07:35:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
('01a1a00d-2379-720c-b2e7-38fb42cc1dd8', 'Demo Admin', 'admin@demo-tradeflow.com', '2026-11-03 01:16:17', '$2y$12$mNE3tSNM710OhTYBjBCKRuCzmNrJSgfXiJDmVkjlMqNhp1AxyBJBK', 'active', NULL, '2026-11-03 01:16:18', '2026-11-03 01:16:18');

-- --------------------------------------------------------

--
-- Table structure for table `user_branch`
--

CREATE TABLE `user_branch` (
  `user_id` char(36) NOT NULL,
  `branch_id` char(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_branch`
--

INSERT INTO `user_branch` (`user_id`, `branch_id`, `created_at`, `updated_at`) VALUES
('01a1a00d-2379-720c-b2e7-38fb42cc1dd8', '01a1a009-5ce4-7307-b010-33d1406552a6', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` char(36) NOT NULL,
  `branch_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `branch_id`, `name`, `code`, `address`, `status`, `created_at`, `updated_at`) VALUES
('01a1a009-5ce8-73c5-89dd-881e9a53222e', '01a1a009-5ce4-7307-b010-33d1406552a6', 'Main Warehouse', 'WH-001', 'Karachi, Pakistan', 'active', '2026-11-03 01:12:10', '2026-11-03 01:12:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounting_periods`
--
ALTER TABLE `accounting_periods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ap_fy_period_uq` (`fiscal_year_id`,`period_number`),
  ADD KEY `ap_fy_status_idx` (`fiscal_year_id`,`status`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_actor_type_actor_id_index` (`actor_type`,`actor_id`),
  ADD KEY `audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  ADD KEY `audit_company_created_index` (`company_id`,`created_at`),
  ADD KEY `audit_branch_created_index` (`branch_id`,`created_at`),
  ADD KEY `audit_action_created_index` (`action`,`created_at`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branches_company_code_unique` (`company_id`,`code`),
  ADD UNIQUE KEY `branches_company_name_unique` (`company_id`,`name`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brands_company_id_slug_unique` (`company_id`,`slug`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `cash_bank_accounts`
--
ALTER TABLE `cash_bank_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cash_bank_accounts_branch_id_foreign` (`branch_id`),
  ADD KEY `cash_bank_account_lookup_index` (`company_id`,`branch_id`,`status`);

--
-- Indexes for table `cash_bank_transactions`
--
ALTER TABLE `cash_bank_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cash_bank_transactions_branch_id_foreign` (`branch_id`),
  ADD KEY `cash_bank_transactions_created_by_foreign` (`created_by`),
  ADD KEY `cash_bank_reference_index` (`reference_type`,`reference_id`),
  ADD KEY `cash_bank_date_index` (`cash_bank_account_id`,`transaction_date`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_company_id_slug_unique` (`company_id`,`slug`),
  ADD KEY `categories_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coa_comp_code_uq` (`company_id`,`account_code`),
  ADD KEY `coa_comp_type_idx` (`company_id`,`account_type`),
  ADD KEY `coa_parent_idx` (`parent_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `companies_email_unique` (`email`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_company_code_unique` (`company_id`,`customer_code`);

--
-- Indexes for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_addresses_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `customer_payments`
--
ALTER TABLE `customer_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_payment_branch_receipt_unique` (`branch_id`,`receipt_number`),
  ADD KEY `customer_payments_customer_id_foreign` (`customer_id`),
  ADD KEY `customer_payments_cash_bank_account_id_foreign` (`cash_bank_account_id`),
  ADD KEY `customer_payments_payment_method_id_foreign` (`payment_method_id`),
  ADD KEY `customer_payments_created_by_foreign` (`created_by`);

--
-- Indexes for table `customer_payment_allocations`
--
ALTER TABLE `customer_payment_allocations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_payment_invoice_unique` (`customer_payment_id`,`sales_invoice_id`),
  ADD KEY `customer_payment_allocations_sales_invoice_id_foreign` (`sales_invoice_id`);

--
-- Indexes for table `customer_prices`
--
ALTER TABLE `customer_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_prices_product_unit_id_foreign` (`product_unit_id`),
  ADD KEY `customer_price_lookup_index` (`customer_id`,`product_unit_id`,`status`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `delivery_warehouse_number_unique` (`warehouse_id`,`delivery_number`),
  ADD KEY `deliveries_sales_order_id_foreign` (`sales_order_id`),
  ADD KEY `deliveries_created_by_foreign` (`created_by`);

--
-- Indexes for table `delivery_invoice`
--
ALTER TABLE `delivery_invoice`
  ADD PRIMARY KEY (`delivery_id`,`sales_invoice_id`),
  ADD KEY `delivery_invoice_sales_invoice_id_foreign` (`sales_invoice_id`);

--
-- Indexes for table `delivery_items`
--
ALTER TABLE `delivery_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_items_delivery_id_foreign` (`delivery_id`),
  ADD KEY `delivery_items_sales_order_item_id_foreign` (`sales_order_item_id`),
  ADD KEY `delivery_items_product_id_foreign` (`product_id`),
  ADD KEY `delivery_items_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `delivery_status_histories`
--
ALTER TABLE `delivery_status_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_status_histories_delivery_id_foreign` (`delivery_id`),
  ADD KEY `delivery_status_histories_changed_by_foreign` (`changed_by`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dept_company_code_uq` (`company_id`,`code`),
  ADD UNIQUE KEY `dept_company_name_uq` (`company_id`,`name`);

--
-- Indexes for table `designations`
--
ALTER TABLE `designations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `desg_company_code_uq` (`company_id`,`code`),
  ADD UNIQUE KEY `desg_company_name_uq` (`company_id`,`name`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `emp_company_code_uq` (`company_id`,`employee_code`),
  ADD UNIQUE KEY `employees_user_id_unique` (`user_id`),
  ADD KEY `emp_profile_dept_idx` (`department_id`),
  ADD KEY `emp_profile_desg_idx` (`designation_id`);

--
-- Indexes for table `employee_branch_histories`
--
ALTER TABLE `employee_branch_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_branch_histories_branch_id_foreign` (`branch_id`),
  ADD KEY `employee_branch_histories_changed_by_foreign` (`changed_by`),
  ADD KEY `emp_branch_history_idx` (`employee_id`,`branch_id`);

--
-- Indexes for table `employee_documents`
--
ALTER TABLE `employee_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_documents_uploaded_by_foreign` (`uploaded_by`),
  ADD KEY `emp_doc_profile_idx` (`employee_id`);

--
-- Indexes for table `employee_emergency_contacts`
--
ALTER TABLE `employee_emergency_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emp_emergency_profile_idx` (`employee_id`);

--
-- Indexes for table `employee_employment_histories`
--
ALTER TABLE `employee_employment_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_employment_histories_department_id_foreign` (`department_id`),
  ADD KEY `employee_employment_histories_designation_id_foreign` (`designation_id`),
  ADD KEY `employee_employment_histories_changed_by_foreign` (`changed_by`),
  ADD KEY `emp_history_profile_date_idx` (`employee_id`,`effective_from`);

--
-- Indexes for table `employee_invitations`
--
ALTER TABLE `employee_invitations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_invitations_token_hash_unique` (`token_hash`),
  ADD KEY `employee_invitations_invited_by_foreign` (`invited_by`),
  ADD KEY `employee_invitations_accepted_user_id_foreign` (`accepted_user_id`),
  ADD KEY `emp_invite_company_email_idx` (`company_id`,`email`),
  ADD KEY `emp_invite_company_status_idx` (`company_id`,`status`);

--
-- Indexes for table `employee_status_histories`
--
ALTER TABLE `employee_status_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_status_histories_changed_by_foreign` (`changed_by`),
  ADD KEY `emp_status_profile_date_idx` (`employee_id`,`effective_date`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `expense_branch_number_unique` (`branch_id`,`expense_number`),
  ADD KEY `expenses_expense_category_id_foreign` (`expense_category_id`),
  ADD KEY `expenses_cash_bank_account_id_foreign` (`cash_bank_account_id`),
  ADD KEY `expenses_payment_method_id_foreign` (`payment_method_id`),
  ADD KEY `expenses_created_by_foreign` (`created_by`);

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `expense_category_company_name_unique` (`company_id`,`name`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `fiscal_years`
--
ALTER TABLE `fiscal_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fy_comp_name_uq` (`company_id`,`name`),
  ADD KEY `fy_comp_status_idx` (`company_id`,`status`);

--
-- Indexes for table `goods_receipts`
--
ALTER TABLE `goods_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `goods_receipt_number_unique` (`warehouse_id`,`receipt_number`),
  ADD KEY `goods_receipts_purchase_order_id_foreign` (`purchase_order_id`),
  ADD KEY `goods_receipts_received_by_foreign` (`received_by`);

--
-- Indexes for table `goods_receipt_items`
--
ALTER TABLE `goods_receipt_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `goods_receipt_items_goods_receipt_id_foreign` (`goods_receipt_id`),
  ADD KEY `goods_receipt_items_purchase_order_item_id_foreign` (`purchase_order_item_id`),
  ADD KEY `goods_receipt_items_product_id_foreign` (`product_id`),
  ADD KEY `goods_receipt_items_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `je_comp_number_uq` (`company_id`,`entry_number`),
  ADD KEY `journal_entries_branch_id_foreign` (`branch_id`),
  ADD KEY `journal_entries_fiscal_year_id_foreign` (`fiscal_year_id`),
  ADD KEY `journal_entries_accounting_period_id_foreign` (`accounting_period_id`),
  ADD KEY `journal_entries_created_by_foreign` (`created_by`),
  ADD KEY `journal_entries_posted_by_foreign` (`posted_by`),
  ADD KEY `je_comp_date_idx` (`company_id`,`entry_date`),
  ADD KEY `je_comp_status_idx` (`company_id`,`status`),
  ADD KEY `je_source_idx` (`source_type`,`source_id`);

--
-- Indexes for table `journal_entry_lines`
--
ALTER TABLE `journal_entry_lines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jel_entry_line_uq` (`journal_entry_id`,`line_number`),
  ADD KEY `jel_account_idx` (`chart_of_account_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_method_company_name_unique` (`company_id`,`name`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`);

--
-- Indexes for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `permission_role_role_id_foreign` (`role_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_company_id_sku_unique` (`company_id`,`sku`),
  ADD UNIQUE KEY `products_company_id_barcode_unique` (`company_id`,`barcode`),
  ADD KEY `products_category_id_foreign` (`category_id`),
  ADD KEY `products_brand_id_foreign` (`brand_id`);

--
-- Indexes for table `product_prices`
--
ALTER TABLE `product_prices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prod_price_unit_type_qty_date_unique` (`product_unit_id`,`price_type`,`minimum_quantity`,`effective_from`);

--
-- Indexes for table `product_units`
--
ALTER TABLE `product_units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_units_product_id_unit_id_unique` (`product_id`,`unit_id`),
  ADD KEY `product_units_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_order_number_unique` (`branch_id`,`order_number`),
  ADD KEY `purchase_orders_supplier_id_foreign` (`supplier_id`),
  ADD KEY `purchase_orders_warehouse_id_foreign` (`warehouse_id`),
  ADD KEY `purchase_orders_purchase_request_id_foreign` (`purchase_request_id`),
  ADD KEY `purchase_orders_created_by_foreign` (`created_by`),
  ADD KEY `purchase_orders_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_order_items_purchase_order_id_foreign` (`purchase_order_id`),
  ADD KEY `purchase_order_items_product_id_foreign` (`product_id`),
  ADD KEY `purchase_order_items_unit_id_foreign` (`unit_id`),
  ADD KEY `purchase_order_items_purchase_request_item_id_foreign` (`purchase_request_item_id`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_request_number_unique` (`branch_id`,`request_number`),
  ADD KEY `purchase_requests_warehouse_id_foreign` (`warehouse_id`),
  ADD KEY `purchase_requests_requested_by_foreign` (`requested_by`),
  ADD KEY `purchase_requests_approved_by_foreign` (`approved_by`),
  ADD KEY `purchase_requests_rejected_by_foreign` (`rejected_by`),
  ADD KEY `purchase_requests_canceled_by_foreign` (`canceled_by`);

--
-- Indexes for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_request_items_purchase_request_id_foreign` (`purchase_request_id`),
  ADD KEY `purchase_request_items_product_id_foreign` (`product_id`),
  ADD KEY `purchase_request_items_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_return_number_unique` (`warehouse_id`,`return_number`),
  ADD KEY `purchase_returns_supplier_id_foreign` (`supplier_id`),
  ADD KEY `purchase_returns_goods_receipt_id_foreign` (`goods_receipt_id`),
  ADD KEY `purchase_returns_created_by_foreign` (`created_by`);

--
-- Indexes for table `purchase_return_items`
--
ALTER TABLE `purchase_return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_return_items_purchase_return_id_foreign` (`purchase_return_id`),
  ADD KEY `purchase_return_items_product_id_foreign` (`product_id`),
  ADD KEY `purchase_return_items_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quotation_branch_number_unique` (`branch_id`,`quotation_number`),
  ADD KEY `quotations_customer_id_foreign` (`customer_id`),
  ADD KEY `quotations_warehouse_id_foreign` (`warehouse_id`),
  ADD KEY `quotations_created_by_foreign` (`created_by`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_items_quotation_id_foreign` (`quotation_id`),
  ADD KEY `quotation_items_product_id_foreign` (`product_id`),
  ADD KEY `quotation_items_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_company_slug_unique` (`company_id`,`slug`);

--
-- Indexes for table `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`role_id`,`user_id`),
  ADD KEY `role_user_user_id_foreign` (`user_id`);

--
-- Indexes for table `sales_invoices`
--
ALTER TABLE `sales_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sales_invoice_branch_number_unique` (`branch_id`,`invoice_number`),
  ADD KEY `sales_invoices_customer_id_foreign` (`customer_id`),
  ADD KEY `sales_invoices_sales_order_id_foreign` (`sales_order_id`),
  ADD KEY `sales_invoices_created_by_foreign` (`created_by`);

--
-- Indexes for table `sales_invoice_items`
--
ALTER TABLE `sales_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_invoice_items_sales_invoice_id_foreign` (`sales_invoice_id`),
  ADD KEY `sales_invoice_items_delivery_item_id_foreign` (`delivery_item_id`),
  ADD KEY `sales_invoice_items_product_id_foreign` (`product_id`),
  ADD KEY `sales_invoice_items_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sales_order_branch_number_unique` (`branch_id`,`order_number`),
  ADD KEY `sales_orders_customer_id_foreign` (`customer_id`),
  ADD KEY `sales_orders_warehouse_id_foreign` (`warehouse_id`),
  ADD KEY `sales_orders_quotation_id_foreign` (`quotation_id`),
  ADD KEY `sales_orders_created_by_foreign` (`created_by`),
  ADD KEY `sales_orders_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_order_items_sales_order_id_foreign` (`sales_order_id`),
  ADD KEY `sales_order_items_product_id_foreign` (`product_id`),
  ADD KEY `sales_order_items_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `sales_order_status_histories`
--
ALTER TABLE `sales_order_status_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_order_status_histories_sales_order_id_foreign` (`sales_order_id`),
  ADD KEY `sales_order_status_histories_changed_by_foreign` (`changed_by`);

--
-- Indexes for table `sales_returns`
--
ALTER TABLE `sales_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sales_return_warehouse_number_unique` (`warehouse_id`,`return_number`),
  ADD KEY `sales_returns_customer_id_foreign` (`customer_id`),
  ADD KEY `sales_returns_sales_invoice_id_foreign` (`sales_invoice_id`),
  ADD KEY `sales_returns_created_by_foreign` (`created_by`);

--
-- Indexes for table `sales_return_items`
--
ALTER TABLE `sales_return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sales_return_items_sales_return_id_foreign` (`sales_return_id`),
  ADD KEY `sales_return_items_sales_invoice_item_id_foreign` (`sales_invoice_item_id`),
  ADD KEY `sales_return_items_product_id_foreign` (`product_id`),
  ADD KEY `sales_return_items_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_adjustment_number_unique` (`warehouse_id`,`adjustment_number`),
  ADD KEY `stock_adjustments_created_by_foreign` (`created_by`),
  ADD KEY `stock_adjustments_approved_by_foreign` (`approved_by`);

--
-- Indexes for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `adjustment_product_unique` (`stock_adjustment_id`,`product_id`),
  ADD KEY `stock_adjustment_items_product_id_foreign` (`product_id`),
  ADD KEY `stock_adjustment_items_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `stock_balances`
--
ALTER TABLE `stock_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_balance_warehouse_product_unique` (`warehouse_id`,`product_id`),
  ADD KEY `stock_balances_product_id_foreign` (`product_id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_movements_product_id_foreign` (`product_id`),
  ADD KEY `stock_movements_unit_id_foreign` (`unit_id`),
  ADD KEY `stock_movements_created_by_foreign` (`created_by`),
  ADD KEY `stock_movement_reference_index` (`reference_type`,`reference_id`),
  ADD KEY `stock_movement_product_history_index` (`warehouse_id`,`product_id`,`created_at`);

--
-- Indexes for table `stock_reservations`
--
ALTER TABLE `stock_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_reservations_product_id_foreign` (`product_id`),
  ADD KEY `stock_reservations_created_by_foreign` (`created_by`),
  ADD KEY `stock_reservation_reference_index` (`reference_type`,`reference_id`),
  ADD KEY `stock_reservation_product_status_index` (`warehouse_id`,`product_id`,`status`);

--
-- Indexes for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_transfer_number_unique` (`source_warehouse_id`,`transfer_number`),
  ADD KEY `stock_transfers_destination_warehouse_id_foreign` (`destination_warehouse_id`),
  ADD KEY `stock_transfers_created_by_foreign` (`created_by`),
  ADD KEY `stock_transfers_dispatched_by_foreign` (`dispatched_by`),
  ADD KEY `stock_transfers_received_by_foreign` (`received_by`);

--
-- Indexes for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transfer_product_unique` (`stock_transfer_id`,`product_id`),
  ADD KEY `stock_transfer_items_product_id_foreign` (`product_id`),
  ADD KEY `stock_transfer_items_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supplier_company_code_unique` (`company_id`,`supplier_code`);

--
-- Indexes for table `supplier_addresses`
--
ALTER TABLE `supplier_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_addresses_supplier_id_foreign` (`supplier_id`);

--
-- Indexes for table `supplier_invoices`
--
ALTER TABLE `supplier_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supplier_invoice_number_unique` (`branch_id`,`invoice_number`),
  ADD KEY `supplier_invoices_supplier_id_foreign` (`supplier_id`),
  ADD KEY `supplier_invoices_purchase_order_id_foreign` (`purchase_order_id`),
  ADD KEY `supplier_invoices_goods_receipt_id_foreign` (`goods_receipt_id`),
  ADD KEY `supplier_invoices_created_by_foreign` (`created_by`);

--
-- Indexes for table `supplier_invoice_items`
--
ALTER TABLE `supplier_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_invoice_items_supplier_invoice_id_foreign` (`supplier_invoice_id`),
  ADD KEY `supplier_invoice_items_product_id_foreign` (`product_id`),
  ADD KEY `supplier_invoice_items_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `supplier_payments`
--
ALTER TABLE `supplier_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supplier_payment_branch_number_unique` (`branch_id`,`payment_number`),
  ADD KEY `supplier_payments_supplier_id_foreign` (`supplier_id`),
  ADD KEY `supplier_payments_cash_bank_account_id_foreign` (`cash_bank_account_id`),
  ADD KEY `supplier_payments_payment_method_id_foreign` (`payment_method_id`),
  ADD KEY `supplier_payments_created_by_foreign` (`created_by`);

--
-- Indexes for table `supplier_payment_allocations`
--
ALTER TABLE `supplier_payment_allocations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supplier_payment_invoice_unique` (`supplier_payment_id`,`supplier_invoice_id`),
  ADD KEY `supplier_payment_allocations_supplier_invoice_id_foreign` (`supplier_invoice_id`);

--
-- Indexes for table `supplier_payment_trade_ins`
--
ALTER TABLE `supplier_payment_trade_ins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_payment_trade_ins_supplier_payment_id_foreign` (`supplier_payment_id`),
  ADD KEY `supplier_payment_trade_ins_product_id_foreign` (`product_id`),
  ADD KEY `supplier_payment_trade_ins_warehouse_id_foreign` (`warehouse_id`),
  ADD KEY `supplier_payment_trade_ins_unit_id_foreign` (`unit_id`);

--
-- Indexes for table `supplier_product_prices`
--
ALTER TABLE `supplier_product_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_product_prices_product_unit_id_foreign` (`product_unit_id`),
  ADD KEY `supplier_product_price_lookup_index` (`supplier_id`,`product_unit_id`,`status`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `units_company_id_code_unique` (`company_id`,`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_branch`
--
ALTER TABLE `user_branch`
  ADD PRIMARY KEY (`user_id`,`branch_id`),
  ADD KEY `user_branch_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `warehouses_branch_name_unique` (`branch_id`,`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounting_periods`
--
ALTER TABLE `accounting_periods`
  ADD CONSTRAINT `accounting_periods_fiscal_year_id_foreign` FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `branches_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `brands`
--
ALTER TABLE `brands`
  ADD CONSTRAINT `brands_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cash_bank_accounts`
--
ALTER TABLE `cash_bank_accounts`
  ADD CONSTRAINT `cash_bank_accounts_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cash_bank_accounts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cash_bank_transactions`
--
ALTER TABLE `cash_bank_transactions`
  ADD CONSTRAINT `cash_bank_transactions_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cash_bank_transactions_cash_bank_account_id_foreign` FOREIGN KEY (`cash_bank_account_id`) REFERENCES `cash_bank_accounts` (`id`),
  ADD CONSTRAINT `cash_bank_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  ADD CONSTRAINT `chart_of_accounts_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chart_of_accounts_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `chart_of_accounts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD CONSTRAINT `customer_addresses_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_payments`
--
ALTER TABLE `customer_payments`
  ADD CONSTRAINT `customer_payments_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `customer_payments_cash_bank_account_id_foreign` FOREIGN KEY (`cash_bank_account_id`) REFERENCES `cash_bank_accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customer_payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `customer_payments_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customer_payment_allocations`
--
ALTER TABLE `customer_payment_allocations`
  ADD CONSTRAINT `customer_payment_allocations_customer_payment_id_foreign` FOREIGN KEY (`customer_payment_id`) REFERENCES `customer_payments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_payment_allocations_sales_invoice_id_foreign` FOREIGN KEY (`sales_invoice_id`) REFERENCES `sales_invoices` (`id`);

--
-- Constraints for table `customer_prices`
--
ALTER TABLE `customer_prices`
  ADD CONSTRAINT `customer_prices_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_prices_product_unit_id_foreign` FOREIGN KEY (`product_unit_id`) REFERENCES `product_units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deliveries_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`),
  ADD CONSTRAINT `deliveries_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `delivery_invoice`
--
ALTER TABLE `delivery_invoice`
  ADD CONSTRAINT `delivery_invoice_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delivery_invoice_sales_invoice_id_foreign` FOREIGN KEY (`sales_invoice_id`) REFERENCES `sales_invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_items`
--
ALTER TABLE `delivery_items`
  ADD CONSTRAINT `delivery_items_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delivery_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `delivery_items_sales_order_item_id_foreign` FOREIGN KEY (`sales_order_item_id`) REFERENCES `sales_order_items` (`id`),
  ADD CONSTRAINT `delivery_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `delivery_status_histories`
--
ALTER TABLE `delivery_status_histories`
  ADD CONSTRAINT `delivery_status_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `delivery_status_histories_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `designations`
--
ALTER TABLE `designations`
  ADD CONSTRAINT `designations_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employees_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_designation_id_foreign` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_branch_histories`
--
ALTER TABLE `employee_branch_histories`
  ADD CONSTRAINT `employee_branch_histories_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `employee_branch_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employee_branch_histories_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_documents`
--
ALTER TABLE `employee_documents`
  ADD CONSTRAINT `employee_documents_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employee_emergency_contacts`
--
ALTER TABLE `employee_emergency_contacts`
  ADD CONSTRAINT `employee_emergency_contacts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_employment_histories`
--
ALTER TABLE `employee_employment_histories`
  ADD CONSTRAINT `employee_employment_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employee_employment_histories_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employee_employment_histories_designation_id_foreign` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employee_employment_histories_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_invitations`
--
ALTER TABLE `employee_invitations`
  ADD CONSTRAINT `employee_invitations_accepted_user_id_foreign` FOREIGN KEY (`accepted_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employee_invitations_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_invitations_invited_by_foreign` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `employee_status_histories`
--
ALTER TABLE `employee_status_histories`
  ADD CONSTRAINT `employee_status_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employee_status_histories_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `expenses_cash_bank_account_id_foreign` FOREIGN KEY (`cash_bank_account_id`) REFERENCES `cash_bank_accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `expenses_expense_category_id_foreign` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`),
  ADD CONSTRAINT `expenses_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD CONSTRAINT `expense_categories_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fiscal_years`
--
ALTER TABLE `fiscal_years`
  ADD CONSTRAINT `fiscal_years_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `goods_receipts`
--
ALTER TABLE `goods_receipts`
  ADD CONSTRAINT `goods_receipts_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`),
  ADD CONSTRAINT `goods_receipts_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `goods_receipts_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `goods_receipt_items`
--
ALTER TABLE `goods_receipt_items`
  ADD CONSTRAINT `goods_receipt_items_goods_receipt_id_foreign` FOREIGN KEY (`goods_receipt_id`) REFERENCES `goods_receipts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `goods_receipt_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `goods_receipt_items_purchase_order_item_id_foreign` FOREIGN KEY (`purchase_order_item_id`) REFERENCES `purchase_order_items` (`id`),
  ADD CONSTRAINT `goods_receipt_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD CONSTRAINT `journal_entries_accounting_period_id_foreign` FOREIGN KEY (`accounting_period_id`) REFERENCES `accounting_periods` (`id`),
  ADD CONSTRAINT `journal_entries_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `journal_entries_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `journal_entries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `journal_entries_fiscal_year_id_foreign` FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years` (`id`),
  ADD CONSTRAINT `journal_entries_posted_by_foreign` FOREIGN KEY (`posted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `journal_entry_lines`
--
ALTER TABLE `journal_entry_lines`
  ADD CONSTRAINT `journal_entry_lines_chart_of_account_id_foreign` FOREIGN KEY (`chart_of_account_id`) REFERENCES `chart_of_accounts` (`id`),
  ADD CONSTRAINT `journal_entry_lines_journal_entry_id_foreign` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_prices`
--
ALTER TABLE `product_prices`
  ADD CONSTRAINT `product_prices_product_unit_id_foreign` FOREIGN KEY (`product_unit_id`) REFERENCES `product_units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_units`
--
ALTER TABLE `product_units`
  ADD CONSTRAINT `product_units_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_units_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_orders_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `purchase_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_orders_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_orders_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `purchase_order_items_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_items_purchase_request_item_id_foreign` FOREIGN KEY (`purchase_request_item_id`) REFERENCES `purchase_request_items` (`id`),
  ADD CONSTRAINT `purchase_order_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD CONSTRAINT `purchase_requests_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_requests_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `purchase_requests_canceled_by_foreign` FOREIGN KEY (`canceled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_requests_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_requests_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  ADD CONSTRAINT `purchase_request_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `purchase_request_items_purchase_request_id_foreign` FOREIGN KEY (`purchase_request_id`) REFERENCES `purchase_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_request_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD CONSTRAINT `purchase_returns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_returns_goods_receipt_id_foreign` FOREIGN KEY (`goods_receipt_id`) REFERENCES `goods_receipts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_returns_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_returns_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `purchase_return_items`
--
ALTER TABLE `purchase_return_items`
  ADD CONSTRAINT `purchase_return_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `purchase_return_items_purchase_return_id_foreign` FOREIGN KEY (`purchase_return_id`) REFERENCES `purchase_returns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_return_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `quotations_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `quotations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quotations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `quotations_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD CONSTRAINT `quotation_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `quotation_items_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quotation_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_invoices`
--
ALTER TABLE `sales_invoices`
  ADD CONSTRAINT `sales_invoices_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `sales_invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_invoices_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `sales_invoices_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sales_invoice_items`
--
ALTER TABLE `sales_invoice_items`
  ADD CONSTRAINT `sales_invoice_items_delivery_item_id_foreign` FOREIGN KEY (`delivery_item_id`) REFERENCES `delivery_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_invoice_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `sales_invoice_items_sales_invoice_id_foreign` FOREIGN KEY (`sales_invoice_id`) REFERENCES `sales_invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_invoice_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD CONSTRAINT `sales_orders_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_orders_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `sales_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `sales_orders_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_orders_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `sales_order_items`
--
ALTER TABLE `sales_order_items`
  ADD CONSTRAINT `sales_order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `sales_order_items_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_order_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `sales_order_status_histories`
--
ALTER TABLE `sales_order_status_histories`
  ADD CONSTRAINT `sales_order_status_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_order_status_histories_sales_order_id_foreign` FOREIGN KEY (`sales_order_id`) REFERENCES `sales_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_returns`
--
ALTER TABLE `sales_returns`
  ADD CONSTRAINT `sales_returns_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_returns_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `sales_returns_sales_invoice_id_foreign` FOREIGN KEY (`sales_invoice_id`) REFERENCES `sales_invoices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_returns_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `sales_return_items`
--
ALTER TABLE `sales_return_items`
  ADD CONSTRAINT `sales_return_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `sales_return_items_sales_invoice_item_id_foreign` FOREIGN KEY (`sales_invoice_item_id`) REFERENCES `sales_invoice_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_return_items_sales_return_id_foreign` FOREIGN KEY (`sales_return_id`) REFERENCES `sales_returns` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_return_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD CONSTRAINT `stock_adjustments_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_adjustments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_adjustments_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `stock_adjustment_items`
--
ALTER TABLE `stock_adjustment_items`
  ADD CONSTRAINT `stock_adjustment_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_adjustment_items_stock_adjustment_id_foreign` FOREIGN KEY (`stock_adjustment_id`) REFERENCES `stock_adjustments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_adjustment_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `stock_balances`
--
ALTER TABLE `stock_balances`
  ADD CONSTRAINT `stock_balances_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_balances_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_movements_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  ADD CONSTRAINT `stock_movements_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `stock_reservations`
--
ALTER TABLE `stock_reservations`
  ADD CONSTRAINT `stock_reservations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_reservations_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_reservations_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD CONSTRAINT `stock_transfers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_transfers_destination_warehouse_id_foreign` FOREIGN KEY (`destination_warehouse_id`) REFERENCES `warehouses` (`id`),
  ADD CONSTRAINT `stock_transfers_dispatched_by_foreign` FOREIGN KEY (`dispatched_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_transfers_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_transfers_source_warehouse_id_foreign` FOREIGN KEY (`source_warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  ADD CONSTRAINT `stock_transfer_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_transfer_items_stock_transfer_id_foreign` FOREIGN KEY (`stock_transfer_id`) REFERENCES `stock_transfers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_transfer_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `suppliers_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_addresses`
--
ALTER TABLE `supplier_addresses`
  ADD CONSTRAINT `supplier_addresses_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_invoices`
--
ALTER TABLE `supplier_invoices`
  ADD CONSTRAINT `supplier_invoices_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `supplier_invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supplier_invoices_goods_receipt_id_foreign` FOREIGN KEY (`goods_receipt_id`) REFERENCES `goods_receipts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supplier_invoices_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supplier_invoices_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `supplier_invoice_items`
--
ALTER TABLE `supplier_invoice_items`
  ADD CONSTRAINT `supplier_invoice_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `supplier_invoice_items_supplier_invoice_id_foreign` FOREIGN KEY (`supplier_invoice_id`) REFERENCES `supplier_invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplier_invoice_items_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

--
-- Constraints for table `supplier_payments`
--
ALTER TABLE `supplier_payments`
  ADD CONSTRAINT `supplier_payments_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`),
  ADD CONSTRAINT `supplier_payments_cash_bank_account_id_foreign` FOREIGN KEY (`cash_bank_account_id`) REFERENCES `cash_bank_accounts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supplier_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supplier_payments_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supplier_payments_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Constraints for table `supplier_payment_allocations`
--
ALTER TABLE `supplier_payment_allocations`
  ADD CONSTRAINT `supplier_payment_allocations_supplier_invoice_id_foreign` FOREIGN KEY (`supplier_invoice_id`) REFERENCES `supplier_invoices` (`id`),
  ADD CONSTRAINT `supplier_payment_allocations_supplier_payment_id_foreign` FOREIGN KEY (`supplier_payment_id`) REFERENCES `supplier_payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_payment_trade_ins`
--
ALTER TABLE `supplier_payment_trade_ins`
  ADD CONSTRAINT `supplier_payment_trade_ins_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `supplier_payment_trade_ins_supplier_payment_id_foreign` FOREIGN KEY (`supplier_payment_id`) REFERENCES `supplier_payments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplier_payment_trade_ins_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  ADD CONSTRAINT `supplier_payment_trade_ins_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `supplier_product_prices`
--
ALTER TABLE `supplier_product_prices`
  ADD CONSTRAINT `supplier_product_prices_product_unit_id_foreign` FOREIGN KEY (`product_unit_id`) REFERENCES `product_units` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplier_product_prices_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `units`
--
ALTER TABLE `units`
  ADD CONSTRAINT `units_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_branch`
--
ALTER TABLE `user_branch`
  ADD CONSTRAINT `user_branch_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_branch_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD CONSTRAINT `warehouses_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
