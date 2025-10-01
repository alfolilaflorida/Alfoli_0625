# 📖 Manual de Usuario - Sistema Alfolí

## 🎯 Introducción

Bienvenido al **Sistema de Gestión de Alfolí** de la Iglesia Cristiana Internacional Familia de Dios, La Florida. Este manual te guiará paso a paso en el uso de todas las funcionalidades del sistema.

## 👥 Roles de Usuario

### 🔴 Administrador
- **Acceso completo** a todas las funcionalidades
- **Gestión de usuarios** y configuración del sistema
- **Reportes avanzados** y configuración de alertas

### 🟡 Moderador/Editor
- **Gestión de alfolí** (registrar aportes)
- **Gestión de artículos** y hermanos
- **Control de productos** vencidos

### 🟢 Consultas/Visualizador
- **Solo lectura** de dashboards
- **Exportación** de reportes
- **Visualización** de indicadores

## 🚪 Acceso al Sistema

### Iniciar Sesión
1. Abrir navegador web e ir a: `https://alfoli.laflorida-icifd.com`
2. Ingresar **nombre de usuario** y **contraseña**
3. Hacer clic en **"Ingresar"**

![Login Screen](docs/images/login-screen.png)

### Primer Acceso
Si es tu primer acceso o el administrador ha reseteado tu contraseña:
1. El sistema te redirigirá automáticamente a **"Cambiar Contraseña"**
2. Ingresar nueva contraseña (mínimo 8 caracteres)
3. Confirmar la nueva contraseña
4. Hacer clic en **"Actualizar Contraseña"**

### Cerrar Sesión
- Hacer clic en tu nombre (esquina superior derecha)
- Seleccionar **"Cerrar Sesión"**

## 🏠 Dashboard Principal

### Vista General
El dashboard muestra tres secciones principales:

#### 📊 Indicadores de Cumplimiento
- **Verde**: Hermanos que han cumplido con sus aportes
- **Rojo**: Hermanos que NO han cumplido
- **Información mostrada**: Hermano, mes, artículo, cantidad requerida, cantidad aportada

#### ⚠️ Productos Próximos a Caducar
- **Amarillo**: Productos que vencen en menos de 60 días
- **Información mostrada**: Fecha de registro, fecha de caducidad, descripción, cantidad

#### 📈 Comparativa por Artículo
- **Análisis mensual**: Total esperado vs. total aportado por artículo
- **Diferencias**: Identifica faltantes o excesos

### Exportar Reportes
1. Hacer clic en **"Opciones"**
2. Seleccionar formato:
   - **📤 Exportar a Excel**: Descarga archivo .xlsx
   - **📄 Exportar a PDF**: Descarga archivo .pdf
   - **🖨️ Imprimir**: Abre diálogo de impresión

## 📝 Gestión de Alfolí

### Registrar Nuevo Aporte

#### Acceso
- **Rol requerido**: Administrador o Moderador
- **Navegación**: Menú Principal → "Ingreso Alfolí" → "Agregar Alfolí"

#### Pasos para Registrar
1. **Seleccionar Hermano**
   - Elegir de la lista desplegable
   - Solo aparecen hermanos activos

2. **Seleccionar Artículo**
   - Solo aparecen artículos del mes actual
   - Si no hay artículos, primero debe crear uno

3. **Ingresar Cantidad**
   - Número entre 1 y 9
   - Representa las unidades aportadas

4. **Fecha de Caducidad**
   - Usar el selector de fecha
   - **Importante**: Debe ser al menos 60 días posterior a hoy
   - El sistema no permitirá fechas menores

5. **Guardar**
   - **"Guardar"**: Guarda y regresa a la lista
   - **"Guardar y Agregar Otro"**: Guarda y limpia el formulario para otro registro

![Formulario Alfolí](docs/images/form-alfoli.png)

### Ver Registros de Alfolí

#### Filtros Disponibles
- **🧾 Mostrar Todos**: Todos los registros
- **⚠️ Pronto a Vencer**: Solo productos que vencen en menos de 60 días
- **🔍 Búsqueda**: Buscar por descripción, hermano o código

#### Información Mostrada
- **Cantidad**: Unidades aportadas
- **Fecha Registro**: Cuándo se registró el aporte
- **Mes**: Mes del aporte
- **F. Caducidad**: Fecha de vencimiento del producto
- **Descripción**: Descripción del artículo
- **Nombre del Hermano**: Quién hizo el aporte

#### Alertas Visuales
- **Fondo amarillo**: Productos próximos a vencer (< 60 días)
- **Contador**: Muestra cantidad de registros filtrados

## 🛍️ Gestión de Artículos

### Crear Nuevo Artículo

#### Acceso
- **Rol requerido**: Administrador o Moderador
- **Navegación**: Menú Principal → "Ingreso Alfolí" → "Agregar Artículo"

#### Información Requerida
1. **Código de Barra/QR**
   - Hasta 13 dígitos
   - Debe ser único en el sistema
   - **Escáner disponible**: Usar cámara para leer códigos

2. **Descripción**
   - Máximo 150 caracteres
   - Descripción clara del producto

3. **Cantidad del Mes**
   - Número entre 1-9
   - Cantidad que cada hermano debe aportar

4. **Mes del Artículo**
   - Seleccionar mes correspondiente
   - Por defecto aparece el mes actual

#### Usar Escáner de Códigos
1. Hacer clic en **"Escanear QR"**
2. Permitir acceso a la cámara
3. Enfocar el código QR o código de barras
4. El código se completará automáticamente

![Escáner QR](docs/images/qr-scanner.png)

## 👥 Gestión de Hermanos

### Agregar Nuevo Hermano

#### Acceso
- **Rol requerido**: Administrador o Moderador
- **Navegación**: Menú Principal → "Ingreso Alfolí" → "Agregar Hermano"

#### Información Requerida
1. **Nombres**: Campo obligatorio
2. **Apellidos**: Campo obligatorio
3. **Teléfono**: Campo opcional, formato: +56 9 XXXX XXXX

#### Características Especiales
- **Prefijo automático**: El sistema agrega "Hno." o "Hna." automáticamente
- **Lista actualizada**: Después de agregar, se muestra la lista completa
- **Validación**: No permite nombres duplicados

### Ver Lista de Hermanos
- **Tabla completa**: Muestra todos los hermanos registrados
- **Información**: Nombre completo y teléfono
- **Búsqueda**: Filtrar por nombre

## ♻️ Gestión de Productos Vencidos

### Acceso
- **Rol requerido**: Administrador o Moderador
- **Navegación**: Menú Principal → "Productos Caducados"

### Estados de Productos
- **🔴 Vencido**: Fecha de caducidad ya pasó
- **🟠 Pronto a Vencer**: Vence en menos de 60 días
- **🟢 OK**: Fecha de caducidad lejana

### Acciones Disponibles

#### Editar Producto
1. Hacer clic en **"✏️ Editar"** en la fila del producto
2. Modificar información en el popup:
   - Código de barra
   - Descripción
   - Cantidad
   - **Nueva fecha de caducidad** (mínimo 60 días)
3. Hacer clic en **"Guardar cambios"**

#### Eliminar Producto
1. Hacer clic en **"🗑️ Eliminar"** en la fila del producto
2. **Confirmar** la eliminación en el popup
3. **⚠️ Importante**: Esta acción no se puede deshacer

### Filtros y Búsqueda
- **Filtro por Estado**: Todos, Pronto a Vencer, Vencido
- **Búsqueda**: Por descripción o código de barra
- **Contador**: Muestra productos filtrados

![Productos Vencidos](docs/images/productos-vencidos.png)

## 👤 Gestión de Usuarios (Solo Administradores)

### Crear Nuevo Usuario

#### Acceso
- **Rol requerido**: Solo Administrador
- **Navegación**: Menú Principal → "Gestión de Usuarios" → "Agregar Nuevo Usuario"

#### Información Requerida
1. **Nombre de Usuario**: Único en el sistema
2. **Contraseña**: Mínimo 8 caracteres
3. **Confirmar Contraseña**: Debe coincidir
4. **Nombre Completo**: Nombre real del usuario
5. **Correo Electrónico**: Email válido y único
6. **Rol**: Administrador, Moderador o Consultas

#### Características
- **Contraseña temporal**: El usuario debe cambiarla en el primer acceso
- **Validación**: Email único, usuario único
- **Notificación**: Se puede enviar credenciales por email

### Gestionar Usuarios Existentes

#### Lista de Usuarios
- **Información mostrada**: Usuario, nombre, email, rol, estado
- **Filtros**: Por estado (activo/inactivo) y rol
- **Búsqueda**: Por nombre o email

#### Acciones Disponibles

##### Editar Usuario
1. Hacer clic en **"✏️ Editar"**
2. Modificar información (excepto contraseña)
3. Guardar cambios

##### Resetear Contraseña
1. Hacer clic en **"🔑 Resetear"**
2. Ingresar nueva contraseña temporal
3. El usuario deberá cambiarla en su próximo acceso

##### Activar/Desactivar Usuario
1. Hacer clic en **"🔄 Activar"** o **"⏸️ Desactivar"**
2. Confirmar la acción
3. **Usuarios inactivos** no pueden acceder al sistema

![Gestión Usuarios](docs/images/gestion-usuarios.png)

## 🔔 Sistema de Notificaciones

### Acceso
- **Rol requerido**: Todos los roles
- **Navegación**: Menú Principal → "Notificaciones"

### Funcionalidades

#### Ver Hermanos Pendientes
- **Lista visual**: Tarjetas con hermanos que no han cumplido
- **Información**: Hermano, mes, artículo pendiente
- **Estado**: Actualización en tiempo real

#### Enviar Notificaciones
1. Hacer clic en **"📧 Notificar a encargados por correo"**
2. El sistema enviará automáticamente emails a:
   - Administradores
   - Moderadores
3. **Contenido del email**:
   - Lista de hermanos pendientes
   - Tabla con detalles de incumplimientos
   - Información de contacto

#### Configurar Destinatarios
- **Automático**: Se envía a usuarios con rol Admin/Moderador
- **Requisito**: Usuarios deben tener email válido configurado

## ⚠️ Sistema de Alertas

### Acceso
- **Rol requerido**: Todos los roles
- **Navegación**: Menú Principal → "Control de alertas"

### Tipos de Alertas Disponibles

#### 📦 Alerta de Stock
- **Propósito**: Notificar niveles de inventario
- **Configuración**: Email destinatario específico
- **Frecuencia**: Configurable

#### 🔴 Productos Vencidos
- **Propósito**: Alertar sobre productos ya vencidos
- **Criterio**: Fecha de caducidad < hoy
- **Acción recomendada**: Eliminar productos

#### 🟠 Productos Por Vencer
- **Propósito**: Alertar productos próximos a vencer
- **Criterio**: Fecha de caducidad < 60 días
- **Acción recomendada**: Usar pronto o reemplazar

#### 🚨 Incumplimientos
- **Propósito**: Notificar hermanos que no han aportado
- **Criterio**: Basado en indicadores de cumplimiento
- **Acción recomendada**: Contactar hermanos

### Configurar Alertas

#### Activar Alerta Individual
1. **Marcar checkbox** "Activar esta alerta"
2. **Ingresar email** destinatario
3. **Repetir** para cada tipo de alerta deseada

#### Programar Alertas
1. **Marcar** "Activar programación"
2. **Seleccionar frecuencia**:
   - **Diaria**: Todos los días a la hora especificada
   - **Semanal**: Día específico de la semana
   - **Mensual**: Día específico del mes
3. **Configurar horario**: Hora exacta de envío
4. **Opción especial**: "Programar todas las alertas" envía todos los tipos

#### Opciones de Envío
- **📧 Enviar Alertas Ahora**: Envío inmediato
- **💾 Guardar Configuración**: Solo guarda la programación

![Configuración Alertas](docs/images/config-alertas.png)

## 📊 Exportación de Datos

### Formatos Disponibles
- **📤 Excel (.xlsx)**: Para análisis en hojas de cálculo
- **📄 PDF**: Para impresión y archivo
- **🖨️ Impresión**: Directa desde el navegador

### Datos Exportados
- **Con filtros aplicados**: Solo se exportan los datos visibles
- **Formato profesional**: Headers y formato apropiado
- **Nombre automático**: Incluye fecha de exportación

### Pasos para Exportar
1. **Aplicar filtros** deseados en la tabla
2. Hacer clic en **"Opciones"**
3. Seleccionar formato de exportación
4. **Descargar** archivo generado

## 🔍 Búsqueda y Filtros

### Búsqueda General
- **Campo de búsqueda**: Disponible en todas las tablas
- **Búsqueda en tiempo real**: Resultados mientras escribes
- **Campos incluidos**: Nombres, descripciones, códigos

### Filtros Específicos

#### Dashboard
- **Sin filtros**: Muestra todos los datos actuales

#### Gestión de Alfolí
- **Por vencimiento**: Todos / Próximos a vencer
- **Búsqueda general**: Descripción, hermano, código

#### Productos Vencidos
- **Por estado**: Todos / Pronto a vencer / Vencido
- **Búsqueda**: Descripción o código de barra

#### Gestión de Usuarios
- **Por estado**: Todos / Activo / Inactivo
- **Por rol**: Todos / Administrador / Moderador / Consultas
- **Búsqueda**: Nombre de usuario, nombre completo, email

## 🚨 Alertas y Notificaciones del Sistema

### Tipos de Alertas Visuales

#### ✅ Éxito (Verde)
- **Cuándo aparece**: Operaciones completadas exitosamente
- **Ejemplos**: "Usuario creado", "Registro guardado"
- **Acción**: Continuar con el flujo normal

#### ⚠️ Advertencia (Amarillo)
- **Cuándo aparece**: Situaciones que requieren atención
- **Ejemplos**: "Productos próximos a vencer", "Hermanos pendientes"
- **Acción**: Revisar y tomar medidas preventivas

#### ❌ Error (Rojo)
- **Cuándo aparece**: Errores en operaciones
- **Ejemplos**: "Datos inválidos", "Error de conexión"
- **Acción**: Revisar datos y reintentar

#### ℹ️ Información (Azul)
- **Cuándo aparece**: Información general
- **Ejemplos**: "Mostrando X registros", "Sistema actualizado"
- **Acción**: Solo informativo

### Confirmaciones de Seguridad
- **Eliminar registros**: Siempre pide confirmación
- **Cambios importantes**: Popup de confirmación
- **Acciones irreversibles**: Doble confirmación

## 🔧 Solución de Problemas Comunes

### Problemas de Acceso

#### "Usuario o contraseña incorrectos"
- **Verificar**: Mayúsculas/minúsculas en usuario y contraseña
- **Contactar**: Administrador para verificar estado de cuenta
- **Revisar**: Si la cuenta está activa

#### "Tu cuenta ha sido desactivada"
- **Contactar**: Administrador del sistema
- **Razón**: Cuenta temporalmente suspendida
- **Solución**: Solo el administrador puede reactivar

#### "Es necesario cambiar tu contraseña"
- **Razón**: Primera vez o reset por administrador
- **Acción**: Seguir pasos de cambio de contraseña
- **Requisitos**: Mínimo 8 caracteres, segura

### Problemas en Formularios

#### "Fecha de caducidad inválida"
- **Causa**: Fecha menor a 60 días desde hoy
- **Solución**: Seleccionar fecha al menos 2 meses posterior
- **Ayuda visual**: El calendario bloquea fechas no válidas

#### "El artículo no corresponde al mes actual"
- **Causa**: Intentar usar artículo de otro mes
- **Solución**: Crear artículo para el mes actual o esperar al mes correspondiente

#### "Código de barra ya existe"
- **Causa**: Código duplicado en el sistema
- **Solución**: Verificar si el artículo ya está registrado o usar código diferente

### Problemas de Rendimiento

#### "La página carga lentamente"
- **Causa**: Muchos registros en tabla
- **Solución**: Usar filtros para reducir datos mostrados
- **Recomendación**: Filtrar por mes o estado específico

#### "Error al exportar"
- **Causa**: Demasiados datos para exportar
- **Solución**: Aplicar filtros antes de exportar
- **Límite**: Máximo 10,000 registros por exportación

## 📱 Uso en Dispositivos Móviles

### Compatibilidad
- **Navegadores soportados**: Chrome, Safari, Firefox, Edge
- **Sistemas**: iOS 12+, Android 8+
- **Orientación**: Vertical y horizontal

### Características Móviles
- **Menú colapsible**: Navegación optimizada para pantallas pequeñas
- **Botones grandes**: Fácil interacción táctil
- **Tablas deslizables**: Scroll horizontal en tablas grandes
- **Formularios adaptados**: Campos optimizados para teclados móviles

### Recomendaciones Móviles
- **Usar WiFi**: Para mejor rendimiento
- **Pantalla completa**: Rotar a horizontal para tablas
- **Zoom**: Pellizcar para ampliar texto pequeño

## 🔐 Buenas Prácticas de Seguridad

### Para Todos los Usuarios

#### Contraseñas Seguras
- **Mínimo 8 caracteres**
- **Incluir**: Mayúsculas, minúsculas, números, símbolos
- **Evitar**: Nombres, fechas de nacimiento, palabras comunes
- **Cambiar**: Cada 90 días (recomendado)

#### Sesiones Seguras
- **Cerrar sesión**: Siempre al terminar
- **No compartir**: Credenciales con otras personas
- **Computadoras públicas**: Usar modo incógnito y cerrar sesión
- **Tiempo límite**: El sistema cierra automáticamente después de 2 horas

#### Navegación Segura
- **URL correcta**: Verificar siempre la dirección del sitio
- **HTTPS**: Verificar el candado en el navegador
- **Actualizaciones**: Mantener navegador actualizado

### Para Administradores

#### Gestión de Usuarios
- **Principio de menor privilegio**: Asignar solo permisos necesarios
- **Revisión periódica**: Verificar usuarios activos mensualmente
- **Cuentas temporales**: Desactivar cuando no se necesiten
- **Monitoreo**: Revisar logs de acceso regularmente

#### Configuración del Sistema
- **Backups regulares**: Verificar que se ejecuten correctamente
- **Actualizaciones**: Aplicar parches de seguridad
- **Monitoreo**: Revisar alertas de seguridad
- **Documentación**: Mantener registro de cambios

## 📞 Soporte y Contacto

### Niveles de Soporte

#### 🟢 Nivel 1: Consultas Generales
- **Tiempo de respuesta**: 4 horas hábiles
- **Ejemplos**: Dudas de uso, capacitación básica
- **Canal**: Email o teléfono

#### 🟡 Nivel 2: Problemas Técnicos
- **Tiempo de respuesta**: 2 horas hábiles
- **Ejemplos**: Errores en formularios, problemas de exportación
- **Canal**: Email con capturas de pantalla

#### 🔴 Nivel 3: Emergencias
- **Tiempo de respuesta**: 30 minutos
- **Ejemplos**: Sistema no disponible, pérdida de datos
- **Canal**: Teléfono directo

### Información de Contacto
- **Email Soporte**: soporte@aurasolutions.cl
- **Email Emergencias**: emergencias@aurasolutions.cl
- **Teléfono**: +56 9 XXXX XXXX
- **Horario**: Lunes a Viernes, 9:00 - 18:00 CLT

### Información para Reportar Problemas
1. **Descripción detallada** del problema
2. **Pasos para reproducir** el error
3. **Captura de pantalla** si es posible
4. **Navegador y versión** utilizada
5. **Hora exacta** del incidente
6. **Usuario afectado**

## 📚 Recursos Adicionales

### Videos Tutoriales
- **Introducción al Sistema**: 5 minutos
- **Registro de Alfolí**: 3 minutos
- **Gestión de Usuarios**: 7 minutos
- **Configuración de Alertas**: 4 minutos

### Documentación Técnica
- **Manual de Administrador**: Configuraciones avanzadas
- **Guía de Seguridad**: Mejores prácticas
- **API Documentation**: Para integraciones

### Capacitación
- **Sesión inicial**: 1 hora para nuevos usuarios
- **Capacitación avanzada**: 2 horas para administradores
- **Actualizaciones**: Notificación de nuevas funcionalidades

## 📋 Checklist de Inicio

### Para Nuevos Usuarios
- [ ] Recibir credenciales del administrador
- [ ] Primer acceso y cambio de contraseña
- [ ] Revisar este manual de usuario
- [ ] Practicar con datos de prueba
- [ ] Contactar soporte si hay dudas

### Para Administradores
- [ ] Configurar usuarios iniciales
- [ ] Configurar alertas de email
- [ ] Verificar conexión de base de datos
- [ ] Configurar backup automático
- [ ] Revisar logs de seguridad
- [ ] Capacitar a usuarios finales

## 🔄 Actualizaciones del Sistema

### Notificaciones de Actualización
- **Email automático**: A administradores cuando hay actualizaciones
- **Changelog**: Detalle de nuevas funcionalidades
- **Tiempo de inactividad**: Notificación previa de mantenimientos

### Nuevas Funcionalidades
- **Capacitación**: Sesiones para funcionalidades importantes
- **Documentación**: Actualización automática del manual
- **Soporte**: Asistencia durante período de adaptación

---

## 📞 Contacto de Emergencia

**🚨 Para emergencias del sistema (24/7)**
- **Teléfono**: +56 9 XXXX XXXX
- **Email**: emergencias@aurasolutions.cl
- **WhatsApp**: +56 9 XXXX XXXX

**📧 Para consultas generales**
- **Email**: soporte@aurasolutions.cl
- **Horario**: Lunes a Viernes, 9:00 - 18:00 CLT

---

**Manual de Usuario v1.0**  
**Última Actualización**: 2025-01-15  
**Iglesia Cristiana Internacional Familia de Dios, La Florida**  
**Desarrollado por**: Aura Solutions Group SpA