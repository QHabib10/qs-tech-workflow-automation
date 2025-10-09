# QS Tech Workflow Automation

A PHP/MySQL web app automating the journey from lead capture to completion: auto Jira task creation, inventory monitoring with low‑stock alerts, KPI dashboard, and customer communications. Designed for cPanel; supports local development.

## Project Overview

**6-Week MVP** that creates an end-to-end workflow automation system connecting lead intake, Jira, inventory, and email communications.

### Key Features

- **Lead Capture**: Form saves to DB, keyword parsing, auto‑reply via SMTP
- **Jira Integration**: Creates issue with lead info and link back to app
- **Calendar View**: Simple list/cards for issues due this week/next 7 days
- **Inventory & Alerts**: Hourly checks, emails PM, alerts UI, draft PO view
- **KPI Dashboard**: 5 tiles (Open, Overdue, Low‑stock, Avg budget, Completed)
- **Audit Logging**: All key actions stored in `audit_log`

## Technology Stack

- **Backend**: PHP 8.x, MySQL 8.x
- **Packages**: Composer, `guzzlehttp/guzzle`, `phpmailer/phpmailer`, `vlucas/phpdotenv`
- **Integrations**: Jira REST API, SMTP
- **Automation**: Cron Jobs (cPanel or local scheduler)
- **Documentation**: Confluence

## Project Structure

```
qs-tech-workflow-automation/
├── docs/
│   └── architecture.md
├── src/
│   ├── components/
│   │   ├── sidebar.php
│   │   └── alert.php
│   ├── pages/
│   │   ├── lead_capture.php
│   │   ├── calendar.php
│   │   ├── inventory.php
│   │   ├── po.php
│   │   └── dashboard.php
│   ├── services/
│   │   ├── EmailService.php
│   │   ├── JiraService.php
│   │   └── KeywordService.php
│   ├── config.php
│   └── index.php
├── assets/
│   ├── sidebar.css
│   ├── lead_form.css
│   ├── calendar.css
│   ├── inventory.css
│   └── dashboard.css
├── scripts/
│   ├── cron/
│   │   ├── inventory_check.php
│   │   └── jira_completion_check.php
│   └── db/
│       └── schema.sql
├── .env.example
├── .gitignore
└── README.md
```

## Database Schema

Defined in `scripts/db/schema.sql`.

### Core Tables

- **leads**: id, name, email, phone, budget, message, jira_issue_key, created_at
- **inventory**: id, name, qty, threshold, last_updated
- **alerts**: id, item_name, item_qty, threshold, status, created_at, resolved_at
- **audit_log**: id, action, entity, entity_id, details(JSON), created_at

## Environment Variables

Copy `.env.example` to `.env` and set values 

- DB_HOST, DB_USER, DB_PASS, DB_NAME
- SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_SECURE, FROM_EMAIL, FROM_NAME, REPLY_TO
- JIRA_URL, JIRA_EMAIL, JIRA_API_TOKEN, JIRA_PROJECT_KEY, JIRA_ISSUE_TYPE
- PROCUREMENT_PM_EMAIL

## Local Setup

1) Install prerequisites
- PHP 8.x, MySQL 8.x, Composer

2) Install dependencies
```bash
composer install
```

3) Create database and tables
- Create database (e.g., `qs_tech`)
- Run `scripts/db/schema.sql` in MySQL Workbench or CLI

4) Configure environment
- Copy `.env.example` → `.env`
- Fill DB/SMTP/Jira settings

5) Run locally
```bash
php -S localhost:8000
```
Open `http://localhost:8000/` → Lead Capture.

## Cron Jobs

Two scripts under `scripts/cron/`:

- `inventory_check.php`:
  - Creates an alert and emails PM when `inventory.qty < threshold` and no open alert exists
  - Resolves open alerts when `qty >= threshold` and updates `alerts.item_qty`, `alerts.threshold`, `resolved_at`
  - Logs actions to `audit_log` with `entity='inventory'` and `entity_id` of the inventory row

- `jira_completion_check.php`:
  - Finds Jira issues moved to Done recently, maps by `leads.jira_issue_key`, and sends completion emails
  - Deduplicates via `audit_log`

Run locally (manual):
```bash
php scripts/cron/inventory_check.php
php scripts/cron/jira_completion_check.php
```

## Jira Integration Notes

- Requires Composer: `guzzlehttp/guzzle`
- `JiraService` builds descriptions in ADF JSON; `priority` omitted to avoid 400 errors
- Include `vendor/autoload.php` in pages/services using Guzzle

## UI/UX

- Consistent light theme; responsive sidebar and calendar filters
- Reusable `alert.php` with auto‑hide behavior
- PRG pattern in lead submission prevents duplicate inserts on refresh

## Git Workflow (recommended)

- Branches: `main` (protected), `dev`
- Conventional Commits; PRs from `dev` → `main`
- Protect `main` with required PR review and status checks


