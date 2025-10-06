# Internal Audit - VisionMetrics Hostinger Production Readiness

**Date**: 2025-10-06  
**Branch**: feature/hostinger-prod  
**Purpose**: Comprehensive audit before transformation to Hostinger-ready deployment

---

## 1. Docker Artifacts (TO BE MOVED)

The following Docker-related files need to be moved to `deprecated/docker/`:

- `docker-compose.yml` - Docker Compose configuration with MySQL and Redis
- `Dockerfile` - Main application Dockerfile
- `docker/` directory:
  - `docker/apache.conf`
  - `docker/vhost.conf`
- `worker/Dockerfile` - Worker container Dockerfile
- `reset-docker.ps1` - PowerShell reset script
- `reset-docker.sh` - Bash reset script

**Action**: Move all to `deprecated/docker/` preserving directory structure

---

## 2. Environment Configuration Issues

### Current State:
- `env.example` exists with Docker-oriented defaults (mysql, redis hosts)
- References Redis (not available on Hostinger)
- No `.env` file in repository (correct)
- Multiple ENV documentation files that need consolidation:
  - `ENV_HOSTINGER_EXAMPLE.txt`
  - `env_pronto.txt`
  - `COPIE_ESTE_CONTEUDO_PARA_ENV.txt`
  - `COPIE_ESTE_CONTEUDO_PARA_ENV_NO_SERVIDOR.txt`

### Required ENV Variables (Missing from current env.example):
- `WHATSAPP_VERIFY_TOKEN` - WhatsApp webhook verification
- `WHATSAPP_PHONE_NUMBER_ID` - WhatsApp Cloud API phone number ID
- `WHATSAPP_ACCESS_TOKEN` - WhatsApp Cloud API access token
- `MERCADOPAGO_ACCESS_TOKEN` - MercadoPago API access
- `MERCADOPAGO_PUBLIC_KEY` - MercadoPago public key
- `MERCADOPAGO_WEBHOOK_TOKEN` - MercadoPago webhook validation
- `ADMIN_EMAIL` - Default admin account email
- `ADMIN_PASS` - Default admin account password
- `ADAPTER_MODE` - Set to 'live' or 'simulate' for integrations
- `RATE_LIMIT_SHORTENER` - Rate limit for shortener redirect (requests per minute)

### Redis References to Remove:
- `REDIS_HOST` and `REDIS_PORT` in env.example
- Any Redis usage in code (needs DB-based queue instead)

**Action**: Create single `.env` file with all placeholders, remove Redis references

---

## 3. Database Schema Analysis

### Existing Tables (sql/schema.sql):
✅ `users` - User authentication  
✅ `workspaces` - Multi-tenant workspaces  
✅ `workspace_members` - Workspace membership  
✅ `subscriptions` - Billing and subscriptions  
✅ `payments` - Payment records  
✅ `leads` - Lead tracking  
✅ `events` - Event tracking  
✅ `sessions` - Session management  
✅ `integrations` - Integration credentials  
✅ `webhooks_logs` - Webhook logging  
✅ `trackable_links` - Link shortener  
✅ `whatsapp_numbers` - WhatsApp number registration  
✅ `conversations` - WhatsApp conversations  
✅ `messages` - WhatsApp messages  

### Tables Mentioned in Migrations:
✅ `tags` - Lead tags  
✅ `lead_tags` - Lead-tag relationship  
✅ `tasks` - Task management  
✅ `notes` - Lead notes  
✅ `custom_fields` - Custom field definitions  
✅ `workflows` - Automation workflows  
✅ `sales` - Sales records  

### Missing Critical Tables:
❌ `queue_jobs` - Job queue for worker (MUST CREATE)  
❌ `clicks` or `link_clicks` - Click tracking for shortener (EXISTS as link_clicks in backend/l.php but not in schema)  
❌ `conversions` - Conversion tracking (referenced in requirements but not present)  
⚠️ `jobs_log` - Exists in schema.sql but may need renaming to `queue_jobs` for consistency  

### Schema Issues:
- `jobs_log` exists but uses different structure than needed
- `trackable_links` missing `slug` column (schema.sql uses `short_code`, migration tries to add `slug`)
- `leads` table missing `first_touch_token` column for cookie tracking
- No `link_clicks` table in schema (code references it)
- `subscriptions` has `mercadopago_*` fields but may need additional conversion tracking fields

**Action**: Create migration for:
1. Rename `jobs_log` to `queue_jobs` OR create new `queue_jobs` table
2. Create `link_clicks` table with columns: id, trackable_link_id, ip_address, user_agent, referrer, utm_source, utm_medium, utm_campaign, utm_term, utm_content, cookie_token, created_at
3. Add `first_touch_token` VARCHAR(255) to `leads` table with index
4. Create `conversions` table with fields: id, workspace_id, lead_id, subscription_id, conversion_type, value, currency, utm_*, provider_payment_id, idempotency_key, metadata (JSON), created_at
5. Ensure `trackable_links` has `slug`, `expires_at`, `is_active`, `type` (whatsapp/redirect), `whatsapp_phone`, `whatsapp_message_template`

---

## 4. Code Audit - TODOs and Placeholders

### src/adapters/TikTokAdapter.php
- Line 14: `TODO: Implementar TikTok Events API v1.3`

### src/adapters/MercadoPagoAdapter.php
- Line 108: `TODO: Implementar validação real` (webhook signature validation)

### webhooks/whatsapp.php
- Line 71: `TODO: Associar ao workspace correto` (hardcoded workspace_id = 1)
- Missing vm_token extraction and attribution logic

### backend/l.php (Shortener)
**CRITICAL - Needs Complete Rewrite:**
- ❌ No cookie setting (`vm_first_touch`)
- ❌ No UTM extraction from querystring
- ❌ No queue job creation
- ❌ No rate limiting
- ❌ Uses `link_clicks` table (not in schema)
- ❌ No WhatsApp deeplink generation
- ❌ No expiry checking
- ✅ Basic redirect working
- ✅ IP and UA capture working

### mercadopago/webhook.php
**Needs Hardening:**
- ✅ Logs to `webhooks_logs`
- ✅ Basic payment processing
- ⚠️ Webhook validation not implemented (TODO in adapter)
- ❌ No idempotency check (provider_payment_id uniqueness)
- ❌ No conversion record creation
- ❌ No lead mapping via cookie token
- ❌ No queue job creation for analytics

### backend/whatsapp.php (UI)
- This is the admin UI page (correct)

### webhooks/whatsapp.php (Handler)
- ✅ Webhook verification working
- ✅ Basic message processing
- ⚠️ Hardcoded workspace_id = 1
- ❌ No vm_token extraction from message text
- ❌ No lead attribution by token
- ❌ No conversion event tracking

---

## 5. Analytics Adapters Status

### src/adapters/GA4Adapter.php
✅ **READY**
- Supports workspace-based credentials
- Supports simulate mode
- Has `sendEvent()` method accepting clientId, eventName, params
- Returns success/failure for retry logic
- Uses Measurement Protocol API

### src/adapters/MetaAdapter.php
✅ **READY**
- Supports workspace-based credentials
- Supports simulate mode
- Has `sendConversion()` method with user_data hashing
- Returns success/failure for retry logic
- Supports event_id for deduplication

### Integration Pattern:
Both adapters:
1. Check `ADAPTER_MODE` env variable
2. Load workspace-specific credentials from `integrations` table
3. Fallback to global env vars if no workspace credentials
4. Return structured response with success/failure

**Action**: Connect these adapters to worker queue processing

---

## 6. Worker/Queue System

### Current State:
- `worker/Dockerfile` exists (Docker-based)
- `worker/process_jobs.php` exists (need to review)
- Uses Redis in Docker context
- No CRON-friendly script

### Required:
- Create `scripts/worker.php` for CRON execution
- Query `queue_jobs` table for pending jobs
- Process by type: `click`, `conversion`, `whatsapp_message`
- Call GA4Adapter and MetaAdapter
- Implement exponential backoff on failure
- Log to `logs/worker.log`

**Action**: Create new worker script suitable for Hostinger cron

---

## 7. Missing Implementation Components

### A. Redirect Handler (backend/l.php or new routing)
Must implement:
1. ✅ Slug validation
2. ❌ Expiry check (`expires_at` field)
3. ❌ Cookie `vm_first_touch` generation and reading (365 days)
4. ❌ UTM extraction from querystring (with referer fallback)
5. ❌ Insert to `link_clicks` with cookie_token
6. ❌ Insert to `queue_jobs` with click event
7. ❌ WhatsApp deeplink generation (if type=whatsapp)
8. ❌ Rate limiting (configurable via env)

### B. WhatsApp Webhook Enhancement
Must implement:
1. ✅ Webhook verification
2. ✅ Basic message processing
3. ❌ Extract `vm_token:<UUID>` from message text
4. ❌ Lookup lead by `first_touch_token`
5. ❌ Fallback to phone number matching
6. ❌ Create/update conversation with attribution
7. ❌ Queue conversion event if qualifying

### C. MercadoPago Webhook Hardening
Must implement:
1. ✅ Raw payload logging
2. ❌ Idempotency via `provider_payment_id` unique index
3. ❌ Lookup subscription by preference_id
4. ❌ Extract lead_id via metadata or session lookup
5. ❌ Create `conversion` record
6. ❌ Queue analytics event (purchase)

### D. Admin Creation Script
Must create:
- `scripts/create_admin.php` reading ADMIN_EMAIL/ADMIN_PASS from .env

---

## 8. File Organization Issues

### Documentation Overload:
Too many instruction files in root (50+ MD/TXT files):
- `!!! COMECE AQUI !!!.txt`
- `COMECE_AQUI_AGORA.txt`
- `COMO_USAR.txt`
- `FACIL_SO_2_PASSOS.txt`
- `FAZER_UPLOAD_AGORA.txt`
- `FINAL_INSTRUCTIONS.txt`
- Multiple `SISTEMA_*.md`, `INSTRUCOES_*.txt`, etc.

**Recommendation**: Keep only essential documentation, move rest to `deprecated/docs/` if needed

### Tests Directory:
- `tests/` directory exists with 4 PHP files
- Not being used in CI/CD
- Can remain but not priority for Hostinger deployment

---

## 9. Dependencies Check

### composer.json Review Needed:
- Check for Docker-specific dependencies
- Ensure PHP extensions available on Hostinger: PDO, mysqli, curl, json, mbstring

### Required PHP Extensions for Hostinger:
- ✅ PDO + pdo_mysql
- ✅ mysqli
- ✅ curl (for API calls)
- ✅ json
- ✅ mbstring
- ✅ openssl (for hashing)
- ⚠️ Check: GD or Imagick (for QR codes if used)

---

## 10. Security Audit

### CSRF Protection:
✅ Implemented in `backend/config.php` (csrf_token(), csrf_field(), csrf_verify())

### Rate Limiting:
✅ Basic rate limiting exists for login (`checkRateLimit()`)  
❌ Need to add for shortener redirect

### SQL Injection:
✅ Using prepared statements throughout

### Sensitive Data:
⚠️ Multiple plain-text credential files in root (need cleanup)

---

## 11. Hostinger-Specific Requirements

### Changes Needed:
1. ✅ Remove Redis dependency
2. ❌ Change from Docker database to Hostinger MySQL
3. ❌ Configure CRON jobs (worker script)
4. ❌ Set up .htaccess for Apache (if needed)
5. ❌ Configure PHP version and extensions
6. ❌ Set proper file permissions for `logs/` and `uploads/`

### Database Migrations on Hostinger:
- Run `sql/schema.sql`
- Run `sql/migrations/*.sql`
- Run new migrations created for queue_jobs, clicks, conversions
- Run `sql/seed.sql` (if applicable)

---

## 12. Deployment Checklist Items for DEPLOY_SUMMARY.txt

Must include:
1. PHP version requirements (7.4+ or 8.0+)
2. Required PHP extensions
3. Database setup commands
4. Migration run order
5. Environment variables complete list
6. CRON job configuration (worker script every 1-5 minutes)
7. File permissions for logs/ and uploads/
8. WhatsApp Cloud API webhook configuration URL
9. MercadoPago webhook configuration URL
10. Google Analytics 4 setup (measurement_id + api_secret)
11. Meta/Facebook setup (pixel_id + access_token)
12. Initial admin account creation script

---

## 13. Priority Implementation Order

1. ✅ **Audit Complete** - This document
2. ⬜ Move Docker artifacts
3. ⬜ Create consolidated `.env`
4. ⬜ Create database migrations (queue_jobs, clicks, conversions, lead columns)
5. ⬜ Rewrite `backend/l.php` redirect handler
6. ⬜ Create `scripts/worker.php` for CRON
7. ⬜ Connect adapters to worker
8. ⬜ Enhance `webhooks/whatsapp.php` with vm_token attribution
9. ⬜ Harden `mercadopago/webhook.php` with idempotency
10. ⬜ Create `scripts/create_admin.php`
11. ⬜ Create `DEPLOY_SUMMARY.txt`
12. ⬜ Final audit and PR creation

---

## 14. Acceptance Criteria

### Must Pass:
- [ ] `GET /backend/l.php?slug=test123` sets `vm_first_touch` cookie
- [ ] Click record created in `link_clicks` table
- [ ] Queue job created in `queue_jobs` table
- [ ] CRON worker processes queue_jobs successfully
- [ ] GA4Adapter receives event and logs HTTP 200/204
- [ ] MetaAdapter receives event and logs success
- [ ] WhatsApp webhook associates message with lead via vm_token
- [ ] WhatsApp webhook falls back to phone number matching
- [ ] MercadoPago webhook is idempotent (duplicate payment_id doesn't create duplicate records)
- [ ] MercadoPago webhook creates conversion tied to lead
- [ ] No Docker files remain in root directory
- [ ] Only one `.env` file (with placeholders) in repository
- [ ] `DEPLOY_SUMMARY.txt` created with complete instructions

---

**End of Audit**

