# Sistema de Autenticación Web

Proyecto del curso de Desarrollo de Software. Permite registrar usuarios con DNI, iniciar sesión y recuperar la contraseña mediante correo electrónico.

## Tecnologías

- Laravel 12 y PHP 8.2 o superior.
- MySQL o MariaDB, usando XAMPP en el entorno local.
- Blade, CSS y JavaScript.
- Composer.
- Laravel Socialite para el acceso opcional con Google.

Los estilos y scripts están en public/. No hace falta ejecutar npm ni Vite.

## Funcionalidades

- Registro con nombre completo, DNI, correo y contraseña.
- Validación de correo y DNI únicos.
- Inicio de sesión con opción de recordar al usuario.
- Panel privado con los datos de la cuenta.
- Cierre de sesión.
- Recuperación de contraseña con enlace temporal de un solo uso.
- Vinculación de una cuenta de Google desde el panel.

Las contraseñas se guardan con hash. Los formularios usan validación del servidor y protección CSRF. El panel está protegido por el middleware auth.

## Instalación

### 1. Preparar XAMPP

Inicia MySQL desde el panel de XAMPP. Para utilizar phpMyAdmin, inicia también Apache y abre http://localhost/phpmyadmin.

Crea una base de datos vacía llamada sistema_login, con cotejamiento utf8mb4_unicode_ci. Comprueba el puerto de MySQL en la configuración de XAMPP.

### 2. Instalar las dependencias

Abre una terminal en la carpeta del proyecto:

```powershell
composer install
Copy-Item .env.example .env
```

La copia de .env se hace solo durante la primera instalación. Si el archivo ya existe, conserva su configuración y su APP_KEY.

Si PHP no está en el PATH de Windows, puedes agregarlo a la terminal actual:

```powershell
$env:Path = 'C:\xampp\php;' + $env:Path
```

### 3. Configurar la conexión

Edita .env con los datos de tu servidor. Ejemplo para una instalación local:

```dotenv
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistema_login
DB_USERNAME=root
DB_PASSWORD=
```

Utiliza el puerto, usuario y contraseña de tu instalación. El ejemplo con root sin contraseña no corresponde a todas las configuraciones de XAMPP.

### 4. Crear las tablas e iniciar el servidor

En una instalación nueva:

```powershell
php artisan key:generate
php artisan migrate
php artisan serve
```

Abre http://127.0.0.1:8000/login. En Windows también puedes usar INICIAR.cmd una vez preparada la conexión.

Si cambias .env, ejecuta php artisan config:clear antes de volver a probar.

## Recuperación de contraseña

El sistema utiliza el Password Broker de Laravel y la tabla password_reset_tokens. Los enlaces vencen en 60 minutos. Al cambiar la contraseña se elimina el token utilizado y se revocan las sesiones anteriores.

### Prueba local

Con esta configuración, el correo se guarda en un archivo:

```dotenv
MAIL_MAILER=log
MAIL_LOG_CHANNEL=mail
```

Solicita un enlace desde ¿Olvidaste tu contraseña? y revisa storage/logs/mail.log. También puedes obtener el último enlace con:

```powershell
php artisan acceso:ultimo-enlace
```

Este comando está disponible únicamente en el entorno local y con correo en modo log.

### Envío por Gmail

Activa la verificación en dos pasos de Google y genera una contraseña de aplicación, si tu cuenta permite esa opción. Guárdala únicamente en .env:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_cuenta@gmail.com
MAIL_PASSWORD="contraseña_de_aplicacion"
MAIL_FROM_ADDRESS=tu_cuenta@gmail.com
MAIL_FROM_NAME="Sistema de Autenticación Web"
```

Después ejecuta php artisan config:clear y solicita un enlace nuevo. La configuración SMTP no depende del inicio de sesión con Google.

## Acceso con Google

1. Crea un cliente OAuth de tipo Aplicación web en Google Cloud.
2. Configura la pantalla de consentimiento y los usuarios de prueba cuando corresponda.
3. Agrega esta URI de redirección autorizada:

   http://127.0.0.1:8000/auth/google/callback

4. Completa las variables locales:

```dotenv
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback
```

5. Ejecuta php artisan config:clear.
6. Registra la cuenta local con el correo de Google y tu DNI.
7. Inicia sesión y vincula Google desde el panel, confirmando la contraseña actual.

La vinculación requiere un correo verificado que coincida con el de la cuenta local. No se asocian cuentas automáticamente por tener el mismo correo.

El envío real a Gmail y el acceso real con Google requieren configurar las credenciales correspondientes. Las pruebas automatizadas de Google utilizan respuestas controladas del proveedor.

## Archivos principales

| Archivo o carpeta | Función |
|---|---|
| routes/web.php | Rutas y middleware de acceso |
| app/Http/Controllers/AuthController.php | Formularios, registro, login, panel y cierre de sesión |
| app/Http/Controllers/ForgotPasswordController.php | Solicitud de enlace y cambio de contraseña |
| app/Http/Controllers/GoogleAuthController.php | Redirección, vinculación y acceso con Google |
| app/Models/User.php | Modelo de usuario y hash de contraseña |
| app/Providers/AppServiceProvider.php | Límites de solicitudes y correo de recuperación |
| database/migrations/ | Tablas e índices |
| resources/views/auth/ | ingresar, registrarse, recuperacion y restaurar, en Blade |
| resources/views/dashboard.blade.php | Datos del usuario y cierre de sesión |
| public/css/app.css | Estilos y diseño adaptable |
| public/js/app.js | Mostrar contraseña y controlar el envío de formularios |
| tests/Feature/ | Pruebas de los flujos de autenticación |

## Recorrido de prueba

1. Abre /registro, completa tus datos y pulsa Registrarme.
2. Inicia sesión y comprueba tu nombre, DNI y correo en el panel.
3. Cierra sesión y solicita un enlace desde ¿Olvidaste tu contraseña?
4. En modo log, usa php artisan acceso:ultimo-enlace para abrir el mensaje local.
5. Cambia la contraseña y comprueba que la anterior falla y la nueva permite entrar.

## Pruebas

Para ejecutar las pruebas en SQLite en memoria:

```powershell
php artisan test
```

Se necesita la extensión pdo_sqlite. Para probar en MySQL, crea una base desechable llamada sistema_login_test y ejecuta:

```powershell
php artisan config:clear
php vendor/phpunit/phpunit/phpunit -c phpunit.mysql.xml --testdox
```

Las pruebas utilizan una base separada y verifican registro, duplicados, login, protección del panel, logout, recuperación, vencimiento y reutilización de tokens, y vinculación con Google.

## Archivos del repositorio

Se incluyen el código, composer.lock, las migraciones y .env.example. El archivo .gitignore excluye .env, dependencias, registros, sesiones y cachés generadas.

Para trabajar desde otra computadora, clona el repositorio y sigue la instalación de este README. Conserva las credenciales únicamente en tu .env local.

## Documentación de referencia

- [Laravel 12](https://laravel.com/docs/12.x)
- [Recuperación de contraseñas](https://laravel.com/docs/12.x/passwords)
- [Laravel Socialite](https://laravel.com/docs/12.x/socialite)
- [Contraseñas de aplicaciones de Google](https://support.google.com/accounts/answer/185833)
