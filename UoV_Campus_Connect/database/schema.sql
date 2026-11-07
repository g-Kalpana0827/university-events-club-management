-- Development-only example project; not an official University of Vavuniya system.
CREATE DATABASE IF NOT EXISTS uov_event_club CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uov_event_club;

CREATE TABLE IF NOT EXISTS users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 student_number VARCHAR(50) NULL UNIQUE,
 faculty VARCHAR(100) NULL,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('student','club_leader','admin') NOT NULL DEFAULT 'student',
 status ENUM('active','suspended') NOT NULL DEFAULT 'active',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS clubs (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(150) NOT NULL,
 slug VARCHAR(170) NOT NULL UNIQUE,
 description TEXT NOT NULL,
 contact_email VARCHAR(190) NULL,
 category VARCHAR(80) NOT NULL DEFAULT 'Student Community',
 faculty VARCHAR(100) NOT NULL DEFAULT 'University-wide',
 leader_user_id INT UNSIGNED NULL,
 status ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'approved',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX ix_clubs_status (status),
 CONSTRAINT fk_club_leader FOREIGN KEY (leader_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS club_memberships (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 club_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NOT NULL,
 membership_role ENUM('member','leader') NOT NULL DEFAULT 'member',
 status ENUM('pending','approved','rejected','left') NOT NULL DEFAULT 'pending',
 joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_club_user (club_id,user_id),
 INDEX ix_memberships_status (club_id,status),
 CONSTRAINT fk_membership_club FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
 CONSTRAINT fk_membership_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Proposal reference data (IT2133(P), Group 17)
CREATE TABLE IF NOT EXISTS event_categories (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL UNIQUE,
 description VARCHAR(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS venues (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(180) NOT NULL UNIQUE,
 location VARCHAR(180) NOT NULL DEFAULT '',
 capacity INT UNSIGNED NOT NULL,
 CONSTRAINT ck_venues_capacity CHECK(capacity > 0)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS events (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 club_id INT UNSIGNED NOT NULL,
 created_by INT UNSIGNED NOT NULL,
 title VARCHAR(180) NOT NULL,
 description TEXT NOT NULL,
 venue VARCHAR(180) NOT NULL,
 category_id INT UNSIGNED NULL,
 venue_id INT UNSIGNED NULL,
 registration_deadline DATETIME NULL,
 review_note TEXT NULL,
 starts_at DATETIME NOT NULL,
 ends_at DATETIME NOT NULL,
 capacity INT UNSIGNED NOT NULL,
 status ENUM('draft','pending','approved','rejected','cancelled','completed') NOT NULL DEFAULT 'pending',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX ix_events_status_date (status,starts_at),
 CONSTRAINT fk_event_club FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
 CONSTRAINT fk_event_category FOREIGN KEY (category_id) REFERENCES event_categories(id) ON DELETE RESTRICT,
 CONSTRAINT fk_event_venue FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE RESTRICT,
 CONSTRAINT fk_event_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS event_registrations (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 event_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NOT NULL,
 status ENUM('registered','cancelled') NOT NULL DEFAULT 'registered',
 registered_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 attended_at DATETIME NULL,
 UNIQUE KEY uq_event_user (event_id,user_id),
 INDEX ix_event_user (user_id,status),
 CONSTRAINT fk_registration_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
 CONSTRAINT fk_registration_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Attendance is a separate proposal entity: an absent attendance row means not marked.
CREATE TABLE IF NOT EXISTS attendance (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 registration_id INT UNSIGNED NOT NULL UNIQUE,
 is_present BOOLEAN NOT NULL,
 marked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_attendance_registration FOREIGN KEY (registration_id) REFERENCES event_registrations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS announcements (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 club_id INT UNSIGNED NOT NULL,
 posted_by INT UNSIGNED NOT NULL,
 title VARCHAR(180) NOT NULL,
 body TEXT NOT NULL,
 status ENUM('published','hidden') NOT NULL DEFAULT 'published',
 updated_at TIMESTAMP NULL DEFAULT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_announcement_club FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE,
 CONSTRAINT fk_announcement_author FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS event_feedback (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 event_id INT UNSIGNED NOT NULL,
 user_id INT UNSIGNED NOT NULL,
 registration_id INT UNSIGNED NULL UNIQUE,
 rating TINYINT UNSIGNED NOT NULL,
 comment TEXT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_feedback_event_user (event_id,user_id),
 CONSTRAINT fk_feedback_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
 CONSTRAINT fk_feedback_registration FOREIGN KEY (registration_id) REFERENCES event_registrations(id) ON DELETE CASCADE,
 CONSTRAINT fk_feedback_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT chk_feedback_rating CHECK(rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 title VARCHAR(180) NOT NULL,
 message TEXT NOT NULL,
 link VARCHAR(255) NOT NULL DEFAULT '',
 is_read TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX ix_notify_user (user_id,is_read),
 CONSTRAINT fk_notify_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS activity_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 actor_user_id INT UNSIGNED NULL,
 action VARCHAR(100) NOT NULL,
 entity_type VARCHAR(70) NOT NULL,
 entity_id INT UNSIGNED NULL,
 details TEXT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_log_actor FOREIGN KEY (actor_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
