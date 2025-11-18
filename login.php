<?php
// ====================================================================
// PASO 1: CONFIGURACIÓN Y CONEXIÓN A LA BASE DE DATOS (MYSQLI)
// ====================================================================

// **AJUSTA ESTOS VALORES** a los de tu servidor local
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');      // Por defecto en XAMPP
define('DB_PASSWORD', '');          // Por defecto en XAMPP
define('DB_NAME', 'mesa_bd'); // ¡Asegúrate de que este nombre sea correcto!

// Iniciar sesión (debe estar al principio antes de cualquier salida HTML)
session_start();

// Si el usuario ya está logeado, lo redirigimos a la página principal
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.html");
    exit;
}

// Conexión a la Base de Datos
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    // Si falla la conexión, muestra un mensaje amigable y el error real para debug
    die("Error de conexión: Por favor, revisa que XAMPP/MySQL esté corriendo y que la base de datos '" . DB_NAME . "' exista. Error detallado: " . $conn->connect_error);
}

// Variables para almacenar los datos de entrada y mensajes de error
$email = $password = "";
$email_err = $password_err = $login_err = "";

// ====================================================================
// PASO 2: PROCESAMIENTO DEL FORMULARIO
// ====================================================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Validar Email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor, ingresa tu correo electrónico.";
    } else {
        $email = trim($_POST["email"]);
    }

    // 2. Validar Contraseña
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor, ingresa tu contraseña.";
    } else {
        $password = trim($_POST["password"]);
    }

    // 3. Verificar credenciales en la DB si no hay errores de validación
    if (empty($email_err) && empty($password_err)) {
        
        // Uso de consultas preparadas para prevenir inyecciones SQL
        $sql = "SELECT id_usuario, email, contrasena, nombre FROM usuarios WHERE email = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Vincular variable como parámetro
            $stmt->bind_param("s", $param_email);
            $param_email = $email;
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                // Si existe un usuario con ese email
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $email_db, $hashed_password, $nombre_usuario);
                    
                    if ($stmt->fetch()) {
                        // Verificar la contraseña con el hash almacenado (SEGURIDAD CRÍTICA)
                        if (password_verify($password, $hashed_password)) {
                            
                            // Contraseña correcta: iniciar sesión y redirigir
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id_usuario"] = $id;
                            $_SESSION["email"] = $email_db;
                            $_SESSION["nombre"] = $nombre_usuario; 
                            
                            // Redirigir a la página de inicio o al panel de usuario
                            header("location: index.html"); 
                            exit;

                        } else {
                            // Contraseña no válida
                            $login_err = "El email o la contraseña que has introducido no son válidos.";
                        }
                    }
                } else {
                    // Email no encontrado
                    $login_err = "El email o la contraseña que has introducido no son válidos.";
                }
            } else {
                echo "¡Ups! Algo salió mal. Por favor, inténtalo de nuevo más tarde.";
            }

            $stmt->close();
        }
    }
    
    // La conexión se cerrará automáticamente al final del script, pero la cerramos aquí si el POST falla.
    if ($conn) { $conn->close(); }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Mesa Feliz</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .error-message {
            color: red;
            font-size: 0.9em;
            margin-bottom: 10px;
            margin-top: -10px;
        }
        /* Esto aplica el centrado de la clase .login-page definida en style.css */
        .login-page {
            padding-top: 0; 
            min-height: 100vh; 
        }
    </style>
</head>
<body class="login-page">

    <div class="container">

        <div class="info">
            <p class="txt-1"> Gracias por visitarnos</p>
            <h2>Bienvenido de nuevo</h2>
        </div>

        <!-- FORMULARIO: Usará action="" y method="POST" para enviarse a sí mismo -->
        <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <h2>Iniciar Sesión</h2>

            <!-- Muestra el error general de credenciales -->
            <?php 
            if(!empty($login_err)){
                echo '<div class="error-message">' . $login_err . '</div>';
            }        
            ?>

            <div class="inputs">

                <!-- Campo de Email: Agregamos name="email" y el valor para no perderlo si hay error -->
                <input type="email" class="box" name="email" placeholder="Ingresa tu correo" value="<?php echo htmlspecialchars($email); ?>">
                <!-- Muestra el error de validación del email -->
                <?php if(!empty($email_err)) echo '<div class="error-message">' . $email_err . '</div>'; ?>

                <!-- Campo de Contraseña: Agregamos name="password" -->
                <input type="password" class="box" name="password" placeholder="Ingresa tu contraseña">
                <!-- Muestra el error de validación de la contraseña -->
                <?php if(!empty($password_err)) echo '<div class="error-message">' . $password_err . '</div>'; ?>

                <a href="#">Olvidaste tu contraseña</a>

                <input type="submit" value="Ingresar">

            </div>
            <!-- Asegúrate de crear el archivo registro.php -->
            <p>¿Todavia no tienes una cuenta? <a href="registro.php">Regístrate</a></p>
        </form>

    </div>

</body>
</html>
