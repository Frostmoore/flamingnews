#!/usr/bin/env bash
# FlamingNews — VPS Diagnostic Script
# Esegui con: bash diagnose-vps.sh
# Non modifica nulla, solo legge lo stato del server.

set -euo pipefail
SEP="━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

section() { echo -e "\n${SEP}\n▶ $1\n${SEP}"; }

# ── Sistema ────────────────────────────────────────────────
section "OS & KERNEL"
lsb_release -a 2>/dev/null || cat /etc/os-release
uname -r

section "RISORSE"
echo "--- CPU ---"
nproc && lscpu | grep -E "Model name|CPU\(s\)|Thread"
echo "--- RAM ---"
free -h
echo "--- DISCO ---"
df -h /

# ── Utenti & permessi ──────────────────────────────────────
section "UTENTE CORRENTE"
whoami && id
echo "Sudo senza password?"
sudo -n true 2>/dev/null && echo "SÌ" || echo "NO (richiede password)"

# ── Rete ──────────────────────────────────────────────────
section "RETE"
echo "--- IP pubblico ---"
curl -s https://api.ipify.org || curl -s https://ifconfig.me || echo "n/a"
echo ""
echo "--- Interfacce ---"
ip -4 addr show scope global | grep -E "inet |^[0-9]"
echo "--- Porte in ascolto ---"
ss -tlnp 2>/dev/null || netstat -tlnp 2>/dev/null || echo "ss/netstat non disponibili"

section "FIREWALL"
if command -v ufw &>/dev/null; then
    ufw status verbose
elif command -v iptables &>/dev/null; then
    iptables -L INPUT -n --line-numbers | head -30
else
    echo "Nessun firewall rilevato"
fi

# ── Software installato ────────────────────────────────────
section "WEB SERVER"
for svc in nginx apache2 caddy; do
    if command -v $svc &>/dev/null; then
        echo "✓ $svc: $(${svc} -v 2>&1 | head -1)"
        systemctl is-active $svc 2>/dev/null && echo "  → attivo" || echo "  → non attivo"
    else
        echo "✗ $svc: non installato"
    fi
done

section "PHP"
if command -v php &>/dev/null; then
    php -v | head -1
    echo "Estensioni: $(php -m | tr '\n' ' ')"
    echo "php.ini: $(php --ini | grep 'Loaded Configuration')"
else
    echo "✗ PHP non installato"
fi
for ver in 8.3 8.2 8.1; do
    [ -f "/usr/bin/php${ver}" ] && echo "php${ver} disponibile"
done

section "MYSQL / MARIADB / POSTGRESQL"
for db in mysql mariadb psql; do
    if command -v $db &>/dev/null; then
        echo "✓ $db: $($db --version 2>&1 | head -1)"
        case $db in
            mysql|mariadb) systemctl is-active mysql mariadb 2>/dev/null | head -1 ;;
            psql) systemctl is-active postgresql 2>/dev/null ;;
        esac
    else
        echo "✗ $db: non installato"
    fi
done

section "NODE / NPM / PM2"
for cmd in node npm pm2; do
    command -v $cmd &>/dev/null \
        && echo "✓ $cmd: $($cmd --version 2>&1 | head -1)" \
        || echo "✗ $cmd: non installato"
done

section "PYTHON"
for cmd in python3 pip3 uvicorn; do
    command -v $cmd &>/dev/null \
        && echo "✓ $cmd: $($cmd --version 2>&1 | head -1)" \
        || echo "✗ $cmd: non installato"
done
[ -d "/usr/bin/python3" ] || python3 -c "import sklearn, nltk, fastapi" 2>/dev/null \
    && echo "Librerie ML: sklearn/nltk/fastapi presenti" \
    || echo "Librerie ML: non installate"

section "GIT & COMPOSER"
command -v git      &>/dev/null && echo "✓ git: $(git --version)"      || echo "✗ git: non installato"
command -v composer &>/dev/null && echo "✓ composer: $(composer --version 2>&1 | head -1)" || echo "✗ composer: non installato"

section "CERTBOT / SSL"
command -v certbot &>/dev/null \
    && echo "✓ certbot: $(certbot --version 2>&1)" \
    || echo "✗ certbot: non installato"
ls /etc/letsencrypt/live/ 2>/dev/null && echo "Certificati esistenti:" && ls /etc/letsencrypt/live/ || echo "Nessun certificato Let's Encrypt"

# ── Configurazioni esistenti ───────────────────────────────
section "VIRTUAL HOSTS NGINX"
if [ -d /etc/nginx/sites-enabled ]; then
    ls -la /etc/nginx/sites-enabled/
    for f in /etc/nginx/sites-enabled/*; do
        echo -e "\n--- $f ---"
        cat "$f" 2>/dev/null | grep -E "server_name|root|listen|proxy_pass" || true
    done
else
    echo "Nessuna configurazione nginx trovata"
fi

section "VIRTUAL HOSTS APACHE"
if [ -d /etc/apache2/sites-enabled ]; then
    ls -la /etc/apache2/sites-enabled/
else
    echo "Nessuna configurazione apache trovata"
fi

# ── Processi & servizi ─────────────────────────────────────
section "SERVIZI ATTIVI"
systemctl list-units --type=service --state=running --no-pager 2>/dev/null | grep -v "^$" | head -30

section "PM2 PROCESSI"
command -v pm2 &>/dev/null && pm2 list || echo "PM2 non installato"

# ── Directory web ──────────────────────────────────────────
section "DIRECTORY WEB ESISTENTI"
for dir in /var/www /srv/www /home/*/www /home/*/public_html; do
    [ -d "$dir" ] 2>/dev/null && echo "✓ $dir esiste:" && ls "$dir" 2>/dev/null || true
done

section "DOMINIO (da configurare)"
echo "Inserisci manualmente nelle risposte:"
echo "  - Dominio: es. flamingnews.it"
echo "  - Sottodomini previsti: es. api.flamingnews.it"
echo "  - Il dominio punta già a questo IP? (verifica: dig +short tuodominio.it)"
command -v dig &>/dev/null && {
    echo ""
    echo "Digita il tuo dominio per verifica DNS (oppure premi ENTER per saltare):"
    read -t 10 DOMAIN || true
    [ -n "${DOMAIN:-}" ] && dig +short "$DOMAIN" A || true
}

echo -e "\n${SEP}"
echo "✅ Diagnostica completata. Incolla tutto l'output nel chat."
echo "${SEP}"
