#!/bin/bash
# Run ONCE on the PUBLIC server (Servidor A) as root, after provisioning the VM.
# Sets up UFW (host firewall) + fail2ban (brute-force protection) for a box
# that only terminates SSL and reverse-proxies to the private server.
#
# NOTE: also configure the cloud-level firewall (Oracle Security List / AWS
# Security Group) to only allow 22, 80, 443 inbound — UFW alone is not enough,
# see infra/DEPLOYMENT.md.
#
# Usage: ./firewall-public.sh <PRIVATE_SERVER_PRIVATE_IP>
set -euo pipefail

PRIVATE_SERVER_IP="${1:?Usage: firewall-public.sh <PRIVATE_SERVER_PRIVATE_IP>}"

apt-get update -y
apt-get install -y ufw fail2ban

# --- UFW: default deny, allow only what the public server needs ---
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp comment "SSH"
ufw allow 80/tcp comment "HTTP"
ufw allow 443/tcp comment "HTTPS"

# Only Servidor B (which now runs Prometheus too, 2-server topology) may
# scrape this box's node-exporter.
ufw allow from "$PRIVATE_SERVER_IP" to any port 9100 proto tcp comment "node-exporter - private server only"

ufw --force enable

# --- fail2ban: ban repeated SSH auth failures ---
cat > /etc/fail2ban/jail.local <<'EOF'
[DEFAULT]
bantime  = 1h
findtime = 10m
maxretry = 5
backend  = systemd

[sshd]
enabled = true
EOF

systemctl enable fail2ban
systemctl restart fail2ban

# --- Feed fail2ban ban counts into Prometheus via node_exporter's textfile
# collector, so bans show up in Grafana (rubric: "monitoreo de firewall"). ---
mkdir -p /var/log/node-exporter
cat > /usr/local/bin/fail2ban-metrics.sh <<'EOF'
#!/bin/bash
OUT=/var/log/node-exporter/fail2ban.prom.$$
{
    echo "# HELP fail2ban_banned_current Currently banned IPs per jail"
    echo "# TYPE fail2ban_banned_current gauge"
    for jail in $(fail2ban-client status | grep "Jail list" | sed 's/.*://;s/,//g'); do
        count=$(fail2ban-client status "$jail" | grep "Currently banned" | sed 's/.*://')
        echo "fail2ban_banned_current{jail=\"$jail\"} ${count:-0}"
    done
} > "$OUT"
chmod 644 "$OUT"
mv "$OUT" /var/log/node-exporter/fail2ban.prom
EOF
chmod +x /usr/local/bin/fail2ban-metrics.sh

# `|| true` matters: under `set -e`, "crontab -l" exiting non-zero (no
# crontab yet — the normal case on a fresh VM) would abort the script here
# even with stderr redirected, silently skipping the cron install (confirmed
# in the real deploy of the private server).
( crontab -l 2>/dev/null || true; echo "* * * * * /usr/local/bin/fail2ban-metrics.sh" ) | crontab -

echo "Firewall + fail2ban configured on PUBLIC server."
ufw status verbose
