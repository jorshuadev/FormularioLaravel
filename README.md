# 📋 Prueba Técnica – Registro de Personas en Laravel

Este proyecto es una solución para la prueba técnica que consiste en desarrollar una aplicación web y una API REST con Laravel para el registro de personas. La aplicación permite validar datos, registrar información adicional como IP y zona horaria, y enviar notificaciones por email y SMS de forma condicional.

## 🛠️ Requisitos

-   PHP >= 8.1
-   Composer
-   MySQL o MariaDB
-   Node.js y npm (si se usan assets frontend)
-   Laravel 10+
-   Cuenta de Gmail con App Password (para notificaciones por email)
-   Cuenta de Twilio (para notificaciones por SMS)

## 🚀 Instalación

1. **Clonar el repositorio**

```bash
git clone https://github.com/jorshuadev/FormularioLaravel.git
cd FormularioLaravel
```

2. **Instalar dependencias**

```bash
composer install
```

3. **Instalar Twilio SDK para SMS**

```bash
composer require twilio/sdk
```

> Si usas assets (como Vue, React o Bootstrap):

```bash
npm install && npm run dev
```

4. **Configurar variables de entorno**

```bash
cp .env.example .env
```

Abre `.env` y configura tus datos:

```env
# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=formulario
DB_USERNAME=root
DB_PASSWORD=

# URL de la aplicación
APP_URL=http://persona.test

# Configuración de email (Gmail)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD="tu-app-password"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME="Formulario"

# Configuración de SMS (Twilio)
TWILIO_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_TOKEN=tu_auth_token_aqui
TWILIO_FROM=+1234567890

# Token para API
TOKEN_API=AMAXONIA_TOKEN

# Configuración de colas
QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
```

5. **Generar la clave de la aplicación**

```bash
php artisan key:generate
```

---

## 📧 Configuración de Email

### Configurar Gmail App Password

1. **Activar verificación en 2 pasos** en tu cuenta de Google
2. **Generar App Password**:
    - Ve a [Configuración de Google](https://myaccount.google.com/security)
    - Busca "Contraseñas de aplicaciones"
    - Genera una nueva contraseña para "Correo"
3. **Usar la App Password** en `MAIL_PASSWORD` (no tu contraseña normal)

### Variables de Email Requeridas

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD="app-password-de-16-caracteres"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME="Tu Aplicación"
```

---

## 📱 Configuración de SMS con Twilio

### Crear Cuenta en Twilio

1. **Regístrate** en [Twilio](https://www.twilio.com/)
2. **Completa la verificación** de tu número de teléfono
3. **Obtén $15 USD** de crédito gratis para pruebas

### Obtener Credenciales de Twilio

1. **Ve al Dashboard** de Twilio
2. **Copia estas credenciales**:
    - **Account SID**: `ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`
    - **Auth Token**: Haz clic en "Show" para verlo
3. **Compra un número de teléfono**:
    - Ve a Console → Phone Numbers → Manage → Buy a number
    - Busca un número con capacidades SMS
    - Compra el número (gratis con cuenta trial)

### Variables de SMS Requeridas

```env
# Twilio SMS Configuration
TWILIO_SID=ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TWILIO_TOKEN=tu_auth_token_real_aqui
TWILIO_FROM=+1234567890  # Número comprado en Twilio
```

### Configurar services.php

Agrega la configuración de Twilio en `config/services.php`:

```php
'twilio' => [
    'sid' => env('TWILIO_SID'),
    'token' => env('TWILIO_TOKEN'),
    'from' => env('TWILIO_FROM'),
],
```

### Verificar Números de Destino (Solo para Trial)

Si usas cuenta trial de Twilio:

1. **Ve a**: Console → Phone Numbers → Manage → Verified Caller IDs
2. **Agrega tu número** de teléfono panameño (+507XXXXXXXX)
3. **Verifica** con el código que te envíen por SMS

### Costos de Twilio

-   **Cuenta Trial**: $15 USD gratis
-   **SMS a Panamá**: ~$0.075 USD por SMS
-   **Número de teléfono**: ~$1 USD/mes
-   **Capacidad con crédito gratis**: ~200 SMS

### Limitaciones de Cuenta Trial

-   Solo puedes enviar SMS a **números verificados**
-   Los SMS tendrán el prefijo: "Sent from your Twilio trial account - "
-   Para enviar a cualquier número, necesitarás actualizar a cuenta pagada

---

## 🗃️ Migraciones y Configuración de Base de Datos

```bash
# Crear las tablas
php artisan migrate

# Crear tabla de colas (para notificaciones)
php artisan queue:table
php artisan migrate

# Crear tabla de sesiones
php artisan session:table
php artisan migrate
```

---

## ⚙️ Ejecutar la Aplicación

```bash
php artisan serve
```

La app estará disponible en: [http://persona.test](http://persona.test) o [http://127.0.0.1:8000](http://127.0.0.1:8000)

### Rutas Disponibles

-   **Formulario Web**: `/personas/crear`
-   **Ver Registros**: `/personas/ver`
-   **API Endpoint**: `/api/personas`

---

## ⏳ Ejecutar Colas (Para Notificaciones Asincrónicas)

```bash
php artisan queue:work
```

> **Nota**: Las colas son opcionales. Los emails y SMS se pueden enviar de forma síncrona sin configurar colas.

---

## 📬 API REST

### Endpoint

```
POST /api/personas
```

### Headers

```
Authorization: AMAXONIA_TOKEN
Content-Type: application/json
Accept: application/json
```

### Cuerpo JSON de ejemplo

```json
{
    "nombre_completo": "Juan Pérez",
    "tipo_documento": "cedula",
    "nro_documento": "123456789",
    "correo_electronico": "juan@example.com",
    "telefono": "61234567",
    "timezone": "America/Panama",
    "notificar_por_correo": true,
    "notificar_por_sms": true
}
```

### Respuesta exitosa

```json
{
    "success": true,
    "data": {
        "id": 1,
        "nombre": "Juan",
        "apellido": "Pérez",
        "tipo_documento": "cedula",
        "nro_documento": "123456789",
        "correo_electronico": "juan@example.com",
        "telefono": "61234567",
        "notificacion_via_correo": true,
        "notificacion_via_sms": true,
        "ip": "127.0.0.1",
        "timezone": "America/Panama",
        "registro_via": "mobile",
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z"
    },
    "message": "Persona registrada exitosamente. Email de confirmación enviado. SMS de confirmación enviado."
}
```

---

## 📝 Formulario Web

### Funcionalidades

-   **Formulario de registro** con validación en tiempo real
-   **Detección automática** de zona horaria del usuario
-   **Validación de teléfono** (debe empezar con 6 y tener 8 dígitos)
-   **Validación de email** con formato correcto
-   **Checkboxes opcionales** para notificaciones:
    -   ✅ **Notificar por correo**: Envía email de confirmación
    -   ✅ **Notificar por SMS**: Envía SMS de confirmación

### Campos del Formulario

-   **Nombre Completo**: Se separa automáticamente en nombre y apellido
-   **Tipo de Documento**: Cédula, Pasaporte, Otros
-   **Número de Documento**: Validación automática (remueve guiones)
-   **Correo Electrónico**: Validación de formato
-   **Teléfono**: Debe empezar con 6 y tener 8 dígitos
-   **Notificaciones**: Checkboxes opcionales

---

## ✨ Funcionalidades Destacadas

### 🔧 Procesamiento de Datos

-   **Separación automática** de nombres y apellidos
-   **Captura de IP** del usuario automáticamente
-   **Detección de zona horaria** del navegador
-   **Validación robusta** con mensajes de error personalizados

### 📧 Sistema de Notificaciones

-   **Email condicional**: Solo se envía si el usuario marca la casilla
-   **SMS condicional**: Solo se envía si el usuario marca la casilla
-   **Plantilla HTML** profesional para emails
-   **Formateo automático** de números de teléfono para SMS
-   **Manejo de errores** sin interrumpir el registro
-   **Soporte para colas** (opcional)

### 🛡️ Seguridad y Validación

-   **API protegida** con token de autorización
-   **Validación de datos** tanto en frontend como backend
-   **Sanitización** de números de documento
-   **Manejo seguro** de errores

### 🎨 Interfaz de Usuario

-   **Diseño responsivo** con CSS personalizado
-   **Validación en tiempo real** con JavaScript
-   **Mensajes de éxito/error** informativos
-   **Experiencia de usuario** fluida

---

## 🧪 Pruebas

### Probar el Formulario Web

1. Ve a `/personas/crear`
2. Llena todos los campos
3. Marca/desmarca "Notificar por correo" y "Notificar por SMS"
4. Envía el formulario
5. Verifica el email y SMS si marcaste las opciones

### Probar la API

```bash
curl -X POST http://persona.test/api/personas \\
  -H "Authorization: AMAXONIA_TOKEN" \\
  -H "Content-Type: application/json" \\
  -d '{
    "nombre_completo": "María González",
    "tipo_documento": "cedula",
    "nro_documento": "987654321",
    "correo_electronico": "maria@example.com",
    "telefono": "65555555",
    "timezone": "America/Panama",
    "notificar_por_correo": true,
    "notificar_por_sms": true
  }'
```

### Probar SMS con Tinker

```bash
php artisan tinker
```

```php
$sms = new App\\Services\\SmsService();
$resultado = $sms->sendSms('61234567', 'Mensaje de prueba desde Laravel');
dd($resultado);
```

### Verificar Logs

```bash
# Ver logs de la aplicación
tail -f storage/logs/laravel.log

# Limpiar caché si hay problemas
php artisan config:clear
php artisan cache:clear
```

---

## 🔧 Solución de Problemas

### Email no se envía

1. **Verificar configuración** en `.env`
2. **Confirmar App Password** de Gmail
3. **Revisar carpeta SPAM** del destinatario
4. **Verificar logs**: `tail -f storage/logs/laravel.log`

### SMS no se envía

1. **Verificar credenciales** de Twilio en `.env`
2. **Confirmar que el número FROM** sea un número de Twilio válido
3. **Verificar que el número de destino** esté verificado (solo para trial)
4. **Revisar saldo** de la cuenta de Twilio
5. **Verificar formato** del número: +507XXXXXXXX

### Errores comunes de SMS

-   **Error 21211**: Número de destino no verificado (solo trial)
-   **Error 20003**: Credenciales de autenticación incorrectas
-   **Error 21614**: Número de destino no es válido para móvil
-   **Error 21408**: Permisos insuficientes para el número FROM

### Error de base de datos

1. **Verificar conexión** a MySQL
2. **Ejecutar migraciones**: `php artisan migrate`
3. **Verificar permisos** de la base de datos

### Problemas con colas

1. **Verificar configuración**: `QUEUE_CONNECTION=database`
2. **Crear tabla de colas**: `php artisan queue:table && php artisan migrate`
3. **Ejecutar worker**: `php artisan queue:work`

---

## 📊 Estructura de la Base de Datos

### Tabla: `personas`

```sql
- id (bigint, primary key)
- nombre (varchar 255)
- apellido (varchar 255)
- tipo_documento (varchar 50)
- nro_documento (varchar 100)
- correo_electronico (varchar 255)
- telefono (varchar 50)
- ip (varchar 45)
- timezone (varchar 100)
- registro_via (enum: 'web', 'mobile')
- notificacion_via_correo (boolean)
- notificacion_via_sms (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

---

## 🏗️ Arquitectura del Proyecto

### Servicios

-   **SmsService**: Maneja el envío de SMS con Twilio
-   **FormularioSubmitted**: Mailable para emails de confirmación

### Controladores

-   **PersonaController**: Maneja el formulario web
-   **Api\\PersonaController**: Maneja las peticiones de la API

### Modelos

-   **Persona**: Modelo principal con validaciones y relaciones

### Middleware

-   **ValidateApiToken**: Valida el token de autorización para la API

---

## 🚀 Funcionalidades Implementadas

### ✅ Completamente Funcional

-   ✅ **Formulario web** con validación
-   ✅ **API REST** con autenticación
-   ✅ **Email real** (Gmail configurado)
-   ✅ **SMS real** (Twilio configurado)
-   ✅ **Notificaciones condicionales** (solo si se marcan las casillas)
-   ✅ **Base de datos** funcionando
-   ✅ **Validaciones robustas**
-   ✅ **Manejo de errores**

### 📊 Funcionalidades Avanzadas

-   ✅ **Separación automática** de nombres y apellidos
-   ✅ **Captura de IP** y zona horaria
-   ✅ **Formateo automático** de números de teléfono
-   ✅ **Sanitización** de documentos
-   ✅ **Respuestas estructuradas** en JSON
-   ✅ **Modo simulación** para desarrollo sin credenciales

---

## 💰 Costos y Consideraciones

### Twilio

-   **Cuenta Trial**: $15 USD gratis
-   **SMS a Panamá**: ~$0.075 USD por SMS
-   **Número de teléfono**: ~$1 USD/mes
-   **Capacidad estimada**: ~200 SMS con crédito gratis

### Gmail

-   **Gratis** hasta 500 emails por día
-   **Sin costo adicional** para App Passwords

---

## 📄 Licencia

Este proyecto es de uso libre únicamente con fines evaluativos.

---

## 👨‍💻 Desarrollado por

**Jorshua Jiménez**  
GitHub: [@jorshuadev](https://github.com/jorshuadev)

---

## 🆘 Soporte

Si encuentras algún problema:

1. **Revisa la documentación** de configuración
2. **Verifica los logs** de Laravel
3. **Consulta la documentación** de [Twilio](https://www.twilio.com/docs)
4. **Abre un issue** en el repositorio de GitHub

```

## 🎯 Resumen de Cambios Agregados

### ✅ **Nuevas Secciones**:
- 📱 **Configuración completa de SMS con Twilio**
- 💰 **Información de costos y limitaciones**
- 🧪 **Pruebas específicas para SMS**
- 🔧 **Solución de problemas de SMS**
- 🏗️ **Arquitectura del proyecto**

### ✅ **Información Detallada**:
- **Proceso completo** de registro en Twilio
- **Configuración paso a paso** de credenciales
- **Ejemplos de uso** con Tinker
- **Errores comunes** y sus soluciones
- **Costos estimados** de operación

### ✅ **Funcionalidades Actualizadas**:
- **Notificaciones duales** (Email + SMS)
- **Modo simulación** para desarrollo
- **Validación de números** panameños
- **Manejo robusto de errores**

¡El README ahora está completo con toda la información necesaria para implementar tanto email como SMS!
```
