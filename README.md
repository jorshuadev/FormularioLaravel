# 📋 Prueba Técnica – Registro de Personas en Laravel

Este proyecto es una solución para la prueba técnica que consiste en desarrollar una aplicación web y una API REST con Laravel para el registro de personas. La aplicación permite validar datos, registrar información adicional como IP y zona horaria, y enviar notificaciones por email de forma condicional.

## 🛠️ Requisitos

-   PHP >= 8.1
-   Composer
-   MySQL o MariaDB
-   Node.js y npm (si se usan assets frontend)
-   Laravel 10+
-   Cuenta de Gmail con App Password (para notificaciones por email)

## 🚀 Instalación

1. **Clonar el repositorio**

```shellscript
git clone https://github.com/jorshuadev/FormularioLaravel.git
cd FormularioLaravel
```

2. **Instalar dependencias**

```shellscript
composer install
```

> Si usas assets (como Vue, React o Bootstrap):

```shellscript
npm install && npm run dev
```

3. **Configurar variables de entorno**

```shellscript
cp .env.example .env
```

Abre `.env` y configura tus datos:

```plaintext
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

# Token para API
TOKEN_API=AMAXONIA_TOKEN

# Configuración de colas
QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
```

4. **Generar la clave de la aplicación**

```shellscript
php artisan key:generate
```

---

## 📧 Configuración de Email

### Configurar Gmail App Password

1. **Activar verificación en 2 pasos** en tu cuenta de Google
2. **Generar App Password**:

3. Ve a [Configuración de Google](https://myaccount.google.com/security)
4. Busca "Contraseñas de aplicaciones"
5. Genera una nueva contraseña para "Correo"

6. **Usar la App Password** en `MAIL_PASSWORD` (no tu contraseña normal)

### Variables de Email Requeridas

```plaintext
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

## 🗃️ Migraciones y Configuración de Base de Datos

```shellscript
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

```shellscript
php artisan serve
```

La app estará disponible en: [http://persona.test](http://persona.test) o [http://127.0.0.1:8000](http://127.0.0.1:8000)

### Rutas Disponibles

-   **Formulario Web**: `/personas/crear`
-   **Ver Registros**: `/personas/ver`
-   **API Endpoint**: `/api/personas`

---

## ⏳ Ejecutar Colas (Para Notificaciones Asincrónicas)

```shellscript
php artisan queue:work
```

> **Nota**: Las colas son opcionales. Los emails se pueden enviar de forma síncrona sin configurar colas.

---

## 📬 API REST

### Endpoint

```plaintext
POST /api/personas
```

### Headers

```plaintext
Authorization: AMAXONIA_TOKEN
Content-Type: application/json
Accept: application/json
```

### Cuerpo JSON de ejemplo

```json
{
    "nombre": "Juan Pérez",
    "tipo_documento": "cedula",
    "numero_documento": "123456789",
    "correo": "juan@example.com",
    "telefono": "61234567",
    "timezone": "America/Panama",
    "notificar_correo": true,
    "notificar_sms": false
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
        "notificacion_via_sms": false,
        "ip": "127.0.0.1",
        "timezone": "America/Panama",
        "registro_via": "api",
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z"
    },
    "message": "Persona registrada exitosamente"
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
-   ✅ **Notificar por SMS**: Guarda preferencia (funcionalidad futura)

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
-   **Plantilla HTML** profesional para emails
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
3. Marca/desmarca "Notificar por correo"
4. Envía el formulario
5. Verifica el email si marcaste la opción

### Probar la API

```shellscript
curl -X POST http://persona.test/api/personas \
  -H "Authorization: AMAXONIA_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "María González",
    "tipo_documento": "cedula",
    "numero_documento": "987654321",
    "correo": "maria@example.com",
    "telefono": "65555555",
    "timezone": "America/Panama",
    "notificar_correo": true
  }'
```

### Verificar Logs

```shellscript
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
- registro_via (enum: 'web', 'api')
- notificacion_via_correo (boolean)
- notificacion_via_sms (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

---

## 📄 Licencia

Este proyecto es de uso libre únicamente con fines evaluativos.

---

## 👨‍💻 Desarrollado por

**Jorshua Jiménez**GitHub: [@jorshuadev](https://github.com/jorshuadev)
