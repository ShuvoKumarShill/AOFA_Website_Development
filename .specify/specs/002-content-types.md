---
title: AOFA Core Content Types & Importer
status: implemented
version: 1.0.0
---

# Spec: 002 - Content Types

## Objective
Define the custom data structures necessary to migrate the historical content from the archived AOFA website and provide a mechanism to import this data.

## Requirements
1. **Core Plugin:**
   - Create a custom plugin named `aofa-core` to house all data modeling (separating content from presentation).
2. **Custom Post Types (CPTs):**
   - `aofa_member`: For Regular and Honorary members.
   - `aofa_ec_member`: For Executive Committee members across different terms.
   - `aofa_notice`: For official organization notices.
   - `aofa_article`: For member articles and book reviews.
   - All CPTs must support the REST API (Gutenberg block editor).
3. **Custom Taxonomies:**
   - `aofa_member_type`: Hierarchical (e.g., Regular, Honorary) attached to `aofa_member`.
   - `aofa_committee_term`: Hierarchical (e.g., 2022-2023) attached to `aofa_ec_member`.
   - `aofa_article_category`: Hierarchical (e.g., Article, Book Review) attached to `aofa_article`.
4. **WP-CLI Importer:**
   - Create a WP-CLI command (`wp aofa import members`) to ingest a CSV file of members.
   - The command must support a `--dry-run` flag.
   - Inputs must be sanitized before creating posts or saving metadata.
   - Command must be idempotent (avoiding duplicate member entries).

## Acceptance Criteria
- [x] Activating the `aofa-core` plugin successfully registers the CPTs and Taxonomies.
- [x] CPTs are visible in the WordPress Admin dashboard and the block editor functions correctly.
- [x] Running `wp aofa import members --dry-run` successfully parses the CSV without errors.
- [x] Running `wp aofa import members` creates member profiles securely.
- [x] All code includes a reference to `Spec Item: 002-content-types`.
