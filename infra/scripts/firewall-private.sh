#!/bin/bash
# Run ONCE on the PRIVATE server (Servidor B) as root, after provisioning the VM.
# This box must NOT be reachable from the internet except through the public
# server's reverse proxy (and SSH, ideally restricted to your own IP).
#
# 2-server topology: this box also runs Prometheus/Grafana (colocated with
# the app/db), so node-exporter/mysqld-exporter no longer need a firewall
# rule at all — they're not published to the host, only reachable inside
# the Docker network by Prometheus. Only Grafana (3000) needs to be opened,
# for the public server's Nginx to reverse-proxy /grafana/ here.
#
# Usage: ./firewall-private.sh <PUBLIC_SERVER_PRIVATE_IP> [YOUR_ADMIN_IP]
set -euo pipefail

PUBLIC_SERVER_IP="${1:?Usage: firewall-private.sh <PUBLIC_SERVER_PRIVATE_IP> [YOUR_ADMIN_IP]}"
ADMIN_IP="${2:-}"

apt-get update -y
apt-get install -y ufw fail2ban

ufw default deny incoming
ufw default allow outgoing

if [ -n "$ADMIN_IP" ]; then
    ufw allow from "$ADMIN_IP" to any port 22 proto tcp comment "SSH admin only"
else
    echo "WARNING: no admin IP given, opening SSH to 0.0.0.0/0. Pass your IP as \$2 to restrict it." >&2
    ufw allow 22/tcp comment "SSH (unrestricted - fix this)"
fi

# Only the public server may reach the internal LB (8080) and Grafana (3000).
ufw allow from "$PUBLIC_SERVER_IP" to any port 8080 proto tcp comment "Internal LB - public server only"
ufw allow from "$PUBLIC_SERVER_IP" to any port 3000 proto tcp comment "Grafana - public server only"

ufw --force enable

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

( crontab -l 2>/dev/null; echo "* * * * * /usr/local/bin/fail2ban-metrics.sh" ) | crontab -

echo "Firewall + fail2ban configured on PRIVATE server."
ufw status verbose
