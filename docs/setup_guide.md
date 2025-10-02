## Setup Guide – QS Tech Workflow Automation

## 1. Prerequisites.

* **Git** (latest version recommended)
* **PHP 8.x** (CLI version for local testing)
* **MySQL 8.x** (server + client tools)
* **Confluence** (for documentation and runbook)
* **cPanel** (Hosting environment for deployment)

## 2. Repository Structure.

```
qs-tech-workflow-automation/
├── src/          # Application source code (PHP)
├── scripts/      # Utility and database scripts
│   └── db/       # Schema definition
│   └── cron/     # Cron job scripts 
├── docs/         # Documentation (mirrors Confluence pages)
├── .gitignore
├── README.md
```

## 3. PHP Version 8.x Setup in cPanel

1. Log in to **cPanel** and go to **Tools** → **Software → MultiPHP Manager**.
2. Find your domain (e.g., `23596401.it.scu.edu.au`).
3. From the dropdown, select **PHP 8.x** and click **Apply**.

## 4. Database Setup in cPanel.

1. Navigate to **cPanel → Tools → Databases → MySQL Databases.**
2. Under **Create New Database**: enter the database name (e.g., `qs_tech`).
3. Under **MySQL Users**: create database user (e.g., `qs_user`) with a strong password → **Create**.
4. Under **Add User To Database**: add `qs_user` → `qs_tech`, grant **All Privileges** → **Make Changes**.
5. Note down the DB host (by default, localhost), name, user, and password.

## 5. Configuration & Deployment Strategy

### 1. Secrets Management

* All sensitive credentials (DB, SMTP, Jira API tokens) are stored in the **home directory** (e.g., `/home/qhabib10/private/env.php`).
* The file is kept **outside** `public_html` to prevent web access.
* File permissions are restricted (`chmod 600`), so only the cPanel user and the PHP runtime can read it.

### 2. Application Settings

* **Non-sensitive settings** (e.g., inventory thresholds, constants, etc) are stored directly in code or in the database.
* **Environment-specific configurations** (e.g., dev vs. production) are handled via environment variables loaded from the private `env.php` file.

### 3. Deployment Strategy

* Code is deployed from **GitHub → cPanel** (manual upload or Git clone).
* **Production runs on the** `main` **branch only**.
* **No direct code edits on the server** — all changes go through Git and Pull Requests.
* **Cron jobs** are configured in cPanel for scheduled tasks (e.g., inventory checks, Jira polling, KPI updates).

## 6. Git Workflow

### **1. Branching model:**

* `main` → production branch (protected).
* `dev` → integration branch.

### 2. Workflow

1. Developer pulls the latest changes from `dev`.
2. Makes changes locally (features, fixes, docs).
3. Commits changes with **clear commit messages**.
4. Pushes changes to `dev`.
5. After review and testing, `dev` is merged into `main` via Pull Request.

### 3. Commit Messages

We follow the Conventional Commits standard:

* `feat:` → new features
* `fix:` → bug fixes
* `docs:` → documentation changes
* `chore:` → non-code tasks (config, setup, cleanup)

### **4. Commit History Example:**

```java
docs: sync architecture content from Confluence
chore: Initial Project Setup
```