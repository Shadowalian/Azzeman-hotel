# Azzeman Hotel

PHP hotel site (landing + booking + admin) for Azzeman Hotel, Addis Ababa.

## Local

1. MySQL database `azzemanhotel_sinq` (import `azzemanhotel_mysql.sql` if needed)
2. Copy `.env.example` → `.env` and set DB credentials
3. `composer install`
4. `php -S localhost:8000 router.php`
5. Site: http://localhost:8000 — Admin: http://localhost:8000/admin-login

## GitHub

Repo: https://github.com/Shadowalian/Azzeman-hotel

## Vercel deploy

This app is PHP + MySQL. Vercel runs PHP via `vercel-php`; **MySQL must be a remote database** Vercel can reach (PlanetScale, Railway, Neon MySQL-compatible, or host MySQL with remote access).

1. Push this repo to GitHub
2. Import the project in [Vercel](https://vercel.com/new)
3. Set Environment Variables (from `.env.example`):
   - `APP_URL` = your Vercel URL (e.g. `https://azzeman-hotel.vercel.app`)
   - `EMAIL_BASE_URL` = same with trailing slash
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
   - Optional later: `BREVO_API_KEY`, `SMTP_*`, `OPENROUTER_API_KEY`, `GEMINI_API_KEY`
4. Deploy

### Notes

- **Uploads** on Vercel’s serverless disk are ephemeral — for production media, keep using your existing host storage or S3 later.
- **Brevo / SMTP** can stay empty until you are ready; booking emails will activate when you set `BREVO_API_KEY` (and SMTP if used).
- Never commit `.env` or live API keys.

## Admin (default seed)

See SQL seed / your DB. Local default often: `azzeman_admin` (rotate password in production).
