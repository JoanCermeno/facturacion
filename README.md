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
-estás rutas comparten la misma cualidad de las rutas de vendedores 👆ver lss observaciones de las rutas de los vendedores 
#### 🗂 Departamentos
- `GET /departments` → Listar departamentos.  
- `POST /departments` → Crear departamento.  
- `PUT /departments/{id}` → Editar departamento.  
- `DELETE /departments/{id}` → Eliminar departamento.  

#### 💱 Monedas
- `apiResource('currencies')` → CRUD completo para monedas.  

#### 📦 Productos
- `apiResource('products')` → CRUD completo para productos.  

---

## 🚧 Pendientes por implementar
- Facturación (notas de entrega y facturas fiscales).  
- Reportes de ventas.  
- Control de inventario.  👈👈
- Presentaciones de productos (caja, bulto, paquete).  
- Integración de múltiples precios (crédito, mayor, detal).  

---

# ✅ Estado de tests
- [x] **AuthTest** → registro y login de usuario.  
- [x] **SellerTest** → creación de vendedores.  
- [x] **CompaniesTest** → creación de empresas.  
- [x] **CurrenciesTest** → completado.  
- [x] **DepartmentsTest** → Creacion comletada.  
- [x] **CashiersTest** → Creacion completada.  
- [x] **ProductsTest** → Realizado.  

