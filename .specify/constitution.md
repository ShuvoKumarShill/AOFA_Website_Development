---
name: WordPress Development Constitution
description: Core principles and architectural rules for the AOFA website project.
version: 1.0.0
---

# 📜 AOFA Website Constitution

This document defines the core principles, technical constraints, and coding standards for the **Association of Former BCS(FA) Ambassadors (AOFA)** website (`aofabd.com`).

**All AI agents and developers must strictly adhere to these rules.**

## 1. Technical Stack
- **CMS:** WordPress (Latest)
- **Environment:** Dockerized (Docker Compose)
  - `wordpress:latest` (PHP 8.2+ with Apache)
  - `mariadb:11`
  - `wp-cli` via one-shot container
- **Deployment:** Managed via Git and WP-CLI. Core files must never be tracked in Git.

## 2. Coding Standards
- **PHP:** Adhere to [WordPress Coding Standards (WPCS)](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/).
- **Validation & Sanitization:** Security-first. All inputs must be sanitized (`sanitize_text_field`, `sanitize_email`, etc.). All outputs must be escaped (`esc_html`, `esc_url`, `esc_attr`, etc.).
- **Nonces:** Required for all form submissions and admin AJAX requests.
- **Direct Database Queries:** Forbidden. Use `WP_Query`, `get_posts`, or `wp_insert_post()`. Never write raw SQL unless absolutely necessary and properly prepared.

## 3. Architecture & Design
- **No Page Builders:** Elementor, Divi, WPBakery, etc., are strictly forbidden.
- **Block Editor (FSE):** The site must use Full Site Editing (Gutenberg) with a custom block theme.
- **Performance First:** No unnecessary plugins. Keep the codebase lightweight.
- **Data Modeling:** Content types must be modeled correctly using Custom Post Types (CPTs) and Taxonomies in a custom Must-Use (or core) plugin.

## 4. Spec-Driven Development Workflow
- **Source of Truth:** GitHub Spec Kit is the ultimate source of truth.
- **Traceability:** Every code change must map to a defined spec item (`Spec Item: XXX`).
- **Explainability:** Briefly explain technical decisions in code comments.

## 5. Content Migration Rules
- Recovered content from `archive_recovery/` is the source of truth for historical data.
- Missing resources must be clearly documented, not hallucinated.
- Historical data must be imported via WP-CLI scripts, not manual entry.
