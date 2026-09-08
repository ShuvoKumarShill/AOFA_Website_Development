---
title: Initial Dockerized WordPress Setup
status: implemented
version: 1.0.0
---

# Spec: 001 - Initial Setup

## Objective
Establish a secure, performant, and reproducible local development environment for the AOFA WordPress website using Docker.

## Requirements
1. **Docker Compose Stack:**
   - MariaDB 11 for the database.
   - WordPress image running PHP 8.2+ and Apache.
   - phpMyAdmin for database management (development only).
   - WP-CLI service for executing CLI commands.
2. **Environment Configuration:**
   - Abstract credentials using a `.env` file (gitignored).
   - Provide a `.env.example` template.
   - Inject additional secure WordPress constants via `wp-config-extra.php` (e.g., `DISALLOW_FILE_EDIT`, `WP_DEBUG`).
3. **Custom Theme Scaffold:**
   - Create a barebones Full Site Editing (FSE) block theme named `aofa-theme`.
   - Provide `theme.json` defining the brand colors (Navy & Gold) and typography (Inter & Playfair Display).
   - Provide initial template parts (`header.html`, `footer.html`) and templates (`index.html`, `single.html`, `page.html`, `archive.html`, `404.html`).
4. **Developer Tools:**
   - Provide a `Makefile` to simplify common operations (e.g., `make up`, `make install`, `make shell`).

## Acceptance Criteria
- [x] Running `make up` successfully starts all containers.
- [x] Running `make install` correctly provisions the WordPress site, activates the theme, and configures permalinks.
- [x] The `aofa-theme` is active and utilizes the defined `theme.json` styles.
- [x] All code includes a reference to `Spec Item: 001-initial-setup`.
