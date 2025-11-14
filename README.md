# TicketUp – Sistema Interno de Gestión de Gastos

TicketUp es una aplicación desarrollada con **Laravel 11**, **Vue 3**, **Docker Sail** y **Spatie Permissions**, diseñada para gestionar tickets internos creados por empleados, revisados por supervisores y validados por administradores.

---

## 📦 Requisitos

Antes de comenzar, asegúrate de tener instalados:

-   **Docker**
-   **Docker Compose**
-   **Git**
-   **Node.js 18+**
-   **npm** o **yarn**

---

## 🚀 Instalación del Proyecto

Clonar el repositorio:

```bash
git clone https://github.com/tu-repo/ticketup.git
cd ticketup
```

Instalar dependencias de backend:

```bash
composer install
```

Instalar dependencias de frontend:

```bash
npm install
```

---

## 🐳 Levantar el Entorno con Docker (Laravel Sail)

Si Sail no está disponible, crea el alias:

```bash
alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
```

Levantar los contenedores:

```bash
sail up -d
```

Esto inicia:

-   PHP-FPM
-   MySQL
-   Redis (si está configurado)
-   Nginx

---

## ⚙️ Configuración del Entorno

Copiar archivo de entorno:

```bash
cp .env.example .env
```

Generar key de la aplicación:

```bash
sail artisan key:generate
```

---

## 🗄 Migraciones y Seeders

Ejecutar migraciones:

```bash
sail artisan migrate
```

Ejecutar seeders (roles, permisos, admin inicial):

```bash
sail artisan db:seed
```

---

## 🔐 Configuración de Spatie Permissions

Instalar (si es necesario):

```bash
sail composer require spatie/laravel-permission
```

Publicar:

```bash
sail artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

---

## 🖼 Habilitar almacenamiento de archivos (Imágenes de tickets)

```bash
sail artisan storage:link
```

Esto permite que las imágenes se sirvan desde:

```
/public/storage
```

Los archivos se guardarán en:

```
storage/app/public/tickets/{timestamp-único}
```

Cada ticket almacena una `uri` con la ruta.

---

## 🧩 Compilación del Frontend (Vue 3 + Vite)

Modo desarrollo:

```bash
npm run dev
```

Build de producción:

```bash
npm run build
```

---

## ▶️ Acceso a la Aplicación

```bash
http://localhost
```

o si usas un puerto diferente:

```bash
http://localhost:8080
```

---

## 🛠 Comandos Útiles

### Ver logs

```bash
sail logs -f
```

### Ejecutar comandos dentro del contenedor

```bash
sail artisan <comando>
```

### Entrar al contenedor

```bash
sail shell
```

### Reiniciar contenedores

```bash
sail down
sail up -d
```

---

## ✔ Estructura General del Proyecto

```
app/
bootstrap/
config/
database/
 ├─ migrations/
 ├─ seeders/
public/
resources/
 ├─ js/ (Vue 3)
 ├─ views/
 ├─ images/
routes/
storage/
```

---

## 🔐 Roles Soportados

### 👤 Empleado

-   Crea tickets
-   Sube imágenes
-   Responde a solicitudes de corrección

### 🧑‍🏫 Supervisor

-   Revisa tickets de los empleados asignados
-   Solicita correcciones
-   Aprueba o rechaza tickets

### 🧑‍💼 Administrador

-   Tiene visibilidad total
-   Puede modificar decisiones del supervisor
-   Gestiona usuarios, roles y categorías

---

## 📤 Flujo de Subida y Aprobación de Tickets

1. El empleado crea un ticket y sube imágenes.
2. El sistema crea un **URI único** para los archivos.
3. El supervisor revisa:
    - Puede solicitar correcciones
    - Puede aprobar
    - Puede rechazar
4. El administrador puede cambiar el estado final si es necesario.

---

## 🧪 Tests

Ejecutar tests:

```bash
sail artisan test
```

© 2025 TicketUp
