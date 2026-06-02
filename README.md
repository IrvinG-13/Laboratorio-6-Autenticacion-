# 🔐 Laboratorio: Autenticación con 2FA en PHP

Sistema de autenticación de dos factores implementado con PHP, MySQL y Google Authenticator. El usuario inicia sesión con correo y contraseña, luego debe confirmar su identidad con un código temporal generado en su teléfono.

---

## 🗄️ Sección 1: Base de Datos y Usuarios MySQL

### Tabla `usuarios`

La tabla almacena los datos de cada usuario registrado. Los campos clave son `HashMagic` (la contraseña encriptada con bcrypt) y `secret_2fa` (la clave TOTP que se genera cuando el usuario activa el 2FA por primera vez).

```sql
ALTER TABLE `usuarios`
ADD `secret_2fa` VARCHAR(255) NULL AFTER `HashMagic`;
```
<img width="1600" height="676" alt="image" src="https://github.com/user-attachments/assets/3705ae98-3e5e-46fe-91a2-54367b158e41" />


### Tabla `intentos_login`

Guarda un registro de cada intento de acceso al sistema, sea exitoso o fallido. Incluye el correo del usuario, la IP, el estado (`success` / `fail`), una bandera de detección de anomalía y el timestamp exacto. Sirve como tabla de auditoría.

```sql
SELECT * FROM `intentos_login`;
```
<img width="1498" height="811" alt="image" src="https://github.com/user-attachments/assets/aaf9dabc-92a0-4db9-9496-b1d70def2558" />


### Privilegios del usuario de BD (`SHOW GRANTS`)

El usuario de base de datos que usa la aplicación **no es root**. Solo tiene permisos de SELECT, INSERT, UPDATE y DELETE sobre `company_info`. Se verifica con:

```sql
SHOW GRANTS FOR 'login_user'@'localhost';
```

Resultado esperado:
```
GRANT USAGE ON *.* TO 'login_user'@'localhost'
GRANT SELECT, INSERT, UPDATE, DELETE ON `company_info`.* TO 'login_user'@'localhost'
```
<img width="1600" height="666" alt="image" src="https://github.com/user-attachments/assets/d22377c8-53be-4130-b5f6-3db5aeffe150" />

---

## 📝 Sección 2: Registro de Usuario y Seguridad

### Registrar usuario nuevo

El formulario de registro pide nombre, apellido, correo, contraseña y confirmación. Al enviarlo correctamente, el sistema redirige al login y muestra el mensaje **"Usuario registrado correctamente."**

Los datos se sanitizan antes de guardarse usando métodos estáticos de la clase `Sanitizador` y la contraseña se hashea con `password_hash()` antes de tocar la base de datos.

<img width="772" height="790" alt="image" src="https://github.com/user-attachments/assets/c2761b7f-15ac-4888-aa0d-e384ccbeac33" />


### Intentar registrar el mismo correo

Si se intenta crear una cuenta con un correo que ya existe, el sistema muestra el error **"Este correo ya está registrado."** directamente bajo el campo, sin enviar el formulario. La validación ocurre tanto en el frontend como en el backend.
<img width="751" height="820" alt="image" src="https://github.com/user-attachments/assets/4103d7e8-e9cc-461c-82a8-0ecbc6c1eb82" />

### Probar contraseña y confirmación

Si los campos de contraseña y confirmación no coinciden, se muestra el mensaje **"Las contraseñas no coinciden."** El sistema puede mostrar ambos errores al mismo tiempo (correo duplicado + contraseñas no coincidentes) sin procesar nada.
<img width="706" height="817" alt="image" src="https://github.com/user-attachments/assets/1c346a7d-8a07-4696-81fb-669dbd7ac1d7" />


### Generar hash

La interfaz **"Generar y Validar Hash"** permite ingresar una contraseña en texto plano y obtener su hash bcrypt usando `password_hash()` con `PASSWORD_BCRYPT`. Útil para verificar que el hashing funciona correctamente.

```php
$hash = password_hash($claveMagica, PASSWORD_BCRYPT, ['cost' => 13]);
```
<img width="703" height="352" alt="image" src="https://github.com/user-attachments/assets/034fcb53-cce4-43b4-bcc3-722a86e09c65" />


### Verificar el hash

En la misma interfaz, se puede ingresar una contraseña y un hash para comprobar si coinciden. Si son compatibles, muestra **"El hash corresponde a la contraseña ingresada."**

```php
if (password_verify($password, $hash)) {
    echo "El hash corresponde a la contraseña ingresada.";
}
```
<img width="714" height="742" alt="image" src="https://github.com/user-attachments/assets/0f17e810-c388-4c01-8a6d-a83130ecc11c" />

---

## 🔑 Sección 3: Inicio de Sesión y Autenticación 2FA

### Iniciar sesión (primera fase)

El usuario ingresa su correo y contraseña en la pantalla de login. Si las credenciales son correctas, el sistema valida con `password_verify()` y crea una sesión parcial, redirigiendo a la segunda fase de verificación.

```php
if (password_verify($password, $user->HashMagic)) {
    $_SESSION['autenticado'] = 'FASE1';
    header("Location: activar_2fa.php");
}
```
<img width="891" height="742" alt="image" src="https://github.com/user-attachments/assets/66e3c33c-8b00-48db-bb8b-871b550293e3" />


### Activar código QR

Si el usuario no tiene 2FA activo aún, el sistema genera un secreto TOTP único con `generateSecret()`, lo guarda en `secret_2fa` y construye el QR. El usuario lo escanea con Google Authenticator en su teléfono. También se muestra la clave manual por si el QR no funciona.

```php
$g = new GoogleAuthenticator();
$secret = $g->generateSecret();
// guardar $secret en la BD para este usuario
$url = GoogleQrUrl::generate($correo, $secret, 'MiSistema');
```
<img width="684" height="754" alt="image" src="https://github.com/user-attachments/assets/9e0b5ff4-6306-4d16-b6c7-d10fe67e5e26" />

### Verificar código 2FA correcto

Después de escanear el QR, el usuario ingresa el código de 6 dígitos que aparece en Google Authenticator. Si es válido, el sistema crea la sesión completa y muestra el **Panel Principal** con el mensaje de bienvenida.

```php
if ($g->checkCode($secret, $codigo)) {
    $_SESSION['autenticado'] = 'SI';
    header("Location: dashboard.php");
}
```
<img width="421" height="370" alt="image" src="https://github.com/user-attachments/assets/026ce098-6727-49f8-aa93-4fa98ba91507" />


### Código 2FA incorrecto

Si el código ingresado es inválido (expiró, se equivocó al escribirlo, etc.), el sistema muestra **"Código 2FA incorrecto. Intentar nuevamente"** y no concede acceso. El intento queda registrado en la tabla de auditoría con `deteccion_anomalia = 1`.
<img width="234" height="42" alt="image" src="https://github.com/user-attachments/assets/f41d54a6-82a9-4232-94dc-f8ed28f401de" />

---

## 📋 Sección 4: Registro y Auditoría

### Archivo `registro.log`

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

<img width="1600" height="861" alt="image" src="https://github.com/user-attachments/assets/3071e609-e881-44e7-9680-d9349fdd196d" />


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
  Este laboratorio ha sido desarrollado por el estudiante de la Universidad Tecnológica de Panamá:

| Campo | Información |
|------|------------|
| Nombre | Elisa Oses , Irvin González|
| Correo | elisa.oses@utp.ac.pa, irvin.gonzalez3@utp.ac.pa |
| Curso | Desarrollo De Software VII |
| Instructor | Ing. Irina Fong |
| Fecha | 2 de junio 2026 |
