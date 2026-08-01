# FoodMatch — Despliegue en DigitalOcean (Droplets Basic)

Esta guía lleva la arquitectura de 3 servidores (público / privado-app /
privado-monitoreo) de cero a funcionando en DigitalOcean. Está pensada para
**Droplets Basic** (1 vCPU / 1 GB RAM, ~6 USD/mes cada uno).

> Con solo 1 GB de RAM por instancia, separar Prometheus/Grafana en su
> propia VM (en vez de meterlos junto al backend, como en el plan original
> de Oracle) es necesario para que el servidor de la app no se quede sin
> memoria. Si en algún momento el servidor privado-app se queda corto de
> RAM igual (2 réplicas Laravel + MySQL + Redis), quita `app2`/`web2` del
> `docker-compose.yml` para correr con 1 sola réplica.

## 0. Prerrequisitos

- Cuenta DigitalOcean (solo tarjeta, sin verificación de identidad como en
  AWS/Oracle; buscar si hay crédito de bienvenida vigente para cuentas
  nuevas — ver nota de costos abajo).
- Un dominio propio, o usa **sslip.io** (gratis, sin registro): si la IP
  pública del Servidor A es `52.10.20.30`, el dominio es
  `52-10-20-30.sslip.io`. Certbot funciona igual con él vía el reto HTTP-01.
- Docker + Docker Compose instalados en las 3 VMs
  (`curl -fsSL https://get.docker.com | sh`).

### Nota de costos

DigitalOcean no tiene free tier permanente. 3 Droplets Basic ($6/mes c/u,
1 vCPU/1GB) son **~18 USD/mes corriendo 24/7**. Las cuentas nuevas suelen
recibir crédito de bienvenida (históricamente rondaba los 200 USD por 60
días — confirmá la oferta vigente al crear la cuenta, cambia con el
tiempo), lo que normalmente cubre de sobra el período de desarrollo y
evaluación del proyecto. Si el crédito no aplica o se agota antes de la
evaluación, podés apagar los 3 Droplets (`Power off`, no `Destroy`) cuando
no los estés usando — DO no cobra por CPU de un Droplet apagado, solo el
storage del disco (~centavos).

## 1. Crear los 3 Droplets

Primero subí tu llave SSH pública una sola vez: en la consola de
DigitalOcean, **Settings → Security → SSH Keys → Add SSH Key**, pegá el
contenido de `~/.ssh/foodmatch_oci.pub`.

Luego, **Create → Droplets**, tres veces, con estas specs para las 3:

- **Image**: Ubuntu 22.04 (LTS) x64.
- **Plan**: Basic → Regular SSD → **$6/mes** (1 vCPU / 1 GB RAM / 25 GB SSD).
- **Datacenter region**: la **misma región** para las 3 (necesario para que
  compartan la VPC privada por defecto y se vean entre sí por IP privada).
- **VPC Network**: dejá la VPC "default" de esa región seleccionada para
  las 3 — DigitalOcean le asigna automáticamente una IP privada (rango
  `10.x.x.x`) a cada Droplet además de la pública, sin costo extra ni
  configuración de NAT Gateway.
- **Authentication**: SSH Key → seleccioná la que subiste arriba (no uses
  contraseña).
- **Hostname / Tags**: nombrá cada uno y agregale un **tag** distinto —
  esto es importante porque los Firewalls de DO se aplican por tag, no por
  referencia directa a otro firewall como en AWS:
  - `foodmatch-server-a`, tag `server-public`
  - `foodmatch-server-b`, tag `server-private-app`
  - `foodmatch-server-monitoring`, tag `server-monitoring`

Creá 3 Cloud Firewalls (**Networking → Firewalls → Create Firewall**),
aplicando cada uno por tag (campo "Apply to Droplets", escribí el tag):

- **fw-public** (aplica a tag `server-public`): inbound TCP 22 (Sources: tu
  IP), TCP 80 y 443 (Sources: All IPv4/IPv6).
- **fw-private-app** (aplica a tag `server-private-app`): inbound TCP 22
  (Sources: tu IP), TCP 8080 (Sources: tag `server-public`), TCP 9100 y
  9104 (Sources: tag `server-monitoring`) — en "Sources" podés elegir
  "Tag" en vez de una IP puntual, DO abre el puerto a cualquier Droplet con
  ese tag.
- **fw-monitoring** (aplica a tag `server-monitoring`): inbound TCP 22
  (Sources: tu IP), TCP 3000 (Sources: tag `server-public`).

Una vez "Active" los 3 Droplets, asigná una **Reserved IP** al Servidor A
(**Networking → Reserved IPs → Assign to Droplet**) — es gratis mientras
esté asociada a un Droplet activo, y evita que la IP pública cambie si lo
reiniciás.

Anotá (Droplets → click en cada uno, se ven ambas en la vista general):

- IP pública del Servidor A (la Reserved IP asignada).
- IP privada (VPC) del Servidor A.
- IP privada (VPC) del Servidor B.
- IP privada (VPC) del Servidor C.

## 2. Servidor B (privado-app) — Laravel, MySQL, Redis

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
            # REDIS_PASSWORD, MYSQL_EXPORTER_PASSWORD

docker compose up -d --build
docker compose ps   # confirma que app1, app2, web1, web2, lb, mysql, redis,
                     # node-exporter, mysqld-exporter estén "healthy"/"running"

# PASO OBLIGATORIO la primera vez: una BD recién migrada (php artisan migrate,
# que ya corrió arriba dentro del contenedor) NO trae configuración base —
# business_settings y currencies quedan vacías, lo que rompe /api/v1/config,
# login, registro y casi cualquier endpoint real con 500 (confirmado en una
# validación local: ver seed-business-config.sql para el detalle exacto).
# Este dump se generó a partir del entorno local ya funcional; solo trae
# configuración (moneda, textos del negocio, flags de pasarelas de pago
# desactivadas), no usuarios ni datos de prueba. Es seguro re-ejecutarlo
# (usa INSERT IGNORE).
docker exec -i $(docker compose ps -q mysql) mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < mysql/seed-business-config.sql
```

> Nota: la creación del cliente personal de Passport (necesario para que
> login/registro emitan tokens) ya la hace `entrypoint.sh` automáticamente
> en el primer arranque — no requiere un paso manual aparte.

> Nota APP_KEY: si no tienes PHP instalado en tu máquina, corre
> `docker run --rm -v ${PWD}:/app -w /app php:8.2-cli php artisan key:generate --show`
> y pega el valor en `.env` como `APP_KEY=base64:...` antes del primer `docker compose up`.

Verifica que el LB interno responde:
```bash
curl http://localhost:8080/api/v1/config
```

Aplica el firewall del servidor privado-app (necesita la IP privada del
Servidor A y la del Servidor C):
```bash
sudo ../infra/scripts/firewall-private.sh <IP_PRIVADA_SERVIDOR_A> <IP_PRIVADA_SERVIDOR_C> <TU_IP_PARA_SSH>
```

## 3. Servidor C (monitoreo) — Prometheus, Grafana

```bash
git clone <tu-repo-infra> infra
cd infra/server-monitoring
cp .env.example .env
nano .env   # GRAFANA_ADMIN_USER, GRAFANA_ADMIN_PASSWORD, GRAFANA_ROOT_URL

# Reemplaza los placeholders de prometheus.yml con las IPs privadas reales
sed -i "s/PRIVATE_SERVER_IP/<IP_PRIVADA_SERVIDOR_B>/g" prometheus/prometheus.yml
sed -i "s/PUBLIC_SERVER_IP/<IP_PRIVADA_SERVIDOR_A>/g" prometheus/prometheus.yml

docker compose up -d
docker compose ps   # prometheus, grafana, node-exporter "running"
curl http://localhost:9090/-/healthy   # prometheus ok
```

Aplica el firewall del servidor de monitoreo (necesita la IP privada del
Servidor A):
```bash
sudo ../infra/scripts/firewall-monitoring.sh <IP_PRIVADA_SERVIDOR_A> <TU_IP_PARA_SSH>
```

## 4. Servidor A (público) — Nginx, SSL, firewall

```bash
git clone <tu-repo-infra> infra
cd infra/server-public
cp .env.example .env
nano .env   # DOMAIN=tu-dominio.com (o el sslip.io),
            # PRIVATE_SERVER_IP=<IP_PRIVADA_SERVIDOR_B>,
            # MONITORING_SERVER_IP=<IP_PRIVADA_SERVIDOR_C>

docker compose up -d
curl http://tu-dominio.com/api/v1/config   # debe responder vía el proxy -> Servidor B
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

Aplica el firewall del servidor público (necesita la IP privada del
Servidor C):
```bash
sudo ../infra/scripts/firewall-public.sh <IP_PRIVADA_SERVIDOR_C>
```

**Además**, en la consola de DigitalOcean, confirma que `fw-public` solo
tiene abierto 22 (a tu IP), 80 y 443 (All IPv4/IPv6) — es la capa de
firewall a nivel de nube, equivalente a la Security List que se iba a usar
en Oracle o al Security Group de AWS.

## 5. Verificación contra la rúbrica

| Verificación | Comando |
|---|---|
| SSL activo | `curl -I https://tu-dominio.com` (debe dar 200, no error de certificado) |
| API protegida con JWT | `curl https://tu-dominio.com/api/v1/customer/info` sin token → 401; con `Authorization: Bearer <token>` de `/api/v1/auth/login` → 200 |
| Balanceador de carga | `for i in 1 2 3 4; do curl -s https://tu-dominio.com/api/v1/config -H "X-Debug: $i"; done` + revisa logs (`docker compose logs web1 web2`) alternando |
| Firewall activo | `sudo ufw status verbose` en las 3 VMs + revisa los Cloud Firewalls en la consola de DigitalOcean |
| Monitoreo | `https://tu-dominio.com/grafana/` (login con `GRAFANA_ADMIN_USER`/`PASSWORD`), dashboard "FoodMatch - Overview" con métricas de las 3 cajas |
| BD no expuesta | Desde tu laptop: `nc -zv <IP_PUBLICA_SERVIDOR_A> 3306` → debe fallar (timeout/refused) |
| Hasheo/encriptado | `php artisan tinker` → `Hash::make('demo')` y `Crypt::encryptString('demo')` en el Servidor B |

## 6. Pendiente para el móvil

- Apuntar `API_BASE_URL` del app Expo a `https://tu-dominio.com/api/v1`.
- Build de producción: `eas build --platform android --profile production`.
- Instalar el APK resultante en el teléfono físico que llevarán a la
  evaluación.

## Notas de seguridad para producción real

- Restringe `/grafana/` con IP allowlist o auth básica adicional en Nginx si
  lo vas a dejar público durante la evaluación.
- Cambia todas las contraseñas de los `.env.example` — nunca subas los
  `.env` reales a git (ya están en `.gitignore`).
- El usuario `exporter` de MySQL tiene permisos de solo lectura
  (`PROCESS, REPLICATION CLIENT, SELECT`), no reutiliza la cuenta root.
