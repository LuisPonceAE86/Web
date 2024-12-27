<?php
session_start();
require_once 'fpdf/fpdf.php'; // Incluir FPDF

// Verificación de inicio de sesión
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Conexión a la base de datos
$conn = new mysqli('localhost', 'root', '', 'acosa');
if ($conn->connect_error) die('Error en la conexión: ' . $conn->connect_error);

// Agregar útil escolar
if (isset($_POST['add_util'])) {
    $categoria = $conn->real_escape_string($_POST['categoria']);
    $nombre = $conn->real_escape_string($_POST['nombre']);

    $conn->query("INSERT INTO utiles (categoria, nombre) VALUES ('$categoria', '$nombre')");
}

// Editar útil escolar
if (isset($_POST['edit_util'])) {
    $id = intval($_POST['id']);
    $categoria = $conn->real_escape_string($_POST['categoria']);
    $nombre = $conn->real_escape_string($_POST['nombre']);

    $conn->query("UPDATE utiles SET categoria='$categoria', nombre='$nombre' WHERE id=$id");
}

// Eliminar útil escolar
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM utiles WHERE id=$id");
}

// Filtro de búsqueda
$filtro_categoria = '';
$filtro_nombre = '';
if (isset($_POST['buscar'])) {
    $filtro_categoria = $conn->real_escape_string($_POST['categoria']);
    $filtro_nombre = $conn->real_escape_string($_POST['nombre']);
    
    // Se modificó la consulta para buscar por categoría o nombre
    $result = $conn->query("SELECT * FROM utiles WHERE categoria LIKE '%$filtro_categoria%' AND nombre LIKE '%$filtro_nombre%' ORDER BY categoria, nombre");
} else {
    $result = $conn->query("SELECT * FROM utiles ORDER BY categoria, nombre");
}

$utiles = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Listado de Útiles</title>
    <link rel="stylesheet" href="utiles.css">
</head>
<body>

    <h1>Gestión de Útiles Escolares</h1>

    <!-- Formulario para agregar útil -->
    <h2>Agregar Útil Escolar</h2>
    <form method="post">
        <input type="text" name="categoria" placeholder="Categoría" required>
        <input type="text" name="nombre" placeholder="Nombre del Útil" required>
        <button type="submit" name="add_util">Agregar</button>
    </form>

    <!-- Formulario de búsqueda -->
    <h2>Buscar Útiles</h2>
    <form method="post">
        <input type="text" name="categoria" placeholder="Categoría" value="<?= htmlspecialchars($filtro_categoria) ?>">
        <input type="text" name="nombre" placeholder="Nombre del Útil" value="<?= htmlspecialchars($filtro_nombre) ?>">
        <button type="submit" name="buscar">Buscar</button>
    </form>
    <br>
<button onclick="window.location.href='menuadmin.php'" style="margin-bottom: 20px; padding: 10px 20px; font-size: 16px;">Regresar al Menú</button>
    <!-- Listado de útiles escolares -->
    <h2>Listado de Útiles</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Categoría</th>
                <th>Nombre</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($utiles as $util): ?>
                <tr>
                    <td><?= $util['id'] ?></td>
                    <td><?= htmlspecialchars($util['categoria']) ?></td>
                    <td><?= htmlspecialchars($util['nombre']) ?></td>
                    <td>
                        <div class="acciones">
                        <!-- Botón para editar -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $util['id'] ?>">
                            <input type="text" name="categoria" value="<?= htmlspecialchars($util['categoria']) ?>" required>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($util['nombre']) ?>" required>
                            <button type="submit" name="edit_instituto" class="editar-btn">Editar</button>
                            <a href="?delete=<?= $util['id'] ?>" onclick="return confirm('¿Estás seguro de eliminar este útil?');" class="eliminar-btn">Eliminar</a>

                        </form>

                        <!-- Botón para eliminar -->
                        
            </div> 
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
