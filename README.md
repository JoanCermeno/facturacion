---

# 📌 API Endpoints – Facturación (Laravel 11)

Todos los endpoints están bajo el prefijo `/api`.
Las rutas que requieren autenticación usan **Laravel Sanctum** y deben incluir el header:

```http
Authorization: Bearer {TOKEN}
```

---

## 🔑 Autenticación

### `POST /api/auth/register`

Registrar un nuevo usuario (sin empresa al inicio).

**Body:**

```json
{
  "name": "Joan",
  "email": "joan@example.com",
  "password": "secret123",
  "password_confirmation": "secret123"
}
```

**Respuestas:**

* ✅ `201 Created`: Usuario registrado
* ❌ `422 Unprocessable Entity`: Error de validación

---

### `POST /api/auth/login`

Iniciar sesión y obtener token Sanctum.

**Body:**

```json
{
  "email": "joan@example.com",
  "password": "secret123"
}
```

**Respuestas:**

* ✅ `200 OK`: Devuelve `token` y datos del usuario
* ❌ `401 Unauthorized`: Credenciales incorrectas

---

### `POST /api/auth/logout`

Cerrar sesión y revocar el token.

🔒 **Requiere autenticación**.

---

## 👤 Perfil del Usuario

### `GET /api/user`

Obtener datos del usuario autenticado.

**Respuestas:**

```json
{
  "message": "Información del usuario autenticado",
  "user": {
    "id": 1,
    "name": "Joan",
    "email": "joan@example.com",
    "role": "admin",
    "fk_company": 1
  }
}
```

---

### `GET /api/profile`

Mostrar datos del perfil del usuario autenticado.

---

### `PUT /api/profile`

Actualizar datos generales del perfil.

**Body (ejemplo):**

```json
{
  "name": "Joan Cermeño",
  "email": "joan.c@example.com"
}
```

---

### `PUT /api/profile/password`

Cambiar la contraseña del usuario.

**Body:**

```json
{
  "current_password": "secret123",
  "new_password": "NuevoPass123",
  "new_password_confirmation": "NuevoPass123"
}
```

---

## 🏢 Empresa

### `GET /api/company`

Mostrar los datos de la empresa asociada al usuario autenticado.

---

### `PUT /api/company`

Crear o actualizar los datos de la empresa del admin autenticado.

**Body (ejemplo):**

```json
{
  "name": "Mi Empresa C.A",
  "rif": "J-12345678-9",
  "phone": "04121234567",
  "address": "Caracas, Venezuela"
}
```

---

## 👨‍💼 Vendedores

### `GET /api/sellers`

Listar vendedores de la empresa del admin autenticado.

**Respuestas (ejemplo):**

```json
[
  {
    "id": 1,
    "ci": "12345678",
    "name": "Pedro Pérez",
    "phone": "04121234567",
    "commission": 10,
    "company_id": 1
  }
]
```

---

### `POST /api/sellers`

Crear un nuevo vendedor bajo la empresa del admin autenticado.

**Body:**

```json
{
  "ci": "87654321",
  "name": "María López",
  "phone": "04124567890",
  "commission": 15
}
```

---

## 💳 Cajeros

Un **cajero** es un usuario (`users.role = cashier`) que pertenece a la empresa de un admin.

### `GET /api/cashiers`

Listar todos los cajeros de la empresa del admin autenticado.

**Respuestas (ejemplo):**

```json
[
  {
    "id": 5,
    "name": "Juan Torres",
    "email": "juan@example.com",
    "role": "cashier",
    "fk_company": 1,
    "created_at": "2025-09-14T10:23:00"
  }
]
```

---

### `POST /api/cashiers`

Crear un nuevo cajero.

**Body:**

```json
{
  "name": "Juan Torres",
  "email": "juan@example.com",
  "password": "secret123",
  "password_confirmation": "secret123",
  "phone": "04121234567"
}
```

**Respuestas:**

* ✅ `201 Created`: Cajero creado correctamente
* ❌ `403 Forbidden`: El usuario autenticado no es admin
* ❌ `422 Unprocessable Entity`: Validación fallida

---

## ⚡ Resumen rápido

* **Auth:** `register`, `login`, `logout`
* **Perfil:** `GET/PUT profile`, `PUT profile/password`
* **Empresa:** `GET/PUT company`
* **Vendedores:** `GET/POST sellers`
* **Cajeros:** `GET/POST cashiers`

---
