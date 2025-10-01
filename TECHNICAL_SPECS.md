# 🔧 Especificaciones Técnicas - Sistema Alfolí

## 📋 Índice
1. [Arquitectura del Sistema](#arquitectura-del-sistema)
2. [Modelos de Datos](#modelos-de-datos)
3. [API Endpoints](#api-endpoints)
4. [Stored Procedures](#stored-procedures)
5. [Seguridad](#seguridad)
6. [Performance](#performance)
7. [Deployment](#deployment)

## 🏗️ Arquitectura del Sistema

### Patrón Arquitectónico
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Presentation  │    │    Business     │    │   Data Access   │
│     Layer       │◄──►│     Layer       │◄──►│     Layer       │
│                 │    │                 │    │                 │
│ • Blade Views   │    │ • Controllers   │    │ • Models        │
│ • JavaScript    │    │ • Services      │    │ • Repositories  │
│ • CSS/Bootstrap │    │ • Middleware    │    │ • Stored Procs  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### Flujo de Datos
```
Usuario → Ruta → Middleware → Controlador → Servicio → Modelo → Base de Datos
   ↑                                                                    ↓
   ←─────────────── Vista ←─────── Respuesta ←─────────────────────────┘
```

## 🗃️ Modelos de Datos

### Modelo: User
```php
class User extends Authenticatable
{
    protected $table = 'usuarios';
    
    protected $fillable = [
        'nombre_usuario', 'nombre_completo', 'email', 
        'clave_hash', 'rol', 'activo', 'cambiar_password'
    ];
    
    protected $hidden = ['clave_hash'];
    
    protected $casts = [
        'activo' => 'boolean',
        'cambiar_password' => 'boolean',
    ];
    
    // Relaciones
    public function detallesAlfoli()
    {
        return $this->hasMany(DetalleAlfoli::class, 'id_usrregistra', 'nombre_usuario');
    }
    
    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
    
    // Accessors
    public function getRolDisplayAttribute()
    {
        return match($this->rol) {
            'admin' => 'Administrador',
            'editor' => 'Moderador',
            'visualizador' => 'Consultas',
            default => 'Usuario'
        };
    }
}
```

### Modelo: Hermano
```php
class Hermano extends Model
{
    protected $table = 'hermanos';
    
    protected $fillable = ['nombres', 'apellidos', 'telefono'];
    
    // Relaciones
    public function detallesAlfoli()
    {
        return $this->hasMany(DetalleAlfoli::class, 'id_hermano');
    }
    
    // Accessors
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombres . ' ' . $this->apellidos);
    }
    
    // Scopes
    public function scopeActivos($query)
    {
        return $query->whereNotNull('nombres');
    }
}
```

### Modelo: Articulo
```php
class Articulo extends Model
{
    protected $table = 'articulos';
    
    protected $fillable = [
        'codigo_barra', 'descripcion', 'cantidad', 'mes_articulo'
    ];
    
    protected $casts = ['cantidad' => 'integer'];
    
    // Relaciones
    public function detallesAlfoli()
    {
        return $this->hasMany(DetalleAlfoli::class, 'id_articulo');
    }
    
    // Scopes
    public function scopeDelMes($query, $mes)
    {
        return $query->where('mes_articulo', $mes);
    }
    
    public function scopeDelMesActual($query)
    {
        $mesActual = ucfirst(now()->locale('es')->translatedFormat('F'));
        return $query->where('mes_articulo', $mesActual);
    }
}
```

### Modelo: DetalleAlfoli
```php
class DetalleAlfoli extends Model
{
    protected $table = 'detalle_alfoli';
    
    protected $fillable = [
        'id_hermano', 'id_articulo', 'cantidad', 
        'fecha_caducidad', 'fecha_registro', 'id_usrregistra'
    ];
    
    protected $casts = [
        'cantidad' => 'integer',
        'fecha_caducidad' => 'date',
        'fecha_registro' => 'date',
    ];
    
    // Relaciones
    public function hermano()
    {
        return $this->belongsTo(Hermano::class, 'id_hermano');
    }
    
    public function articulo()
    {
        return $this->belongsTo(Articulo::class, 'id_articulo');
    }
    
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usrregistra', 'nombre_usuario');
    }
    
    // Accessors
    public function getEstadoCaducidadAttribute()
    {
        $hoy = Carbon::now();
        $fechaLimite = $hoy->copy()->addDays(59);
        
        if ($this->fecha_caducidad < $hoy) {
            return 'vencido';
        } elseif ($this->fecha_caducidad <= $fechaLimite) {
            return 'pronto_vencer';
        }
        
        return 'ok';
    }
    
    // Scopes
    public function scopeVencidos($query)
    {
        return $query->where('fecha_caducidad', '<', Carbon::now());
    }
    
    public function scopeProntoAVencer($query, $dias = 59)
    {
        return $query->where('fecha_caducidad', '<=', Carbon::now()->addDays($dias))
                    ->where('fecha_caducidad', '>=', Carbon::now());
    }
}
```

## 🛣️ API Endpoints

### Autenticación
```
POST   /login                    # Iniciar sesión
POST   /logout                   # Cerrar sesión
GET    /cambiar-password         # Mostrar formulario cambio contraseña
POST   /cambiar-password         # Procesar cambio contraseña
```

### Dashboard
```
GET    /dashboard                # Vista principal dashboard
GET    /dashboard/export-excel   # Exportar dashboard a Excel
GET    /dashboard/export-pdf     # Exportar dashboard a PDF
```

### Gestión de Alfolí
```
GET    /alfoli                   # Lista de registros alfolí
GET    /alfoli/data              # Datos JSON para tabla
GET    /alfoli/crear             # Formulario nuevo registro
POST   /alfoli                   # Guardar nuevo registro
```

### Gestión de Artículos
```
GET    /articulos/crear          # Formulario nuevo artículo
POST   /articulos                # Guardar nuevo artículo
```

### Gestión de Hermanos
```
GET    /hermanos/crear           # Formulario nuevo hermano
POST   /hermanos                 # Guardar nuevo hermano
```

### Productos Vencimiento
```
GET    /productos-vencimiento         # Vista gestión productos
GET    /productos-vencimiento/data    # Datos JSON productos
PUT    /productos-vencimiento         # Actualizar producto
DELETE /productos-vencimiento         # Eliminar producto
```

### Gestión de Usuarios (Solo Admin)
```
GET    /usuarios                      # Lista usuarios
GET    /usuarios/crear                # Formulario nuevo usuario
POST   /usuarios                      # Guardar usuario
GET    /usuarios/{id}/editar          # Formulario editar usuario
PUT    /usuarios/{id}                 # Actualizar usuario
POST   /usuarios/{id}/toggle-status   # Cambiar estado usuario
POST   /usuarios/{id}/reset-password  # Reset contraseña
```

### Notificaciones
```
GET    /notificaciones               # Vista notificaciones
POST   /notificaciones/enviar        # Enviar notificaciones
```

### Alertas
```
GET    /alertas                      # Configuración alertas
POST   /alertas                      # Guardar configuración
```

## 🗄️ Stored Procedures

### ObtCumpAportes()
**Propósito**: Obtener indicadores de cumplimiento de aportes por hermano

**Lógica**:
```sql
-- Obtiene hermanos que han cumplido o no con sus aportes mensuales
-- Compara cantidad requerida vs cantidad aportada por artículo
-- Retorna estado: 'Cumple' o 'No Cumple'
```

**Campos Retornados**:
- `hermano`: Nombre completo del hermano
- `mes`: Mes del aporte
- `articulo`: Descripción del artículo
- `cant`: Cantidad requerida
- `aporte`: Cantidad aportada
- `estado`: 'Cumple' o 'No Cumple'

### ObtCompTotalesArt()
**Propósito**: Comparativa total esperado vs aportado por artículo

**Lógica**:
```sql
-- Calcula total esperado: (cantidad_requerida * número_hermanos)
-- Calcula total aportado: suma de aportes reales
-- Calcula diferencia para identificar faltantes
```

**Campos Retornados**:
- `mes`: Mes de referencia
- `articulo`: Descripción del artículo
- `cant_requerida_articulo`: Cantidad por hermano
- `total_esperado_articulo`: Total que debería haberse aportado
- `total_aportado_articulo`: Total realmente aportado
- `diferencia`: Calculada en frontend

### ObtArtProxAVencer(dias)
**Propósito**: Obtener productos próximos a vencer

**Parámetros**:
- `dias`: Número de días límite (ej: 60 para "próximos a vencer")

**Lógica**:
```sql
-- Filtra productos donde fecha_caducidad <= (HOY + dias)
-- Incluye información del hermano y artículo
-- Ordena por fecha de caducidad ascendente
```

**Campos Retornados**:
- `fecha_registro`: Fecha de registro del aporte
- `mes`: Mes del aporte
- `fecha_caducidad`: Fecha de vencimiento
- `codigo_barra`: Código del artículo
- `descripcion`: Descripción del producto
- `cantidad`: Cantidad registrada
- `nombre_hermano`: Nombre del hermano aportante

### InsAlfoli(hermano, articulo, cantidad, fecha_caducidad, usuario)
**Propósito**: Insertar nuevo registro de alfolí

**Parámetros**:
- `hermano`: ID del hermano
- `articulo`: ID del artículo
- `cantidad`: Cantidad aportada (1-9)
- `fecha_caducidad`: Fecha de vencimiento
- `usuario`: Usuario que registra

**Validaciones Internas**:
- Verificar existencia de hermano y artículo
- Validar que cantidad esté en rango permitido
- Verificar que fecha_caducidad > HOY + 59 días
- Prevenir duplicados (hermano + artículo + mes)

### participantes(accion, nombres, apellidos, telefono)
**Propósito**: CRUD completo para gestión de hermanos

**Parámetros**:
- `accion`: 'LISTAR', 'AGREGAR', 'EDITAR', 'ELIMINAR'
- `nombres`: Nombres del hermano
- `apellidos`: Apellidos del hermano
- `telefono`: Teléfono (opcional)

**Acciones**:
- **LISTAR**: Retorna todos los hermanos activos
- **AGREGAR**: Inserta nuevo hermano con validaciones
- **EDITAR**: Actualiza información existente
- **ELIMINAR**: Soft delete (marca como inactivo)

## 🔐 Especificaciones de Seguridad

### Autenticación
```php
// Configuración en config/auth.php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
],
```

### Middleware de Autorización
```php
class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        if (!in_array($user->rol, $roles)) {
            return redirect()->route('access.denied');
        }

        return $next($request);
    }
}
```

### Validaciones de Entrada
```php
// Ejemplo: Validación para registro de alfolí
class StoreAlfoliRequest extends FormRequest
{
    public function rules()
    {
        return [
            'hermano' => 'required|exists:hermanos,id',
            'articulo' => 'required|exists:articulos,id',
            'cantidad' => 'required|integer|min:1|max:9',
            'fecha_caducidad' => 'required|date|after:' . Carbon::now()->addDays(59)->format('Y-m-d'),
        ];
    }
    
    public function messages()
    {
        return [
            'hermano.required' => 'Debe seleccionar un hermano.',
            'hermano.exists' => 'El hermano seleccionado no es válido.',
            'cantidad.min' => 'La cantidad mínima es 1.',
            'cantidad.max' => 'La cantidad máxima es 9.',
            'fecha_caducidad.after' => 'La fecha debe ser al menos 60 días posterior a hoy.',
        ];
    }
}
```

### Protección CSRF
```html
<!-- Automático en formularios Blade -->
<form method="POST" action="{{ route('alfoli.store') }}">
    @csrf
    <!-- campos del formulario -->
</form>
```

```javascript
// Configuración AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
```

## ⚡ Optimizaciones de Performance

### Database Queries
```php
// Uso de Stored Procedures para consultas complejas
$indicadores = DB::select('CALL ObtCumpAportes()');

// Eager Loading para evitar N+1 queries
$detalles = DetalleAlfoli::with(['hermano', 'articulo', 'usuario'])->get();

// Query Scopes para reutilización
$productosVencidos = DetalleAlfoli::vencidos()->with('articulo')->get();
```

### Caching Strategy
```php
// Cache de configuraciones
$config = Cache::remember('alertas_config', 3600, function () {
    return DB::table('alertas_programadas')->get();
});

// Cache de datos estáticos
$meses = Cache::rememberForever('meses_articulos', function () {
    return ['Enero', 'Febrero', 'Marzo', /* ... */];
});
```

### Asset Optimization
```javascript
// vite.config.js
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['bootstrap', 'sweetalert2'],
                    charts: ['chart.js'],
                    export: ['xlsx', 'jspdf']
                }
            }
        }
    }
});
```

## 📡 Integración con APIs Externas

### PHPMailer Configuration
```php
// config/mail.php
'mailers' => [
    'smtp' => [
        'transport' => 'smtp',
        'host' => env('MAIL_HOST', 'mail.laflorida-icifd.com'),
        'port' => env('MAIL_PORT', 465),
        'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
    ],
],
```

### Servicio de Notificaciones
```php
class NotificationService
{
    public function enviarNotificacionIncumplimiento($hermanos, $destinatarios)
    {
        $mail = new PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host = config('mail.mailers.smtp.host');
            $mail->SMTPAuth = true;
            $mail->Username = config('mail.mailers.smtp.username');
            $mail->Password = config('mail.mailers.smtp.password');
            $mail->SMTPSecure = config('mail.mailers.smtp.encryption');
            $mail->Port = config('mail.mailers.smtp.port');
            $mail->CharSet = 'UTF-8';
            
            $mail->setFrom(config('mail.from.address'), config('mail.from.name'));
            
            foreach ($destinatarios as $destinatario) {
                $mail->addAddress($destinatario['email'], $destinatario['nombre']);
            }
            
            $mail->isHTML(true);
            $mail->Subject = '📩 Notificación - Hermanos Pendientes de Alfolí';
            $mail->Body = view('emails.incumplimiento', compact('hermanos'))->render();
            
            $mail->send();
            
            return ['success' => true, 'message' => 'Notificaciones enviadas correctamente.'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al enviar: ' . $e->getMessage()];
        }
    }
}
```

## 🧪 Testing Strategy

### Unit Tests
```php
class UserTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'nombre_usuario' => 'testuser',
            'clave_hash' => Hash::make('password123'),
            'activo' => true,
        ]);
        
        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);
        
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
    
    public function test_inactive_user_cannot_login()
    {
        $user = User::factory()->create([
            'activo' => false,
        ]);
        
        $response = $this->post('/login', [
            'username' => $user->nombre_usuario,
            'password' => 'password123',
        ]);
        
        $response->assertSessionHasErrors(['username']);
        $this->assertGuest();
    }
}
```

### Feature Tests
```php
class AlfoliManagementTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_admin_can_create_alfoli_record()
    {
        $admin = User::factory()->admin()->create();
        $hermano = Hermano::factory()->create();
        $articulo = Articulo::factory()->create();
        
        $response = $this->actingAs($admin)
            ->post('/alfoli', [
                'hermano' => $hermano->id,
                'articulo' => $articulo->id,
                'cantidad' => 5,
                'fecha_caducidad' => now()->addDays(70)->format('Y-m-d'),
            ]);
        
        $response->assertRedirect('/alfoli');
        $this->assertDatabaseHas('detalle_alfoli', [
            'id_hermano' => $hermano->id,
            'id_articulo' => $articulo->id,
            'cantidad' => 5,
        ]);
    }
    
    public function test_visualizador_cannot_create_alfoli_record()
    {
        $visualizador = User::factory()->visualizador()->create();
        
        $response = $this->actingAs($visualizador)
            ->get('/alfoli/crear');
        
        $response->assertRedirect('/acceso-denegado');
    }
}
```

## 🚀 Deployment Specifications

### Servidor de Producción
```yaml
# docker-compose.yml
version: '3.8'
services:
  app:
    build: .
    ports:
      - "80:80"
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    volumes:
      - ./storage:/var/www/html/storage
    depends_on:
      - mysql
      
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: laflorid_alfoli_ts
      MYSQL_USER: laflorid_admin_alf
      MYSQL_PASSWORD: y0PlhTGy{Nfq}
    volumes:
      - mysql_data:/var/lib/mysql
      
volumes:
  mysql_data:
```

### Configuración Apache
```apache
<VirtualHost *:80>
    ServerName alfoli.laflorida-icifd.com
    DocumentRoot /var/www/html/sistema-alfoli/public
    
    <Directory /var/www/html/sistema-alfoli/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security Headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</VirtualHost>
```

### Configuración Nginx
```nginx
server {
    listen 80;
    server_name alfoli.laflorida-icifd.com;
    root /var/www/html/sistema-alfoli/public;
    
    index index.php;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_hide_header X-Powered-By;
    }
    
    # Deny access to sensitive files
    location ~ /\.(env|git) {
        deny all;
    }
}
```

## 📊 Monitoreo y Logs

### Logging Configuration
```php
// config/logging.php
'channels' => [
    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
    ],
    
    'audit' => [
        'driver' => 'daily',
        'path' => storage_path('logs/audit.log'),
        'level' => 'info',
        'days' => 90,
    ],
],
```

### Métricas de Sistema
```php
// Middleware para métricas
class MetricsMiddleware
{
    public function handle($request, Closure $next)
    {
        $start = microtime(true);
        
        $response = $next($request);
        
        $duration = microtime(true) - $start;
        
        Log::channel('metrics')->info('Request processed', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user' => auth()->id(),
            'duration' => $duration,
            'memory' => memory_get_peak_usage(true),
        ]);
        
        return $response;
    }
}
```

## 🔄 Procesos Automatizados

### Cron Jobs
```bash
# /etc/crontab
# Envío de alertas programadas cada hora
0 * * * * cd /var/www/html/sistema-alfoli && php artisan alertas:enviar

# Limpieza de logs antiguos diariamente
0 2 * * * cd /var/www/html/sistema-alfoli && php artisan logs:cleanup

# Backup de base de datos diario
0 3 * * * cd /var/www/html/sistema-alfoli && php artisan backup:run
```

### Comandos Artisan Personalizados
```php
// app/Console/Commands/EnviarAlertasProgramadas.php
class EnviarAlertasProgramadas extends Command
{
    protected $signature = 'alertas:enviar';
    protected $description = 'Envía alertas programadas según configuración';
    
    public function handle()
    {
        $alertas = DB::table('alertas_programadas')
            ->where('programada', true)
            ->get();
            
        foreach ($alertas as $alerta) {
            if ($this->debeEnviarAlerta($alerta)) {
                $this->enviarAlerta($alerta);
            }
        }
        
        $this->info('Alertas procesadas correctamente.');
    }
}
```

## 📱 Responsive Design Specifications

### Breakpoints
```css
/* Mobile First Approach */
:root {
    --mobile: 320px;
    --tablet: 768px;
    --desktop: 1024px;
    --large: 1200px;
}

/* Media Queries */
@media (min-width: 768px) {
    .container { max-width: 750px; }
}

@media (min-width: 1024px) {
    .container { max-width: 970px; }
}

@media (min-width: 1200px) {
    .container { max-width: 1170px; }
}
```

### Component Responsiveness
```css
/* Tablas responsivas */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

@media (max-width: 767px) {
    .table-responsive table {
        font-size: 0.875rem;
    }
    
    .table-responsive th,
    .table-responsive td {
        padding: 0.5rem;
        white-space: nowrap;
    }
}

/* Formularios móviles */
@media (max-width: 767px) {
    .form-control {
        font-size: 16px; /* Previene zoom en iOS */
    }
    
    .btn {
        padding: 12px 20px;
        font-size: 16px;
    }
}
```

## 🔧 Configuraciones Avanzadas

### Queue Configuration
```php
// config/queue.php
'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
    ],
],

// Para notificaciones asíncronas
class EnviarNotificacion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle()
    {
        // Lógica de envío de notificación
    }
}
```

### Cache Configuration
```php
// config/cache.php
'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
    ],
    
    'file' => [
        'driver' => 'file',
        'path' => storage_path('framework/cache/data'),
    ],
],
```

## 🛡️ Security Checklist

### ✅ Implementado
- [x] Autenticación robusta con Laravel Auth
- [x] Autorización basada en roles
- [x] Protección CSRF en todos los formularios
- [x] Validación de entrada en backend y frontend
- [x] Hash seguro de contraseñas (bcrypt)
- [x] Sanitización automática de outputs
- [x] Prepared statements para queries
- [x] Headers de seguridad HTTP
- [x] Logs de auditoría
- [x] Validación de archivos subidos

### 🔄 Pendiente
- [ ] Rate limiting para prevenir ataques
- [ ] Two-factor authentication (2FA)
- [ ] Encriptación de datos sensibles
- [ ] Backup automático encriptado
- [ ] Monitoreo de intrusiones
- [ ] Certificado SSL/TLS

## 📈 Métricas y KPIs

### Métricas Técnicas
```php
// Middleware para capturar métricas
class CaptureMetrics
{
    public function handle($request, Closure $next)
    {
        $start = microtime(true);
        $memoryStart = memory_get_usage();
        
        $response = $next($request);
        
        $metrics = [
            'response_time' => microtime(true) - $start,
            'memory_usage' => memory_get_usage() - $memoryStart,
            'peak_memory' => memory_get_peak_usage(),
            'queries_count' => DB::getQueryLog()->count(),
        ];
        
        Log::channel('metrics')->info('Request metrics', $metrics);
        
        return $response;
    }
}
```

### Métricas de Negocio
- **Tasa de Cumplimiento**: % hermanos que aportan mensualmente
- **Productos Vencidos**: Cantidad y valor de productos perdidos
- **Eficiencia de Registro**: Tiempo promedio de registro por aporte
- **Adopción del Sistema**: % usuarios activos vs. registrados

## 🔄 Versionado y Releases

### Semantic Versioning
```
MAJOR.MINOR.PATCH

MAJOR: Cambios incompatibles en API
MINOR: Nueva funcionalidad compatible
PATCH: Bug fixes compatibles
```

### Release Notes Template
```markdown
## [1.2.0] - 2025-01-15

### Added
- Nueva funcionalidad de escáner QR para artículos
- Dashboard mejorado con gráficos interactivos
- Sistema de alertas programadas

### Changed
- Interfaz de usuario rediseñada con Bootstrap 5
- Optimización de consultas de base de datos
- Mejoras en validaciones de formularios

### Fixed
- Corrección en cálculo de productos vencidos
- Fix en exportación PDF con caracteres especiales
- Resolución de problemas de responsive en móviles

### Security
- Implementación de rate limiting
- Mejoras en validación de entrada
- Actualización de dependencias de seguridad
```

---

**Documento Técnico v1.0**  
**Última Actualización**: 2025-01-15  
**Autor**: Aura Solutions Group SpA  
**Revisado por**: Equipo Técnico