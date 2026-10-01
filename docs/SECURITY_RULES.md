# 🔒 Security Rules (SECURITY_RULES)

*(This file contains strict security constraints that AI Agents must follow to avoid introducing security vulnerabilities. Customize for your project)*

## 1. Secrets & Token Management
- **NEVER** hardcode API Keys, JWT Tokens, SSH Keys, or passwords in source code.
- All sensitive configs must be read from environment variables (`.env`, or local config files like Android's `local.properties`).
- Make sure local config files like `.env`, `local.properties`, or local build folders are always listed in `.gitignore`.

## 2. Authorization & Data Isolation
- Every API endpoint that handles user-provided data must validate tokens and verify role-based permissions (Role-based Access Control).
- Never return sensitive user information (such as hashed passwords or PIN codes) in API payloads.
- Always use Prepared Statements or ORMs to prevent SQL Injection vulnerabilities.
