## 🎯 Objetivos de la app

Desarrollar una aplicación tipo **POS (punto de venta)** para que un usuario propietario de un negocio o empresa pueda:

- Registrar su **empresa**.
- Administrar **usuarios** con diferentes roles: propietarios, administradores, cajeros y vendedores.
- Registrar **productos y servicios** de la empresa.
  - Clasificación de productos por **departamentos**.
  - Posibilidad de registrar **presentaciones** (ej: caja, bulto, paquete).
  - Múltiples precios por producto:
    - Precio a crédito.
    - Precio al mayor.
    - Precio al detal.
- Manejar **facturación y notas de entrega**:
  - Identificar cuáles facturas son fiscales y cuáles son solo notas de entrega.(Ojo sin emitir acturas fiscales)
- Llevar **control de stock e inventario** de productos físicos.
- Generar **reportes de ventas y ganancias**.
- Manejar diferentes **monedas** según la configuración de la empresa (dólares, bolívares, pesos colombianos).

---

## ✅ Lo construido hasta ahora

### 1. Tablas principales creadas
- **Users** → Usuarios con roles.  
- **Roles** → Administración de permisos y jerarquías.  
- **Companies** → Datos de las empresas registradas.  
- **Sellers** → Vendedores asociados a una empresa.  
- **Cashiers** → Cajeros asociados a una empresa.  
- **Departments** → Departamentos para clasificar productos.  
- **Currencies** → Monedas de operación.  
- **Products** → Productos y servicios de la empresa.
- **InventoryOperation** → Modulo para ajustar el stock en la tabla de productos, con las operaciones basicas de cargo, descargo y ajustes de inventario.

---

### 2. Controladores implementados
- **AuthController** → Registro, login y logout de usuarios.  (Aquellos que podrán iniciar sesión )
- **ProfileController** → Gestión del perfil y cambio de contraseña.
- **CompaniesController** → Mostrar y actualizar datos de la empresa. 
- **SellerController** → Gestión de vendedores.  
- **CashierController** → Gestión de cajeros.  
- **DepartmentController** → CRUD de departamentos.  
- **CurrencyController** → Gestión de monedas (API Resource).  
- **ProductController** → Gestión de productos (API Resource).  
- **InventoryOperationController** → Gestión de las operaciones de inventario (API Resource).  

---

### 3. Rutas actuales (`api.php`)

#### 🔐 Autenticación (`/auth`)
- `POST /auth/register` → Registro de usuario.  
- `POST /auth/login` → Login de usuario.  
- `POST /auth/logout` → Logout (requiere autenticación).

#### 👤 Perfil
- `GET /user` → Ver datos del usuario autenticado.  
- `GET /profile` → Mostrar perfil.  
- `PUT /profile` → Actualizar perfil.  
- `PUT /profile/password` → Cambiar contraseña.  

#### 🏢 Empresa
- `GET /companies` → Mostrar datos de la empresa.  (Solo la que el usuario que está conectado es dueño o tiene permiso de leer, ejemplo un admin solo puede ver y editar los datos de su empresa la que el mismo creo)
- `PUT /companies` → Actualizar datos de la empresa.  (A la que el usuario tenga permiso )
- `GET /companies/{id}` right arrow puedes ver cómo un súper admin la empresa registrada por id. Esto es para un futuro master que pueda observar todo el sistema...

#### 🧑‍💼 Vendedores
- `GET /sellers` → Listar vendedores. 
- `POST /sellers` → Crear vendedor. 
##### Observación de estas rutas.
- son rutas protegidas que requieren autenticación y que toman como referencia la empresa a la que pertenece el subsidio que está con la session iniciada mediante el token 

#### 💳 Cajeros
- `GET /cashiers` → Listar cajeros.  
- `POST /cashiers` → Crear cajero.  
- estás rutas comparten la misma cualidad de las rutas de vendedores 👆ver lss observaciones de las rutas de los vendedores 
#### 🗂 Departamentos
- `GET /departments` → Listar departamentos.  
- `POST /departments` → Crear departamento.  
- `PUT /departments/{id}` → Editar departamento.  
- `DELETE /departments/{id}` → Eliminar departamento.  

#### 💱 Monedas
- `apiResource('currencies')` → CRUD completo para monedas.  

#### 📦 Productos
- `apiResource('products')` → CRUD completo para productos.  


### Operaciones de inventario
- `apiResource('inventory-operations`)
- `GET /api/inventory-operations`
- `GET /api/inventory-operations?operation_type=cargo`
- `GET /api/inventory-operations?operation_type=ajuste&from=2025-09-01&to=2025-09-30`
- `POST /api/inventory-operations` 


---

## 🚧 Pendientes por implementar
- Facturación (notas de entrega y facturas fiscales).  


---

# ✅ Estado de tests
- [x] **AuthTest** → registro y login de usuario.  
- [x] **SellerTest** → creación de vendedores.  
- [x] **CompaniesTest** → creación de empresas.  
- [x] **CurrenciesTest** → completado.  
- [x] **DepartmentsTest** → Creacion comletada.  
- [x] **CashiersTest** → Creacion completada.  
- [x] **ProductsTest** → Realizado.  
- [x] OperationInventory 




# 📦 Inventario – Plan y Tareas (según Joel)

## 📝 Contexto

El inventario se maneja con **operaciones** que registran los movimientos que no son facturas.  
El stock de productos se actualiza en base a esas operaciones.

---

## ✅ Requisitos de Joel

### Tabla `inventory_operations`

Campos:

- `id`
    
- `operation_type` → enum: `cargo`, `descargo`, `ajuste`.
    
- `operation_number` → correlativo por tipo.
    
- `operation_date` → fecha de operación.
    
- `note` → motivo/observación.
    
- `user_id` → usuario que hace la operación.
    
- `responsible` → varchar (nombre de la persona responsable).
    
- `company_id` → para filtrar por empresa.
    
- timestamps.
    

### Tabla `inventory_operation_details`

Campos:

- `id`
    
- `operation_id` → FK a `inventory_operations`.
    
- `product_id` → FK a productos.
    
- `quantity` → cantidad afectada.
    

---


# Modulo [[Operaciones de inventario]] Modulo de operaciones de inventario



## 🧾 Módulo de Operaciones de Inventario — Resumen Técnico (para Obsidian)

### 📘 Descripción

Permite registrar, listar y controlar las operaciones de inventario (entradas, salidas, y ajustes) de cada compañía.  
Cada operación puede ser:

- **Cargo:** Aumenta el stock del producto.
    
- **Descargo:** Disminuye el stock.
    
- **Ajuste:** Reemplaza el stock existente por una cantidad exacta.
    

---

### 🧱 Tablas involucradas

- **products** → contiene el campo `stock`.
    
- **inventory_operations** → cabecera de la operación.
    
- **inventory_operation_details** → detalle por producto afectado.
    

---

### ⚙️ Controlador principal: `InventoryOperationController`

#### ➤ `store()` — Crear operación

- Valida los campos requeridos.
    
- Usa una **transacción (DB::transaction)** para garantizar integridad.
    
- Calcula el número consecutivo de la operación.
    
- Crea la cabecera (`inventory_operations`).
    
- Crea los detalles (`inventory_operation_details`).
    
- Actualiza el stock del producto según tipo de operación.
    
- Impide stock negativo.
    
- Retorna JSON con cabecera + detalles.
    

#### ➤ `index()` — Listar operaciones

- Permite filtrar por tipo (`cargo`, `descargo`, `ajuste`).
    
- Filtro por búsqueda (`responsible`, `note`).
    
- Filtros de fechas (`from`, `to`).
    
- Paginación dinámica (`per_page`).
    
- Incluye relaciones: `details.product` y `user`.
    

#### 🔗 Endpoints principales

`GET    /api/inventory-operations
`GET    /api/inventory-operations?operation_type=cargo
`GET    /api/inventory-operations?search=Joan
`GET    /api/inventory-operations?from=2025-10-01&to=2025-10-04
`POST   /api/inventory-operations

### 🧠 Lógica de stock

|Tipo|Acción sobre `stock`|
|---|---|
|Cargo|stock += cantidad|
|Descargo|stock -= cantidad|
|Ajuste|stock = cantidad|
### ✅ Estado actual del módulo

- Migraciones creadas correctamente
- Controlador funcional con validaciones
- Transacciones implementadas
- Actualización de stock en productos
- Validación de stock no negativo
- Filtros + paginación en index
- Tests automatizados (pendiente)



### 🧪 Próximo paso: Testing del módulo


### 🎯 Objetivo

Verificar que las operaciones funcionen correctamente y que el stock se actualice de manera coherente según el tipo de operación.

### 📂 Ubicación sugerida

`tests/Feature/InventoryOperationTest.php`

### 🧱 Casos de prueba recomendados

|Tipo de test|Descripción|
|---|---|
|✅ **1. Crear operación tipo cargo**|Verifica que aumente el stock de los productos.|
|✅ **2. Crear operación tipo descargo**|Verifica que disminuya el stock y no quede negativo.|
|✅ **3. Crear operación tipo ajuste**|Verifica que el stock se reemplace por el valor exacto.|
|✅ **4. Error por falta de compañía**|Si el usuario no tiene `companies_id`, debe devolver 403.|
|✅ **5. Error por stock insuficiente**|Si el descargo intenta bajar más de lo que hay, debe fallar con mensaje.|
|✅ **6. Listado general**|Retorna operaciones con paginación.|
|✅ **7. Filtros por tipo y fecha**|Verifica que los filtros funcionen correctamente.|
