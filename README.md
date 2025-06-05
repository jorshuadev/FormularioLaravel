# 📋 Prueba Técnica – Registro de Personas en Laravel

Este proyecto es una solución para la prueba técnica que consiste en desarrollar una aplicación web y una API REST con Laravel para el registro de personas. La aplicación permite validar datos, registrar información adicional como IP y zona horaria, y enviar notificaciones de forma asincrónica.

## 🛠️ Requisitos

-   PHP >= 8.1
-   Composer
-   MySQL o MariaDB
-   Node.js y npm (si se usan assets frontend)
-   Laravel 10+

## 🚀 Instalación

1. **Clonar el repositorio**

```bash
git clone https://github.com/jorshuadev/FormularioLaravel.git
cd repositorio
```

2. **Instalar dependencias**

```bash
composer install
```

> Si usas assets (como Vue, React o Bootstrap):

```bash
npm install && npm run dev
```

3. **Configurar variables de entorno**

```bash
cp .env.example .env
```

Abre `.env` y configura tus datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=formulario
DB_USERNAME=root
DB_PASSWORD=

TOKEN_API=AMAXONIA_TOKEN
```

4. **Generar la clave de la aplicación**

```bash
php artisan key:generate
```

---

## 🗃️ Migraciones y Seeders

```bash
php artisan migrate --seed
```

---

## ⚙️ Ejecutar servidor

```bash
php artisan serve
```

La app estará disponible en: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## ⏳ Ejecutar colas (notificaciones asincrónicas)

```bash
php artisan queue:work
```

Asegúrate de tener configurado:

```env
QUEUE_CONNECTION=database
```

Y haber ejecutado antes:

```bash
php artisan queue:table
php artisan migrate
```

---

## ✅ Pruebas Unitarias (opcional)

```bash
php artisan test
```

---

## 📬 API REST

### Endpoint

```
POST /api/personas
```

### Headers

```
Authorization:AMAXONIA_TOKEN
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
    "telefono": "600000000",
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
    ...
  }
}
```

---

## 📝 Web (Formulario)

-   `GET /personas/crear`: Muestra el formulario.
-   `POST /personas`: Procesa el registro desde la web.
-   Muestra mensaje de éxito tras registrar.

---

## ✨ Funcionalidades Destacadas

-   Separación de nombres y apellidos.
-   Validación robusta con Form Requests.
-   Middleware para token de autorización.
-   Jobs encolados para envío de notificaciones.
-   API protegida y estructurada.

---

## 📄 Licencia

Este proyecto es de uso libre únicamente con fines evaluativos.

```

---

¿Deseas que ahora prepare el archivo `.env.example` o algún otro archivo como parte de la entrega?
```
