-- =====================================
-- TABLA DE EMPRESA
-- Cada sistema solo tendrá UNA empresa registrada
-- El campo invoice_sequence sirve para definir el correlativo inicial de las facturas
-- =====================================
CREATE TABLE `companys` (
	`id` INTEGER AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`phone` VARCHAR(255) NOT NULL,
	`email` VARCHAR(255) NOT NULL,
	`invoice_sequence` INTEGER NOT NULL DEFAULT 1 COMMENT 'correlativo inicial de facturas definido por el admin',
	PRIMARY KEY(`id`)
);

-- =====================================
-- TABLA DE USUARIOS
-- Representan los usuarios que entran al sistema (admin o cajeros)
-- =====================================
CREATE TABLE `users` (
	`id` INTEGER AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`pass` VARCHAR(255) NOT NULL,
	`fk_company` INTEGER NOT NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	PRIMARY KEY(`id`)
);

-- =====================================
-- TABLA DE ROLES
-- Por ahora solo hay 2 roles: admin y cajero
-- Se asigna a cada usuario
-- =====================================
CREATE TABLE `roles` (
	`id` INTEGER AUTO_INCREMENT,
	`role` ENUM('admin', 'cashier') NOT NULL,
	`fk_user` INTEGER NOT NULL,
	`phone` VARCHAR(255) NOT NULL,
	PRIMARY KEY(`id`)
);

-- =====================================
-- TABLA DE CLIENTES
-- Información de los clientes finales
-- =====================================
CREATE TABLE `customers` (
	`id` INTEGER AUTO_INCREMENT,
	`id_card` VARCHAR(255) NOT NULL,
	`name` VARCHAR(255) NOT NULL,
	`email` VARCHAR(255),
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	`adres` VARCHAR(255),
	`phone` VARCHAR(255),
	PRIMARY KEY(`id`)
);

-- =====================================
-- TABLA DE VENDEDORES
-- Son los vendedores de campo, no entran al sistema
-- Solo se registran con su comisión y empresa
-- =====================================
CREATE TABLE `sellers` (
	`id` INTEGER AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL,
	`ci` VARCHAR(12) NOT NULL COMMENT 'numero de cedula del vendedor',
	`commission_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT 'porcentaje de comisión',
	`fk_company` INTEGER NOT NULL,
	PRIMARY KEY(`id`)
);

-- =====================================
-- TABLA DE MONEDAS
-- Se definen las monedas soportadas con su tasa de cambio
-- =====================================
CREATE TABLE `currencies` (
	`id` INTEGER AUTO_INCREMENT,
	`code` VARCHAR(5) NOT NULL COMMENT 'Codigo de la moneda Ej: USD, VES, COP',
	`symbol` VARCHAR(5) NOT NULL,
	`rate` DECIMAL(10,4) NOT NULL COMMENT 'tasa de cambio respecto a USD o tu moneda base',
	PRIMARY KEY(`id`)
);

-- =====================================
-- TABLA DE PRODUCTOS
-- Información básica del producto con stock general
-- =====================================
CREATE TABLE `products` (
	`id` INTEGER AUTO_INCREMENT,
	`bar_code` TEXT DEFAULT NULL,
	`name` VARCHAR(255) NOT NULL,
	`des` TEXT DEFAULT NULL,
	`stock` INTEGER NOT NULL DEFAULT 0 COMMENT 'stock general',
	`unit` ENUM('kg', 'gr', 'lt', 'unit', 'ml') NOT NULL DEFAULT 'unit',
	`fk_company` INTEGER NOT NULL,
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	PRIMARY KEY(`id`)
);

-- =====================================
-- TABLA DE TIPOS DE PRECIOS
-- Define los tipos de precio (ejemplo: detal, mayorista, full)
-- =====================================
CREATE TABLE `price_types` (
	`id` INTEGER AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL COMMENT 'Ej: detal, mayorista, full',
	PRIMARY KEY(`id`)
);

-- =====================================
-- TABLA DE PRECIOS DE PRODUCTOS
-- Relaciona un producto con un tipo de precio y su valor en una moneda
-- =====================================
CREATE TABLE `product_prices` (
	`id` INTEGER AUTO_INCREMENT,
	`product_id` INTEGER NOT NULL,
	`price_type_id` INTEGER NOT NULL,
	`amount` DECIMAL(10,2) NOT NULL,
	`currency_id` INTEGER NOT NULL,
	PRIMARY KEY(`id`)
);

-- =====================================
-- TABLA DE FACTURAS / NOTAS
-- Aquí se maneja el correlativo de facturas/notas
-- El admin define el correlativo inicial
-- =====================================
CREATE TABLE `invoices` (
	`id` INTEGER AUTO_INCREMENT,
	`company_id` INTEGER NOT NULL,
	`invoice_number` INTEGER NOT NULL COMMENT 'Correlativo global',
	`type` ENUM('fiscal', 'note') NOT NULL COMMENT 'Factura fiscal o nota de entrega',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	PRIMARY KEY(`id`)
);

-- =====================================
-- TABLA DE VENTAS
-- Guarda la venta realizada con su factura/nota asociada
-- Incluye al usuario (cajero/admin), el cliente y opcionalmente un vendedor de campo
-- =====================================
CREATE TABLE `sales` (
	`id` INTEGER AUTO_INCREMENT,
	`user_id` INTEGER NOT NULL COMMENT 'Usuario que realizó la venta (admin o cajero)',
	`client_id` INTEGER NOT NULL,
	`seller_id` INTEGER DEFAULT NULL COMMENT 'Vendedor de campo (opcional)',
	`company_id` INTEGER NOT NULL,
	`invoice_id` INTEGER NOT NULL COMMENT 'Factura asociada',
	`commission_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT 'Porcentaje aplicado en esta venta',
	`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
	PRIMARY KEY(`id`)
);

-- =====================================
-- RELACIONES ENTRE TABLAS
-- =====================================
ALTER TABLE `users`
ADD FOREIGN KEY(`fk_company`) REFERENCES `companys`(`id`)
ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE `roles`
ADD FOREIGN KEY(`fk_user`) REFERENCES `users`(`id`)
ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `sellers`
ADD FOREIGN KEY(`fk_company`) REFERENCES `companys`(`id`)
ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE `products`
ADD FOREIGN KEY(`fk_company`) REFERENCES `companys`(`id`)
ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE `product_prices`
ADD FOREIGN KEY(`product_id`) REFERENCES `products`(`id`)
ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `product_prices`
ADD FOREIGN KEY(`price_type_id`) REFERENCES `price_types`(`id`)
ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `product_prices`
ADD FOREIGN KEY(`currency_id`) REFERENCES `currencies`(`id`)
ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE `invoices`
ADD FOREIGN KEY(`company_id`) REFERENCES `companys`(`id`)
ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE `sales`
ADD FOREIGN KEY(`user_id`) REFERENCES `users`(`id`)
ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE `sales`
ADD FOREIGN KEY(`client_id`) REFERENCES `customers`(`id`)
ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE `sales`
ADD FOREIGN KEY(`seller_id`) REFERENCES `sellers`(`id`)
ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE `sales`
ADD FOREIGN KEY(`company_id`) REFERENCES `companys`(`id`)
ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE `sales`
ADD FOREIGN KEY(`invoice_id`) REFERENCES `invoices`(`id`)
ON UPDATE NO ACTION ON DELETE NO ACTION;

-- =====================================
-- EJEMPLOS DE DATOS INICIALES
-- =====================================

-- Empresa
INSERT INTO companys (name, phone, email, invoice_sequence) 
VALUES ('Supermercado La Estrella', '0414-1234567', 'contacto@estrella.com', 100);

-- Monedas
INSERT INTO currencies (code, symbol, rate) VALUES 
('USD', '$', 1.0000),
('VES', 'Bs', 40.0000),
('COP', '$', 4200.0000);

-- Tipos de precios
INSERT INTO price_types (name) VALUES 
('detal'),
('mayorista'),
('full');
