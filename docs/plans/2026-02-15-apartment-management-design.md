# Apartment Management System — Design Document

## Overview

A Laravel-based apartment management system for a single building (~50 units). The system manages maintenance billing, ad-hoc charges, payment tracking via GPay screenshots and HDFC bank statement imports, with Google Sheets as the primary record.

## Users

- Admin and committee members (3-5 people)
- No resident-facing portal

## Data Model

### units
- id, flat_number, flat_type (1BHK/2BHK/3BHK), floor, area_sqft
- created_at, updated_at

### residents
- id, unit_id (FK), name, phone, email, is_owner (bool), gpay_name
- `gpay_name` is used for auto-matching GPay screenshot OCR results
- created_at, updated_at

### maintenance_slabs
- id, flat_type, amount, effective_from
- Allows rate changes over time; latest slab for a flat_type is the active rate

### charges
- id, unit_id (nullable for community-wide), type (maintenance/ad-hoc), description, amount, billing_month, due_date, status (pending/partial/paid)
- Community-wide ad-hoc charges: created with unit_id = null, then expanded into one charge per unit
- created_at, updated_at

### payments
- id, charge_id (FK), unit_id (FK), amount, paid_date, source (gpay/bank_transfer/cash), reference_number, matched_by (auto/manual), reconciliation_status (pending_verification/bank_verified)
- created_at, updated_at

### expenses
- id, description, amount, paid_date, category (electricity/water/maintenance/service/other), source (gpay/bank_transfer/cash), reference_number, reconciliation_status (pending_verification/bank_verified)
- created_at, updated_at

### uploads
- id, file_path, type (gpay_screenshot/bank_statement), status (pending/processing/processed/failed), processed_at, uploaded_by
- created_at, updated_at

### parsed_transactions
- id, upload_id (FK), raw_text, sender_name, amount, date, direction (credit/debit), fingerprint (hash for dedup), match_type (payment/expense/unmatched), matched_payment_id (nullable FK), matched_expense_id (nullable FK), reconciliation_status (auto_matched/manual_matched/unmatched/reconciled)
- `fingerprint` = hash(amount + date + normalized_name) for deduplication
- created_at, updated_at

## Modules

### Apartment Module
- CRUD for units and residents
- Maintenance slab management
- Filament resources for admin panel

### Billing Module
- Monthly maintenance charge generation (based on slabs)
- Ad-hoc charge creation (community-wide or per-unit)
- Payment recording (manual entry)
- Dashboard showing dues, collections, balances per unit

### Import Module
- File upload handling (GPay screenshots, HDFC bank PDFs)
- GPay screenshot OCR via Laravel AI SDK (Claude Vision / Gemini)
- HDFC bank statement PDF parsing via Laravel AI SDK
- Auto-matching logic:
  - GPay screenshots: fuzzy match sender_name against residents.gpay_name
  - Bank statements: match by amount + date (±1 day) + name similarity
- Review queue for uncertain matches
- Deduplication: bank statement entries checked against existing payments/expenses using fingerprint

### Sheet Module
- Google Sheets integration via revolution/laravel-google-sheets
- Monthly tabs: one row per transaction (date, unit, resident, amount, type, reconciliation status)
- Summary tab: one row per unit, columns per month, total due, total paid, outstanding balance
- Sync triggered on payment/charge/expense changes via queued jobs

## Key Workflows

### 1. Monthly Maintenance Generation
1. Admin selects billing month and triggers generation
2. System looks up each unit's flat_type → finds active slab rate
3. Creates one charge record per unit
4. Queues Google Sheets sync to update monthly tab and summary tab

### 2. Ad-hoc Charge Creation
1. Admin creates charge — selects community-wide or specific unit(s)
2. If community-wide: system creates one charge per unit using the specified amount
3. Queues Google Sheets sync

### 3. GPay Screenshot Processing (Incoming Payments)
1. Admin uploads one or more screenshots
2. Job dispatched per screenshot → Laravel AI SDK sends image to Claude/Gemini
3. AI extracts: sender name, amount, date, direction (credit/debit)
4. Credit transactions: fuzzy match sender_name against residents.gpay_name
   - Confident match → auto-create payment, link to oldest pending charge for that unit
   - Uncertain → add to review queue
5. Debit transactions: create expense record, admin categorizes later
6. Fingerprint generated for deduplication

### 4. Bank Statement Processing
1. Admin uploads HDFC PDF
2. Job dispatched → AI-assisted PDF parsing extracts transaction rows
3. Each row becomes a parsed_transaction with fingerprint
4. Deduplication check: compare fingerprint against existing parsed_transactions
5. For non-duplicate credits: match against existing payments by amount + date
   - Match found → update payment reconciliation_status to bank_verified
   - No match → check against residents, create payment or flag for review
6. For non-duplicate debits: match against existing expenses
   - Match found → update expense reconciliation_status to bank_verified
   - No match → create expense or flag for review

### 5. Google Sheets Sync
- Triggered async on any payment/charge/expense change
- Monthly tab format: Date | Unit | Resident | Amount | Type | Source | Reconciliation Status
- Summary tab format: Unit | Resident | Jan | Feb | ... | Dec | Total Due | Total Paid | Balance
- Summary tab auto-updates running balances

### 6. Reconciliation Audit
- Dashboard view showing:
  - Payments with pending_verification (GPay captured, not yet in bank statement)
  - Bank-only transactions (in statement but no GPay record)
  - Fully reconciled transactions
- Helps committee verify no amounts are double-counted or missed

## Tech Stack

- **Laravel 12** with modular architecture (nwidart/laravel-modules)
- **Filament 3** for admin panel
- **Laravel AI SDK** (laravel/ai) for OCR and PDF parsing (Claude Vision + Gemini)
- **revolution/laravel-google-sheets** for Sheets integration
- **Laravel Queues** for async processing
- **SQLite/MySQL** for local database
- **HDFC PDF parser** tailored to HDFC statement format

## Reconciliation & Deduplication Strategy

The core problem: a resident pays via GPay → admin uploads screenshot → later admin uploads bank statement containing the same transaction. Without deduplication, the payment would be counted twice.

Solution:
1. Every parsed transaction gets a fingerprint: hash(amount + date + normalized_sender_name)
2. Bank statement processing checks fingerprints against all existing parsed_transactions
3. If fingerprint matches → link to existing record, mark as reconciled (not re-added)
4. If amount + date match but name differs slightly → flag for manual review
5. Reconciliation statuses provide clear audit trail:
   - `pending_verification`: captured from GPay, awaiting bank confirmation
   - `bank_verified`: confirmed in both GPay and bank statement
   - `bank_only`: only appears in bank statement
