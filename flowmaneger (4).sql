-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 03/04/2026 às 16:37
-- Versão do servidor: 9.2.0
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `flowmaneger`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `achievements`
--

CREATE TABLE `achievements` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rarity` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int NOT NULL DEFAULT '0',
  `criteria` json DEFAULT NULL,
  `is_secret` tinyint(1) NOT NULL DEFAULT '0',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `banks`
--

CREATE TABLE `banks` (
  `id_bank` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `caminho_icone` varchar(255) DEFAULT NULL COMMENT 'Caminho para o icone/logo do banco',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` bigint UNSIGNED NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cashbook`
--

CREATE TABLE `cashbook` (
  `id` int NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `is_pending` tinyint(1) NOT NULL DEFAULT '0',
  `attachment` varchar(255) DEFAULT NULL,
  `inc_datetime` datetime DEFAULT NULL COMMENT 'insert date',
  `edit_datetime` datetime DEFAULT NULL COMMENT 'edit date',
  `user_id` bigint UNSIGNED NOT NULL,
  `category_id` int NOT NULL,
  `type_id` int NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `segment_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_bank` int DEFAULT NULL,
  `client_id` int DEFAULT NULL,
  `cofrinho_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cashbook_uploads_history`
--

CREATE TABLE `cashbook_uploads_history` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `cofrinho_id` int DEFAULT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint DEFAULT NULL,
  `total_transactions` int NOT NULL DEFAULT '0',
  `transactions_created` int NOT NULL DEFAULT '0',
  `transactions_skipped` int NOT NULL DEFAULT '0',
  `status` enum('pending','processing','completed','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `category`
--

CREATE TABLE `category` (
  `id_category` int NOT NULL,
  `parent_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `desc_category` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `hexcolor_category` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `icone` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `descricao_detalhada` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `tipo` enum('gasto','receita','ambos') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT 'ambos',
  `limite_orcamento` decimal(10,2) DEFAULT NULL,
  `compartilhavel` tinyint(1) DEFAULT '0',
  `tags` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `regras_auto_categorizacao` json DEFAULT NULL,
  `id_bank` int DEFAULT NULL,
  `id_clients` int DEFAULT NULL,
  `id_produtos_clientes` int DEFAULT NULL,
  `historico_alteracoes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `is_active` int NOT NULL DEFAULT '1',
  `sort_order` int DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` enum('product','transaction') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'product',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='transaction';

-- --------------------------------------------------------

--
-- Estrutura para tabela `clients`
--

CREATE TABLE `clients` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(16) DEFAULT NULL,
  `address` text,
  `caminho_foto` varchar(255) DEFAULT NULL COMMENT 'Caminho para a foto do cliente',
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cofrinhos`
--

CREATE TABLE `cofrinhos` (
  `id` int NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_valor` decimal(10,2) DEFAULT NULL,
  `status` enum('ativo','arquivado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ativo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `icone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'fa-piggy-bank'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `consortiums`
--

CREATE TABLE `consortiums` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `monthly_value` decimal(10,2) NOT NULL,
  `duration_months` int NOT NULL,
  `total_value` decimal(10,2) NOT NULL,
  `max_participants` int NOT NULL DEFAULT '100',
  `start_date` date NOT NULL,
  `status` enum('active','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `draw_frequency` enum('monthly','bimonthly','weekly') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'monthly',
  `mode` enum('draw','payoff') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draw',
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `consortium_contemplations`
--

CREATE TABLE `consortium_contemplations` (
  `id` bigint UNSIGNED NOT NULL,
  `consortium_participant_id` bigint UNSIGNED NOT NULL,
  `draw_id` bigint UNSIGNED DEFAULT NULL,
  `contemplation_type` enum('draw','bid','payoff') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draw',
  `contemplation_date` datetime NOT NULL,
  `redemption_type` enum('cash','products','pending') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `redemption_value` decimal(10,2) DEFAULT NULL,
  `redemption_date` date DEFAULT NULL,
  `products` json DEFAULT NULL,
  `status` enum('pending','redeemed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `consortium_draws`
--

CREATE TABLE `consortium_draws` (
  `id` bigint UNSIGNED NOT NULL,
  `consortium_id` bigint UNSIGNED NOT NULL,
  `draw_date` datetime NOT NULL,
  `draw_number` int NOT NULL,
  `winner_participant_id` bigint UNSIGNED DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `consortium_notifications`
--

CREATE TABLE `consortium_notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `module` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'consortium' COMMENT 'Módulo da notificação: consortium, sale, payment, client, etc',
  `consortium_id` bigint UNSIGNED DEFAULT NULL,
  `entity_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo da entidade relacionada (Consortium, Sale, Payment, Client, etc)',
  `entity_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID da entidade relacionada',
  `user_id` bigint UNSIGNED NOT NULL,
  `related_participant_id` bigint UNSIGNED DEFAULT NULL,
  `type` enum('draw_available','redemption_pending','sale_pending','sale_completed','payment_overdue','payment_received','client_new','client_birthday','system_backup','system_update','system_error') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `priority` enum('low','medium','high') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `action_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `consortium_participants`
--

CREATE TABLE `consortium_participants` (
  `id` bigint UNSIGNED NOT NULL,
  `consortium_id` bigint UNSIGNED NOT NULL,
  `client_id` int NOT NULL,
  `participation_number` int NOT NULL,
  `entry_date` date NOT NULL,
  `status` enum('active','contemplated','quit','defaulter') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `total_paid` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_contemplated` tinyint(1) NOT NULL DEFAULT '0',
  `contemplation_date` date DEFAULT NULL,
  `contemplation_type` enum('draw','bid','payoff') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `consortium_payments`
--

CREATE TABLE `consortium_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `consortium_participant_id` bigint UNSIGNED NOT NULL,
  `reference_month` int NOT NULL,
  `reference_year` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','paid','late','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `daily_habits`
--

CREATE TABLE `daily_habits` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bi-check-circle',
  `color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#3B82F6',
  `goal_frequency` int NOT NULL DEFAULT '1',
  `reminder_time` time DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `daily_habit_completions`
--

CREATE TABLE `daily_habit_completions` (
  `id` bigint UNSIGNED NOT NULL,
  `habit_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `completion_date` date NOT NULL,
  `times_completed` int NOT NULL DEFAULT '1',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `daily_habit_streaks`
--

CREATE TABLE `daily_habit_streaks` (
  `id` bigint UNSIGNED NOT NULL,
  `habit_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `current_streak` int NOT NULL DEFAULT '0',
  `longest_streak` int NOT NULL DEFAULT '0',
  `last_completion_date` date DEFAULT NULL,
  `total_completions` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `goals`
--

CREATE TABLE `goals` (
  `id` bigint UNSIGNED NOT NULL,
  `list_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `periodo` enum('diario','semanal','mensal','trimestral','semestral','anual','custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'custom',
  `recorrencia_dia` int DEFAULT NULL,
  `prioridade` enum('baixa','media','alta','urgente') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'media',
  `data_inicio` date DEFAULT NULL,
  `data_vencimento` date DEFAULT NULL,
  `progresso` decimal(5,2) NOT NULL DEFAULT '0.00',
  `valor_meta` decimal(10,2) DEFAULT NULL,
  `valor_atual` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cofrinho_id` int DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `cor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#6366F1',
  `labels` json DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `is_archived` tinyint(1) NOT NULL DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `goal_activities`
--

CREATE TABLE `goal_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `goal_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `action` enum('created','moved','updated','completed','commented','archived','deleted','checklist_added','attachment_added') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'updated',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_value` json DEFAULT NULL,
  `new_value` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `goal_attachments`
--

CREATE TABLE `goal_attachments` (
  `id` bigint UNSIGNED NOT NULL,
  `goal_id` bigint UNSIGNED NOT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `goal_boards`
--

CREATE TABLE `goal_boards` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tipo` enum('pessoal','financeiro','profissional','saude','estudos','outro') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pessoal',
  `background_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#0079BF',
  `background_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_favorite` tinyint(1) NOT NULL DEFAULT '0',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `goal_checklists`
--

CREATE TABLE `goal_checklists` (
  `id` bigint UNSIGNED NOT NULL,
  `goal_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `goal_checklist_items`
--

CREATE TABLE `goal_checklist_items` (
  `id` bigint UNSIGNED NOT NULL,
  `checklist_id` bigint UNSIGNED NOT NULL,
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT '0',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `goal_comments`
--

CREATE TABLE `goal_comments` (
  `id` bigint UNSIGNED NOT NULL,
  `goal_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `goal_habit`
--

CREATE TABLE `goal_habit` (
  `id` bigint UNSIGNED NOT NULL,
  `goal_id` bigint UNSIGNED NOT NULL,
  `daily_habit_id` bigint UNSIGNED NOT NULL,
  `peso` decimal(5,2) NOT NULL DEFAULT '1.00' COMMENT 'Peso do hábito no cálculo de progresso da meta',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `goal_lists`
--

CREATE TABLE `goal_lists` (
  `id` bigint UNSIGNED NOT NULL,
  `board_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#344563',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `invoice`
--

CREATE TABLE `invoice` (
  `id_invoice` int NOT NULL,
  `id_bank` int NOT NULL,
  `description` varchar(255) NOT NULL,
  `installments` varchar(255) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `invoice_date` date NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `category_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `client_id` int DEFAULT NULL,
  `dividida` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `invoice_category_learning`
--

CREATE TABLE `invoice_category_learning` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `description_pattern` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int NOT NULL,
  `confidence` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `invoice_uploads_history`
--

CREATE TABLE `invoice_uploads_history` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `bank_id` int NOT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_transactions` int NOT NULL DEFAULT '0',
  `transactions_created` int NOT NULL DEFAULT '0',
  `transactions_updated` int NOT NULL DEFAULT '0',
  `transactions_skipped` int NOT NULL DEFAULT '0',
  `status` enum('processing','completed','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'processing',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `summary` json DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `lancamentos_recorrentes`
--

CREATE TABLE `lancamentos_recorrentes` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `descricao` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `type_id` int NOT NULL COMMENT 'FK para a tabela type (receita/despesa)',
  `category_id` int NOT NULL,
  `frequencia` enum('diaria','semanal','mensal','anual') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_inicio` date NOT NULL,
  `proximo_vencimento` date NOT NULL COMMENT 'Controla a proxima vez que deve ser lancado',
  `data_fim` date DEFAULT NULL COMMENT 'Opcional: data para parar a recorrencia',
  `ativo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `mercadolivre_orders`
--

CREATE TABLE `mercadolivre_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `ml_order_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID do pedido no ML',
  `ml_item_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID do produto no ML',
  `product_id` int UNSIGNED DEFAULT NULL COMMENT 'ID do produto no sistema (products.id é unsigned int)',
  `buyer_id` bigint NOT NULL COMMENT 'ID do comprador no ML',
  `buyer_nickname` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_address` text COLLATE utf8mb4_unicode_ci COMMENT 'Endereço de entrega JSON',
  `quantity` int NOT NULL COMMENT 'Quantidade comprada',
  `unit_price` decimal(10,2) NOT NULL COMMENT 'Preço unitário',
  `total_amount` decimal(10,2) NOT NULL COMMENT 'Valor total do pedido',
  `currency_id` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BRL',
  `order_status` enum('pending','paid','confirmed','ready_to_ship','shipped','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Status do pedido',
  `payment_status` enum('pending','approved','in_process','rejected','cancelled','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Status do pagamento',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Método de pagamento',
  `payment_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tipo de pagamento (credit_card, pix, etc)',
  `shipping_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID do envio no ML',
  `tracking_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Código de rastreamento',
  `shipping_method` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Método de envio',
  `shipping_cost` decimal(10,2) DEFAULT NULL COMMENT 'Custo do frete',
  `date_created` timestamp NOT NULL COMMENT 'Data de criação no ML',
  `date_closed` timestamp NULL DEFAULT NULL COMMENT 'Data de fechamento no ML',
  `date_last_updated` timestamp NULL DEFAULT NULL COMMENT 'Última atualização no ML',
  `imported_to_sale_id` int UNSIGNED DEFAULT NULL COMMENT 'ID da venda importada no sistema',
  `sync_status` enum('pending','imported','error') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `raw_data` json DEFAULT NULL COMMENT 'Dados completos da API ML',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `mercadolivre_products`
--

CREATE TABLE `mercadolivre_products` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `ml_item_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID do anúncio no Mercado Livre',
  `ml_category_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID da categoria MLB',
  `listing_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gold_special' COMMENT 'Tipo de anúncio: free, bronze, silver, gold',
  `status` enum('active','paused','closed','under_review','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Status do anúncio no ML',
  `ml_permalink` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Link permanente do anúncio no ML',
  `sync_status` enum('synced','pending','error') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `last_sync_at` timestamp NULL DEFAULT NULL COMMENT 'Última sincronização bem-sucedida',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Mensagem de erro na última tentativa',
  `ml_attributes` json DEFAULT NULL COMMENT 'Atributos específicos da categoria MLB',
  `ml_price` decimal(10,2) DEFAULT NULL COMMENT 'Preço publicado no ML',
  `ml_quantity` int DEFAULT NULL COMMENT 'Quantidade publicada no ML',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `mercadolivre_sync_log`
--

CREATE TABLE `mercadolivre_sync_log` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `sync_type` enum('stock','price','product','order','status','full') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de sincronização realizada',
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Product, Order, etc',
  `entity_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID da entidade',
  `action` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'create, update, delete, sync, import',
  `status` enum('success','error','warning') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Resultado da operação',
  `message` text COLLATE utf8mb4_unicode_ci COMMENT 'Mensagem descritiva',
  `request_data` json DEFAULT NULL COMMENT 'Dados enviados para API',
  `response_data` json DEFAULT NULL COMMENT 'Resposta da API',
  `http_status` int DEFAULT NULL COMMENT 'Status HTTP da resposta',
  `execution_time` int DEFAULT NULL COMMENT 'Tempo de execução em milissegundos',
  `api_calls_remaining` int DEFAULT NULL COMMENT 'Chamadas restantes da API',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `mercadolivre_tokens`
--

CREATE TABLE `mercadolivre_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `ml_user_id` bigint DEFAULT NULL COMMENT 'ID do usuário no Mercado Livre',
  `access_token` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Token de acesso',
  `refresh_token` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Token de renovação',
  `token_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Bearer',
  `expires_at` timestamp NOT NULL COMMENT 'Data/hora de expiração do token',
  `scope` text COLLATE utf8mb4_unicode_ci COMMENT 'Permissões do token',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Token está ativo',
  `ml_nickname` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nome do usuário no ML',
  `user_info` json DEFAULT NULL COMMENT 'Informações do usuário ML',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `mercadolivre_webhooks`
--

CREATE TABLE `mercadolivre_webhooks` (
  `id` bigint UNSIGNED NOT NULL,
  `topic` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'orders, items, questions, claims, shipments',
  `resource` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL do recurso afetado',
  `ml_user_id` bigint NOT NULL COMMENT 'ID do usuário ML',
  `application_id` bigint NOT NULL COMMENT 'ID da aplicação',
  `attempts` int NOT NULL DEFAULT '0' COMMENT 'Tentativas de processamento',
  `sent` timestamp NOT NULL COMMENT 'Quando o ML enviou o webhook',
  `received_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Quando recebemos o webhook',
  `processed` tinyint(1) NOT NULL DEFAULT '0',
  `processed_at` timestamp NULL DEFAULT NULL,
  `raw_data` json NOT NULL COMMENT 'Payload completo do webhook',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `http_status` int DEFAULT NULL COMMENT 'Status HTTP ao buscar resource',
  `processing_result` json DEFAULT NULL COMMENT 'Resultado do processamento'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ml_publications`
--

CREATE TABLE `ml_publications` (
  `id` bigint UNSIGNED NOT NULL,
  `ml_item_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID único da publicação no ML',
  `ml_category_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Categoria ML (MLB1051, etc)',
  `ml_permalink` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL da publicação no ML',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Título do anúncio',
  `description` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Descrição completa',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Preço de venda',
  `available_quantity` int NOT NULL DEFAULT '0' COMMENT 'Quantidade disponível',
  `publication_type` enum('simple','kit') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'simple' COMMENT 'Simples ou Kit',
  `listing_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gold_special' COMMENT 'Tipo de anúncio (gold_special, gold_pro, gold, free)',
  `condition` enum('new','used') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new' COMMENT 'Novo ou usado',
  `warranty` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Garantia do produto',
  `free_shipping` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Frete grátis?',
  `local_pickup` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Retirada local?',
  `status` enum('pending','active','paused','closed','under_review') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Status da publicação no ML',
  `sync_status` enum('pending','synced','error') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Status de sincronização com ML',
  `last_sync_at` timestamp NULL DEFAULT NULL COMMENT 'Última sincronização com ML',
  `error_message` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Mensagem de erro se houver',
  `ml_attributes` json DEFAULT NULL COMMENT 'Atributos específicos da categoria',
  `pictures` json DEFAULT NULL COMMENT 'URLs das imagens',
  `user_id` bigint UNSIGNED NOT NULL COMMENT 'Usuário proprietário',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ml_publication_products`
--

CREATE TABLE `ml_publication_products` (
  `id` bigint UNSIGNED NOT NULL,
  `ml_publication_id` bigint UNSIGNED NOT NULL COMMENT 'Publicação ML',
  `product_id` int UNSIGNED NOT NULL COMMENT 'Produto do sistema',
  `quantity` int NOT NULL DEFAULT '1' COMMENT 'Quantidade por venda (para kits)',
  `unit_cost` decimal(10,2) DEFAULT NULL COMMENT 'Snapshot do custo unitário',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT 'Ordem de apresentação',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `ml_stock_logs`
--

CREATE TABLE `ml_stock_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL COMMENT 'Produto afetado',
  `ml_publication_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Publicação ML relacionada (se aplicável)',
  `operation_type` enum('ml_sale','manual_update','import_excel','internal_sale','sync_to_ml','adjustment','return') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tipo de operação',
  `quantity_before` int NOT NULL COMMENT 'Quantidade antes',
  `quantity_after` int NOT NULL COMMENT 'Quantidade depois',
  `quantity_change` int NOT NULL COMMENT 'Delta (+/-)',
  `source` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Fonte da operação (ex: MercadoLivreWebhook, ProductController)',
  `ml_order_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID do pedido ML (se aplicável)',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Observações adicionais',
  `transaction_id` varchar(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'UUID da transação (agrupa operações atômicas)',
  `rolled_back` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Se a operação foi revertida',
  `user_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Usuário responsável (se manual)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `orcamentos`
--

CREATE TABLE `orcamentos` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `category_id` int NOT NULL,
  `valor` decimal(10,2) NOT NULL COMMENT 'Valor maximo orcado para o periodo',
  `mes` int NOT NULL COMMENT 'Mes (1-12)',
  `ano` int NOT NULL COMMENT 'Ano (ex: 2025)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `descricao_custos_adicionais` text,
  `price` decimal(10,2) NOT NULL,
  `price_sale` decimal(10,2) NOT NULL,
  `custos_adicionais` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Custos fixos extras para kits (mao de obra, etc)',
  `stock_quantity` int DEFAULT '0',
  `category_id` int NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `product_code` varchar(100) NOT NULL,
  `barcode` varchar(15) DEFAULT NULL COMMENT 'Código de barras EAN/GTIN para integração Mercado Livre',
  `brand` varchar(100) DEFAULT NULL COMMENT 'Marca do produto',
  `model` varchar(100) DEFAULT NULL COMMENT 'Modelo específico do produto',
  `warranty_months` int DEFAULT '3' COMMENT 'Meses de garantia do produto',
  `condition` enum('new','used') NOT NULL DEFAULT 'new' COMMENT 'Condição do produto: new (novo) ou used (usado)',
  `weight_grams` int UNSIGNED DEFAULT NULL COMMENT 'Peso do produto em gramas (obrigatório Shopee)',
  `length_cm` decimal(8,2) DEFAULT NULL COMMENT 'Comprimento do pacote em cm',
  `width_cm` decimal(8,2) DEFAULT NULL COMMENT 'Largura do pacote em cm',
  `height_cm` decimal(8,2) DEFAULT NULL COMMENT 'Altura do pacote em cm',
  `image` varchar(255) DEFAULT NULL,
  `status` enum('ativo','inativo','descontinuado') DEFAULT 'ativo',
  `tipo` enum('simples','kit') NOT NULL DEFAULT 'simples',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `product_category_learning`
--

CREATE TABLE `product_category_learning` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `product_name_pattern` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` int NOT NULL,
  `confidence` int NOT NULL DEFAULT '1',
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `product_uploads_history`
--

CREATE TABLE `product_uploads_history` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_products` int NOT NULL DEFAULT '0',
  `products_created` int NOT NULL DEFAULT '0',
  `products_updated` int NOT NULL DEFAULT '0',
  `products_skipped` int NOT NULL DEFAULT '0',
  `status` enum('processing','completed','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'processing',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `summary` json DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto_componentes`
--

CREATE TABLE `produto_componentes` (
  `id` bigint UNSIGNED NOT NULL,
  `kit_produto_id` int NOT NULL,
  `componente_produto_id` int NOT NULL,
  `quantidade` int NOT NULL COMMENT 'Quantidade do componente no kit',
  `preco_custo_unitario` decimal(10,2) NOT NULL COMMENT 'Preço de custo unitário do componente no momento da criação do kit',
  `preco_venda_unitario` decimal(10,2) NOT NULL COMMENT 'Preço de venda unitário do componente no momento da criação do kit',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sales`
--

CREATE TABLE `sales` (
  `id` int NOT NULL,
  `client_id` int NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pendente',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `payment_method` varchar(100) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `tipo_pagamento` enum('a_vista','parcelado') NOT NULL DEFAULT 'a_vista',
  `parcelas` int DEFAULT '1' COMMENT 'Numero de parcelas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int NOT NULL,
  `sale_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `price_sale` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sale_payments`
--

CREATE TABLE `sale_payments` (
  `id` int NOT NULL,
  `sale_id` int NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_method` varchar(75) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `payment_date` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `segment`
--

CREATE TABLE `segment` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `category_id` int DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `shopee_orders`
--

CREATE TABLE `shopee_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `shop_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Shop ID onde o pedido foi gerado',
  `shopee_order_sn` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Order SN da Shopee (identificador único)',
  `shopee_item_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shopee_model_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` json DEFAULT NULL,
  `order_items` json DEFAULT NULL COMMENT 'Array de itens do pedido',
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BRL',
  `order_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UNPAID, READY_TO_SHIP, SHIPPED, COMPLETED, CANCELLED',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_carrier` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `days_to_ship` tinyint UNSIGNED DEFAULT NULL,
  `ship_by_date` timestamp NULL DEFAULT NULL,
  `imported_to_sale_id` int DEFAULT NULL COMMENT 'Venda interna gerada a partir deste pedido',
  `sync_status` enum('pending','synced','error') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `raw_data` json DEFAULT NULL,
  `shopee_created_at` timestamp NULL DEFAULT NULL,
  `shopee_updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `shopee_publications`
--

CREATE TABLE `shopee_publications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `shop_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Shop ID da Shopee onde foi publicado',
  `shopee_item_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Item ID na Shopee (após publicação)',
  `shopee_category_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Categoria na Shopee',
  `shopee_permalink` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL do anúncio na Shopee',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Título do anúncio',
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(12,2) NOT NULL COMMENT 'Preço base na Shopee',
  `available_quantity` int UNSIGNED NOT NULL DEFAULT '0',
  `condition` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NEW' COMMENT 'NEW ou USED',
  `weight_grams` int UNSIGNED NOT NULL COMMENT 'Peso em gramas',
  `length_cm` decimal(8,2) DEFAULT NULL COMMENT 'Comprimento pacote (cm)',
  `width_cm` decimal(8,2) DEFAULT NULL COMMENT 'Largura pacote (cm)',
  `height_cm` decimal(8,2) DEFAULT NULL COMMENT 'Altura pacote (cm)',
  `days_to_ship` tinyint UNSIGNED NOT NULL DEFAULT '3' COMMENT 'Prazo de envio em dias (DTS)',
  `has_variations` tinyint(1) NOT NULL DEFAULT '0',
  `pictures` json DEFAULT NULL,
  `shopee_attributes` json DEFAULT NULL,
  `status` enum('draft','published','inactive','deleted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `sync_status` enum('pending','synced','error','updating') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `last_sync_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `shopee_publication_products`
--

CREATE TABLE `shopee_publication_products` (
  `id` bigint UNSIGNED NOT NULL,
  `shopee_publication_id` bigint UNSIGNED NOT NULL,
  `product_id` int NOT NULL,
  `shopee_model_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Model ID da variação na Shopee (hierarquia item → model)',
  `shopee_model_sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SKU do modelo na Shopee',
  `variation_attributes` json DEFAULT NULL COMMENT 'Ex: {"name":"Cor","value_name":"Azul"}',
  `quantity` int UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Quantidade deste produto na publicação',
  `unit_cost` decimal(12,2) DEFAULT NULL,
  `sort_order` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `shopee_sync_logs`
--

CREATE TABLE `shopee_sync_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `platform` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'shopee | mercadolivre | internal',
  `sync_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'stock_update | publish | order_import | auth',
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'product | publication | order',
  `entity_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'create | update | delete | sync',
  `status` enum('success','error','warning','info') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `message` text COLLATE utf8mb4_unicode_ci,
  `request_data` json DEFAULT NULL,
  `response_data` json DEFAULT NULL,
  `http_status` smallint UNSIGNED DEFAULT NULL,
  `execution_time_ms` int UNSIGNED DEFAULT NULL,
  `reference_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID externo de referência (shopee_order_sn, shopee_item_id, etc.)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `shopee_tokens`
--

CREATE TABLE `shopee_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `shop_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID da loja no Shopee (Shop ID)',
  `shop_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nome da loja no Shopee',
  `partner_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Partner ID da aplicação Shopee',
  `access_token` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Token de acesso',
  `refresh_token` text COLLATE utf8mb4_unicode_ci COMMENT 'Token de renovação',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Expiração do access_token',
  `refresh_expires_at` timestamp NULL DEFAULT NULL COMMENT 'Expiração do refresh_token',
  `region` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BR' COMMENT 'Região: BR, MX, etc.',
  `shop_info` json DEFAULT NULL COMMENT 'Informações completas da loja',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_refreshed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `targets`
--

CREATE TABLE `targets` (
  `id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default Title',
  `target_date` date DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` decimal(10,2) DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT '0',
  `user_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `type`
--

CREATE TABLE `type` (
  `id_type` int NOT NULL,
  `desc_type` varchar(45) NOT NULL,
  `hexcolor_type` varchar(45) DEFAULT NULL,
  `icon_type` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `about_me` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_picture` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `userscol` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `user_achievements`
--

CREATE TABLE `user_achievements` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `achievement_id` bigint UNSIGNED NOT NULL,
  `unlocked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `progress` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `venda_parcelas`
--

CREATE TABLE `venda_parcelas` (
  `id` bigint UNSIGNED NOT NULL,
  `sale_id` int NOT NULL,
  `numero_parcela` int NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_vencimento` date NOT NULL,
  `status` enum('pendente','paga','vencida') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendente',
  `pago_em` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `achievements_key_unique` (`key`);

--
-- Índices de tabela `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`id_bank`),
  ADD KEY `idx_banks_user_id` (`user_id`);

--
-- Índices de tabela `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Índices de tabela `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Índices de tabela `cashbook`
--
ALTER TABLE `cashbook`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `segment_id` (`segment_id`),
  ADD KEY `date` (`date`),
  ADD KEY `idx_cashbook_date` (`date`),
  ADD KEY `idx_cashbook_user_id` (`user_id`),
  ADD KEY `cashbook_fk_id_bank` (`id_bank`),
  ADD KEY `fk_cashbook_clients` (`client_id`),
  ADD KEY `cashbook_cofrinho_id_foreign` (`cofrinho_id`);

--
-- Índices de tabela `cashbook_uploads_history`
--
ALTER TABLE `cashbook_uploads_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cashbook_uploads_history_user_id_foreign` (`user_id`);

--
-- Índices de tabela `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id_category`),
  ADD KEY `idx_category_user_id` (`user_id`),
  ADD KEY `fk_category_banks` (`id_bank`),
  ADD KEY `fk_category_clients` (`id_clients`),
  ADD KEY `category_sort_order_index` (`sort_order`);

--
-- Índices de tabela `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_clients_user_id` (`user_id`),
  ADD KEY `idx_clients_name` (`name`);

--
-- Índices de tabela `cofrinhos`
--
ALTER TABLE `cofrinhos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Índices de tabela `consortiums`
--
ALTER TABLE `consortiums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `consortiums_user_id_foreign` (`user_id`);

--
-- Índices de tabela `consortium_contemplations`
--
ALTER TABLE `consortium_contemplations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `consortium_contemplations_consortium_participant_id_foreign` (`consortium_participant_id`),
  ADD KEY `consortium_contemplations_draw_id_foreign` (`draw_id`);

--
-- Índices de tabela `consortium_draws`
--
ALTER TABLE `consortium_draws`
  ADD PRIMARY KEY (`id`),
  ADD KEY `consortium_draws_consortium_id_foreign` (`consortium_id`),
  ADD KEY `consortium_draws_winner_participant_id_foreign` (`winner_participant_id`);

--
-- Índices de tabela `consortium_notifications`
--
ALTER TABLE `consortium_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `consortium_notifications_related_participant_id_foreign` (`related_participant_id`),
  ADD KEY `consortium_notifications_user_id_is_read_created_at_index` (`user_id`,`is_read`,`created_at`),
  ADD KEY `consortium_notifications_consortium_id_type_index` (`consortium_id`,`type`),
  ADD KEY `idx_module_read_created` (`module`,`is_read`,`created_at`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`);

--
-- Índices de tabela `consortium_participants`
--
ALTER TABLE `consortium_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `consortium_part_unique` (`consortium_id`,`participation_number`),
  ADD KEY `consortium_participants_client_id_foreign` (`client_id`);

--
-- Índices de tabela `consortium_payments`
--
ALTER TABLE `consortium_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `consortium_payments_consortium_participant_id_foreign` (`consortium_participant_id`);

--
-- Índices de tabela `daily_habits`
--
ALTER TABLE `daily_habits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `daily_habits_user_id_is_active_index` (`user_id`,`is_active`);

--
-- Índices de tabela `daily_habit_completions`
--
ALTER TABLE `daily_habit_completions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `daily_habit_completions_habit_id_completion_date_unique` (`habit_id`,`completion_date`),
  ADD KEY `daily_habit_completions_user_id_completion_date_index` (`user_id`,`completion_date`);

--
-- Índices de tabela `daily_habit_streaks`
--
ALTER TABLE `daily_habit_streaks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `daily_habit_streaks_habit_id_user_id_unique` (`habit_id`,`user_id`),
  ADD KEY `daily_habit_streaks_user_id_index` (`user_id`);

--
-- Índices de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Índices de tabela `goals`
--
ALTER TABLE `goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `goals_list_id_foreign` (`list_id`),
  ADD KEY `goals_user_id_foreign` (`user_id`),
  ADD KEY `goals_cofrinho_id_foreign` (`cofrinho_id`),
  ADD KEY `goals_category_id_foreign` (`category_id`);

--
-- Índices de tabela `goal_activities`
--
ALTER TABLE `goal_activities`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `goal_attachments`
--
ALTER TABLE `goal_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `goal_attachments_goal_id_foreign` (`goal_id`);

--
-- Índices de tabela `goal_boards`
--
ALTER TABLE `goal_boards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `goal_boards_user_id_foreign` (`user_id`);

--
-- Índices de tabela `goal_checklists`
--
ALTER TABLE `goal_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `goal_checklists_goal_id_foreign` (`goal_id`);

--
-- Índices de tabela `goal_checklist_items`
--
ALTER TABLE `goal_checklist_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `goal_checklist_items_checklist_id_foreign` (`checklist_id`);

--
-- Índices de tabela `goal_comments`
--
ALTER TABLE `goal_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `goal_comments_goal_id_foreign` (`goal_id`),
  ADD KEY `goal_comments_user_id_foreign` (`user_id`);

--
-- Índices de tabela `goal_habit`
--
ALTER TABLE `goal_habit`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `goal_habit_goal_id_daily_habit_id_unique` (`goal_id`,`daily_habit_id`),
  ADD KEY `goal_habit_daily_habit_id_foreign` (`daily_habit_id`);

--
-- Índices de tabela `goal_lists`
--
ALTER TABLE `goal_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `goal_lists_board_id_foreign` (`board_id`);

--
-- Índices de tabela `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`id_invoice`),
  ADD KEY `id_bank` (`id_bank`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_invoice_user_id` (`user_id`),
  ADD KEY `idx_invoice_value` (`value`),
  ADD KEY `fk_invoice_clients` (`client_id`);

--
-- Índices de tabela `invoice_category_learning`
--
ALTER TABLE `invoice_category_learning`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `icl_unique_pattern` (`user_id`,`description_pattern`,`category_id`),
  ADD KEY `invoice_category_learning_user_id_description_pattern_index` (`user_id`,`description_pattern`),
  ADD KEY `invoice_category_learning_user_id_category_id_index` (`user_id`,`category_id`),
  ADD KEY `invoice_category_learning_category_id_foreign` (`category_id`);

--
-- Índices de tabela `invoice_uploads_history`
--
ALTER TABLE `invoice_uploads_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_uploads_history_user_id_index` (`user_id`),
  ADD KEY `invoice_uploads_history_bank_id_index` (`bank_id`),
  ADD KEY `invoice_uploads_history_status_index` (`status`),
  ADD KEY `invoice_uploads_history_created_at_index` (`created_at`);

--
-- Índices de tabela `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Índices de tabela `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `lancamentos_recorrentes`
--
ALTER TABLE `lancamentos_recorrentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Índices de tabela `mercadolivre_orders`
--
ALTER TABLE `mercadolivre_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mercadolivre_orders_ml_order_id_unique` (`ml_order_id`),
  ADD KEY `mercadolivre_orders_product_id_index` (`product_id`),
  ADD KEY `mercadolivre_orders_imported_to_sale_id_index` (`imported_to_sale_id`),
  ADD KEY `mercadolivre_orders_ml_order_id_index` (`ml_order_id`),
  ADD KEY `mercadolivre_orders_ml_item_id_index` (`ml_item_id`),
  ADD KEY `mercadolivre_orders_buyer_id_index` (`buyer_id`),
  ADD KEY `mercadolivre_orders_order_status_index` (`order_status`),
  ADD KEY `mercadolivre_orders_payment_status_index` (`payment_status`),
  ADD KEY `mercadolivre_orders_sync_status_index` (`sync_status`),
  ADD KEY `mercadolivre_orders_date_created_index` (`date_created`),
  ADD KEY `mercadolivre_orders_sync_status_order_status_index` (`sync_status`,`order_status`);

--
-- Índices de tabela `mercadolivre_products`
--
ALTER TABLE `mercadolivre_products`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `mercadolivre_sync_log`
--
ALTER TABLE `mercadolivre_sync_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_sync_date` (`user_id`,`sync_type`,`created_at`),
  ADD KEY `mercadolivre_sync_log_status_index` (`status`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `mercadolivre_sync_log_created_at_index` (`created_at`),
  ADD KEY `mercadolivre_sync_log_sync_type_status_index` (`sync_type`,`status`);

--
-- Índices de tabela `mercadolivre_tokens`
--
ALTER TABLE `mercadolivre_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mercadolivre_tokens_user_id_index` (`user_id`),
  ADD KEY `mercadolivre_tokens_ml_user_id_index` (`ml_user_id`),
  ADD KEY `mercadolivre_tokens_expires_at_index` (`expires_at`),
  ADD KEY `mercadolivre_tokens_is_active_index` (`is_active`),
  ADD KEY `mercadolivre_tokens_user_id_is_active_index` (`user_id`,`is_active`);

--
-- Índices de tabela `mercadolivre_webhooks`
--
ALTER TABLE `mercadolivre_webhooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mercadolivre_webhooks_topic_index` (`topic`),
  ADD KEY `mercadolivre_webhooks_ml_user_id_index` (`ml_user_id`),
  ADD KEY `mercadolivre_webhooks_processed_index` (`processed`),
  ADD KEY `mercadolivre_webhooks_received_at_index` (`received_at`),
  ADD KEY `mercadolivre_webhooks_topic_processed_index` (`topic`,`processed`),
  ADD KEY `mercadolivre_webhooks_received_at_processed_index` (`received_at`,`processed`);

--
-- Índices de tabela `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `ml_publications`
--
ALTER TABLE `ml_publications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ml_publications_ml_item_id_unique` (`ml_item_id`),
  ADD KEY `ml_publications_ml_item_id_index` (`ml_item_id`),
  ADD KEY `ml_publications_status_index` (`status`),
  ADD KEY `ml_publications_sync_status_index` (`sync_status`),
  ADD KEY `ml_publications_publication_type_index` (`publication_type`),
  ADD KEY `ml_publications_user_id_status_index` (`user_id`,`status`),
  ADD KEY `ml_publications_ml_category_id_index` (`ml_category_id`);

--
-- Índices de tabela `ml_publication_products`
--
ALTER TABLE `ml_publication_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ml_publication_products_ml_publication_id_product_id_unique` (`ml_publication_id`,`product_id`),
  ADD KEY `ml_publication_products_product_id_index` (`product_id`),
  ADD KEY `ml_publication_products_sort_order_index` (`sort_order`);

--
-- Índices de tabela `ml_stock_logs`
--
ALTER TABLE `ml_stock_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ml_stock_logs_product_id_index` (`product_id`),
  ADD KEY `ml_stock_logs_ml_publication_id_index` (`ml_publication_id`),
  ADD KEY `ml_stock_logs_operation_type_index` (`operation_type`),
  ADD KEY `ml_stock_logs_ml_order_id_index` (`ml_order_id`),
  ADD KEY `ml_stock_logs_transaction_id_index` (`transaction_id`),
  ADD KEY `ml_stock_logs_created_at_index` (`created_at`),
  ADD KEY `ml_stock_logs_product_id_created_at_index` (`product_id`,`created_at`);

--
-- Índices de tabela `orcamentos`
--
ALTER TABLE `orcamentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario_categoria_periodo` (`user_id`,`category_id`,`mes`,`ano`),
  ADD KEY `category_id` (`category_id`);

--
-- Índices de tabela `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Índices de tabela `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_category_id` (`category_id`),
  ADD KEY `idx_products_user_id` (`user_id`),
  ADD KEY `idx_products_barcode` (`barcode`);

--
-- Índices de tabela `product_category_learning`
--
ALTER TABLE `product_category_learning`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_pattern_category` (`user_id`,`product_name_pattern`,`category_id`),
  ADD KEY `product_category_learning_user_id_product_name_pattern_index` (`user_id`,`product_name_pattern`),
  ADD KEY `product_category_learning_user_id_product_code_index` (`user_id`,`product_code`),
  ADD KEY `product_category_learning_user_id_category_id_index` (`user_id`,`category_id`),
  ADD KEY `product_category_learning_last_used_at_index` (`last_used_at`),
  ADD KEY `product_category_learning_category_id_foreign` (`category_id`);

--
-- Índices de tabela `product_uploads_history`
--
ALTER TABLE `product_uploads_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_uploads_history_user_id_index` (`user_id`),
  ADD KEY `product_uploads_history_status_index` (`status`),
  ADD KEY `product_uploads_history_created_at_index` (`created_at`);

--
-- Índices de tabela `produto_componentes`
--
ALTER TABLE `produto_componentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kit_produto_id` (`kit_produto_id`),
  ADD KEY `componente_produto_id` (`componente_produto_id`);

--
-- Índices de tabela `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sales_client_id` (`client_id`),
  ADD KEY `idx_sales_user_id` (`user_id`);

--
-- Índices de tabela `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sale_items_sale_id` (`sale_id`),
  ADD KEY `idx_sale_items_product_id` (`product_id`);

--
-- Índices de tabela `sale_payments`
--
ALTER TABLE `sale_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Índices de tabela `segment`
--
ALTER TABLE `segment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_segment_category_id` (`category_id`),
  ADD KEY `idx_segment_user_id` (`user_id`);

--
-- Índices de tabela `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Índices de tabela `shopee_orders`
--
ALTER TABLE `shopee_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shopee_orders_shopee_order_sn_unique` (`shopee_order_sn`),
  ADD KEY `shopee_orders_imported_to_sale_id_foreign` (`imported_to_sale_id`),
  ADD KEY `shopee_orders_user_id_order_status_index` (`user_id`,`order_status`),
  ADD KEY `shopee_orders_shopee_item_id_index` (`shopee_item_id`),
  ADD KEY `shopee_orders_sync_status_index` (`sync_status`);

--
-- Índices de tabela `shopee_publications`
--
ALTER TABLE `shopee_publications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shopee_publications_user_id_shop_id_index` (`user_id`,`shop_id`),
  ADD KEY `shopee_publications_shopee_item_id_index` (`shopee_item_id`),
  ADD KEY `shopee_publications_sync_status_index` (`sync_status`);

--
-- Índices de tabela `shopee_publication_products`
--
ALTER TABLE `shopee_publication_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shopee_pub_product_model_unique` (`shopee_publication_id`,`product_id`,`shopee_model_id`),
  ADD KEY `shopee_publication_products_product_id_foreign` (`product_id`);

--
-- Índices de tabela `shopee_sync_logs`
--
ALTER TABLE `shopee_sync_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shopee_sync_logs_user_id_platform_status_index` (`user_id`,`platform`,`status`),
  ADD KEY `shopee_sync_logs_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  ADD KEY `shopee_sync_logs_created_at_index` (`created_at`);

--
-- Índices de tabela `shopee_tokens`
--
ALTER TABLE `shopee_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shopee_tokens_user_id_shop_id_unique` (`user_id`,`shop_id`),
  ADD KEY `shopee_tokens_user_id_is_active_index` (`user_id`,`is_active`);

--
-- Índices de tabela `targets`
--
ALTER TABLE `targets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_targets_user_id` (`user_id`),
  ADD KEY `idx_targets_target_date` (`target_date`);

--
-- Índices de tabela `type`
--
ALTER TABLE `type`
  ADD PRIMARY KEY (`id_type`),
  ADD KEY `idx_type_desc_type` (`desc_type`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `idx_users_email` (`email`);

--
-- Índices de tabela `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_achievements_user_id_achievement_id_unique` (`user_id`,`achievement_id`),
  ADD KEY `user_achievements_achievement_id_foreign` (`achievement_id`);

--
-- Índices de tabela `venda_parcelas`
--
ALTER TABLE `venda_parcelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `banks`
--
ALTER TABLE `banks`
  MODIFY `id_bank` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cashbook`
--
ALTER TABLE `cashbook`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cashbook_uploads_history`
--
ALTER TABLE `cashbook_uploads_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `category`
--
ALTER TABLE `category`
  MODIFY `id_category` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cofrinhos`
--
ALTER TABLE `cofrinhos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `consortiums`
--
ALTER TABLE `consortiums`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `consortium_contemplations`
--
ALTER TABLE `consortium_contemplations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `consortium_draws`
--
ALTER TABLE `consortium_draws`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `consortium_notifications`
--
ALTER TABLE `consortium_notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `consortium_participants`
--
ALTER TABLE `consortium_participants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `consortium_payments`
--
ALTER TABLE `consortium_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `daily_habits`
--
ALTER TABLE `daily_habits`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `daily_habit_completions`
--
ALTER TABLE `daily_habit_completions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `daily_habit_streaks`
--
ALTER TABLE `daily_habit_streaks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `goals`
--
ALTER TABLE `goals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `goal_activities`
--
ALTER TABLE `goal_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `goal_attachments`
--
ALTER TABLE `goal_attachments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `goal_boards`
--
ALTER TABLE `goal_boards`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `goal_checklists`
--
ALTER TABLE `goal_checklists`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `goal_checklist_items`
--
ALTER TABLE `goal_checklist_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `goal_comments`
--
ALTER TABLE `goal_comments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `goal_habit`
--
ALTER TABLE `goal_habit`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `goal_lists`
--
ALTER TABLE `goal_lists`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `invoice`
--
ALTER TABLE `invoice`
  MODIFY `id_invoice` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `invoice_category_learning`
--
ALTER TABLE `invoice_category_learning`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `invoice_uploads_history`
--
ALTER TABLE `invoice_uploads_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `lancamentos_recorrentes`
--
ALTER TABLE `lancamentos_recorrentes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `mercadolivre_orders`
--
ALTER TABLE `mercadolivre_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `mercadolivre_products`
--
ALTER TABLE `mercadolivre_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `mercadolivre_sync_log`
--
ALTER TABLE `mercadolivre_sync_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `mercadolivre_tokens`
--
ALTER TABLE `mercadolivre_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `mercadolivre_webhooks`
--
ALTER TABLE `mercadolivre_webhooks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ml_publications`
--
ALTER TABLE `ml_publications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ml_publication_products`
--
ALTER TABLE `ml_publication_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `ml_stock_logs`
--
ALTER TABLE `ml_stock_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `orcamentos`
--
ALTER TABLE `orcamentos`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `product_category_learning`
--
ALTER TABLE `product_category_learning`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `product_uploads_history`
--
ALTER TABLE `product_uploads_history`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `produto_componentes`
--
ALTER TABLE `produto_componentes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `sale_payments`
--
ALTER TABLE `sale_payments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `segment`
--
ALTER TABLE `segment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `shopee_orders`
--
ALTER TABLE `shopee_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `shopee_publications`
--
ALTER TABLE `shopee_publications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `shopee_publication_products`
--
ALTER TABLE `shopee_publication_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `shopee_sync_logs`
--
ALTER TABLE `shopee_sync_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `shopee_tokens`
--
ALTER TABLE `shopee_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `targets`
--
ALTER TABLE `targets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `type`
--
ALTER TABLE `type`
  MODIFY `id_type` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `user_achievements`
--
ALTER TABLE `user_achievements`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `venda_parcelas`
--
ALTER TABLE `venda_parcelas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `banks`
--
ALTER TABLE `banks`
  ADD CONSTRAINT `banks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `cashbook`
--
ALTER TABLE `cashbook`
  ADD CONSTRAINT `cashbook_cofrinho_id_foreign` FOREIGN KEY (`cofrinho_id`) REFERENCES `cofrinhos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cashbook_fk_id_bank` FOREIGN KEY (`id_bank`) REFERENCES `banks` (`id_bank`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `cashbook_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cashbook_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id_category`),
  ADD CONSTRAINT `cashbook_ibfk_3` FOREIGN KEY (`type_id`) REFERENCES `type` (`id_type`),
  ADD CONSTRAINT `cashbook_ibfk_4` FOREIGN KEY (`segment_id`) REFERENCES `segment` (`id`),
  ADD CONSTRAINT `fk_cashbook_clients` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `cashbook_uploads_history`
--
ALTER TABLE `cashbook_uploads_history`
  ADD CONSTRAINT `cashbook_uploads_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `category`
--
ALTER TABLE `category`
  ADD CONSTRAINT `category_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_category_banks` FOREIGN KEY (`id_bank`) REFERENCES `banks` (`id_bank`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_category_clients` FOREIGN KEY (`id_clients`) REFERENCES `clients` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `cofrinhos`
--
ALTER TABLE `cofrinhos`
  ADD CONSTRAINT `cofrinhos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `consortiums`
--
ALTER TABLE `consortiums`
  ADD CONSTRAINT `consortiums_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `consortium_contemplations`
--
ALTER TABLE `consortium_contemplations`
  ADD CONSTRAINT `consortium_contemplations_consortium_participant_id_foreign` FOREIGN KEY (`consortium_participant_id`) REFERENCES `consortium_participants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consortium_contemplations_draw_id_foreign` FOREIGN KEY (`draw_id`) REFERENCES `consortium_draws` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `consortium_draws`
--
ALTER TABLE `consortium_draws`
  ADD CONSTRAINT `consortium_draws_consortium_id_foreign` FOREIGN KEY (`consortium_id`) REFERENCES `consortiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consortium_draws_winner_participant_id_foreign` FOREIGN KEY (`winner_participant_id`) REFERENCES `consortium_participants` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `consortium_notifications`
--
ALTER TABLE `consortium_notifications`
  ADD CONSTRAINT `consortium_notifications_consortium_id_foreign` FOREIGN KEY (`consortium_id`) REFERENCES `consortiums` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consortium_notifications_related_participant_id_foreign` FOREIGN KEY (`related_participant_id`) REFERENCES `consortium_participants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consortium_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `consortium_participants`
--
ALTER TABLE `consortium_participants`
  ADD CONSTRAINT `consortium_participants_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consortium_participants_consortium_id_foreign` FOREIGN KEY (`consortium_id`) REFERENCES `consortiums` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `consortium_payments`
--
ALTER TABLE `consortium_payments`
  ADD CONSTRAINT `consortium_payments_consortium_participant_id_foreign` FOREIGN KEY (`consortium_participant_id`) REFERENCES `consortium_participants` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `daily_habits`
--
ALTER TABLE `daily_habits`
  ADD CONSTRAINT `daily_habits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `daily_habit_completions`
--
ALTER TABLE `daily_habit_completions`
  ADD CONSTRAINT `daily_habit_completions_habit_id_foreign` FOREIGN KEY (`habit_id`) REFERENCES `daily_habits` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `daily_habit_completions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `daily_habit_streaks`
--
ALTER TABLE `daily_habit_streaks`
  ADD CONSTRAINT `daily_habit_streaks_habit_id_foreign` FOREIGN KEY (`habit_id`) REFERENCES `daily_habits` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `daily_habit_streaks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `goals`
--
ALTER TABLE `goals`
  ADD CONSTRAINT `goals_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `category` (`id_category`) ON DELETE SET NULL,
  ADD CONSTRAINT `goals_cofrinho_id_foreign` FOREIGN KEY (`cofrinho_id`) REFERENCES `cofrinhos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `goals_list_id_foreign` FOREIGN KEY (`list_id`) REFERENCES `goal_lists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `goals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `goal_attachments`
--
ALTER TABLE `goal_attachments`
  ADD CONSTRAINT `goal_attachments_goal_id_foreign` FOREIGN KEY (`goal_id`) REFERENCES `goals` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `goal_boards`
--
ALTER TABLE `goal_boards`
  ADD CONSTRAINT `goal_boards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `goal_checklists`
--
ALTER TABLE `goal_checklists`
  ADD CONSTRAINT `goal_checklists_goal_id_foreign` FOREIGN KEY (`goal_id`) REFERENCES `goals` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `goal_checklist_items`
--
ALTER TABLE `goal_checklist_items`
  ADD CONSTRAINT `goal_checklist_items_checklist_id_foreign` FOREIGN KEY (`checklist_id`) REFERENCES `goal_checklists` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `goal_comments`
--
ALTER TABLE `goal_comments`
  ADD CONSTRAINT `goal_comments_goal_id_foreign` FOREIGN KEY (`goal_id`) REFERENCES `goals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `goal_comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `goal_habit`
--
ALTER TABLE `goal_habit`
  ADD CONSTRAINT `goal_habit_daily_habit_id_foreign` FOREIGN KEY (`daily_habit_id`) REFERENCES `daily_habits` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `goal_habit_goal_id_foreign` FOREIGN KEY (`goal_id`) REFERENCES `goals` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `goal_lists`
--
ALTER TABLE `goal_lists`
  ADD CONSTRAINT `goal_lists_board_id_foreign` FOREIGN KEY (`board_id`) REFERENCES `goal_boards` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `fk_invoice_clients` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `invoice_ibfk_2` FOREIGN KEY (`id_bank`) REFERENCES `banks` (`id_bank`),
  ADD CONSTRAINT `invoice_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `category` (`id_category`);

--
-- Restrições para tabelas `invoice_category_learning`
--
ALTER TABLE `invoice_category_learning`
  ADD CONSTRAINT `invoice_category_learning_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `category` (`id_category`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_category_learning_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `invoice_uploads_history`
--
ALTER TABLE `invoice_uploads_history`
  ADD CONSTRAINT `invoice_uploads_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `lancamentos_recorrentes`
--
ALTER TABLE `lancamentos_recorrentes`
  ADD CONSTRAINT `lancamentos_recorrentes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `lancamentos_recorrentes_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `type` (`id_type`),
  ADD CONSTRAINT `lancamentos_recorrentes_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `category` (`id_category`);

--
-- Restrições para tabelas `mercadolivre_sync_log`
--
ALTER TABLE `mercadolivre_sync_log`
  ADD CONSTRAINT `mercadolivre_sync_log_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `mercadolivre_tokens`
--
ALTER TABLE `mercadolivre_tokens`
  ADD CONSTRAINT `mercadolivre_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `ml_publication_products`
--
ALTER TABLE `ml_publication_products`
  ADD CONSTRAINT `ml_publication_products_ml_publication_id_foreign` FOREIGN KEY (`ml_publication_id`) REFERENCES `ml_publications` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `orcamentos`
--
ALTER TABLE `orcamentos`
  ADD CONSTRAINT `orcamentos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orcamentos_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id_category`) ON DELETE CASCADE;

--
-- Restrições para tabelas `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id_category`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `product_category_learning`
--
ALTER TABLE `product_category_learning`
  ADD CONSTRAINT `product_category_learning_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `category` (`id_category`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_category_learning_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `product_uploads_history`
--
ALTER TABLE `product_uploads_history`
  ADD CONSTRAINT `product_uploads_history_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `produto_componentes`
--
ALTER TABLE `produto_componentes`
  ADD CONSTRAINT `produto_componentes_ibfk_1` FOREIGN KEY (`kit_produto_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `produto_componentes_ibfk_2` FOREIGN KEY (`componente_produto_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Restrições para tabelas `sale_payments`
--
ALTER TABLE `sale_payments`
  ADD CONSTRAINT `sale_payments_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `segment`
--
ALTER TABLE `segment`
  ADD CONSTRAINT `segment_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id_category`),
  ADD CONSTRAINT `segment_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `shopee_orders`
--
ALTER TABLE `shopee_orders`
  ADD CONSTRAINT `shopee_orders_imported_to_sale_id_foreign` FOREIGN KEY (`imported_to_sale_id`) REFERENCES `sales` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shopee_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `shopee_publications`
--
ALTER TABLE `shopee_publications`
  ADD CONSTRAINT `shopee_publications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `shopee_publication_products`
--
ALTER TABLE `shopee_publication_products`
  ADD CONSTRAINT `shopee_publication_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shopee_publication_products_shopee_publication_id_foreign` FOREIGN KEY (`shopee_publication_id`) REFERENCES `shopee_publications` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `shopee_sync_logs`
--
ALTER TABLE `shopee_sync_logs`
  ADD CONSTRAINT `shopee_sync_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `shopee_tokens`
--
ALTER TABLE `shopee_tokens`
  ADD CONSTRAINT `shopee_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `targets`
--
ALTER TABLE `targets`
  ADD CONSTRAINT `targets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_achievement_id_foreign` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_achievements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `venda_parcelas`
--
ALTER TABLE `venda_parcelas`
  ADD CONSTRAINT `venda_parcelas_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
