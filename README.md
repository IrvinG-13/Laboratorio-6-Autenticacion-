# 🔐 Laboratorio: Autenticación con 2FA en PHP

Sistema de autenticación de dos factores implementado con PHP, MySQL y Google Authenticator. El usuario inicia sesión con correo y contraseña, luego debe confirmar su identidad con un código temporal generado en su teléfono.

---

## 🗄️ Sección 1: Base de Datos y Usuarios MySQL

### Paso 9 — Tabla `usuarios`

La tabla almacena los datos de cada usuario registrado. Los campos clave son `HashMagic` (la contraseña encriptada con bcrypt) y `secret_2fa` (la clave TOTP que se genera cuando el usuario activa el 2FA por primera vez).

```sql
ALTER TABLE `usuarios`
ADD `secret_2fa` VARCHAR(255) NULL AFTER `HashMagic`;
```
<img width="1600" height="676" alt="image" src="https://github.com/user-attachments/assets/3705ae98-3e5e-46fe-91a2-54367b158e41" />


### Paso 10 — Tabla `intentos_login`

Guarda un registro de cada intento de acceso al sistema, sea exitoso o fallido. Incluye el correo del usuario, la IP, el estado (`success` / `fail`), una bandera de detección de anomalía y el timestamp exacto. Sirve como tabla de auditoría.

```sql
SELECT * FROM `intentos_login`;
```

### Paso 12 — Privilegios del usuario de BD (`SHOW GRANTS`)

El usuario de base de datos que usa la aplicación **no es root**. Solo tiene permisos de SELECT, INSERT, UPDATE y DELETE sobre `company_info`. Se verifica con:

```sql
SHOW GRANTS FOR 'login_user'@'localhost';
```

Resultado esperado:
```
GRANT USAGE ON *.* TO 'login_user'@'localhost'
GRANT SELECT, INSERT, UPDATE, DELETE ON `company_info`.* TO 'login_user'@'localhost'
```

---

## 📝 Sección 2: Registro de Usuario y Seguridad

### Paso 1 — Registrar usuario nuevo

El formulario de registro pide nombre, apellido, correo, contraseña y confirmación. Al enviarlo correctamente, el sistema redirige al login y muestra el mensaje **"Usuario registrado correctamente."**

Los datos se sanitizan antes de guardarse usando métodos estáticos de la clase `Sanitizador` y la contraseña se hashea con `password_hash()` antes de tocar la base de datos.

### Paso 2 — Intentar registrar el mismo correo

Si se intenta crear una cuenta con un correo que ya existe, el sistema muestra el error **"Este correo ya está registrado."** directamente bajo el campo, sin enviar el formulario. La validación ocurre tanto en el frontend como en el backend.

### Paso 3 — Probar contraseña y confirmación

Si los campos de contraseña y confirmación no coinciden, se muestra el mensaje **"Las contraseñas no coinciden."** El sistema puede mostrar ambos errores al mismo tiempo (correo duplicado + contraseñas no coincidentes) sin procesar nada.

### Paso 13 — Generar hash

La interfaz **"Generar y Validar Hash"** permite ingresar una contraseña en texto plano y obtener su hash bcrypt usando `password_hash()` con `PASSWORD_BCRYPT`. Útil para verificar que el hashing funciona correctamente.

```php
$hash = password_hash($claveMagica, PASSWORD_BCRYPT, ['cost' => 13]);
```

### Paso 13.1 — Verificar el hash

En la misma interfaz, se puede ingresar una contraseña y un hash para comprobar si coinciden. Si son compatibles, muestra **"El hash corresponde a la contraseña ingresada."**

```php
if (password_verify($password, $hash)) {
    echo "El hash corresponde a la contraseña ingresada.";
}
```

---

## 🔑 Sección 3: Inicio de Sesión y Autenticación 2FA

### Paso 4 — Iniciar sesión (primera fase)

El usuario ingresa su correo y contraseña en la pantalla de login. Si las credenciales son correctas, el sistema valida con `password_verify()` y crea una sesión parcial, redirigiendo a la segunda fase de verificación.

```php
if (password_verify($password, $user->HashMagic)) {
    $_SESSION['autenticado'] = 'FASE1';
    header("Location: activar_2fa.php");
}
```

### Paso 5 — Activar código QR

Si el usuario no tiene 2FA activo aún, el sistema genera un secreto TOTP único con `generateSecret()`, lo guarda en `secret_2fa` y construye el QR. El usuario lo escanea con Google Authenticator en su teléfono. También se muestra la clave manual por si el QR no funciona.

```php
$g = new GoogleAuthenticator();
$secret = $g->generateSecret();
// guardar $secret en la BD para este usuario
$url = GoogleQrUrl::generate($correo, $secret, 'MiSistema');
```

### Paso 6 — Verificar código 2FA correcto

Después de escanear el QR, el usuario ingresa el código de 6 dígitos que aparece en Google Authenticator. Si es válido, el sistema crea la sesión completa y muestra el **Panel Principal** con el mensaje de bienvenida.

```php
if ($g->checkCode($secret, $codigo)) {
    $_SESSION['autenticado'] = 'SI';
    header("Location: dashboard.php");
}
```

### Paso 7 — Código 2FA incorrecto

Si el código ingresado es inválido (expiró, se equivocó al escribirlo, etc.), el sistema muestra **"Código 2FA incorrecto. Intentar nuevamente"** y no concede acceso. El intento queda registrado en la tabla de auditoría con `deteccion_anomalia = 1`.

---

## 📋 Sección 4: Registro y Auditoría

### Paso 11 — Archivo `registro.log`

Cada acción importante del sistema queda escrita en el archivo `registro.log` con fecha, hora y correo del usuario. Los eventos registrados incluyen:

| Evento | Ejemplo |
|--------|---------|
| Registro de usuario | `Usuario registrado: correo@gmail.com` |
| Login exitoso (fase 1) | `Usuario y contraseña correctos: correo@gmail.com` |
| Login fallido | `Login fallido: correo@gmail.com` |
| 2FA activado | `2FA activado correctamente: correo@gmail.com` |
| Código 2FA incorrecto | `Código 2FA incorrecto: correo@gmail.com` |
| Login completo con 2FA | `Login completo con 2FA exitoso: correo@gmail.com` |
| Cierre de sesión | `Cierre de sesión: correo@gmail.com` |

---

## 📦 Dependencias

```bash
composer require sonata-project/google-authenticator
```

## 🗂️ Estructura del Proyecto

```
login_2fa/
├── clases/
│   ├── RegistroUsuario.php   # Clase con métodos de registro
│   └── Sanitizador.php       # Métodos estáticos de sanitización
├── css/
│   └── global.css
├── activar_2fa.php           # Genera el QR y activa el secreto
├── confirmar_2fa.php         # Verifica el código TOTP
├── csrf.php                  # Tokens anti-CSRF
├── dashboard.php             # Panel principal (protegido)
├── db.php                    # Conexión PDO a la BD
├── hash_test.php             # Interfaz generar/validar hash
├── login.php                 # Formulario de login
├── login_form.php            # Procesamiento del login
├── logout.php                # Cierre de sesión
├── procesar_registro.php     # Procesamiento del registro
├── registro_form.php         # Formulario de registro
├── registro.log              # Log de auditoría
├── validar_2fa.php
└── verificar_2fa.php
```

## ⚙️ Requisitos

- PHP 8.x con servidor Apache o Nginx (XAMPP/WAMP)
- MySQL 5.7+
- Composer
- Google Authenticator instalado en el teléfono
  ##  Autores
  -Elisa Oses, Irvin Gonzalez 
