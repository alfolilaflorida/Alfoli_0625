# 📋 Sistema de Gestión de Alfolí

## 🎯 Descripción del Proyecto

El **Sistema de Gestión de Alfolí** es una aplicación web desarrollada para la **Iglesia Cristiana Internacional Familia de Dios, La Florida** que permite gestionar de manera eficiente los aportes mensuales de alimentos (alfolí) de los miembros de la congregación.

### 🏛️ Contexto Organizacional

- **Cliente**: Iglesia Cristiana Internacional Familia de Dios, La Florida
- **Desarrollador**: Aura Solutions Group SpA
- **Propósito**: Digitalizar y optimizar el proceso de gestión de aportes alimentarios
- **Usuarios**: Administradores, Moderadores y Personal de Consultas

## 🛠️ Stack Tecnológico

### Backend
- **Framework**: Laravel 10.x (PHP 8.1+)
- **Base de Datos**: MySQL 8.0
- **Autenticación**: Laravel Auth + Custom Guards
- **ORM**: Eloquent + Stored Procedures
- **Validación**: Laravel Validation
- **Email**: PHPMailer 6.9.3

### Frontend
- **CSS Framework**: Bootstrap 5.3
- **JavaScript**: Vanilla JS + jQuery 3.7
- **Iconografía**: Font Awesome 6.4
- **Alertas**: SweetAlert2 11.x
- **Exportación**: SheetJS (Excel) + jsPDF (PDF)
- **Build Tool**: Vite 4.x

### Infraestructura
- **Servidor Web**: Apache/Nginx
- **PHP**: 8.1+
- **Composer**: 2.x
- **Node.js**: 18+ (para assets)

## 🏗️ Arquitectura del Sistema

### Patrón de Diseño
- **MVC (Model-View-Controller)**: Separación clara de responsabilidades
- **Repository Pattern**: Para acceso a datos complejos
- **Service Layer**: Para lógica de negocio
- **Middleware Pattern**: Para autenticación y autorización

### Estructura de Directorios
```
sistema-alfoli/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Controladores principales
│   │   ├── Middleware/      # Middleware personalizado
│   │   └── Requests/        # Form Requests
│   ├── Models/              # Modelos Eloquent
│   ├── Services/            # Servicios de negocio
│   └── Providers/           # Service Providers
├── database/
│   ├── migrations/          # Migraciones de BD
│   └── seeders/            # Datos iniciales
├── resources/
│   ├── views/              # Plantillas Blade
│   ├── css/                # Estilos CSS
│   └── js/                 # JavaScript
├── routes/                 # Definición de rutas
└── public/                 # Assets públicos
```

## 👥 Sistema de Roles y Permisos

### Roles Definidos

#### 🔴 Administrador (`admin`)
- **Acceso Completo**: Todas las funcionalidades del sistema
- **Gestión de Usuarios**: Crear, editar, activar/desactivar usuarios
- **Configuración**: Alertas, notificaciones, parámetros del sistema
- **Reportes**: Acceso a todos los dashboards e indicadores

#### 🟡 Moderador/Editor (`editor`)
- **Gestión de Alfolí**: Registrar aportes de hermanos
- **Gestión de Productos**: Artículos y control de vencimientos
- **Gestión de Hermanos**: Agregar nuevos participantes
- **Reportes**: Dashboards operativos

#### 🟢 Consultas/Visualizador (`visualizador`)
- **Solo Lectura**: Dashboards e indicadores
- **Reportes**: Exportación de datos
- **Notificaciones**: Visualización de alertas

### Matriz de Permisos

| Funcionalidad | Admin | Editor | Visualizador |
|---------------|-------|--------|--------------|
| Dashboard | ✅ | ✅ | ✅ |
| Gestión Alfolí | ✅ | ✅ | ❌ |
| Gestión Artículos | ✅ | ✅ | ❌ |
| Gestión Hermanos | ✅ | ✅ | ❌ |
| Productos Vencidos | ✅ | ✅ | ❌ |
| Gestión Usuarios | ✅ | ❌ | ❌ |
| Configuración Alertas | ✅ | ✅ | ✅ |
| Notificaciones | ✅ | ✅ | ✅ |

## 📊 Funcionalidades Principales

### 1. 🏠 Dashboard Principal
**Objetivo**: Proporcionar una vista general del estado del alfolí

#### Indicadores Clave
- **Cumplimiento de Aportes**: Hermanos que han cumplido vs. pendientes
- **Productos Próximos a Vencer**: Alertas de caducidad (< 60 días)
- **Comparativa por Artículo**: Total esperado vs. total aportado
- **Estadísticas Mensuales**: Tendencias y patrones

#### Tecnologías Utilizadas
- **Stored Procedures**: `ObtCumpAportes()`, `ObtCompTotalesArt()`, `ObtArtProxAVencer()`
- **Charts**: Chart.js para visualizaciones
- **Exportación**: Excel/PDF con filtros aplicados

### 2. 📝 Gestión de Alfolí
**Objetivo**: Registrar los aportes mensuales de cada hermano

#### Flujo de Trabajo
1. **Selección de Hermano**: Lista desplegable con participantes activos
2. **Selección de Artículo**: Artículos del mes actual
3. **Registro de Cantidad**: Validación 1-9 unidades
4. **Fecha de Caducidad**: Mínimo 60 días posteriores
5. **Confirmación**: Opción de agregar otro registro

#### Validaciones Implementadas
- **Hermano**: Debe existir en la base de datos
- **Artículo**: Debe corresponder al mes actual
- **Cantidad**: Entero entre 1-9
- **Fecha Caducidad**: Mínimo 60 días desde hoy
- **Duplicados**: Prevención de registros duplicados

#### Tecnologías
- **Stored Procedure**: `InsAlfoli()`
- **Validación**: Laravel Form Requests
- **UI**: Formularios responsivos con Bootstrap

### 3. 🛍️ Gestión de Artículos
**Objetivo**: Administrar el catálogo de productos por mes

#### Características
- **Código de Barras**: Hasta 13 dígitos, único
- **Descripción**: Máximo 150 caracteres
- **Cantidad Mensual**: Requerimiento por hermano (1-9)
- **Mes Asignado**: Artículo específico por mes

#### Funcionalidades Avanzadas
- **Escáner QR/Código de Barras**: Integración con cámara
- **Autocompletado**: Mes actual por defecto
- **Validación Duplicados**: Prevención de códigos repetidos

#### Tecnologías
- **html5-qrcode**: Lectura de códigos QR
- **Quagga.js**: Lectura de códigos de barras
- **Flatpickr**: Selector de fechas mejorado

### 4. 👥 Gestión de Hermanos/Participantes
**Objetivo**: Administrar la base de datos de miembros

#### Información Gestionada
- **Nombres y Apellidos**: Campos obligatorios
- **Teléfono**: Campo opcional con validación
- **Prefijo Automático**: "Hno./Hna." según corresponda
- **Estado**: Activo/Inactivo

#### Stored Procedure
- **`participantes()`**: CRUD completo con parámetros
  - `LISTAR`: Obtener todos los hermanos
  - `AGREGAR`: Insertar nuevo hermano
  - `EDITAR`: Actualizar información
  - `ELIMINAR`: Soft delete

### 5. ♻️ Gestión de Productos Vencidos
**Objetivo**: Control y gestión de productos próximos a caducar

#### Categorías de Estado
- **🔴 Vencido**: Fecha de caducidad < hoy
- **🟠 Pronto a Vencer**: Fecha de caducidad < 60 días
- **🟢 OK**: Fecha de caducidad > 60 días

#### Acciones Disponibles
- **Editar**: Modificar fecha de caducidad y cantidad
- **Eliminar**: Remover producto del inventario
- **Filtrar**: Por estado y búsqueda de texto
- **Exportar**: Reportes en Excel/PDF

#### Validaciones de Seguridad
- **Fecha Mínima**: No permitir fechas < 60 días
- **Cantidad**: Solo números positivos
- **Permisos**: Solo admin/editor pueden modificar

### 6. 👤 Gestión de Usuarios
**Objetivo**: Administración completa del sistema de usuarios

#### Funcionalidades
- **CRUD Completo**: Crear, leer, actualizar usuarios
- **Gestión de Roles**: Asignación y cambio de permisos
- **Control de Estado**: Activar/desactivar cuentas
- **Reset de Contraseñas**: Forzar cambio de contraseña
- **Auditoría**: Log de todas las acciones

#### Seguridad Implementada
- **Hash Bcrypt**: Contraseñas seguras
- **Cambio Obligatorio**: Primera vez o reset
- **Validación Robusta**: Email único, contraseñas fuertes
- **Logs de Auditoría**: Registro de todas las acciones

### 7. 🔔 Sistema de Notificaciones
**Objetivo**: Comunicación automática de incumplimientos

#### Tipos de Notificaciones
- **Incumplimientos**: Hermanos que no han aportado
- **Productos Vencidos**: Alertas de caducidad
- **Stock Bajo**: Niveles insuficientes
- **Reportes Programados**: Envío automático

#### Configuración Avanzada
- **Frecuencia**: Diaria, semanal, mensual
- **Destinatarios**: Múltiples correos por tipo
- **Plantillas**: HTML responsivo
- **Programación**: Cron jobs automáticos

### 8. ⚠️ Sistema de Alertas
**Objetivo**: Configuración personalizada de alertas

#### Tipos de Alertas
- **Stock**: Niveles de inventario
- **Vencimientos**: Productos próximos a caducar
- **Incumplimientos**: Hermanos pendientes
- **Comparativas**: Diferencias esperado vs. real

#### Configuración
- **Activación Individual**: Por tipo de alerta
- **Correos Específicos**: Destinatario por alerta
- **Programación**: Horarios personalizados
- **Plantillas**: Formato HTML profesional

## 🔄 Metodología de Desarrollo

### Enfoque Utilizado
- **Desarrollo Ágil**: Iteraciones cortas y feedback continuo
- **Clean Code**: Código legible y mantenible
- **SOLID Principles**: Principios de diseño orientado a objetos
- **DRY (Don't Repeat Yourself)**: Reutilización de código
- **Security First**: Seguridad como prioridad

### Fases de Desarrollo

#### Fase 1: Análisis y Diseño (Completada)
- ✅ Análisis del sistema legacy
- ✅ Definición de requerimientos
- ✅ Diseño de arquitectura
- ✅ Modelado de base de datos
- ✅ Definición de roles y permisos

#### Fase 2: Backend Core (Completada)
- ✅ Configuración Laravel
- ✅ Modelos y relaciones
- ✅ Sistema de autenticación
- ✅ Middleware de autorización
- ✅ Controladores principales

#### Fase 3: Frontend y UX (Completada)
- ✅ Diseño responsivo con Bootstrap
- ✅ Componentes reutilizables
- ✅ Interfaz de usuario intuitiva
- ✅ Validaciones del lado cliente
- ✅ Exportación de datos

#### Fase 4: Funcionalidades Avanzadas (Completada)
- ✅ Sistema de notificaciones
- ✅ Alertas programadas
- ✅ Gestión de productos vencidos
- ✅ Dashboard con indicadores
- ✅ Auditoría y logs

#### Fase 5: Testing y Optimización (En Progreso)
- 🔄 Pruebas unitarias
- 🔄 Pruebas de integración
- 🔄 Optimización de rendimiento
- 🔄 Documentación técnica

## 📈 Hitos Detallados por Funcionalidad

### 🏠 Dashboard Principal

#### Hito 1: Indicadores Básicos
- **Duración**: 2 días
- **Entregables**:
  - Vista principal del dashboard
  - Integración con stored procedures
  - Indicadores de cumplimiento
- **Criterios de Aceptación**:
  - Mostrar hermanos que cumplen/no cumplen
  - Productos próximos a vencer
  - Comparativa por artículo

#### Hito 2: Visualizaciones Avanzadas
- **Duración**: 1 día
- **Entregables**:
  - Gráficos interactivos
  - Filtros dinámicos
  - Exportación de reportes
- **Criterios de Aceptación**:
  - Gráficos responsivos
  - Filtros en tiempo real
  - Exportación Excel/PDF funcional

### 📝 Gestión de Alfolí

#### Hito 1: CRUD Básico
- **Duración**: 2 días
- **Entregables**:
  - Formulario de registro
  - Listado con filtros
  - Validaciones backend/frontend
- **Criterios de Aceptación**:
  - Registro exitoso de aportes
  - Validaciones robustas
  - Interfaz intuitiva

#### Hito 2: Funcionalidades Avanzadas
- **Duración**: 1 día
- **Entregables**:
  - Alertas de vencimiento
  - Búsqueda avanzada
  - Exportación filtrada
- **Criterios de Aceptación**:
  - Alertas automáticas < 60 días
  - Búsqueda en tiempo real
  - Exportación con filtros aplicados

### 🛍️ Gestión de Artículos

#### Hito 1: Catálogo de Productos
- **Duración**: 1.5 días
- **Entregables**:
  - CRUD de artículos
  - Validación de códigos únicos
  - Asignación por mes
- **Criterios de Aceptación**:
  - Códigos de barra únicos
  - Validación de longitud (13 dígitos)
  - Asignación correcta por mes

#### Hito 2: Escáner de Códigos
- **Duración**: 1 día
- **Entregables**:
  - Integración con cámara
  - Lectura QR y códigos de barras
  - Autocompletado de formularios
- **Criterios de Aceptación**:
  - Lectura exitosa de códigos
  - Autocompletado automático
  - Compatibilidad móvil

### 👥 Gestión de Hermanos

#### Hito 1: Base de Datos de Miembros
- **Duración**: 1 día
- **Entregables**:
  - CRUD de hermanos
  - Validación de datos
  - Listado organizado
- **Criterios de Aceptación**:
  - Registro completo de información
  - Validación de teléfonos
  - Prefijo automático "Hno./Hna."

### ♻️ Control de Vencimientos

#### Hito 1: Detección Automática
- **Duración**: 1.5 días
- **Entregables**:
  - Algoritmo de detección
  - Categorización por estado
  - Alertas visuales
- **Criterios de Aceptación**:
  - Detección precisa de vencimientos
  - Categorización correcta
  - Alertas visibles en UI

#### Hito 2: Gestión de Productos Vencidos
- **Duración**: 1 día
- **Entregables**:
  - Edición de productos
  - Eliminación segura
  - Auditoría de cambios
- **Criterios de Aceptación**:
  - Edición exitosa con validaciones
  - Eliminación con confirmación
  - Log de todas las acciones

### 👤 Administración de Usuarios

#### Hito 1: Sistema de Usuarios
- **Duración**: 2 días
- **Entregables**:
  - CRUD completo de usuarios
  - Sistema de roles
  - Autenticación segura
- **Criterios de Aceptación**:
  - Registro seguro de usuarios
  - Asignación correcta de roles
  - Login/logout funcional

#### Hito 2: Seguridad Avanzada
- **Duración**: 1 día
- **Entregables**:
  - Cambio obligatorio de contraseñas
  - Reset de contraseñas
  - Auditoría de acciones
- **Criterios de Aceptación**:
  - Contraseñas seguras (bcrypt)
  - Cambio obligatorio en primer login
  - Log completo de acciones

### 🔔 Sistema de Notificaciones

#### Hito 1: Notificaciones Básicas
- **Duración**: 1.5 días
- **Entregables**:
  - Envío de correos
  - Plantillas HTML
  - Lista de destinatarios
- **Criterios de Aceptación**:
  - Envío exitoso de correos
  - Plantillas profesionales
  - Gestión de errores

#### Hito 2: Notificaciones Programadas
- **Duración**: 1.5 días
- **Entregables**:
  - Configuración de horarios
  - Cron jobs automáticos
  - Múltiples tipos de alerta
- **Criterios de Aceptación**:
  - Programación flexible
  - Ejecución automática
  - Configuración por usuario

### ⚠️ Sistema de Alertas

#### Hito 1: Configuración de Alertas
- **Duración**: 1 día
- **Entregables**:
  - Panel de configuración
  - Tipos de alertas
  - Destinatarios específicos
- **Criterios de Aceptación**:
  - Configuración intuitiva
  - Múltiples tipos de alerta
  - Validación de correos

#### Hito 2: Ejecución Automática
- **Duración**: 1 día
- **Entregables**:
  - Script de ejecución
  - Integración con cron
  - Logs de ejecución
- **Criterios de Aceptación**:
  - Ejecución puntual
  - Manejo de errores
  - Registro de actividad

## 🔒 Aspectos de Seguridad

### Autenticación
- **Hash de Contraseñas**: Bcrypt con salt automático
- **Sesiones Seguras**: Laravel session management
- **Tokens CSRF**: Protección automática en formularios
- **Middleware**: Verificación de autenticación en cada request

### Autorización
- **Control de Acceso**: Middleware personalizado por rol
- **Validación de Permisos**: En cada controlador
- **Rutas Protegidas**: Agrupación por nivel de acceso
- **Redirección Segura**: Según rol del usuario

### Validación de Datos
- **Input Sanitization**: Automática con Laravel
- **Validación Backend**: Form Requests personalizados
- **Validación Frontend**: JavaScript con feedback inmediato
- **Prevención XSS**: Escape automático en Blade templates

### Auditoría
- **Logs de Sistema**: Registro de acciones críticas
- **Trazabilidad**: Quién, qué, cuándo para cada acción
- **Monitoreo**: Intentos de acceso no autorizado
- **Backup**: Estrategia de respaldo de datos

## 📊 Base de Datos

### Tablas Principales

#### `usuarios`
- **Propósito**: Gestión de acceso al sistema
- **Campos Clave**: `nombre_usuario`, `clave_hash`, `rol`, `activo`
- **Relaciones**: Uno a muchos con `detalle_alfoli`

#### `hermanos`
- **Propósito**: Registro de miembros de la congregación
- **Campos Clave**: `nombres`, `apellidos`, `telefono`
- **Relaciones**: Uno a muchos con `detalle_alfoli`

#### `articulos`
- **Propósito**: Catálogo de productos por mes
- **Campos Clave**: `codigo_barra`, `descripcion`, `cantidad`, `mes_articulo`
- **Relaciones**: Uno a muchos con `detalle_alfoli`

#### `detalle_alfoli`
- **Propósito**: Registro de aportes individuales
- **Campos Clave**: `id_hermano`, `id_articulo`, `cantidad`, `fecha_caducidad`
- **Relaciones**: Muchos a uno con `hermanos` y `articulos`

#### `alertas_programadas`
- **Propósito**: Configuración de alertas automáticas
- **Campos Clave**: `tipo_alerta`, `correo`, `frecuencia`, `programada`

### Stored Procedures Utilizados

#### `ObtCumpAportes()`
- **Propósito**: Obtener indicadores de cumplimiento
- **Retorna**: Hermano, mes, artículo, cantidad, aporte, estado

#### `ObtCompTotalesArt()`
- **Propósito**: Comparativa total por artículo
- **Retorna**: Mes, artículo, esperado, aportado, diferencia

#### `ObtArtProxAVencer(dias)`
- **Propósito**: Productos próximos a vencer
- **Parámetro**: Días límite para considerar "próximo"
- **Retorna**: Productos con fecha de caducidad cercana

#### `InsAlfoli(hermano, articulo, cantidad, fecha_caducidad, usuario)`
- **Propósito**: Insertar nuevo registro de alfolí
- **Validaciones**: Integridad referencial y reglas de negocio

#### `participantes(accion, nombres, apellidos, telefono)`
- **Propósito**: CRUD completo de hermanos
- **Acciones**: LISTAR, AGREGAR, EDITAR, ELIMINAR

## 🚀 Optimizaciones Implementadas

### Performance
- **Lazy Loading**: Carga diferida de relaciones Eloquent
- **Query Optimization**: Uso de stored procedures para consultas complejas
- **Caching**: Cache de configuraciones y datos estáticos
- **Asset Optimization**: Minificación y compresión con Vite

### UX/UI
- **Responsive Design**: Adaptación a todos los dispositivos
- **Progressive Enhancement**: Funcionalidad básica sin JavaScript
- **Loading States**: Indicadores de carga en operaciones async
- **Error Handling**: Mensajes claros y accionables

### Seguridad
- **Rate Limiting**: Prevención de ataques de fuerza bruta
- **Input Validation**: Doble validación (frontend/backend)
- **SQL Injection Prevention**: Prepared statements y ORM
- **XSS Protection**: Escape automático de outputs

## 📋 Checklist de Funcionalidades

### ✅ Completadas
- [x] Sistema de autenticación y autorización
- [x] Dashboard con indicadores principales
- [x] Gestión completa de alfolí (CRUD)
- [x] Gestión de artículos con escáner
- [x] Gestión de hermanos/participantes
- [x] Control de productos vencidos
- [x] Sistema de usuarios (admin)
- [x] Notificaciones por email
- [x] Alertas programadas
- [x] Exportación Excel/PDF
- [x] Diseño responsivo
- [x] Validaciones robustas

### 🔄 En Desarrollo
- [ ] Pruebas automatizadas (PHPUnit)
- [ ] Optimización de consultas
- [ ] Cache de datos frecuentes
- [ ] API REST para móviles

### 📅 Roadmap Futuro
- [ ] Aplicación móvil nativa
- [ ] Integración con sistemas contables
- [ ] Reportes avanzados con BI
- [ ] Notificaciones push
- [ ] Integración con WhatsApp Business

## 🔧 Configuración y Despliegue

### Requisitos del Sistema
- **PHP**: 8.1 o superior
- **MySQL**: 8.0 o superior
- **Composer**: 2.x
- **Node.js**: 18+ (para assets)
- **Extensiones PHP**: PDO, OpenSSL, Mbstring, Tokenizer

### Variables de Entorno Críticas
```env
APP_NAME="Sistema Alfolí"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=laflorida-icifd.com
DB_PORT=3306
DB_DATABASE=laflorid_alfoli_ts
DB_USERNAME=laflorid_admin_alf
DB_PASSWORD=y0PlhTGy{Nfq

MAIL_MAILER=smtp
MAIL_HOST=mail.laflorida-icifd.com
MAIL_PORT=465
MAIL_USERNAME=notificaciones@laflorida-icifd.com
MAIL_PASSWORD=Adm1n!st5
MAIL_ENCRYPTION=ssl
```

### Comandos de Instalación
```bash
# Clonar repositorio
git clone [repository-url]
cd sistema-alfoli

# Instalar dependencias PHP
composer install --optimize-autoloader --no-dev

# Instalar dependencias Node.js
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build

# Configurar permisos
chmod -R 755 storage bootstrap/cache
```

### Configuración del Servidor Web

#### Apache (.htaccess)
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

#### Nginx
```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /path/to/sistema-alfoli/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 📚 Documentación Técnica

### Convenciones de Código
- **PSR-12**: Estándar de codificación PHP
- **Camel Case**: Para métodos y variables
- **Pascal Case**: Para clases
- **Snake Case**: Para nombres de base de datos
- **Kebab Case**: Para rutas y archivos CSS/JS

### Estructura de Commits
```
tipo(alcance): descripción breve

Descripción detallada del cambio realizado.

- Cambio específico 1
- Cambio específico 2

Fixes #123
```

### Testing Strategy
- **Unit Tests**: Modelos y servicios
- **Feature Tests**: Controladores y rutas
- **Browser Tests**: Flujos completos de usuario
- **API Tests**: Endpoints y respuestas

## 🎯 Métricas de Éxito

### Técnicas
- **Tiempo de Carga**: < 2 segundos
- **Disponibilidad**: 99.9% uptime
- **Seguridad**: 0 vulnerabilidades críticas
- **Performance**: < 100ms respuesta promedio

### Funcionales
- **Adopción**: 100% de usuarios migrados
- **Satisfacción**: > 4.5/5 en encuestas
- **Eficiencia**: 50% reducción en tiempo de gestión
- **Errores**: < 1% tasa de error en operaciones

### Negocio
- **ROI**: Retorno de inversión en 6 meses
- **Productividad**: 40% mejora en procesos
- **Cumplimiento**: 95% de aportes registrados
- **Transparencia**: 100% trazabilidad de operaciones

## 🤝 Equipo y Responsabilidades

### Roles del Proyecto
- **Product Owner**: Liderazgo de la iglesia
- **Tech Lead**: Aura Solutions Group
- **Backend Developer**: Desarrollo Laravel
- **Frontend Developer**: UI/UX y JavaScript
- **QA Engineer**: Testing y validación
- **DevOps**: Despliegue y mantenimiento

### Comunicación
- **Reuniones**: Semanales de seguimiento
- **Reportes**: Dashboard de progreso
- **Feedback**: Canal directo con usuarios finales
- **Documentación**: Wiki actualizada continuamente

---

## 📞 Soporte y Contacto

**Desarrollado por**: Aura Solutions Group SpA  
**Email**: soporte@aurasolutions.cl  
**Teléfono**: +56 9 XXXX XXXX  
**Sitio Web**: https://aurasolutions.cl

**Cliente**: Iglesia Cristiana Internacional Familia de Dios, La Florida  
**Contacto**: administracion@laflorida-icifd.com

---

*Este documento es un living document que se actualiza conforme evoluciona el proyecto.*