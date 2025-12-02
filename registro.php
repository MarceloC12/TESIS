<?php
// ====================================================================
// CONFIGURACIÓN Y CONEXIÓN A LA BASE DE DATOS
// ====================================================================

// Iniciar sesión (necesario para la validación de login posterior)
session_start();

// Si tu versión de PHP es menor a 5.5, esta línea arreglará el error.
// Asegúrate de que el nombre del archivo (password.php) sea correcto.
if (version_compare(PHP_VERSION, '5.5.0', '<')) {
    require_once('password.php'); 
}

// Si el usuario ya está logeado, lo redirigimos
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.html");
    exit;
}

// **AJUSTA ESTOS VALORES SI SON DIFERENTES**
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); 
define('DB_PASSWORD', ''); 
define('DB_NAME', 'mesa_bd'); 

// Conexión a la Base de Datos
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Variables para almacenar los datos de entrada y mensajes de error
$email = $password = $confirm_password = "";
$email_err = $password_err = $confirm_password_err = $register_err = "";

// ====================================================================
// PROCESAMIENTO DEL FORMULARIO DE REGISTRO
// ====================================================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. VALIDAR EMAIL
    if (empty(trim($_POST["email"]))) {
        $email_err = "Por favor, ingrese un correo.";
    } else {
        $email = trim($_POST["email"]);
        // Verificar si el correo ya existe
        $sql = "SELECT id FROM users WHERE email = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = $email;
            
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $email_err = "Este correo ya está registrado.";
                }
            } else {
                $register_err = "Algo salió mal. Por favor, inténtelo de nuevo más tarde.";
            }
            $stmt->close();
        }
    }

    // 2. VALIDAR CONTRASEÑA
    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor, ingrese una contraseña.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $password = trim($_POST["password"]);
    }

    // 3. VALIDAR CONFIRMAR CONTRASEÑA
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Por favor, confirme la contraseña.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Las contraseñas no coinciden.";
        }
    }

    // 4. INSERTAR USUARIO SI NO HAY ERRORES
    if (empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        
        $sql = "INSERT INTO users (email, password) VALUES (?, ?)";
         
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $param_email, $param_password);
            
            $param_email = $email;
            // ¡EL HASHING ES CRÍTICO PARA LA SEGURIDAD! 
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Usa Bcrypt

            if ($stmt->execute()) {
                // Registro exitoso, redirigir al login
                header("location: login.php");
                exit();
            } else {
                $register_err = "Error al intentar registrar el usuario. Inténtelo de nuevo.";
            }
            $stmt->close();
        }
    }
    
    // Cierra la conexión si no se va a redirigir
    // $conn->close(); // No es estrictamente necesario, PHP lo hace al terminar el script
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Mesa Feliz</title>
    <link rel="stylesheet" href="style.css"> 
</head>
<body class="login-page"> 

    <main>
    <div class="container login-page">
        
        <div class="info">
            <h2>Bienvenido a <span class="txt-1">Mesa Feliz</span></h2>
            <p>Regístrate y comienza a coleccionar tus productos favoritos de Digimon, Pokémon y One Piece.</p>
        </div>

        <div class="form">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <h2>Crea tu Cuenta</h2> 
                
                <?php if (!empty($register_err)) echo '<div class="error-message">' . $register_err . '</div>'; ?>

                <div class="inputs">
                    <input type="email" class="box" name="email" placeholder="Ingresa tu correo" value="<?php echo htmlspecialchars($email); ?>" required>
                    <?php if(!empty($email_err)) echo '<div class="error-message">' . $email_err . '</div>'; ?>

                    <input type="password" class="box" name="password" placeholder="Crea una contraseña" required>
                    <?php if(!empty($password_err)) echo '<div class="error-message">' . $password_err . '</div>'; ?>
                    
                    <input type="password" class="box" name="confirm_password" placeholder="Confirma tu contraseña" required>
                    <?php if(!empty($confirm_password_err)) echo '<div class="error-message">' . $confirm_password_err . '</div>'; ?>

                    <input type="submit" value="Registrarme">
                </div>
                
                <p>¿Ya tienes una cuenta? <a href="login.php">Inicia Sesión</a></p>
            </form>
        </div>
    </div>
    </main>
</body>
</html>

