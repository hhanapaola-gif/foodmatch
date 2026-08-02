# FoodMatch — Despliegue en DigitalOcean (2 servidores)

Esta guía lleva la arquitectura de **2 servidores** (público / privado
combinado con app + monitoreo) de cero a funcionando en DigitalOcean.

- **Servidor A (público)**: Basic Regular, **$4/mes** (512 MB RAM / 1 vCPU / 10 GB SSD) — solo Nginx, certbot y node-exporter, carga muy ligera.
- **Servidor B (privado)**: Basic Regular, **$12/mes** (2 GB RAM / 1 vCPU / 50 GB SSD) — Laravel×2, MySQL, Redis, exporters, **y Prometheus + Grafana colocados aquí mismo**.

Total: **~$16/mes**, o prorrateado por hora si no lo dejas corriendo el mes completo (DigitalOcean cobra por hora, no de golpe).

> Este diseño de 2 servidores fue validado localmente (build + arranque +
> comunicación real entre contenedores, incluyendo Prometheus scrapeando
> node-exporter/mysqld-exporter por nombre de servicio Docker ya que están
> en la misma máquina). Si en algún momento el Servidor B se queda corto de
> RAM, quita `app2`/`web2` del `docker-compose.yml` para correr con 1 sola
> réplica de Laravel.

## 0. Prerrequisitos

- Cuenta DigitalOcean (solo tarjeta, sin verificación de identidad como en
  AWS/Oracle). La primera compra puede pedirte una autorización bancaria
  (2FA de tu banco) como cargo de verificación de cuenta — es normal, se
  convierte en saldo de tu cuenta, no es un cargo perdido.
- Un dominio propio, o usa **sslip.io** (gratis, sin registro): si la IP
  pública del Servidor A es `52.10.20.30`, el dominio es
  `52-10-20-30.sslip.io`. Certbot funciona igual con él vía el reto HTTP-01.
- Docker + Docker Compose instalados en las 2 VMs
  (`curl -fsSL https://get.docker.com | sh`).

## 1. Crear los 2 Droplets

Primero subí tu llave SSH pública una sola vez: en la consola de
DigitalOcean, **Settings → Security → SSH Keys → Add SSH Key**, pegá el
contenido de `~/.ssh/foodmatch_oci.pub`.

Luego, **Create → Droplets**, dos veces:

**Droplet 1 — Servidor A (público)**
- **Image**: Ubuntu 22.04 (LTS) x64.
- **Plan**: Basic → **Regular SSD** (no Premium) → **$4/mes** (512 MB / 1 vCPU / 10 GB SSD).
- **Backups**: desactivado.
- **Hostname**: `foodmatch-server-a`, **Tag**: `server-public`.

**Droplet 2 — Servidor B (privado + monitoreo)**
- **Image**: Ubuntu 22.04 (LTS) x64.
- **Plan**: Basic → **Regular SSD** → **$12/mes** (2 GB / 1 vCPU / 50 GB SSD).
- **Backups**: desactivado.
- **Hostname**: `foodmatch-server-b`, **Tag**: `server-private-app`.

Para ambos: **misma Datacenter region** (necesario para que compartan VPC
privada por defecto y se vean entre sí por IP privada), **VPC Network**
default de esa región, **Authentication**: SSH Key (la que subiste).

Antes de confirmar cada creación, verifica que el precio mostrado sea el
esperado ($4 o $12) — si aparece un monto distinto, revisa que "Regular
SSD" esté seleccionado (no "Premium Intel/AMD") y que "Backups" esté
desactivado.

Creá 2 Cloud Firewalls (**Networking → Firewalls → Create Firewall**),
aplicando cada uno por tag:

- **fw-public** (tag `server-public`): inbound TCP 22 (Sources: tu IP), TCP 80 y 443 (Sources: All IPv4/IPv6), **TCP 9100 y TCP 9113 (Sources: tag `server-private-app`)** — Servidor B corre Prometheus y necesita scrapear el node-exporter y el nginx-exporter de este servidor por IP privada. **Importante**: los Cloud Firewalls de DigitalOcean se aplican también al tráfico de la red privada (VPC), no solo a internet — sin esta regla, Prometheus nunca puede alcanzar estos puertos aunque estén en la misma VPC (confirmado en el despliegue real con el 9100: `curl` desde Servidor B a Servidor A por IP privada se quedaba colgado hasta agregar esa regla; 9113 es el mismo caso).
- **fw-private-app** (tag `server-private-app`): inbound TCP 22 (Sources: tu IP), TCP 8080 (Sources: tag `server-public`), TCP 3000 (Sources: tag `server-public`).

Nota: **9100/9104/9113-lb ya no necesitan regla de firewall** — Prometheus
está en la misma máquina que esos exporters ahora (node-exporter,
mysqld-exporter, y el nginx-exporter del LB interno), se scrapea por red
interna de Docker, ni siquiera se publican al host. Solo el nginx-exporter
del Servidor A (edge, puerto 9113) cruza la red, porque vive en la otra caja.

Una vez "Active" los 2 Droplets, asigná una **Reserved IP** al Servidor A
(**Networking → Reserved IPs → Assign to Droplet**).

Anotá: IP pública del Servidor A (la Reserved IP), IP privada (VPC) del
Servidor A, IP privada (VPC) del Servidor B.

## 2. Servidor B (privado) — Laravel, MySQL, Redis, Prometheus, Grafana

```bash
git clone <tu-repo-backend> FoodMatch-Backend
git clone <tu-repo-infra> infra        # o copia la carpeta infra/ si viven en el mismo repo
cd FoodMatch-Backend
cp .env.example .env
php artisan key:generate   # o genera APP_KEY manualmente si no tienes PHP local, ver nota abajo
# Edita .env: DB_HOST=mysql, DB_DATABASE/DB_USERNAME/DB_PASSWORD (deben
# coincidir con infra/server-private/.env), REDIS_HOST=redis,
# REDIS_PASSWORD=(igual que infra/server-private/.env), y descomenta
# CACHE_DRIVER=redis / SESSION_DRIVER=redis — con 2 réplicas detrás del
# balanceador, las sesiones del panel admin (basadas en cookie+servidor)
# se rompen si cada contenedor guarda su sesión en su propio disco.

cd ../infra/server-private
cp .env.example .env
nano .env   # define contraseñas reales de DB_PASSWORD, DB_ROOT_PASSWORD,
            # REDIS_PASSWORD, MYSQL_EXPORTER_PASSWORD,
            # GRAFANA_ADMIN_USER, GRAFANA_ADMIN_PASSWORD, GRAFANA_ROOT_URL

docker compose up -d --build
docker compose ps   # confirma que app1, app2, web1, web2, lb, mysql, redis,
                     # node-exporter, mysqld-exporter, prometheus, grafana
                     # estén "healthy"/"running" (mysql puede tardar hasta
                     # ~90s en healthy la primera vez, es normal)

# PASO OBLIGATORIO la primera vez: una BD recién migrada (php artisan migrate,
# que ya corrió arriba dentro del contenedor) NO trae configuración base —
# business_settings y currencies quedan vacías, lo que rompe /api/v1/config,
# login, registro y casi cualquier endpoint real con 500 (confirmado en
# validación local). Este dump se generó a partir del entorno local ya
# funcional; solo trae configuración (moneda, textos del negocio, flags de
# pasarelas de pago desactivadas), no usuarios ni datos de prueba. Es
# seguro re-ejecutarlo (usa INSERT IGNORE).
docker exec -i $(docker compose ps -q mysql) mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < mysql/seed-business-config.sql

# PASO OBLIGATORIO justo después del anterior: si ya hiciste alguna petición
# a la API antes de importar el seed (incluso sin querer, ej. el health-check
# de algo), Laravel cachea el resultado vacío PARA SIEMPRE en Redis
# (Cache::rememberForever, confirmado en el código) — sin este paso, seguirás
# viendo el 500 aunque la BD ya tenga los datos.
docker compose exec app1 php artisan cache:clear

# Crea el admin (admin@admin.com / 12345678 — cámbiala luego desde el panel).
# El dump de arriba ya incluye admin_roles (id=1, "Master Admin"), que este
# seeder necesita — sin esa fila, /admin da 500 "Attempt to read property
# name on null" aunque el login funcione.
docker compose exec app1 php artisan db:seed --force
```

> Nota: la creación del cliente personal de Passport (necesario para que
> login/registro emitan tokens) ya la hace `entrypoint.sh` automáticamente
> en el primer arranque — no requiere un paso manual aparte.

> Nota APP_KEY: si no tienes PHP instalado en tu máquina, corre
> `docker run --rm -v ${PWD}:/app -w /app php:8.2-cli php artisan key:generate --show`
> y pega el valor en `.env` como `APP_KEY=base64:...` antes del primer `docker compose up`.

Verifica que el LB interno y Grafana responden:
```bash
curl http://localhost:8080/api/v1/config   # debe dar 200 con JSON real, no 500
curl -I http://localhost:3000/grafana/login   # debe dar 200
```

Reemplaza el placeholder de `prometheus.yml` con la IP privada real del Servidor A:
```bash
sed -i "s/PUBLIC_SERVER_IP/<IP_PRIVADA_SERVIDOR_A>/g" prometheus/prometheus.yml
docker compose up -d --force-recreate prometheus
```

Aplica el firewall del servidor privado (necesita la IP privada del Servidor A):
```bash
sudo ../infra/scripts/firewall-private.sh <IP_PRIVADA_SERVIDOR_A> <TU_IP_PARA_SSH>
```

## 3. Servidor A (público) — Nginx, SSL, firewall

```bash
git clone <tu-repo-infra> infra
cd infra/server-public
cp .env.example .env
nano .env   # DOMAIN=tu-dominio.com (o el sslip.io),
            # PRIVATE_SERVER_IP=<IP_PRIVADA_SERVIDOR_B> (una sola variable,
            # se usa tanto para el LB como para Grafana — ambos viven en
            # el mismo Servidor B en esta topología de 2 servidores)

docker compose up -d
curl http://tu-dominio.com/api/v1/config   # debe responder vía el proxy -> Servidor B
curl -I http://tu-dominio.com/grafana/login   # debe responder vía el proxy -> Servidor B
```

### Emitir certificado SSL

```bash
docker compose run --rm --entrypoint certbot certbot certonly \
  --webroot -w /var/www/certbot -d tu-dominio.com \
  --email tu-correo@example.com --agree-tos --no-eff-email

mv nginx/templates/default.conf.template nginx/templates/default.conf.template.bak
mv nginx/templates/ssl.conf.template.disabled nginx/templates/default.conf.template
docker compose up -d --force-recreate nginx
```

El contenedor `certbot` ya corre en loop renovando cada 12h — no hace falta
cron adicional.

Aplica el firewall del servidor público (necesita la IP privada del Servidor B):
```bash
sudo ../infra/scripts/firewall-public.sh <IP_PRIVADA_SERVIDOR_B>
```

**Además**, en la consola de DigitalOcean, confirma que `fw-public` solo
tiene abierto 22 (a tu IP), 80 y 443 (All IPv4/IPv6).

## 4. Verificación contra la rúbrica

| Verificación | Comando |
|---|---|
| SSL activo | `curl -I https://tu-dominio.com` (debe dar 200, no error de certificado) |
| API protegida con JWT | `curl https://tu-dominio.com/api/v1/customer/info` sin token → 401; con `Authorization: Bearer <token>` de `/api/v1/auth/login` → 200 |
| Balanceador de carga | `for i in 1 2 3 4; do curl -s https://tu-dominio.com/api/v1/config -H "X-Debug: $i"; done` + revisa logs (`docker compose logs web1 web2`) alternando |
| Firewall activo | `sudo ufw status verbose` en las 2 VMs + revisa los Cloud Firewalls en la consola de DigitalOcean |
| Monitoreo | `https://tu-dominio.com/grafana/` (login con `GRAFANA_ADMIN_USER`/`PASSWORD`), dashboards "FoodMatch - Overview" (CPU/RAM/disco/MySQL) y "FoodMatch - Nginx" (requests/sec y conexiones del LB interno y del Nginx edge) con métricas de ambas cajas |
| BD no expuesta | Desde tu laptop: `nc -zv <IP_PUBLICA_SERVIDOR_A> 3306` → debe fallar (timeout/refused) |
| Hasheo/encriptado | `php artisan tinker` → `Hash::make('demo')` y `Crypt::encryptString('demo')` en el Servidor B |

## 5. Pendiente para el móvil

- Apuntar `API_BASE_URL` del app Expo a `https://tu-dominio.com/api/v1`.
- Build de producción: `eas build --platform android --profile production`.
- Instalar el APK resultante en el teléfono físico que llevarán a la evaluación.

## Notas de seguridad para producción real

- Restringe `/grafana/` con IP allowlist o auth básica adicional en Nginx si
  lo vas a dejar público durante la evaluación.
- Cambia todas las contraseñas de los `.env.example` — nunca subas los
  `.env` reales a git. **Ojo**: no hay un `.gitignore` a nivel raíz que
  excluya `infra/*/.env` — bórralos manualmente después de usarlos o
  agrega esa regla antes de trabajar aquí con secretos reales.
- El usuario `exporter` de MySQL tiene permisos de solo lectura
  (`PROCESS, REPLICATION CLIENT, SELECT`), no reutiliza la cuenta root.

## Nota histórica: diseño anterior de 3 servidores

Este proyecto se diseñó originalmente con 3 servidores (público / privado-app
/ privado-monitoreo separado), pensado para Droplets de 1GB donde Prometheus
+ Grafana no cabían junto a Laravel+MySQL+Redis. Se simplificó a 2 servidores
cuando se decidió usar un Droplet de 2GB para el servidor privado (suficiente
para todo junto). Si en el futuro hace falta volver a 3 servidores (ej. por
RAM), la carpeta `infra/server-monitoring/` original (Prometheus+Grafana
standalone) se conserva sin usar como referencia, y `firewall-monitoring.sh`
sigue disponible en `infra/scripts/`.
