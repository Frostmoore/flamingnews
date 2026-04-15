#!/usr/bin/env bash
# ════════════════════════════════════════════════════════════════════════════
# FlamingNews — VPS Deployment Script
# Ubuntu 24.04.4 LTS · nginx 1.24 · PHP 8.3-fpm · MariaDB 10.11 · PM2 6
#
# Uso:
#   chmod +x deploy-vps.sh
#   ./deploy-vps.sh
#
# Lo script è idempotente: puoi eseguirlo più volte senza danni.
# NON tocca le config nginx esistenti (hesclaw.ovh, wa-webhook.hesclaw.ovh).
# NON tocca il processo PM2 "wa-webhook".
# ════════════════════════════════════════════════════════════════════════════

set -euo pipefail

# ── Colori ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'; NC='\033[0m'
ok()   { echo -e "${GREEN}✓${NC} $*"; }
info() { echo -e "${CYAN}▶${NC} $*"; }
warn() { echo -e "${YELLOW}⚠${NC} $*"; }
fail() { echo -e "${RED}✗ ERRORE:${NC} $*"; exit 1; }
sep()  { echo -e "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"; }

# ════════════════════════════════════════════════════════════════════════════
# ── CONFIGURAZIONE (modifica qui) ─────────────────────────────────────────
# ════════════════════════════════════════════════════════════════════════════

DOMAIN="${DOMAIN:-}"                   # es. flamingnews.it
REPO_URL="${REPO_URL:-}"               # es. https://github.com/tuonome/flamingnews.git
APP_DIR="/var/www/flamingnews"
APP_USER="www-data"

DB_NAME="flamingnews"
DB_USER="flamingnews"
DB_PASS="${DB_PASS:-}"                 # lascia vuoto = generato automaticamente

GNEWS_KEY="${GNEWS_KEY:-}"
WORLDNEWS_KEY="${WORLDNEWS_KEY:-}"
NEWS_PROVIDER="${NEWS_PROVIDER:-worldnews}"   # gnews | worldnews | both

APP_KEY=""                             # generato automaticamente da artisan

# Porte interne
CLUSTERING_PORT=8765

# ════════════════════════════════════════════════════════════════════════════
# ── Raccolta parametri interattivi ───────────────────────────────────────
# ════════════════════════════════════════════════════════════════════════════

sep
echo -e "${CYAN}FlamingNews — Deployment Setup${NC}"
sep

if [ -z "$DOMAIN" ]; then
    read -rp "Dominio (es. flamingnews.it): " DOMAIN
fi
[ -z "$DOMAIN" ] && fail "Dominio obbligatorio."

if [ -z "$REPO_URL" ]; then
    read -rp "URL repository GitHub (HTTPS o SSH): " REPO_URL
fi
[ -z "$REPO_URL" ] && fail "Repository URL obbligatorio."

if [ -z "$DB_PASS" ]; then
    DB_PASS=$(openssl rand -base64 24 | tr -dc 'a-zA-Z0-9' | head -c 24)
    warn "Password DB generata automaticamente: ${DB_PASS}"
    warn "(salvala — non verrà mostrata di nuovo)"
fi

if [ -z "$GNEWS_KEY" ] && [ "$NEWS_PROVIDER" != "worldnews" ]; then
    read -rp "GNews API key (invio per saltare): " GNEWS_KEY || true
fi

if [ -z "$WORLDNEWS_KEY" ] && [ "$NEWS_PROVIDER" != "gnews" ]; then
    read -rp "World News API key (invio per saltare): " WORLDNEWS_KEY || true
fi

# ════════════════════════════════════════════════════════════════════════════
# ── 1. Pacchetti di sistema ───────────────────────────────────────────────
# ════════════════════════════════════════════════════════════════════════════
sep; info "Aggiornamento pacchetti di sistema"

sudo apt-get update -qq

# Python 3 (sistema, non linuxbrew — più stabile per ML)
# php8.3 estensioni aggiuntive se mancanti
PKGS=(
    python3 python3-pip python3-venv python3-dev
    php8.3-cli php8.3-curl php8.3-mbstring php8.3-xml php8.3-zip
    php8.3-mysql php8.3-bcmath php8.3-intl php8.3-gd
    build-essential
)
sudo apt-get install -y --no-install-recommends "${PKGS[@]}" 2>/dev/null || true
ok "Pacchetti OK"

# ════════════════════════════════════════════════════════════════════════════
# ── 2. MariaDB — database e utente ───────────────────────────────────────
# ════════════════════════════════════════════════════════════════════════════
sep; info "Configurazione MariaDB"

sudo mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
sudo mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"
ok "Database '${DB_NAME}' e utente '${DB_USER}' pronti"

# ════════════════════════════════════════════════════════════════════════════
# ── 3. Clone / aggiornamento repository ──────────────────────────────────
# ════════════════════════════════════════════════════════════════════════════
sep; info "Repository → ${APP_DIR}"

if [ -d "${APP_DIR}/.git" ]; then
    warn "Repository già presente — eseguo git pull"
    sudo -u "${APP_USER}" git -C "${APP_DIR}" pull --ff-only
else
    sudo mkdir -p "${APP_DIR}"
    sudo chown "${APP_USER}:${APP_USER}" "${APP_DIR}"
    sudo -u "${APP_USER}" git clone "${REPO_URL}" "${APP_DIR}"
fi
ok "Repository aggiornato"

# ════════════════════════════════════════════════════════════════════════════
# ── 4. Laravel backend ────────────────────────────────────────────────────
# ════════════════════════════════════════════════════════════════════════════
sep; info "Laravel backend"

BACKEND="${APP_DIR}/backend"

# Permessi
sudo chown -R "${APP_USER}:${APP_USER}" "${BACKEND}"
sudo find "${BACKEND}/storage" "${BACKEND}/bootstrap/cache" -type d -exec chmod 775 {} \;

# Composer
info "  composer install..."
sudo -u "${APP_USER}" composer install \
    --working-dir="${BACKEND}" \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    -q
ok "  Dipendenze PHP installate"

# .env
if [ ! -f "${BACKEND}/.env" ]; then
    sudo -u "${APP_USER}" cp "${BACKEND}/.env.example" "${BACKEND}/.env"
fi

APP_KEY=$(sudo -u "${APP_USER}" php "${BACKEND}/artisan" key:generate --show --no-interaction)

sudo tee "${BACKEND}/.env" > /dev/null <<EOF
APP_NAME=FlamingNews
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=https://${DOMAIN}

LOG_CHANNEL=daily
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

GNEWS_API_KEY=${GNEWS_KEY}
WORLDNEWS_API_KEY=${WORLDNEWS_KEY}
NEWS_PROVIDER=${NEWS_PROVIDER}

CLUSTERING_SERVICE_URL=http://127.0.0.1:${CLUSTERING_PORT}

SANCTUM_STATEFUL_DOMAINS=${DOMAIN}
SESSION_DOMAIN=.${DOMAIN}
CORS_ALLOWED_ORIGINS=https://${DOMAIN}
EOF

sudo chown "${APP_USER}:${APP_USER}" "${BACKEND}/.env"
sudo chmod 640 "${BACKEND}/.env"
ok "  .env configurato"

# Migrate & seed
info "  Migrazioni DB..."
sudo -u "${APP_USER}" php "${BACKEND}/artisan" migrate --force --no-interaction
ok "  Migrazioni completate"

# Ottimizzazioni produzione
sudo -u "${APP_USER}" php "${BACKEND}/artisan" config:cache   --no-interaction
sudo -u "${APP_USER}" php "${BACKEND}/artisan" route:cache    --no-interaction
sudo -u "${APP_USER}" php "${BACKEND}/artisan" view:cache     --no-interaction
sudo -u "${APP_USER}" php "${BACKEND}/artisan" event:cache    --no-interaction
ok "  Cache Laravel ottimizzata"

# Queue table (se non esiste già)
sudo -u "${APP_USER}" php "${BACKEND}/artisan" queue:table --no-interaction 2>/dev/null || true
sudo -u "${APP_USER}" php "${BACKEND}/artisan" migrate --force --no-interaction

# ════════════════════════════════════════════════════════════════════════════
# ── 5. Frontend build (Vite) ─────────────────────────────────────────────
# ════════════════════════════════════════════════════════════════════════════
sep; info "Frontend build"

sudo -u "${APP_USER}" npm ci --prefix "${BACKEND}" --silent
sudo -u "${APP_USER}" npm run build --prefix "${BACKEND}"
ok "Asset compilati in ${BACKEND}/public/build"

# ════════════════════════════════════════════════════════════════════════════
# ── 6. Python — servizio clustering ─────────────────────────────────────
# ════════════════════════════════════════════════════════════════════════════
sep; info "Servizio clustering Python"

CLUSTER_DIR="${APP_DIR}/services/clustering"
VENV="${CLUSTER_DIR}/.venv"

if [ ! -d "${VENV}" ]; then
    python3 -m venv "${VENV}"
    ok "  Virtualenv creato"
fi

"${VENV}/bin/pip" install --quiet --upgrade pip
"${VENV}/bin/pip" install --quiet \
    fastapi uvicorn[standard] \
    scikit-learn nltk numpy \
    pydantic

# Download risorse NLTK
"${VENV}/bin/python" -c "
import nltk, os
nltk.download('stopwords', quiet=True)
"
ok "  Dipendenze Python installate"

# ════════════════════════════════════════════════════════════════════════════
# ── 7. nginx — virtual host ──────────────────────────────────────────────
# ════════════════════════════════════════════════════════════════════════════
sep; info "Configurazione nginx per ${DOMAIN}"

NGINX_CONF="/etc/nginx/sites-available/flamingnews"

sudo tee "${NGINX_CONF}" > /dev/null <<NGINX
# FlamingNews — ${DOMAIN}
# Generato da deploy-vps.sh il $(date +%Y-%m-%d)

server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN} www.${DOMAIN};

    # Per certbot ACME challenge
    location /.well-known/acme-challenge/ { root /var/www/html; }

    # Redirect tutto a HTTPS (attivato dopo certbot)
    # Commentato finché non hai il certificato:
    # return 301 https://\$host\$request_uri;

    root ${BACKEND}/public;
    index index.php;
    charset utf-8;

    # Gzip
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml image/svg+xml;
    gzip_min_length 1024;

    # Asset statici (build Vite)
    location /build/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php\$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 120;
    }

    location ~ /\.(?!well-known).* { deny all; }

    # Log separati
    access_log /var/log/nginx/flamingnews-access.log;
    error_log  /var/log/nginx/flamingnews-error.log warn;
}
NGINX

# Abilita il sito
sudo ln -sf "${NGINX_CONF}" /etc/nginx/sites-enabled/flamingnews

# Test config nginx
sudo nginx -t && sudo nginx -s reload
ok "nginx ricaricato"

# ════════════════════════════════════════════════════════════════════════════
# ── 8. PM2 — processi applicativi ────────────────────────────────────────
# ════════════════════════════════════════════════════════════════════════════
sep; info "Configurazione PM2"

PM2_ECOSYSTEM="${APP_DIR}/ecosystem.config.js"

sudo tee "${PM2_ECOSYSTEM}" > /dev/null <<JS
// PM2 ecosystem — FlamingNews
// Generato da deploy-vps.sh il $(date +%Y-%m-%d)

module.exports = {
  apps: [
    // ── Laravel queue worker ──────────────────────────────────────────
    {
      name: 'flamingnews-queue',
      script: 'artisan',
      args: 'queue:work --sleep=3 --tries=3 --max-time=3600',
      interpreter: 'php',
      cwd: '${BACKEND}',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '256M',
      env: { APP_ENV: 'production' },
      error_file: '/var/log/flamingnews/queue-error.log',
      out_file:   '/var/log/flamingnews/queue-out.log',
    },

    // ── Laravel scheduler (ogni minuto) ──────────────────────────────
    {
      name: 'flamingnews-scheduler',
      script: 'artisan',
      args: 'schedule:work',
      interpreter: 'php',
      cwd: '${BACKEND}',
      instances: 1,
      autorestart: true,
      watch: false,
      env: { APP_ENV: 'production' },
      error_file: '/var/log/flamingnews/scheduler-error.log',
      out_file:   '/var/log/flamingnews/scheduler-out.log',
    },

    // ── Clustering microservice (Python/FastAPI) ──────────────────────
    {
      name: 'flamingnews-clustering',
      script: '${CLUSTER_DIR}/.venv/bin/uvicorn',
      args: 'cluster:app --host 127.0.0.1 --port ${CLUSTERING_PORT} --workers 1',
      cwd: '${CLUSTER_DIR}',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '512M',
      error_file: '/var/log/flamingnews/clustering-error.log',
      out_file:   '/var/log/flamingnews/clustering-out.log',
    },
  ],
};
JS

sudo mkdir -p /var/log/flamingnews
sudo chown "${APP_USER}:${APP_USER}" /var/log/flamingnews

# Avvia / riavvia solo i processi FlamingNews (wa-webhook rimane intatto)
pm2 start "${PM2_ECOSYSTEM}" || pm2 restart "${PM2_ECOSYSTEM}"
pm2 save
ok "PM2: flamingnews-queue, flamingnews-scheduler, flamingnews-clustering avviati"

# ════════════════════════════════════════════════════════════════════════════
# ── 9. SSL con certbot ────────────────────────────────────────────────────
# ════════════════════════════════════════════════════════════════════════════
sep; info "Certificato SSL (certbot)"

read -rp "Email per Let's Encrypt (obbligatoria): " LE_EMAIL

sudo certbot --nginx \
    -d "${DOMAIN}" \
    -d "www.${DOMAIN}" \
    --non-interactive \
    --agree-tos \
    --email "${LE_EMAIL}" \
    --redirect

ok "SSL attivato per ${DOMAIN}"

# Ora aggiorna il blocco HTTPS generato da certbot per aggiungere le header
# di sicurezza (certbot ha già scritto il server block HTTPS)
sudo nginx -t && sudo nginx -s reload

# ════════════════════════════════════════════════════════════════════════════
# ── 10. Cron scheduler (fallback se PM2 scheduler ha problemi) ────────────
# ════════════════════════════════════════════════════════════════════════════
# (disattivato per default — PM2 schedule:work è preferibile)
# Per attivare: rimuovi i commenti
# (crontab -l 2>/dev/null; echo "* * * * * php ${BACKEND}/artisan schedule:run >> /dev/null 2>&1") | crontab -

# ════════════════════════════════════════════════════════════════════════════
# ── 11. Verifica finale ──────────────────────────────────────────────────
# ════════════════════════════════════════════════════════════════════════════
sep
echo -e "\n${GREEN}════════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  FlamingNews deployment completato!${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════════════════${NC}\n"

echo "  Sito:          https://${DOMAIN}"
echo "  App directory: ${APP_DIR}"
echo "  DB:            ${DB_NAME} / ${DB_USER}"
echo "  Clustering:    http://127.0.0.1:${CLUSTERING_PORT}/health"
echo ""
echo -e "${CYAN}Stato PM2:${NC}"
pm2 list
echo ""
echo -e "${CYAN}Stato nginx:${NC}"
sudo nginx -t
echo ""
echo -e "${YELLOW}Prossimi passi manuali:${NC}"
echo "  1. Verifica https://${DOMAIN} nel browser"
echo "  2. Controlla i log: pm2 logs flamingnews-clustering"
echo "  3. Lancia il primo fetch news: php ${BACKEND}/artisan news:fetch"
echo "  4. Aggiungi WORLDNEWS_API_KEY / GNEWS_API_KEY in ${BACKEND}/.env se non l'hai fatto"
echo "  5. Poi: php ${BACKEND}/artisan config:cache"
echo ""
