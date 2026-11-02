# Hostinger/OpenCATS ATS implementation handoff

Source fork: opencats/OpenCATS. Work only on `develop/recruitment-saas` until reviewed.

## Current stack (verified from repository)
- PHP 8.4.1+ (within PHP 8), MariaDB. OpenCATS 0.11.1 is the modern security/maintenance release baseline.
- The repo's `config.php` contains development database credentials and SSL_ENABLED=false; **do not upload unchanged configuration to production**.
- Shared hosting must be checked for MariaDB version (project CI uses 10.7), mysqli, mbstring, gd, zip, soap and writable storage. Document parsers are optional for initial install.
- Use the full release archive containing production dependencies when establishing an installation; raw source checkout can require Composer. Verify release archive is appropriate to a custom fork.
- Preserve original OpenCATS/CATS license notices; `LICENSE.md` contains both MPL-2.0 and legacy CATS Public License 1.1a. Verify branding/commercial redistribution conditions before shipping.

## Important SaaS constraint
OpenCATS v0.11.0 removed unused legacy `site_id` multi-tenant infrastructure. The Companies/Clients feature does **not** isolate SaaS customer data.
Do not re-introduce site_id blindly or claim this is already multi-tenant.
For initial safe tenant isolation use one OpenCATS installation and separate MariaDB database plus storage per company, with controlled provisioning. A shared SaaS control plane / per-row tenancy is a separate redesign requiring security tests.
For MVP, launch a *single private test tenant* first.

## First implementation slice
1. Hostinger PHP/DB/extensions check using a CLI-only script; do not publish a phpinfo page.
2. Non-production install with new credentials and HTTPS on; block installer/maintenance endpoints after setup.
3. Validate jobs, career portal, applications, upload, email and permissions on a single test company.
4. Design Saudi Arabic + English templates; add RTL styles progressively without rewriting the recruitment logic.
5. Build tenant provisioning and isolation design, roles and audit before onboarding another company.
6. Add API/webhooks/Odoo connection after authentication, rate limiting and data controls.

## Security/acceptance
Private CV files, upload limits/type validation and malware scan handling. Apply rate limits and CSRF/XSS protections. Validate backups, exports and recovery. Test tenant-crossing access; keep candidate PII out of logs/analytics. Do not push secrets to GitHub.
Do not deploy this repo as-is or modify the live Hostinger account from this plan.

## Build note
This fork is the near-term usable ATS track, not a direct Cloudflare Worker deploy.
