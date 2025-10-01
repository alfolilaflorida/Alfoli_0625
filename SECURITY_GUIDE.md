# 🔐 Guía de Seguridad - Sistema Alfolí

## 🎯 Objetivos de Seguridad

### Principios Fundamentales
1. **Confidencialidad**: Proteger información sensible de accesos no autorizados
2. **Integridad**: Garantizar que los datos no sean modificados sin autorización
3. **Disponibilidad**: Asegurar que el sistema esté disponible cuando se necesite
4. **Autenticidad**: Verificar la identidad de usuarios y origen de datos
5. **No Repudio**: Mantener trazabilidad de todas las acciones

## 🛡️ Modelo de Amenazas

### Amenazas Identificadas

#### 🔴 Críticas
- **Acceso no autorizado a datos de hermanos**
- **Modificación maliciosa de registros de alfolí**
- **Robo de credenciales de administrador**
- **Inyección SQL en formularios**

#### 🟡 Moderadas
- **Ataques de fuerza bruta en login**
- **Cross-Site Scripting (XSS)**
- **Cross-Site Request Forgery (CSRF)**
- **Enumeración de usuarios**

#### 🟢 Bajas
- **Information disclosure en headers**
- **Clickjacking**
- **Session fixation**
- **Directory traversal**

## 🔒 Controles de Seguridad Implementados

### 1. Autenticación y Autorización

#### Sistema de Autenticación
```php
// Configuración robusta de autenticación
class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Rate limiting
        $this->middleware('throttle:5,1')->only('login');
        
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Buscar usuario activo
        $user = User::where('nombre_usuario', $request->username)
                   ->where('activo', true)
                   ->first();

        if (!$user || !Hash::check($request->password, $user->clave_hash)) {
            // Log intento fallido
            Log::warning('Intento de login fallido', [
                'username' => $request->username,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return back()->withErrors([
                'username' => 'Credenciales incorrectas.'
            ])->withInput();
        }

        // Log login exitoso
        Log::info('Login exitoso', [
            'user_id' => $user->id,
            'username' => $user->nombre_usuario,
            'ip' => $request->ip(),
        ]);

        Auth::login($user, $request->filled('remember'));
        
        return $this->redirectBasedOnRole();
    }
}
```

#### Control de Acceso Basado en Roles
```php
// Middleware personalizado para roles
class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Log intento de acceso
        Log::info('Intento de acceso', [
            'user_id' => $user->id,
            'required_roles' => $roles,
            'user_role' => $user->rol,
            'route' => $request->route()->getName(),
        ]);
        
        if (!in_array($user->rol, $roles)) {
            Log::warning('Acceso denegado', [
                'user_id' => $user->id,
                'required_roles' => $roles,
                'user_role' => $user->rol,
                'route' => $request->route()->getName(),
            ]);
            
            return redirect()->route('access.denied');
        }

        return $next($request);
    }
}
```

### 2. Validación y Sanitización

#### Validaciones de Entrada
```php
// Form Request personalizado
class StoreAlfoliRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->canManage();
    }
    
    public function rules()
    {
        return [
            'hermano' => [
                'required',
                'integer',
                'exists:hermanos,id',
                function ($attribute, $value, $fail) {
                    $hermano = Hermano::find($value);
                    if (!$hermano || !$hermano->activo) {
                        $fail('El hermano seleccionado no está activo.');
                    }
                },
            ],
            'articulo' => [
                'required',
                'integer',
                'exists:articulos,id',
                function ($attribute, $value, $fail) {
                    $articulo = Articulo::find($value);
                    $mesActual = ucfirst(now()->locale('es')->translatedFormat('F'));
                    if ($articulo && $articulo->mes_articulo !== $mesActual) {
                        $fail('El artículo no corresponde al mes actual.');
                    }
                },
            ],
            'cantidad' => [
                'required',
                'integer',
                'min:1',
                'max:9',
            ],
            'fecha_caducidad' => [
                'required',
                'date',
                'after:' . Carbon::now()->addDays(59)->format('Y-m-d'),
                function ($attribute, $value, $fail) {
                    $fecha = Carbon::parse($value);
                    $limite = Carbon::now()->addDays(59);
                    if ($fecha->lte($limite)) {
                        $fail('La fecha debe ser al menos 60 días posterior a hoy.');
                    }
                },
            ],
        ];
    }
    
    protected function prepareForValidation()
    {
        // Sanitizar entrada
        $this->merge([
            'hermano' => (int) $this->hermano,
            'articulo' => (int) $this->articulo,
            'cantidad' => (int) $this->cantidad,
        ]);
    }
}
```

#### Sanitización de Salida
```php
// Blade templates automáticamente escapan output
{{ $user->nombre_completo }} // Escapado automáticamente

// Para HTML sin escapar (usar con precaución)
{!! $trustedHtml !!}

// Helper personalizado para sanitización
function sanitizeOutput($string) {
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
```

### 3. Protección Contra Ataques

#### Protección CSRF
```php
// Automática en Laravel, pero configuración personalizada
// config/session.php
'same_site' => 'strict',
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'lifetime' => 120, // 2 horas

// Verificación manual en casos especiales
class CustomCSRFMiddleware
{
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('DELETE')) {
            $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
            
            if (!hash_equals(session()->token(), $token)) {
                Log::warning('CSRF token mismatch', [
                    'user_id' => auth()->id(),
                    'ip' => $request->ip(),
                    'route' => $request->route()->getName(),
                ]);
                
                abort(419, 'Token CSRF inválido');
            }
        }
        
        return $next($request);
    }
}
```

#### Protección XSS
```php
// Configuración de Content Security Policy
class SecurityHeadersMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Content Security Policy
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com; " .
               "style-src 'self' 'unsafe-inline' cdn.jsdelivr.net cdnjs.cloudflare.com fonts.googleapis.com; " .
               "font-src 'self' fonts.gstatic.com cdnjs.cloudflare.com; " .
               "img-src 'self' data: https:; " .
               "connect-src 'self';";
               
        $response->headers->set('Content-Security-Policy', $csp);
        
        return $response;
    }
}
```

#### Protección SQL Injection
```php
// Uso exclusivo de Eloquent ORM y Prepared Statements
class AlfoliController extends Controller
{
    public function store(Request $request)
    {
        // ✅ Correcto: Uso de Eloquent
        $detalle = DetalleAlfoli::create([
            'id_hermano' => $request->hermano,
            'id_articulo' => $request->articulo,
            'cantidad' => $request->cantidad,
            'fecha_caducidad' => $request->fecha_caducidad,
            'id_usrregistra' => auth()->user()->nombre_usuario,
        ]);
        
        // ✅ Correcto: Stored Procedure con parámetros
        DB::statement('CALL InsAlfoli(?, ?, ?, ?, ?)', [
            $request->hermano,
            $request->articulo,
            $request->cantidad,
            $request->fecha_caducidad,
            auth()->user()->nombre_usuario
        ]);
        
        // ❌ NUNCA hacer esto:
        // DB::statement("INSERT INTO detalle_alfoli VALUES ({$request->hermano})");
    }
}
```

### 4. Gestión de Sesiones

#### Configuración Segura de Sesiones
```php
// config/session.php
return [
    'driver' => env('SESSION_DRIVER', 'file'),
    'lifetime' => env('SESSION_LIFETIME', 120), // 2 horas
    'expire_on_close' => true,
    'encrypt' => true,
    'files' => storage_path('framework/sessions'),
    'connection' => env('SESSION_CONNECTION'),
    'table' => 'sessions',
    'store' => env('SESSION_STORE'),
    'lottery' => [2, 100], // 2% probabilidad de limpieza
    'cookie' => env('SESSION_COOKIE', 'alfoli_session'),
    'path' => '/',
    'domain' => env('SESSION_DOMAIN'),
    'secure' => env('SESSION_SECURE_COOKIE', true),
    'http_only' => true,
    'same_site' => 'strict',
    'partitioned' => false,
];
```

#### Middleware de Sesión Personalizado
```php
class SessionSecurityMiddleware
{
    public function handle($request, Closure $next)
    {
        // Verificar IP del usuario (opcional, puede causar problemas con proxies)
        if (config('app.check_session_ip', false)) {
            $sessionIp = session('user_ip');
            $currentIp = $request->ip();
            
            if ($sessionIp && $sessionIp !== $currentIp) {
                Log::warning('IP change detected', [
                    'user_id' => auth()->id(),
                    'session_ip' => $sessionIp,
                    'current_ip' => $currentIp,
                ]);
                
                auth()->logout();
                session()->invalidate();
                
                return redirect()->route('login')
                    ->withErrors(['security' => 'Sesión invalidada por cambio de IP.']);
            }
        }
        
        // Regenerar ID de sesión periódicamente
        if (!session('last_regeneration') || 
            time() - session('last_regeneration') > 1800) { // 30 minutos
            session()->regenerate();
            session(['last_regeneration' => time()]);
        }
        
        return $next($request);
    }
}
```

### 5. Auditoría y Logging

#### Sistema de Auditoría
```php
// app/Services/AuditService.php
class AuditService
{
    public static function log($action, $model = null, $changes = [])
    {
        $user = auth()->user();
        
        DB::table('audit_logs')->insert([
            'user_id' => $user ? $user->id : null,
            'username' => $user ? $user->nombre_usuario : 'system',
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'changes' => json_encode($changes),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}

// Uso en controladores
class UserController extends Controller
{
    public function store(Request $request)
    {
        $user = User::create($request->validated());
        
        AuditService::log('user_created', $user, [
            'nombre_usuario' => $user->nombre_usuario,
            'rol' => $user->rol,
        ]);
        
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente.');
    }
    
    public function update(Request $request, User $user)
    {
        $originalData = $user->toArray();
        $user->update($request->validated());
        
        AuditService::log('user_updated', $user, [
            'before' => $originalData,
            'after' => $user->fresh()->toArray(),
        ]);
        
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }
}
```

#### Configuración de Logs de Seguridad
```php
// config/logging.php
'channels' => [
    'security' => [
        'driver' => 'daily',
        'path' => storage_path('logs/security.log'),
        'level' => 'info',
        'days' => 90,
        'permission' => 0644,
    ],
    
    'audit' => [
        'driver' => 'daily',
        'path' => storage_path('logs/audit.log'),
        'level' => 'info',
        'days' => 365, // Retener por 1 año
        'permission' => 0644,
    ],
],
```

### 6. Protección de Datos Sensibles

#### Encriptación de Datos
```php
// Model con campos encriptados
class User extends Authenticatable
{
    protected $casts = [
        'email' => 'encrypted',
        'telefono' => 'encrypted',
    ];
    
    // Accessor para datos encriptados
    public function getEmailAttribute($value)
    {
        return decrypt($value);
    }
    
    // Mutator para encriptar al guardar
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = encrypt($value);
    }
}
```

#### Configuración de Encriptación
```php
// config/app.php
'cipher' => 'AES-256-CBC',

// Generar clave fuerte
php artisan key:generate --force
```

### 7. Validación de Archivos

#### Subida Segura de Archivos
```php
class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                'max:2048', // 2MB máximo
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
                function ($attribute, $value, $fail) {
                    // Verificar tipo MIME real
                    $realMime = mime_content_type($value->getPathname());
                    $allowedMimes = [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'image/jpeg',
                        'image/png',
                    ];
                    
                    if (!in_array($realMime, $allowedMimes)) {
                        $fail('Tipo de archivo no permitido.');
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $file = $request->file('file');
        
        // Generar nombre único y seguro
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Guardar en directorio seguro
        $path = $file->storeAs('uploads', $filename, 'private');
        
        AuditService::log('file_uploaded', null, [
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $filename,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);
        
        return back()->with('success', 'Archivo subido exitosamente.');
    }
}
```

## 🚨 Detección y Respuesta a Incidentes

### Sistema de Alertas de Seguridad
```php
// app/Services/SecurityAlertService.php
class SecurityAlertService
{
    public static function detectSuspiciousActivity($user, $activity)
    {
        $suspiciousPatterns = [
            'multiple_failed_logins' => 5,
            'rapid_requests' => 100, // requests per minute
            'unusual_hours' => [22, 6], // 10 PM - 6 AM
            'role_escalation_attempt' => true,
        ];
        
        switch ($activity['type']) {
            case 'failed_login':
                self::checkFailedLogins($user, $activity);
                break;
                
            case 'rapid_requests':
                self::checkRapidRequests($user, $activity);
                break;
                
            case 'role_access_attempt':
                self::checkRoleEscalation($user, $activity);
                break;
        }
    }
    
    private static function checkFailedLogins($user, $activity)
    {
        $recentFailures = DB::table('audit_logs')
            ->where('username', $user)
            ->where('action', 'failed_login')
            ->where('created_at', '>', now()->subMinutes(15))
            ->count();
            
        if ($recentFailures >= 5) {
            self::triggerSecurityAlert('multiple_failed_logins', [
                'username' => $user,
                'failures' => $recentFailures,
                'ip' => request()->ip(),
            ]);
            
            // Bloquear IP temporalmente
            Cache::put("blocked_ip_" . request()->ip(), true, now()->addHours(1));
        }
    }
    
    private static function triggerSecurityAlert($type, $data)
    {
        Log::channel('security')->critical("Security Alert: $type", $data);
        
        // Enviar notificación inmediata a administradores
        $admins = User::where('rol', 'admin')->get();
        
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new SecurityAlertMail($type, $data));
        }
    }
}
```

### Middleware de Detección
```php
class SecurityDetectionMiddleware
{
    public function handle($request, Closure $next)
    {
        // Verificar IP bloqueada
        if (Cache::has("blocked_ip_" . $request->ip())) {
            Log::warning('Blocked IP attempted access', [
                'ip' => $request->ip(),
                'route' => $request->route()->getName(),
            ]);
            
            abort(403, 'Acceso bloqueado temporalmente');
        }
        
        // Detectar patrones sospechosos en parámetros
        $this->detectSqlInjectionAttempts($request);
        $this->detectXssAttempts($request);
        
        $response = $next($request);
        
        // Analizar respuesta para información sensible
        $this->checkResponseForSensitiveData($response);
        
        return $response;
    }
    
    private function detectSqlInjectionAttempts($request)
    {
        $sqlPatterns = [
            '/(\bUNION\b.*\bSELECT\b)/i',
            '/(\bSELECT\b.*\bFROM\b.*\bWHERE\b)/i',
            '/(\bINSERT\b.*\bINTO\b)/i',
            '/(\bDELETE\b.*\bFROM\b)/i',
            '/(\bDROP\b.*\bTABLE\b)/i',
            '/(\'.*OR.*\'.*=.*\')/i',
        ];
        
        foreach ($request->all() as $key => $value) {
            if (is_string($value)) {
                foreach ($sqlPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        Log::channel('security')->critical('SQL Injection attempt detected', [
                            'parameter' => $key,
                            'value' => $value,
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                        ]);
                        
                        abort(403, 'Solicitud bloqueada por seguridad');
                    }
                }
            }
        }
    }
    
    private function detectXssAttempts($request)
    {
        $xssPatterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
        ];
        
        foreach ($request->all() as $key => $value) {
            if (is_string($value)) {
                foreach ($xssPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        Log::channel('security')->critical('XSS attempt detected', [
                            'parameter' => $key,
                            'value' => $value,
                            'ip' => $request->ip(),
                        ]);
                        
                        abort(403, 'Solicitud bloqueada por seguridad');
                    }
                }
            }
        }
    }
}
```

## 🔐 Gestión de Contraseñas

### Política de Contraseñas
```php
// app/Rules/StrongPassword.php
class StrongPassword implements Rule
{
    public function passes($attribute, $value)
    {
        // Mínimo 8 caracteres
        if (strlen($value) < 8) {
            return false;
        }
        
        // Al menos una mayúscula
        if (!preg_match('/[A-Z]/', $value)) {
            return false;
        }
        
        // Al menos una minúscula
        if (!preg_match('/[a-z]/', $value)) {
            return false;
        }
        
        // Al menos un número
        if (!preg_match('/[0-9]/', $value)) {
            return false;
        }
        
        // Al menos un carácter especial
        if (!preg_match('/[^A-Za-z0-9]/', $value)) {
            return false;
        }
        
        // No debe contener el nombre de usuario
        $username = request()->input('username') ?: request()->input('nombre_usuario');
        if ($username && stripos($value, $username) !== false) {
            return false;
        }
        
        return true;
    }
    
    public function message()
    {
        return 'La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales.';
    }
}
```

### Hash de Contraseñas
```php
// Configuración de hashing
// config/hashing.php
return [
    'driver' => 'bcrypt',
    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 12), // Aumentar para mayor seguridad
    ],
];

// Uso en controladores
class UserController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', new StrongPassword()],
        ]);
        
        $user = User::create([
            'nombre_usuario' => $request->nombre_usuario,
            'clave_hash' => Hash::make($request->password),
            'cambiar_password' => true, // Forzar cambio en primer login
            // ... otros campos
        ]);
        
        return redirect()->route('usuarios.index');
    }
}
```

## 🔍 Monitoreo de Seguridad

### Métricas de Seguridad
```php
// app/Console/Commands/SecurityReport.php
class SecurityReport extends Command
{
    protected $signature = 'security:report {--period=daily}';
    
    public function handle()
    {
        $period = $this->option('period');
        $since = match($period) {
            'hourly' => now()->subHour(),
            'daily' => now()->subDay(),
            'weekly' => now()->subWeek(),
            'monthly' => now()->subMonth(),
        };
        
        $metrics = [
            'failed_logins' => $this->getFailedLogins($since),
            'blocked_ips' => $this->getBlockedIPs($since),
            'security_alerts' => $this->getSecurityAlerts($since),
            'suspicious_activities' => $this->getSuspiciousActivities($since),
        ];
        
        $this->info("📊 Reporte de Seguridad ($period)");
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Intentos de login fallidos', $metrics['failed_logins']],
                ['IPs bloqueadas', $metrics['blocked_ips']],
                ['Alertas de seguridad', $metrics['security_alerts']],
                ['Actividades sospechosas', $metrics['suspicious_activities']],
            ]
        );
        
        // Enviar reporte por email si hay actividad sospechosa
        if ($metrics['security_alerts'] > 0 || $metrics['suspicious_activities'] > 0) {
            $this->sendSecurityReport($metrics);
        }
    }
}
```

### Dashboard de Seguridad
```php
// app/Http/Controllers/SecurityDashboardController.php
class SecurityDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }
    
    public function index()
    {
        $metrics = [
            'recent_logins' => $this->getRecentLogins(),
            'failed_attempts' => $this->getFailedAttempts(),
            'active_sessions' => $this->getActiveSessions(),
            'security_events' => $this->getSecurityEvents(),
        ];
        
        return view('admin.security.dashboard', compact('metrics'));
    }
    
    private function getRecentLogins()
    {
        return DB::table('audit_logs')
            ->where('action', 'login_success')
            ->where('created_at', '>', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
    }
    
    private function getFailedAttempts()
    {
        return DB::table('audit_logs')
            ->where('action', 'login_failed')
            ->where('created_at', '>', now()->subDays(7))
            ->selectRaw('ip_address, COUNT(*) as attempts')
            ->groupBy('ip_address')
            ->orderBy('attempts', 'desc')
            ->get();
    }
}
```

## 🛠️ Herramientas de Seguridad

### Fail2Ban Configuration
```ini
# /etc/fail2ban/jail.local
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[nginx-alfoli]
enabled = true
port = http,https
filter = nginx-alfoli
logpath = /var/log/nginx/access.log
maxretry = 10
bantime = 3600

[php-alfoli]
enabled = true
port = http,https
filter = php-alfoli
logpath = /var/log/alfoli/security.log
maxretry = 5
bantime = 7200
```

```ini
# /etc/fail2ban/filter.d/nginx-alfoli.conf
[Definition]
failregex = ^<HOST> -.*"(GET|POST).*" (4\d\d|5\d\d) .*$
ignoreregex =
```

### Script de Análisis de Seguridad
```bash
#!/bin/bash
# security-scan.sh

echo "🔍 Análisis de Seguridad - Sistema Alfolí"
echo "========================================"

# Verificar permisos de archivos críticos
echo "📁 Verificando permisos de archivos..."
find /var/www/html/sistema-alfoli -name "*.php" -perm /o+w -exec echo "⚠️ Archivo escribible por otros: {}" \;
find /var/www/html/sistema-alfoli -name ".env*" -perm /o+r -exec echo "⚠️ Archivo .env legible por otros: {}" \;

# Verificar configuraciones de PHP
echo -e "\n🐘 Verificando configuración PHP..."
php -i | grep -E "(expose_php|display_errors|allow_url_fopen)" | while read line; do
    if echo "$line" | grep -q "expose_php => On"; then
        echo "⚠️ expose_php está habilitado"
    fi
    if echo "$line" | grep -q "display_errors => On"; then
        echo "⚠️ display_errors está habilitado"
    fi
    if echo "$line" | grep -q "allow_url_fopen => On"; then
        echo "⚠️ allow_url_fopen está habilitado"
    fi
done

# Verificar headers de seguridad
echo -e "\n🌐 Verificando headers de seguridad..."
HEADERS=$(curl -s -I http://localhost)
if echo "$HEADERS" | grep -q "X-Frame-Options"; then
    echo "✅ X-Frame-Options configurado"
else
    echo "❌ X-Frame-Options faltante"
fi

if echo "$HEADERS" | grep -q "X-Content-Type-Options"; then
    echo "✅ X-Content-Type-Options configurado"
else
    echo "❌ X-Content-Type-Options faltante"
fi

# Verificar dependencias vulnerables
echo -e "\n📦 Verificando dependencias..."
cd /var/www/html/sistema-alfoli
composer audit --format=json > /tmp/audit.json
if [ -s /tmp/audit.json ]; then
    echo "⚠️ Vulnerabilidades encontradas en dependencias"
    cat /tmp/audit.json
else
    echo "✅ No se encontraron vulnerabilidades en dependencias"
fi

echo -e "\n✅ Análisis de seguridad completado"
```

## 📋 Checklist de Seguridad

### ✅ Implementado
- [x] Autenticación robusta con Laravel Auth
- [x] Control de acceso basado en roles (RBAC)
- [x] Protección CSRF en todos los formularios
- [x] Validación exhaustiva de entrada
- [x] Hash seguro de contraseñas (bcrypt)
- [x] Sanitización automática de salida
- [x] Prepared statements para queries
- [x] Headers de seguridad HTTP
- [x] Logs de auditoría completos
- [x] Sesiones seguras con regeneración
- [x] Protección contra XSS
- [x] Validación de tipos MIME en archivos

### 🔄 En Implementación
- [ ] Rate limiting avanzado
- [ ] Two-factor authentication (2FA)
- [ ] Encriptación de datos sensibles en BD
- [ ] Monitoreo de integridad de archivos
- [ ] Backup encriptado automático
- [ ] Análisis de vulnerabilidades automatizado

### 📅 Roadmap de Seguridad
- [ ] Implementación de WAF (Web Application Firewall)
- [ ] Integración con SIEM
- [ ] Penetration testing trimestral
- [ ] Certificación de seguridad
- [ ] Compliance con estándares internacionales

## 🚨 Plan de Respuesta a Incidentes

### Clasificación de Incidentes

#### 🔴 Crítico (P1)
- **Tiempo de Respuesta**: Inmediato (< 15 minutos)
- **Ejemplos**: Acceso no autorizado, data breach, sistema comprometido
- **Acciones**: Aislar sistema, notificar stakeholders, iniciar investigación

#### 🟡 Alto (P2)
- **Tiempo de Respuesta**: 1 hora
- **Ejemplos**: Múltiples intentos de login, vulnerabilidad detectada
- **Acciones**: Monitorear, aplicar mitigaciones, documentar

#### 🟢 Medio (P3)
- **Tiempo de Respuesta**: 4 horas
- **Ejemplos**: Configuración insegura, logs sospechosos
- **Acciones**: Revisar configuración, aplicar mejores prácticas

#### 🔵 Bajo (P4)
- **Tiempo de Respuesta**: 24 horas
- **Ejemplos**: Actualizaciones de seguridad disponibles
- **Acciones**: Planificar actualización, testing

### Procedimiento de Respuesta
1. **Detección**: Alertas automáticas o reporte manual
2. **Clasificación**: Determinar severidad y prioridad
3. **Contención**: Aislar y limitar el impacto
4. **Investigación**: Determinar causa raíz y alcance
5. **Erradicación**: Eliminar la amenaza
6. **Recuperación**: Restaurar operaciones normales
7. **Lecciones Aprendidas**: Documentar y mejorar

---

**Guía de Seguridad v1.0**  
**Última Actualización**: 2025-01-15  
**Clasificación**: Confidencial  
**Autor**: Aura Solutions Group SpA