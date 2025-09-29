# Architecture - QS Tech Workflow Automation

## 1. System Overview.

**Project Name:** QS Tech Workflow Automation (MVP)

**Purpose:** Provide a central platform to capture customer leads, manage inventory, log all system activities, and calculate key KPIs.

**Scope:**

* Hosted on **cPanel** using **PHP 8.x** and **MySQL 8.x**.
* Integrates with **Jira** for issue tracking.
* Uses **SMTP** for automated notifications (auto-replies, alerts, completion emails).
* Provides a **calendar view** for due dates and a **dashboard** for KPIs.

## 2. Components.

* **cPanel (PHP 8.x + MySQL 8.x)** — hosting platform providing runtime, database, cron scheduler, and email services.
* **App (PHP scripts)** — lead form, Jira integration, cron jobs, KPI dashboard, email handlers.
* **Database** — stores leads, inventory, audit logs, and KPI data.
* **Jira** — issue tracking, integrated via REST API.
* **SMTP** — transactional emails (auto-replies, alerts, completion emails).
* **GitHub** — version control, branching workflow, pull requests, and code reviews.
* **Confluence** — documentation hub.

## 3. Tech Stack.

* **Frontend / Backend**: PHP 8.x
* **Database**: MySQL 8.x
* **Hosting**: cPanel
* **Integrations**:
    * Jira REST API
    * SMTP
* **Version Control**: GitHub
* **Documentation**: Confluence

## 4. Data Flow (End-to-End Overview).

1. Customer submits a **lead form** → record saved in `leads` table.
2. The system sends an **auto-reply email** via SMTP.
3. Jira API automatically creates an issue.
4. App provides a **calendar view** with Jira due dates for visibility.
5. Hourly cron job checks **inventory levels**:
    * If below threshold → log to `audit_log`, email alert to PM, and draft a purchase order.
6. **KPI dashboard** aggregates metrics from Jira + database.
7. When Jira issue status = Done → cron sends a **completion email** to the customer and updates KPIs.

## 5. Database Schema.

### 1. `leads`

|     |     |     |
| --- | --- | --- |
| **Attributes** | **Data Type** | **Description** |
| id  | INT AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | Name of the lead |
| email | VARCHAR(255) | Email address of the lead |
| phone | VARCHAR(50) | Phone number of the lead |
| budget | DECIMAL(12,2) | Budget provided by the lead |
| message | TEXT | Message or inquiry from the lead |
| jira_issue_key | VARCHAR(50) | Associated Jira issue key |
| status | ENUM('new','in_progress','done') | Current status of the lead |
| created_at | TIMESTAMP | Timestamp when the lead was created |

### 2. `inventory`

| **Attributes** | **Data Type** | **Description** |
| --- | --- | --- |
| id  | INT AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | Name of the item |
| qty | INT | Available quantity |
| threashold | INT | threshold for the item |
| last_updated | TIMESTAMP | Timestamp when the record was last updated |

### 3. `audit_log`

| **Attribute** | **Data Type** | **Description** |
| --- | --- | --- |
| id  | INT AUTO_INCREMENT | Primary key |
| action | VARCHAR(100) | Action performed (e.g., create, update, delete) |
| entity | VARCHAR(50) | Name of the affected entity/table |
| entity_id | INT | ID of the affected entity |
| details | TEXT | Additional details about the action |
| created_at | TIMESTAMP | Timestamp when the action was logged |

### 4. `kpis`

| Field | Data Type | Description |
| --- | --- | --- |
| id  | INT AUTO_INCREMENT | Primary key |
| name | VARCHAR(100) | Name of the KPI |
| value | DECIMAL(18,4) | KPI value |
| recorded_at | TIMESTAMP | Timestamp when the KPI was recorded |

## 6. ER Diagram

![ER Diagram.png](https://maazkhalid05.atlassian.net/wiki/download/thumbnails/262148/ER%20Diagram.png?version=1&modificationDate=1758968056920&cacheVersion=1&api=v2&width=760&height=582)

## 7. Architecture Diagram.

![architecture_diagram.png](https://maazkhalid05.atlassian.net/wiki/download/thumbnails/262148/architecture_diagram.png?version=1&modificationDate=1758963241485&cacheVersion=1&api=v2&width=3840&height=961)