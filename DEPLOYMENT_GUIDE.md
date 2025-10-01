# 🚀 Guía de Despliegue - Sistema Alfolí

## 📋 Prerrequisitos del Sistema

### Servidor de Producción
- **Sistema Operativo**: Ubuntu 20.04 LTS o superior / CentOS 8+
- **Memoria RAM**: Mínimo 2GB, recomendado 4GB
- **Almacenamiento**: Mínimo 20GB SSD
- **CPU**: 2 cores mínimo
- **Ancho de Banda**: 100 Mbps

### Software Requerido
```bash
# PHP 8.1 o superior
sudo apt update
sudo apt install php8.1 php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd

# MySQL 8.0
sudo apt install mysql-server-8.0

# Nginx o Apache
sudo apt install nginx
# O
sudo apt install apache2

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js 18+
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs
```

## 🔧 Configuración del Entorno

### 1. Configuración de Base de Datos

#### Crear Base de Datos
```sql
-- Conectar como root
mysql -u root -p

-- Crear base de datos
CREATE DATABASE laflorid_alfoli_ts CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Crear usuario
CREATE USER 'laflorid_admin_alf'@'localhost' IDENTIFIED BY 'y0PlhTGy{Nfq';
GRANT ALL PRIVILEGES ON laflorid_alfoli_ts.* TO 'laflorid_admin_alf'@'localhost';
FLUSH PRIVILEGES;
```

#### Configurar MySQL
```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf
[mysqld]
# Configuraciones de rendimiento
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 200
query_cache_size = 64M

# Configuraciones de seguridad
bind-address = 127.0.0.1
skip-networking = false
sql_mode = STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO

# Configuraciones de charset
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
```

### 2. Configuración de PHP

#### PHP-FPM Configuration
```ini
# /etc/php/8.1/fpm/pool.d/www.conf
[www]
user = www-data
group = www-data
listen = /run/php/php8.1-fpm.sock
listen.owner = www-data
listen.group = www-data

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

# Configuraciones de seguridad
php_admin_value[disable_functions] = exec,passthru,shell_exec,system
php_admin_flag[allow_url_fopen] = off
```

#### PHP Configuration
```ini
# /etc/php/8.1/fpm/php.ini
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20

# Configuraciones de seguridad
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

# Configuraciones de sesión
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

### 3. Configuración del Servidor Web

#### Nginx Configuration
```nginx
# /etc/nginx/sites-available/alfoli
server {
    listen 80;
    listen [::]:80;
    server_name alfoli.laflorida-icifd.com;
    root /var/www/html/sistema-alfoli/public;
    
    index index.php index.html;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_hide_header X-Powered-By;
        fastcgi_read_timeout 300;
    }
    
    # Cache static assets
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # Deny access to sensitive files
    location ~ /\.(env|git|htaccess) {
        deny all;
        return 404;
    }
    
    location ~ /storage/ {
        deny all;
        return 404;
    }
}
```

#### Apache Configuration
```apache
# /etc/apache2/sites-available/alfoli.conf
<VirtualHost *:80>
    ServerName alfoli.laflorida-icifd.com
    DocumentRoot /var/www/html/sistema-alfoli/public
    
    <Directory /var/www/html/sistema-alfoli/public>
        AllowOverride All
        Require all granted
        
        # Security
        Options -Indexes -Includes -ExecCGI
    </Directory>
    
    # Security Headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Compression
    LoadModule deflate_module modules/mod_deflate.so
    <Location />
        SetOutputFilter DEFLATE
        SetEnvIfNoCase Request_URI \
            \.(?:gif|jpe?g|png)$ no-gzip dont-vary
        SetEnvIfNoCase Request_URI \
            \.(?:exe|t?gz|zip|bz2|sit|rar)$ no-gzip dont-vary
    </Location>
    
    # Logs
    ErrorLog ${APACHE_LOG_DIR}/alfoli_error.log
    CustomLog ${APACHE_LOG_DIR}/alfoli_access.log combined
</VirtualHost>
```

## 📦 Proceso de Despliegue

### 1. Preparación del Código

#### Clonar Repositorio
```bash
cd /var/www/html
sudo git clone https://github.com/aura-solutions/sistema-alfoli.git
sudo chown -R www-data:www-data sistema-alfoli
cd sistema-alfoli
```

#### Instalar Dependencias
```bash
# Dependencias PHP
composer install --optimize-autoloader --no-dev

# Dependencias Node.js
npm ci --production

# Compilar assets
npm run build
```

### 2. Configuración de Laravel

#### Variables de Entorno
```bash
# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Configurar permisos
sudo chmod -R 755 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

#### Archivo .env de Producción
```env
APP_NAME="Sistema Alfolí"
APP_ENV=production
APP_KEY=base64:GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://alfoli.laflorida-icifd.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=laflorida-icifd.com
DB_PORT=3306
DB_DATABASE=laflorid_alfoli_ts
DB_USERNAME=laflorid_admin_alf
DB_PASSWORD=y0PlhTGy{Nfq

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=mail.laflorida-icifd.com
MAIL_PORT=465
MAIL_USERNAME=notificaciones@laflorida-icifd.com
MAIL_PASSWORD=Adm1n!st5
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=notificaciones@laflorida-icifd.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. Optimización para Producción

#### Cache de Configuración
```bash
# Cachear configuraciones
php artisan config:cache

# Cachear rutas
php artisan route:cache

# Cachear vistas
php artisan view:cache

# Optimizar autoloader
composer dump-autoload --optimize
```

#### Configuración de Logs
```bash
# Crear directorios de logs
sudo mkdir -p /var/log/alfoli
sudo chown www-data:www-data /var/log/alfoli

# Configurar logrotate
sudo tee /etc/logrotate.d/alfoli << EOF
/var/log/alfoli/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload php8.1-fpm
    endscript
}
EOF
```

## 🔄 Automatización con Scripts

### Script de Despliegue
```bash
#!/bin/bash
# deploy.sh

set -e

echo "🚀 Iniciando despliegue del Sistema Alfolí..."

# Variables
PROJECT_DIR="/var/www/html/sistema-alfoli"
BACKUP_DIR="/var/backups/alfoli"
DATE=$(date +%Y%m%d_%H%M%S)

# Crear backup
echo "📦 Creando backup..."
sudo mkdir -p $BACKUP_DIR
sudo tar -czf $BACKUP_DIR/alfoli_backup_$DATE.tar.gz $PROJECT_DIR

# Actualizar código
echo "📥 Actualizando código..."
cd $PROJECT_DIR
sudo -u www-data git pull origin main

# Instalar dependencias
echo "📚 Instalando dependencias..."
sudo -u www-data composer install --optimize-autoloader --no-dev
sudo -u www-data npm ci --production

# Compilar assets
echo "🔨 Compilando assets..."
sudo -u www-data npm run build

# Limpiar caches
echo "🧹 Limpiando caches..."
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

# Cachear para producción
echo "⚡ Optimizando para producción..."
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Reiniciar servicios
echo "🔄 Reiniciando servicios..."
sudo systemctl reload nginx
sudo systemctl reload php8.1-fpm

# Verificar estado
echo "✅ Verificando estado de la aplicación..."
curl -f http://localhost/health-check || {
    echo "❌ Error: La aplicación no responde correctamente"
    exit 1
}

echo "🎉 Despliegue completado exitosamente!"
```

### Health Check Endpoint
```php
// routes/web.php
Route::get('/health-check', function () {
    try {
        // Verificar conexión a base de datos
        DB::connection()->getPdo();
        
        // Verificar permisos de escritura
        $testFile = storage_path('app/health-check.txt');
        file_put_contents($testFile, 'OK');
        unlink($testFile);
        
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
            'database' => 'connected',
            'storage' => 'writable',
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'unhealthy',
            'error' => $e->getMessage(),
        ], 500);
    }
});
```

## 🔐 Configuración SSL/TLS

### Certificado Let's Encrypt
```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-nginx

# Obtener certificado
sudo certbot --nginx -d alfoli.laflorida-icifd.com

# Configurar renovación automática
sudo crontab -e
# Agregar línea:
0 12 * * * /usr/bin/certbot renew --quiet
```

### Configuración HTTPS
```nginx
# /etc/nginx/sites-available/alfoli-ssl
server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name alfoli.laflorida-icifd.com;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/alfoli.laflorida-icifd.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/alfoli.laflorida-icifd.com/privkey.pem;
    ssl_trusted_certificate /etc/letsencrypt/live/alfoli.laflorida-icifd.com/chain.pem;
    
    # SSL Security
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # HSTS
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    
    # Resto de configuración igual que HTTP
    root /var/www/html/sistema-alfoli/public;
    index index.php;
    
    # ... resto de la configuración
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    listen [::]:80;
    server_name alfoli.laflorida-icifd.com;
    return 301 https://$server_name$request_uri;
}
```

## 📊 Monitoreo y Alertas

### Configuración de Logs
```php
// config/logging.php
'channels' => [
    'production' => [
        'driver' => 'stack',
        'channels' => ['daily', 'slack'],
        'ignore_exceptions' => false,
    ],
    
    'daily' => [
        'driver' => 'daily',
        'path' => '/var/log/alfoli/laravel.log',
        'level' => env('LOG_LEVEL', 'error'),
        'days' => 30,
    ],
    
    'slack' => [
        'driver' => 'slack',
        'url' => env('LOG_SLACK_WEBHOOK_URL'),
        'username' => 'Sistema Alfolí',
        'emoji' => ':warning:',
        'level' => 'error',
    ],
],
```

### Script de Monitoreo
```bash
#!/bin/bash
# monitor.sh

# Verificar estado de servicios
check_service() {
    if systemctl is-active --quiet $1; then
        echo "✅ $1 está ejecutándose"
    else
        echo "❌ $1 no está ejecutándose"
        sudo systemctl start $1
    fi
}

# Verificar servicios críticos
check_service nginx
check_service php8.1-fpm
check_service mysql

# Verificar espacio en disco
DISK_USAGE=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ $DISK_USAGE -gt 80 ]; then
    echo "⚠️ Advertencia: Uso de disco al ${DISK_USAGE}%"
fi

# Verificar memoria
MEMORY_USAGE=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')
if [ $MEMORY_USAGE -gt 90 ]; then
    echo "⚠️ Advertencia: Uso de memoria al ${MEMORY_USAGE}%"
fi

# Verificar logs de errores
ERROR_COUNT=$(tail -n 100 /var/log/alfoli/laravel.log | grep -c "ERROR" || echo "0")
if [ $ERROR_COUNT -gt 10 ]; then
    echo "⚠️ Advertencia: ${ERROR_COUNT} errores en los últimos 100 logs"
fi

echo "✅ Monitoreo completado - $(date)"
```

## 🔄 Backup y Recuperación

### Script de Backup Automático
```bash
#!/bin/bash
# backup.sh

set -e

# Variables
BACKUP_DIR="/var/backups/alfoli"
PROJECT_DIR="/var/www/html/sistema-alfoli"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Crear directorio de backup
mkdir -p $BACKUP_DIR

echo "📦 Iniciando backup del Sistema Alfolí - $DATE"

# Backup de base de datos
echo "🗄️ Respaldando base de datos..."
mysqldump -u laflorid_admin_alf -p'y0PlhTGy{Nfq' \
    --single-transaction \
    --routines \
    --triggers \
    laflorid_alfoli_ts > $BACKUP_DIR/database_$DATE.sql

# Comprimir backup de BD
gzip $BACKUP_DIR/database_$DATE.sql

# Backup de archivos de aplicación
echo "📁 Respaldando archivos de aplicación..."
tar -czf $BACKUP_DIR/application_$DATE.tar.gz \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.git' \
    --exclude='storage/logs' \
    $PROJECT_DIR

# Backup de archivos subidos (si los hay)
if [ -d "$PROJECT_DIR/storage/app/public" ]; then
    echo "🖼️ Respaldando archivos subidos..."
    tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz $PROJECT_DIR/storage/app/public
fi

# Limpiar backups antiguos
echo "🧹 Limpiando backups antiguos..."
find $BACKUP_DIR -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +$RETENTION_DAYS -delete

# Verificar integridad del backup
echo "✅ Verificando integridad..."
if [ -f "$BACKUP_DIR/database_$DATE.sql.gz" ] && [ -f "$BACKUP_DIR/application_$DATE.tar.gz" ]; then
    echo "✅ Backup completado exitosamente - $DATE"
    
    # Enviar notificación de éxito (opcional)
    curl -X POST "https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK" \
        -H 'Content-type: application/json' \
        --data "{\"text\":\"✅ Backup del Sistema Alfolí completado: $DATE\"}"
else
    echo "❌ Error en el backup - $DATE"
    exit 1
fi
```

### Script de Restauración
```bash
#!/bin/bash
# restore.sh

if [ $# -ne 1 ]; then
    echo "Uso: $0 <fecha_backup>"
    echo "Ejemplo: $0 20250115_143000"
    exit 1
fi

BACKUP_DATE=$1
BACKUP_DIR="/var/backups/alfoli"
PROJECT_DIR="/var/www/html/sistema-alfoli"

echo "🔄 Iniciando restauración del backup: $BACKUP_DATE"

# Verificar que existan los archivos de backup
if [ ! -f "$BACKUP_DIR/database_$BACKUP_DATE.sql.gz" ]; then
    echo "❌ Error: No se encontró el backup de base de datos"
    exit 1
fi

if [ ! -f "$BACKUP_DIR/application_$BACKUP_DATE.tar.gz" ]; then
    echo "❌ Error: No se encontró el backup de aplicación"
    exit 1
fi

# Confirmar restauración
read -p "⚠️ ¿Está seguro de que desea restaurar el backup $BACKUP_DATE? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Restauración cancelada"
    exit 1
fi

# Poner aplicación en modo mantenimiento
echo "🚧 Activando modo mantenimiento..."
cd $PROJECT_DIR
sudo -u www-data php artisan down

# Restaurar base de datos
echo "🗄️ Restaurando base de datos..."
gunzip -c $BACKUP_DIR/database_$BACKUP_DATE.sql.gz | \
    mysql -u laflorid_admin_alf -p'y0PlhTGy{Nfq' laflorid_alfoli_ts

# Restaurar archivos de aplicación
echo "📁 Restaurando archivos de aplicación..."
cd /var/www/html
sudo rm -rf sistema-alfoli-temp
sudo tar -xzf $BACKUP_DIR/application_$BACKUP_DATE.tar.gz
sudo mv sistema-alfoli sistema-alfoli-old
sudo mv sistema-alfoli-temp sistema-alfoli
sudo chown -R www-data:www-data sistema-alfoli

# Restaurar archivos subidos
if [ -f "$BACKUP_DIR/uploads_$BACKUP_DATE.tar.gz" ]; then
    echo "🖼️ Restaurando archivos subidos..."
    cd $PROJECT_DIR
    sudo tar -xzf $BACKUP_DIR/uploads_$BACKUP_DATE.tar.gz
fi

# Configurar permisos
sudo chmod -R 755 $PROJECT_DIR/storage $PROJECT_DIR/bootstrap/cache
sudo chown -R www-data:www-data $PROJECT_DIR/storage $PROJECT_DIR/bootstrap/cache

# Limpiar caches
echo "🧹 Limpiando caches..."
cd $PROJECT_DIR
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

# Reactivar aplicación
echo "✅ Reactivando aplicación..."
sudo -u www-data php artisan up

echo "🎉 Restauración completada exitosamente!"
```

## 📈 Configuración de Cron Jobs

### Crontab del Sistema
```bash
# Editar crontab
sudo crontab -e

# Agregar tareas programadas
# Backup diario a las 3:00 AM
0 3 * * * /var/www/html/sistema-alfoli/scripts/backup.sh >> /var/log/alfoli/backup.log 2>&1

# Envío de alertas cada hora
0 * * * * cd /var/www/html/sistema-alfoli && php artisan alertas:enviar >> /var/log/alfoli/alertas.log 2>&1

# Limpieza de logs semanalmente
0 2 * * 0 /var/www/html/sistema-alfoli/scripts/cleanup-logs.sh >> /var/log/alfoli/cleanup.log 2>&1

# Monitoreo cada 5 minutos
*/5 * * * * /var/www/html/sistema-alfoli/scripts/monitor.sh >> /var/log/alfoli/monitor.log 2>&1

# Optimización de base de datos mensualmente
0 4 1 * * cd /var/www/html/sistema-alfoli && php artisan db:optimize >> /var/log/alfoli/optimize.log 2>&1
```

### Comandos Artisan Personalizados
```php
// app/Console/Commands/DatabaseOptimize.php
class DatabaseOptimize extends Command
{
    protected $signature = 'db:optimize';
    protected $description = 'Optimiza las tablas de la base de datos';
    
    public function handle()
    {
        $this->info('Iniciando optimización de base de datos...');
        
        $tables = ['usuarios', 'hermanos', 'articulos', 'detalle_alfoli', 'alertas_programadas'];
        
        foreach ($tables as $table) {
            DB::statement("OPTIMIZE TABLE $table");
            $this->info("✅ Tabla $table optimizada");
        }
        
        $this->info('🎉 Optimización completada');
    }
}
```

## 🔍 Troubleshooting

### Problemas Comunes

#### Error 500 - Internal Server Error
```bash
# Verificar logs de error
sudo tail -f /var/log/alfoli/laravel.log
sudo tail -f /var/log/nginx/error.log

# Verificar permisos
sudo chown -R www-data:www-data /var/www/html/sistema-alfoli/storage
sudo chmod -R 755 /var/www/html/sistema-alfoli/storage

# Limpiar caches
cd /var/www/html/sistema-alfoli
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
```

#### Error de Conexión a Base de Datos
```bash
# Verificar estado de MySQL
sudo systemctl status mysql

# Verificar conectividad
mysql -u laflorid_admin_alf -p'y0PlhTGy{Nfq' -h laflorida-icifd.com -e "SELECT 1"

# Verificar configuración en .env
grep DB_ /var/www/html/sistema-alfoli/.env
```

#### Problemas de Performance
```bash
# Verificar uso de recursos
htop
iotop

# Analizar queries lentas
sudo tail -f /var/log/mysql/slow.log

# Verificar cache de OPcache
php -i | grep opcache
```

### Comandos de Diagnóstico
```bash
# Script de diagnóstico completo
#!/bin/bash
# diagnose.sh

echo "🔍 Diagnóstico del Sistema Alfolí"
echo "=================================="

# Información del sistema
echo "📊 Información del Sistema:"
echo "OS: $(lsb_release -d | cut -f2)"
echo "PHP: $(php -v | head -n1)"
echo "MySQL: $(mysql --version)"
echo "Nginx: $(nginx -v 2>&1)"

# Estado de servicios
echo -e "\n🔧 Estado de Servicios:"
systemctl is-active nginx && echo "✅ Nginx" || echo "❌ Nginx"
systemctl is-active php8.1-fpm && echo "✅ PHP-FPM" || echo "❌ PHP-FPM"
systemctl is-active mysql && echo "✅ MySQL" || echo "❌ MySQL"

# Uso de recursos
echo -e "\n💾 Uso de Recursos:"
echo "Memoria: $(free -h | awk 'NR==2{printf "%.1f%%", $3*100/$2}')"
echo "Disco: $(df -h / | awk 'NR==2{print $5}')"
echo "CPU: $(top -bn1 | grep "Cpu(s)" | awk '{print $2}' | cut -d'%' -f1)%"

# Verificar aplicación
echo -e "\n🌐 Estado de la Aplicación:"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost)
if [ $HTTP_CODE -eq 200 ]; then
    echo "✅ Aplicación respondiendo correctamente"
else
    echo "❌ Aplicación no responde (HTTP $HTTP_CODE)"
fi

# Verificar base de datos
echo -e "\n🗄️ Conexión a Base de Datos:"
cd /var/www/html/sistema-alfoli
if sudo -u www-data php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';" 2>/dev/null | grep -q "OK"; then
    echo "✅ Conexión a base de datos exitosa"
else
    echo "❌ Error de conexión a base de datos"
fi

echo -e "\n✅ Diagnóstico completado"
```

## 🔧 Mantenimiento

### Tareas de Mantenimiento Semanal
```bash
#!/bin/bash
# weekly-maintenance.sh

echo "🔧 Iniciando mantenimiento semanal..."

# Actualizar sistema operativo
sudo apt update && sudo apt upgrade -y

# Limpiar logs antiguos
sudo find /var/log -name "*.log" -mtime +7 -delete

# Optimizar base de datos
cd /var/www/html/sistema-alfoli
sudo -u www-data php artisan db:optimize

# Limpiar caches de aplicación
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan view:clear

# Verificar integridad de archivos
sudo -u www-data php artisan storage:link

# Reiniciar servicios para liberar memoria
sudo systemctl restart php8.1-fpm

echo "✅ Mantenimiento semanal completado"
```

### Tareas de Mantenimiento Mensual
```bash
#!/bin/bash
# monthly-maintenance.sh

echo "🔧 Iniciando mantenimiento mensual..."

# Analizar logs de seguridad
sudo fail2ban-client status

# Verificar certificados SSL
sudo certbot certificates

# Actualizar dependencias de seguridad
cd /var/www/html/sistema-alfoli
sudo -u www-data composer audit

# Generar reporte de uso
sudo -u www-data php artisan reports:usage

# Verificar integridad de backups
/var/www/html/sistema-alfoli/scripts/verify-backups.sh

echo "✅ Mantenimiento mensual completado"
```

## 📞 Contacto y Soporte

### Información de Contacto
- **Desarrollador**: Aura Solutions Group SpA
- **Email Técnico**: soporte@aurasolutions.cl
- **Email Emergencias**: emergencias@aurasolutions.cl
- **Teléfono**: +56 9 XXXX XXXX
- **Horario de Soporte**: Lunes a Viernes, 9:00 - 18:00 CLT

### Escalación de Incidentes
1. **Nivel 1**: Problemas menores, respuesta en 4 horas
2. **Nivel 2**: Problemas moderados, respuesta en 2 horas
3. **Nivel 3**: Problemas críticos, respuesta en 30 minutos
4. **Nivel 4**: Sistema caído, respuesta inmediata

### Información para Reportar Incidentes
- Descripción detallada del problema
- Pasos para reproducir el error
- Logs relevantes (últimas 50 líneas)
- Captura de pantalla si aplica
- Hora exacta del incidente
- Usuarios afectados

---

**Guía de Despliegue v1.0**  
**Última Actualización**: 2025-01-15  
**Autor**: Aura Solutions Group SpA