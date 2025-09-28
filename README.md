# QS Tech Workflow Automation

A PHP/MySQL web application that automates the complete customer journey from lead capture to completion. Features automatic Jira task creation, inventory monitoring with low-stock alerts, KPI dashboard, and customer email automation. Built for cPanel hosting with clean architecture and comprehensive documentation.

## Project Overview

**6-Week MVP** that creates an end-to-end workflow automation system connecting lead intake, Jira, inventory, and email communications.

### Key Features

- **Lead Capture** with auto-reply emails
- **Automatic Jira Integration** for task management
- **Inventory Management** with threshold alerts
- **KPI Dashboard** with business metrics
- **Customer Email Automation** with upsell opportunities
- **Cron-based Automation** and monitoring

## Technology Stack

- **Backend**: PHP 8.x, MySQL 8.x
- **Hosting**: cPanel Shared Hosting
- **Integrations**: Jira REST API, SMTP
- **Automation**: Cron Jobs
- **Documentation**: Confluence

## Project Structure

```
qs-tech-workflow-automation/
├── docs/                          # Documentation
│   ├── architecture.md            # System architecture
│   ├── setup_guide.md            # Installation guide
│   ├── runbook.md                # Operational procedures
│   └── admin_guide.md            # Administration guide
├── src/                          # Source code
│   ├── config.php                # Database configuration
│   └── index.php                 # Application entry point
├── scripts/                      # Automation scripts
│   ├── cron/                     # Scheduled tasks
│   └── db/
│       └── schema.sql            # Database schema
├── .env.example                  # Environment variables template
├── .gitignore                    # Git ignore rules
└── README.md                     # This file
```

## Database Schema

### Core Tables

**1. leads**
- Customer lead information with Jira integration
- Fields: id, name, email, phone, budget, message, jira_issue_key, status, created_at

**2. inventory**
- Stock tracking with threshold monitoring
- Fields: id, name, qty, threshold, last_updated

**3. audit_log**
- System activity and error logging
- Fields: id, action, entity, entity_id, details, created_at

**4. kpis**
- Business metrics and analytics
- Fields: id, name, value, recorded_at
