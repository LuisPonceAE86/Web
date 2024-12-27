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

// Crear el directorio si no existe
$target_dir = "imagenes/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Manejar subida de imagen y agregar instituto
if (isset($_POST['add_instituto'])) {
    $codigo_barra = $conn->real_escape_string($_POST['codigo_barra']);
    $nombre_instituto = $conn->real_escape_string($_POST['nombre_instituto']);
    $lugar = $conn->real_escape_string($_POST['lugar']);
    
    $imagen_url = null;
    if (!empty($_FILES['imagen']['tmp_name'])) {
        $target_file = $target_dir . basename($_FILES["imagen"]["name"]);
        move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file);
        $imagen_url = $target_file; // Guardamos la URL de la imagen en lugar del BLOB
    }

    $conn->query("INSERT INTO institutos (codigo_barra, nombre_instituto, lugar, imagen_url) VALUES ('$codigo_barra', '$nombre_instituto', '$lugar', '$imagen_url')");
}

// Editar instituto
if (isset($_POST['edit_instituto'])) {
    $codigo_barra = $conn->real_escape_string($_POST['codigo_barra']);
    $nombre_instituto = $conn->real_escape_string($_POST['nombre_instituto']);
    $lugar = $conn->real_escape_string($_POST['lugar']);
    
    if (!empty($_FILES['imagen']['tmp_name'])) {
        $target_file = $target_dir . basename($_FILES["imagen"]["name"]);
        move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file);
        $imagen_url = $target_file; // Guardamos la URL de la imagen en lugar del BLOB
        $conn->query("UPDATE institutos SET nombre_instituto='$nombre_instituto', lugar='$lugar', imagen_url='$imagen_url' WHERE codigo_barra='$codigo_barra'");
    } else {
        $conn->query("UPDATE institutos SET nombre_instituto='$nombre_instituto', lugar='$lugar' WHERE codigo_barra='$codigo_barra'");
    }
}

// Eliminar instituto
if (isset($_GET['delete'])) {
    $codigo_barra = $conn->real_escape_string($_GET['delete']);
    $conn->query("DELETE FROM institutos WHERE codigo_barra='$codigo_barra'");
}

// Filtro de búsqueda
$filtro_codigo_barra = '';
$filtro_nombre = '';
$filtro_lugar = '';
if (isset($_POST['buscar'])) {
    $filtro_codigo_barra = $conn->real_escape_string($_POST['codigo_barra']);
    $filtro_nombre = $conn->real_escape_string($_POST['nombre_instituto']);
    $filtro_lugar = $conn->real_escape_string($_POST['lugar']);
    
    $result = $conn->query("SELECT * FROM institutos WHERE codigo_barra LIKE '%$filtro_codigo_barra%' AND nombre_instituto LIKE '%$filtro_nombre%' AND lugar LIKE '%$filtro_lugar%' ORDER BY lugar, nombre_instituto");
} else {
    $result = $conn->query("SELECT * FROM institutos ORDER BY lugar, nombre_instituto");
}

$institutos = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Listado de Institutos</title>
    <link rel="stylesheet" href="codigosbarra.css">
</head>
<body>
<button onclick="window.location.href='menuadmin.php'" style="margin-bottom: 20px; padding: 10px 20px; font-size: 16px;">Regresar al Menú</button>
    <h1>Gestión de Institutos</h1>

    <!-- Formulario para agregar instituto -->
    <h2>Agregar Instituto</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="text" name="codigo_barra" placeholder="Código de Barra" required>
        <input type="text" name="nombre_instituto" placeholder="Nombre del Instituto" required>
        <input type="text" name="lugar" placeholder="Lugar" required>
        <input type="file" name="imagen" accept="image/*">
        <button type="submit" name="add_instituto">Agregar</button>
    </form>

    <!-- Formulario de búsqueda -->
    <h2>Buscar Institutos</h2>
    <form method="post">
        <input type="text" name="codigo_barra" placeholder="Código de Barra" value="<?= htmlspecialchars($filtro_codigo_barra) ?>">
        <input type="text" name="nombre_instituto" placeholder="Nombre del Instituto" value="<?= htmlspecialchars($filtro_nombre) ?>">
        <input type="text" name="lugar" placeholder="Lugar" value="<?= htmlspecialchars($filtro_lugar) ?>">
        <button type="submit" name="buscar">Buscar</button>
    </form>

    <!-- Listado de institutos -->
    <h2>Listado de Institutos</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Código de Barra</th>
                <th>Nombre del Instituto</th>
                <th>Lugar</th>
                <th>Imagen</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($institutos as $instituto): ?>
                <tr>
                <div class="acciones">
                    <td><?= htmlspecialchars($instituto['codigo_barra']) ?></td>
                    <td><?= htmlspecialchars($instituto['nombre_instituto']) ?></td>
                    <td><?= htmlspecialchars($instituto['lugar']) ?></td>
                    <td><?php if (!empty($instituto['imagen_url'])): ?>
                        <img src="<?= htmlspecialchars($instituto['imagen_url']) ?>" width="50">
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post" enctype="multipart/form-data" style="display:inline;">
                            <input type="hidden" name="codigo_barra" value="<?= htmlspecialchars($instituto['codigo_barra']) ?>">
                            <input type="text" name="nombre_instituto" value="<?= htmlspecialchars($instituto['nombre_instituto']) ?>" required>
                            <input type="text" name="lugar" value="<?= htmlspecialchars($instituto['lugar']) ?>" required>
                            <input type="file" name="imagen" accept="image/*">
                            <button type="submit" name="edit_instituto" class="editar-btn">Editar</button>
                            <a href="?delete=<?= htmlspecialchars($instituto['codigo_barra']) ?>" onclick="return confirm('¿Estás seguro de eliminar este instituto?');" class="eliminar-btn">Eliminar</a>
                        </form>
                       
                    </td>
                    </div>
                </tr>

            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
