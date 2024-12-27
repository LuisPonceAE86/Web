<?php
session_start();

// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'acosa');
if ($conn->connect_error) die('Error en la conexión: ' . $conn->connect_error);

// Verificación del formulario de inicio de sesión
$error = '';
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Consulta de verificación
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['user'] = $username;
        header('Location: menuadmin.php');
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ACOSA</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <h2>Bienvenido a ACOSA</h2>
            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="post">
    <div class="input-group">
        <label for="username">Usuario</label>
        <div class="input-with-icon">
            <input type="text" id="username" name="username" placeholder="Ingrese su usuario" required>
            <i class="fas fa-user"></i> <!-- Ícono de usuario -->
        </div>
    </div>

    <div class="input-group">
        <label for="password">Contraseña</label>
        <div class="input-with-icon">
            <input type="password" id="password" name="password" placeholder="Ingrese su contraseña" required>
            <i class="fas fa-lock"></i> <!-- Ícono de candado -->
        </div>
    </div>

    <button type="submit" name="login" class="btn-login">Iniciar Sesión</button>
</form>

        </div>
    </div>
</body>
</html>
