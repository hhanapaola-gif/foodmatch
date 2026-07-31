#!/bin/bash
# Runs once, only on first container init (empty data dir), via the
# official mysql image's docker-entrypoint-initdb.d mechanism.
# Creates a least-privilege user for mysqld-exporter (Prometheus) instead
# of reusing the root account.
set -e

mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" <<-EOSQL
    CREATE USER IF NOT EXISTS 'exporter'@'%' IDENTIFIED BY '${MYSQL_EXPORTER_PASSWORD}';
    GRANT PROCESS, REPLICATION CLIENT, SELECT ON *.* TO 'exporter'@'%';
    FLUSH PRIVILEGES;
EOSQL
