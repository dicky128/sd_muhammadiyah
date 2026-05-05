-- ============================================================
--  SD Muhammadiyah 1 Gentasari — Database Schema
--  Version: 1.0 | Engine: InnoDB | Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS `sd_muhammadiyah` 
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `sd_muhammadiyah`;

-- ----------------------------------------------------------
--  ADMIN USERS
-- ----------------------------------------------------------
CREATE TABLE `admin_users` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username`   VARCHAR(60)  NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL COMMENT 'bcrypt hash',
  `full_name`  VARCHAR(120) NOT NULL,
  `email`      VARCHAR(120) NOT NULL UNIQUE,
  `avatar`     VARCHAR(255) DEFAULT NULL,
  `role`       ENUM('superadmin','admin','editor') DEFAULT 'editor',
  `last_login` DATETIME     DEFAULT NULL,
  `is_active`  TINYINT(1)   DEFAULT 1,
  `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed default superadmin (password: admin123)
INSERT INTO `admin_users` (`username`, `password`, `full_name`, `email`, `role`)
VALUES ('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
        'Administrator', 'admin@sdmuh1gentasari.sch.id', 'superadmin');

-- ----------------------------------------------------------
--  SCHOOL PROFILE
-- ----------------------------------------------------------
CREATE TABLE `school_profile` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `school_name`   VARCHAR(200) NOT NULL,
  `npsn`          VARCHAR(20)  DEFAULT NULL,
  `address`       TEXT         DEFAULT NULL,
  `village`       VARCHAR(100) DEFAULT NULL,
  `district`      VARCHAR(100) DEFAULT NULL,
  `city`          VARCHAR(100) DEFAULT NULL,
  `province`      VARCHAR(100) DEFAULT NULL,
  `postal_code`   VARCHAR(10)  DEFAULT NULL,
  `phone`         VARCHAR(30)  DEFAULT NULL,
  `email`         VARCHAR(120) DEFAULT NULL,
  `website`       VARCHAR(200) DEFAULT NULL,
  `logo`          VARCHAR(255) DEFAULT NULL,
  `hero_image`    VARCHAR(255) DEFAULT NULL,
  `visi`          TEXT         DEFAULT NULL,
  `misi`          TEXT         DEFAULT NULL,
  `sejarah`       LONGTEXT     DEFAULT NULL,
  `akreditasi`    VARCHAR(10)  DEFAULT NULL,
  `tahun_berdiri` YEAR         DEFAULT NULL,
  `facebook`      VARCHAR(200) DEFAULT NULL,
  `instagram`     VARCHAR(200) DEFAULT NULL,
  `youtube`       VARCHAR(200) DEFAULT NULL,
  `maps_embed`    TEXT         DEFAULT NULL,
  `updated_at`    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `school_profile` 
  (`school_name`, `npsn`, `address`, `village`, `district`, `city`, `province`,
   `phone`, `email`, `akreditasi`, `tahun_berdiri`,
   `visi`, `misi`)
VALUES (
  'SD Muhammadiyah 1 Gentasari', '20302857',
  'Jl. Raya Gentasari No. 1', 'Gentasari', 'Kroya', 'Cilacap', 'Jawa Tengah',
  '(0282) 123456', 'info@sdmuh1gentasari.sch.id', 'A', 1962,
  'Menjadi sekolah Islam unggul yang menghasilkan generasi cerdas, berkarakter, dan berakhlak mulia berlandaskan nilai-nilai Al-Islam dan Kemuhammadiyahan.',
  'Menyelenggarakan pendidikan Islam berkualitas tinggi; Membentuk karakter siswa yang berakhlak mulia; Mengembangkan potensi akademik dan non-akademik secara optimal; Menciptakan lingkungan belajar yang inovatif dan menyenangkan.'
);

-- ----------------------------------------------------------
--  TEACHERS & STAFF
-- ----------------------------------------------------------
CREATE TABLE `teachers` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nip`          VARCHAR(30)  DEFAULT NULL,
  `full_name`    VARCHAR(150) NOT NULL,
  `nickname`     VARCHAR(80)  DEFAULT NULL,
  `gender`       ENUM('L','P') DEFAULT NULL,
  `birth_date`   DATE         DEFAULT NULL,
  `education`    VARCHAR(100) DEFAULT NULL,
  `subject`      VARCHAR(150) DEFAULT NULL,
  `position`     VARCHAR(150) DEFAULT NULL COMMENT 'Jabatan struktural',
  `type`         ENUM('guru','staff') DEFAULT 'guru',
  `photo`        VARCHAR(255) DEFAULT NULL,
  `bio`          TEXT         DEFAULT NULL,
  `sort_order`   INT          DEFAULT 0,
  `is_active`    TINYINT(1)   DEFAULT 1,
  `created_at`   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------
--  STUDENTS (Stats/Aggregated — no personal data)
-- ----------------------------------------------------------
CREATE TABLE `student_stats` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `academic_year` VARCHAR(20) NOT NULL COMMENT '2024/2025',
  `grade`         VARCHAR(10) NOT NULL COMMENT 'Kelas I - VI',
  `gender`        ENUM('L','P') NOT NULL,
  `count`         INT UNSIGNED DEFAULT 0,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `student_achievements` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`        VARCHAR(255) NOT NULL,
  `description`  TEXT         DEFAULT NULL,
  `level`        ENUM('sekolah','kecamatan','kabupaten','provinsi','nasional','internasional') DEFAULT 'sekolah',
  `year`         YEAR         DEFAULT NULL,
  `student_name` VARCHAR(150) DEFAULT NULL,
  `grade`        VARCHAR(20)  DEFAULT NULL,
  `photo`        VARCHAR(255) DEFAULT NULL,
  `created_at`   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------
--  GALLERY
-- ----------------------------------------------------------
CREATE TABLE `gallery_categories` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `slug`       VARCHAR(120) NOT NULL UNIQUE,
  `sort_order` INT          DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO `gallery_categories` (`name`, `slug`, `sort_order`) VALUES
  ('Kegiatan Belajar', 'kegiatan-belajar', 1),
  ('Fasilitas Sekolah', 'fasilitas', 2),
  ('Ekstrakurikuler', 'ekskul', 3),
  ('Prestasi', 'prestasi', 4),
  ('Event & Perayaan', 'event', 5);

CREATE TABLE `gallery` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `title`       VARCHAR(255) NOT NULL,
  `description` TEXT         DEFAULT NULL,
  `image`       VARCHAR(255) NOT NULL,
  `is_featured` TINYINT(1)   DEFAULT 0,
  `sort_order`  INT          DEFAULT 0,
  `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `gallery_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------
--  FACILITIES
-- ----------------------------------------------------------
CREATE TABLE `facilities` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(150) NOT NULL,
  `description` TEXT         DEFAULT NULL,
  `icon`        VARCHAR(100) DEFAULT NULL COMMENT 'Lucide icon name',
  `image`       VARCHAR(255) DEFAULT NULL,
  `count`       INT          DEFAULT 1,
  `condition`   ENUM('baik','cukup','rusak_ringan','rusak_berat') DEFAULT 'baik',
  `sort_order`  INT          DEFAULT 0,
  `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `facilities` (`name`, `icon`, `count`, `condition`, `sort_order`) VALUES
  ('Ruang Kelas', 'door-open', 12, 'baik', 1),
  ('Perpustakaan', 'library', 1, 'baik', 2),
  ('Lab Komputer', 'monitor', 1, 'baik', 3),
  ('Musholla', 'building', 1, 'baik', 4),
  ('UKS', 'heart-pulse', 1, 'baik', 5),
  ('Lapangan Olahraga', 'trophy', 2, 'baik', 6),
  ('Kantin Sehat', 'utensils', 1, 'baik', 7),
  ('Toilet Siswa', 'droplets', 6, 'baik', 8);

-- ----------------------------------------------------------
--  EXTRACURRICULAR
-- ----------------------------------------------------------
CREATE TABLE `extracurricular` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(150) NOT NULL,
  `description` TEXT         DEFAULT NULL,
  `schedule`    VARCHAR(200) DEFAULT NULL COMMENT 'Jadwal singkat, e.g. Selasa & Kamis, 14.00-16.00',
  `coach`       VARCHAR(150) DEFAULT NULL,
  `image`       VARCHAR(255) DEFAULT NULL,
  `icon`        VARCHAR(100) DEFAULT NULL,
  `is_active`   TINYINT(1)   DEFAULT 1,
  `sort_order`  INT          DEFAULT 0,
  `created_at`  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------
--  ANNOUNCEMENTS
-- ----------------------------------------------------------
CREATE TABLE `announcements` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(255)  NOT NULL,
  `slug`        VARCHAR(280)  NOT NULL UNIQUE,
  `content`     LONGTEXT      NOT NULL,
  `category`    ENUM('umum','akademik','kegiatan','penting') DEFAULT 'umum',
  `thumbnail`   VARCHAR(255)  DEFAULT NULL,
  `author_id`   INT UNSIGNED  DEFAULT NULL,
  `is_pinned`   TINYINT(1)    DEFAULT 0,
  `is_published` TINYINT(1)   DEFAULT 1,
  `views`       INT UNSIGNED  DEFAULT 0,
  `published_at` DATETIME     DEFAULT CURRENT_TIMESTAMP,
  `created_at`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`author_id`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------
--  COMPLAINTS / PENGADUAN
-- ----------------------------------------------------------
CREATE TABLE `complaints` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ticket_no`    VARCHAR(20)  NOT NULL UNIQUE,
  `name`         VARCHAR(150) NOT NULL,
  `email`        VARCHAR(120) DEFAULT NULL,
  `phone`        VARCHAR(30)  DEFAULT NULL,
  `category`     ENUM('fasilitas','pembelajaran','administrasi','keamanan','lainnya') DEFAULT 'lainnya',
  `subject`      VARCHAR(255) NOT NULL,
  `message`      TEXT         NOT NULL,
  `attachment`   VARCHAR(255) DEFAULT NULL,
  `status`       ENUM('masuk','diproses','selesai','ditutup') DEFAULT 'masuk',
  `admin_note`   TEXT         DEFAULT NULL,
  `responded_by` INT UNSIGNED DEFAULT NULL,
  `responded_at` DATETIME     DEFAULT NULL,
  `created_at`   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`responded_by`) REFERENCES `admin_users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ----------------------------------------------------------
--  CONTACT MESSAGES
-- ----------------------------------------------------------
CREATE TABLE `contact_messages` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(150) NOT NULL,
  `email`      VARCHAR(120) NOT NULL,
  `phone`      VARCHAR(30)  DEFAULT NULL,
  `subject`    VARCHAR(255) NOT NULL,
  `message`    TEXT         NOT NULL,
  `is_read`    TINYINT(1)   DEFAULT 0,
  `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ----------------------------------------------------------
--  SITE SETTINGS (key-value store)
-- ----------------------------------------------------------
CREATE TABLE `settings` (
  `key`        VARCHAR(100) PRIMARY KEY,
  `value`      LONGTEXT     DEFAULT NULL,
  `label`      VARCHAR(200) DEFAULT NULL,
  `group`      VARCHAR(60)  DEFAULT 'general',
  `updated_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `settings` (`key`, `value`, `label`, `group`) VALUES
  ('site_title',        'SD Muhammadiyah 1 Gentasari',   'Judul Situs',             'general'),
  ('site_tagline',      'Cerdas, Berkarakter, Islami',   'Tagline Situs',           'general'),
  ('maintenance_mode',  '0',                             'Mode Maintenance',        'general'),
  ('hero_title',        'Sekolah Dasar Islam Unggulan',  'Judul Hero',              'homepage'),
  ('hero_subtitle',     'Membentuk Generasi Cerdas Berakhlak Mulia', 'Subjudul Hero', 'homepage'),
  ('hero_image',        'assets/images/hero-default.jpg','Gambar Hero',             'homepage'),
  ('stats_students',    '380',                           'Jumlah Siswa',            'homepage'),
  ('stats_teachers',    '24',                            'Jumlah Guru',             'homepage'),
  ('stats_years',       '62',                            'Tahun Berdiri',           'homepage'),
  ('stats_ekskul',      '12',                            'Jumlah Ekskul',           'homepage');

-- ----------------------------------------------------------
--  INDEXES FOR PERFORMANCE
-- ----------------------------------------------------------
ALTER TABLE `announcements`  ADD INDEX `idx_published`  (`is_published`, `published_at`);
ALTER TABLE `announcements`  ADD INDEX `idx_pinned`     (`is_pinned`);
ALTER TABLE `gallery`        ADD INDEX `idx_category`   (`category_id`);
ALTER TABLE `gallery`        ADD INDEX `idx_featured`   (`is_featured`);
ALTER TABLE `complaints`     ADD INDEX `idx_status`     (`status`);
ALTER TABLE `contact_messages` ADD INDEX `idx_read`     (`is_read`);

-- End of schema
