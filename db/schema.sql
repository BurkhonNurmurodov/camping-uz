-- ============================================================
--  Silk Naviora — database schema
--  MySQL 8 / MariaDB 10.4+  ·  utf8mb4
--  Bilingual content stored as *_en / *_ru columns.
--  Rich text (about / tour description / testimonial) is authored in Quill and
--  stored as sanitized HTML in *_html columns; rendered (re-sanitized) on output.
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- ----------------------------------------------------------
-- Admin users
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username      VARCHAR(64)  NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  display_name  VARCHAR(120) NULL,
  email         VARCHAR(190) NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admins_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Key/value settings (hero media, agency name/moto, socials,
-- telegram credentials, default language, login throttle …)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
  `key`      VARCHAR(80) NOT NULL,
  `value`    LONGTEXT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Editable pages (currently: "about"). Rich block content per language.
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS pages (
  `key`         VARCHAR(60) NOT NULL,
  title_en      VARCHAR(190) NULL,
  title_ru      VARCHAR(190) NULL,
  body_en_html  LONGTEXT NULL,
  body_ru_html  LONGTEXT NULL,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Guides (created independently, attached to tours)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS guides (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name   VARCHAR(160) NOT NULL,
  image       VARCHAR(255) NULL,           -- square image path under /uploads/guides
  bio_en      TEXT NULL,
  bio_ru      TEXT NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Guide social links (added one-by-one; ordered)
CREATE TABLE IF NOT EXISTS guide_socials (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  guide_id    INT UNSIGNED NOT NULL,
  -- whatsapp | instagram | telegram | facebook | twitter | linkedin | other
  type        VARCHAR(20) NOT NULL,
  value       VARCHAR(255) NOT NULL,       -- username, or full URL for linkedin/other
  custom_name VARCHAR(80) NULL,            -- only for type=other
  custom_icon VARCHAR(255) NULL,           -- only for type=other (uploaded icon path)
  sort_order  INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_guide_socials_guide (guide_id),
  CONSTRAINT fk_guide_socials_guide FOREIGN KEY (guide_id)
    REFERENCES guides (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Tours
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS tours (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug              VARCHAR(190) NOT NULL,
  status            ENUM('draft','upcoming','past') NOT NULL DEFAULT 'draft',
  poster            VARCHAR(255) NULL,        -- 4:3 poster under /uploads/posters
  title_en          VARCHAR(190) NULL,
  title_ru          VARCHAR(190) NULL,
  description_en_html LONGTEXT NULL,          -- sanitized Quill HTML
  description_ru_html LONGTEXT NULL,
  start_date        DATE NULL,
  end_date          DATE NULL,                -- NULL => single-day tour
  map_provider      VARCHAR(20) NOT NULL DEFAULT 'yandex',
  sort_order        INT NOT NULL DEFAULT 0,
  created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_tours_slug (slug),
  KEY idx_tours_status (status),
  KEY idx_tours_start (start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Route points (pins) for a tour — connected in order on the map
CREATE TABLE IF NOT EXISTS tour_route_points (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tour_id     INT UNSIGNED NOT NULL,
  label_en    VARCHAR(160) NULL,
  label_ru    VARCHAR(160) NULL,
  lat         DECIMAL(10,7) NOT NULL,
  lng         DECIMAL(10,7) NOT NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_route_tour (tour_id),
  CONSTRAINT fk_route_tour FOREIGN KEY (tour_id)
    REFERENCES tours (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tour <-> Guide (many-to-many)
CREATE TABLE IF NOT EXISTS tour_guides (
  tour_id     INT UNSIGNED NOT NULL,
  guide_id    INT UNSIGNED NOT NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  PRIMARY KEY (tour_id, guide_id),
  KEY idx_tg_guide (guide_id),
  CONSTRAINT fk_tg_tour  FOREIGN KEY (tour_id)  REFERENCES tours (id)  ON DELETE CASCADE,
  CONSTRAINT fk_tg_guide FOREIGN KEY (guide_id) REFERENCES guides (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Testimonials ("our clients about us")
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS testimonials (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  author_name     VARCHAR(160) NOT NULL,
  avatar          VARCHAR(255) NULL,         -- NULL => default avatar
  comment_en_html LONGTEXT NULL,             -- formatable (sanitized Quill HTML)
  comment_ru_html LONGTEXT NULL,
  is_visible      TINYINT(1) NOT NULL DEFAULT 1,
  sort_order      INT NOT NULL DEFAULT 0,
  created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_testimonials_visible (is_visible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Registrations (one submission = one group of >=1 people)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS registration_groups (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tour_id     INT UNSIGNED NULL,             -- NULL => general interest
  status      ENUM('new','handled') NOT NULL DEFAULT 'new',
  note        TEXT NULL,                     -- admin note
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_reg_status (status),
  KEY idx_reg_tour (tour_id),
  CONSTRAINT fk_reg_tour FOREIGN KEY (tour_id)
    REFERENCES tours (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS registration_people (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  group_id        INT UNSIGNED NOT NULL,
  full_name       VARCHAR(160) NOT NULL,
  email           VARCHAR(190) NOT NULL,
  whatsapp_phone  VARCHAR(40) NULL,
  is_primary      TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_people_group (group_id),
  CONSTRAINT fk_people_group FOREIGN KEY (group_id)
    REFERENCES registration_groups (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Contact messages (questions / feedback)
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS contact_messages (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  first_name  VARCHAR(120) NOT NULL,
  last_name   VARCHAR(120) NOT NULL,
  email       VARCHAR(190) NOT NULL,
  topic       VARCHAR(190) NULL,
  message     TEXT NOT NULL,
  status      ENUM('unanswered','answered') NOT NULL DEFAULT 'unanswered',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_messages_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Categories
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug        VARCHAR(190) NOT NULL,
  title_en    VARCHAR(190) NOT NULL,
  title_ru    VARCHAR(190) NOT NULL,
  sort_order  INT NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tour_categories (
  tour_id     INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (tour_id, category_id),
  KEY idx_tc_category (category_id),
  CONSTRAINT fk_tc_tour FOREIGN KEY (tour_id) REFERENCES tours (id) ON DELETE CASCADE,
  CONSTRAINT fk_tc_category FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Private Tour Requests
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS private_tour_requests (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name          VARCHAR(160) NOT NULL,
  email         VARCHAR(190) NOT NULL,
  whatsapp      VARCHAR(40) NULL,
  group_size    VARCHAR(40) NULL,
  dates_info    VARCHAR(190) NULL,
  destinations  TEXT NULL, 
  notes         TEXT NULL,
  status        ENUM('new','handled') NOT NULL DEFAULT 'new',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_ptr_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
