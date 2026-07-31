#!/bin/bash
# Run ONCE on the MONITORING server as root, after provisioning the VM.
# This box hosts Prometheus + Grafana. Grafana is reverse-proxied by the
# public server; Prometheus itself is never reachable from outside this box.
#
# Usage: ./firewall-monitoring.sh <PUBLIC_SERVER_PRIVATE_IP> [YOUR_ADMIN_IP]
set -euo pipefail

PUBLIC_SERVER_IP="${1:?Usage: firewall-monitoring.sh <PUBLIC_SERVER_PRIVATE_IP> [YOUR_ADMIN_IP]}"
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

# Only the public server may reach Grafana.
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

echo "Firewall + fail2ban configured on MONITORING server."
ufw status verbose
