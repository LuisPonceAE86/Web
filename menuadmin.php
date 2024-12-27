

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Menú De Administrador</title>
    <link rel="stylesheet" href="menuadmin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div id="menu">
         <!-- Logo principal -->
        <h1>Menú De Administrador</h1>
        <ul>
            <li><a href="utiles.php"><i class="fas fa-users"></i>Agregar utiles</a></li>
            <li><a href="listas.php"><i class="fas fa-chart-line"></i>Ver Listas</a></li>
            <li><a href="codigosbarra.php"><i class="fas fa-chart-line"></i>Ver Codigo de barra</a></li>

           <li><a onclick="location.href='logout.php'" class="logout"><i class="fas fa-sign-out-alt"></i>Cerrar sesión</a></li>
        </ul>
    </div>
</body>
</html>
