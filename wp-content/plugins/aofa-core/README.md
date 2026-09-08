# AOFA Core Plugin

Custom WordPress plugin for the **Association of Former BCS(FA) Ambassadors (AOFA)** website.

**Spec Item:** `002-content-types`  
**Version:** 1.0.0  
**Requires:** WordPress 6.6+, PHP 8.2+

---

## What It Does

Registers AOFA-specific content types and provides WP-CLI tools for importing the recovered archive data.

### Custom Post Types

| Post Type | Slug | Purpose |
|-----------|------|---------|
| `aofa_member` | `/members/` | Regular & Honorary members (92+14 recovered) |
| `aofa_ec_member` | `/executive-committee/` | EC members across 3 terms (2022–2027) |
| `aofa_notice` | `/notice/` | Official notices (11 recovered) |
| `aofa_article` | `/article/` | Articles & book reviews (7+6 recovered) |

### Custom Taxonomies

| Taxonomy | Attached To | Terms |
|----------|-------------|-------|
| `aofa_member_type` | `aofa_member` | Regular, Honorary |
| `aofa_committee_term` | `aofa_ec_member` | EC 2022-2023, EC 2024-2025, EC 2026-2027 |
| `aofa_article_category` | `aofa_article` | Article, Book Review |

---

## WP-CLI Commands

```bash
# Import regular members from bundled CSV
wp aofa import members

# Import with dry run (no DB changes)
wp aofa import members --dry-run

# Import honorary members from custom CSV
wp aofa import members --type=honorary --file=/path/to/honorary.csv

# Check content type counts
wp aofa status
```

---

## Data Files

Place import CSV files in `data/`:

```
aofa-core/
└── data/
    ├── members.csv          # 92 regular members from archive
    └── honorary_members.csv # 14 honorary members from archive
```

### CSV Format

```csv
no,name,address,phone,email
1,Amb. Example Name,"House 1, Road 1, Dhaka",01700-000000,email@example.com
```

---

## Security

- All inputs sanitized with `sanitize_text_field()`, `sanitize_email()`, `sanitize_textarea_field()`
- Uses `wp_insert_post()` (never raw SQL)
- No direct file edit capabilities (`DISALLOW_FILE_EDIT` in wp-config-extra.php)
- Nonces used on any admin forms added in future

## Coding Standards

Follows [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/).
