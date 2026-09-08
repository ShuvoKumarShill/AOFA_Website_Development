# AOFA Website — Makefile
# Spec Item: 001-initial-setup
#
# Developer convenience commands. All Docker operations use
# the compose project name "aofa" for easy identification.

COMPOSE := docker compose
WPCLI   := $(COMPOSE) run --rm wpcli wp

.DEFAULT_GOAL := help

.PHONY: help up down restart logs shell install import-members lint spec

## ── Docker ──────────────────────────────────────────────────────────────────

help: ## Show this help message
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
	  awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Start all Docker services
	$(COMPOSE) up -d
	@echo "\n✅  WordPress:  http://localhost:8080"
	@echo "✅  phpMyAdmin: http://localhost:8081"

down: ## Stop and remove containers (data volumes preserved)
	$(COMPOSE) down

restart: ## Restart all services
	$(COMPOSE) restart

logs: ## Tail logs from all services
	$(COMPOSE) logs -f

logs-wp: ## Tail WordPress container logs only
	$(COMPOSE) logs -f wordpress

## ── WordPress Setup ─────────────────────────────────────────────────────────

install: ## Install WordPress via WP-CLI (run after `make up`)
	@echo "⏳  Waiting for WordPress to be ready..."
	@sleep 5
	$(WPCLI) core install \
	  --url=http://localhost:8080 \
	  --title="Association of Former Ambassadors" \
	  --admin_user=admin \
	  --admin_password=admin123 \
	  --admin_email=admin@localhost.local \
	  --skip-email
	$(WPCLI) theme activate aofa-theme
	$(WPCLI) plugin activate aofa-core
	$(WPCLI) rewrite structure '/%postname%/'
	@echo "\n✅  WordPress installed. Admin: http://localhost:8080/wp-admin"
	@echo "    User: admin | Password: admin123"

theme-activate: ## Activate AOFA theme
	$(WPCLI) theme activate aofa-theme

plugin-activate: ## Activate AOFA Core plugin
	$(WPCLI) plugin activate aofa-core

## ── Content Import ───────────────────────────────────────────────────────────

import-members: ## Import members from recovered CSV via WP-CLI
	$(WPCLI) aofa import-members \
	  --file=/var/www/html/wp-content/plugins/aofa-core/data/members.csv
	@echo "✅  Members imported."

import-ec: ## Import Executive Committee members via WP-CLI
	$(WPCLI) aofa import-ec \
	  --file=/var/www/html/wp-content/plugins/aofa-core/data/ec_members.csv
	@echo "✅  EC Members imported."

import-notices: ## Import Notices via WP-CLI
	$(WPCLI) aofa import-notices
	@echo "✅  Notices imported."

import-articles: ## Import Articles via WP-CLI
	$(WPCLI) aofa import-articles
	@echo "✅  Articles imported."

## ── Quality & Specs ─────────────────────────────────────────────────────────

spec: ## Open Spec Kit constitution
	specify --help

shell: ## Open a bash shell in the WordPress container
	$(COMPOSE) exec wordpress bash

wpcli-shell: ## Run an interactive WP-CLI shell
	$(COMPOSE) run --rm wpcli wp shell

## ── Database ─────────────────────────────────────────────────────────────────

db-export: ## Export database to ./backups/aofa-$(date).sql
	@mkdir -p backups
	$(WPCLI) db export /var/www/html/wp-content/backups/aofa-$$(date +%Y%m%d-%H%M%S).sql
	@echo "✅  Database exported to wp-content/backups/"

db-import: ## Import database from file: make db-import FILE=backups/aofa.sql
	$(WPCLI) db import /var/www/html/$(FILE)
