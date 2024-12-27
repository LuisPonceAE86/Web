<?php
// Conexión a la base de datos
$host = 'localhost';
$dbname = 'acosa';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    die();
}

// Obtener los institutos (agregando la imagen URL)
$queryInstitutos = "SELECT codigo_barra, nombre_instituto, lugar, imagen_url FROM institutos ORDER BY nombre_instituto";
$stmtInstitutos = $pdo->prepare($queryInstitutos);
$stmtInstitutos->execute();
$institutos = $stmtInstitutos->fetchAll(PDO::FETCH_ASSOC);

// Obtener los útiles ordenados
$queryUtiles = "SELECT id, nombre, categoria FROM utiles ORDER BY categoria, nombre";
$stmtUtiles = $pdo->prepare($queryUtiles);
$stmtUtiles->execute();
$utiles = $stmtUtiles->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Lista de Útiles</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Generador de Lista de Útiles 2025</h1>
    <div class="container">
        <!-- Campo de búsqueda dinámica para institutos -->
        <div class="input-group">
            <label for="busquedaInstituto">Buscar instituto:</label>
            <input type="text" id="busquedaInstituto" placeholder="Escriba el nombre, lugar o código de barras" oninput="buscarInstituto()">
            <div class="search-results" id="resultadosInstitutos" style="display: none;"></div>
        </div>

        <div class="input-group">
            <label for="codigoBarra">Código de barra del instituto:</label>
            <input type="text" id="codigoBarra" placeholder="Código de barra" readonly>
        </div>
        <div class="input-group">
            <label for="nombreCentro">Nombre del centro educativo:</label>
            <input type="text" id="nombreCentro" placeholder="Nombre del instituto" readonly>
        </div>
        <div class="input-group">
            <label for="lugarCentro">Lugar del instituto:</label>
            <input type="text" id="lugarCentro" placeholder="Lugar del instituto" readonly>
        </div>

        <!-- Campo de búsqueda dinámica para materiales -->
        <div class="input-group">
            <label for="busquedaMaterial">Buscar material:</label>
            <input type="text" id="busquedaMaterial" placeholder="Escriba el nombre o la categoría del material" oninput="buscarMaterial()">
            <div class="search-results" id="resultadosMateriales" style="display: none;"></div>
        </div>
        <!-- Campo donde se mostrará el material seleccionado -->
        <input type="text" id="nombreMaterial" placeholder="Material seleccionado" readonly>

        <div class="input-group">
            <label for="nivelEducativo">Nivel educativo:</label>
            <input type="text" id="nivelEducativo" placeholder="Ej: PRE-BÁSICO">
        </div>

        <div class="input-group">
            <label for="marca">Marca (opcional):</label>
            <input type="text" id="marca" placeholder="Ej: Genial|Deli">
        </div>
        <div class="input-group">
            <label for="cantidad">Cantidad:</label>
            <input type="number" id="cantidad" placeholder="Ej: 1">
        </div>
        <div class="input-group">
            <label for="comentarios">Comentarios adicionales:</label>
            <input type="text" id="comentarios" placeholder="Ej: negro, azul y rojo">
        </div>

        <button onclick="agregarMaterial()">Agregar material</button>
        <button onclick="descargarLista()">Descargar lista</button>
        <button onclick="limpiarFormulario()">Limpiar Formulario</button>
        <button onclick="window.location.href='menuadmin.php'" style="margin-bottom: 20px; padding: 10px 20px; font-size: 16px;">Regresar al Menú</button>

        <div class="container">
    <!-- Vista previa de materiales -->
    <div id="vista-materiales">
        <h3>Vista previa de materiales agregados:</h3>
        <ul id="lista-materiales">
            <li>No hay materiales seleccionados.</li>
        </ul>
    </div>

    <!-- Vista previa del código de barras -->
    <div id="vista-codigo-barras">
        <h3>Vista previa del código de barras:</h3>
        <div id="imagen-instituto"></div>
    </div>
</div>
    </div>

    <script>
 document.addEventListener("DOMContentLoaded", function () {
    // Datos provenientes del servidor
    let institutos = <?php echo json_encode($institutos); ?>;
    let materialesDisponibles = <?php echo json_encode($utiles); ?>;
    let materialesSeleccionados = JSON.parse(localStorage.getItem('materialesSeleccionados')) || []; // Almacena los materiales seleccionados.

    // Recuperar datos de la institución del almacenamiento local
    const institucionGuardada = JSON.parse(localStorage.getItem('institucionSeleccionada'));
    if (institucionGuardada) {
        document.getElementById("codigoBarra").value = institucionGuardada.codigo_barra;
        document.getElementById("nombreCentro").value = institucionGuardada.nombre_instituto;
        document.getElementById("lugarCentro").value = institucionGuardada.lugar || "Sin lugar";
        const imagenURL = institucionGuardada.imagen_url || "imagenes/default.png";
        document.getElementById("imagen-instituto").innerHTML = `<img src="${imagenURL}" alt="Imagen del instituto" width="150">`;
    }

    // Buscar institutos
    function buscarInstituto() {
        const termino = document.getElementById("busquedaInstituto").value.toLowerCase();
        const resultados = institutos.filter(inst =>
            inst.nombre_instituto.toLowerCase().includes(termino) ||
            inst.lugar?.toLowerCase().includes(termino) ||
            inst.codigo_barra.includes(termino)
        );

        const resultadosDiv = document.getElementById("resultadosInstitutos");
        resultadosDiv.innerHTML = "";
        if (resultados.length > 0) {
            resultadosDiv.style.display = "block";
            resultados.forEach(inst => {
                const div = document.createElement("div");
                div.textContent = `${inst.nombre_instituto} (${inst.lugar || 'Sin lugar'}) - ${inst.codigo_barra}`;
                div.onclick = () => seleccionarInstituto(inst);
                resultadosDiv.appendChild(div);
            });
        } else {
            resultadosDiv.style.display = "none";
        }
    }

    // Seleccionar instituto
    function seleccionarInstituto(instituto) {
        document.getElementById("codigoBarra").value = instituto.codigo_barra;
        document.getElementById("nombreCentro").value = instituto.nombre_instituto;
        document.getElementById("lugarCentro").value = instituto.lugar || "Sin lugar";
        document.getElementById("busquedaInstituto").value = "";
        document.getElementById("resultadosInstitutos").style.display = "none";

        // Mostrar la imagen asociada al instituto desde la base de datos
        const imagenURL = instituto.imagen_url || "imagenes/default.png";
        document.getElementById("imagen-instituto").innerHTML = `<img src="${imagenURL}" alt="Imagen del instituto" width="150">`;

        // Guardar la institución seleccionada en el almacenamiento local
        localStorage.setItem('institucionSeleccionada', JSON.stringify(instituto));
    }

    // Buscar materiales
    function buscarMaterial() {
        const termino = document.getElementById("busquedaMaterial").value.toLowerCase();
        const resultados = materialesDisponibles.filter(mat =>
            mat.nombre?.toLowerCase().includes(termino) ||
            mat.categoria?.toLowerCase().includes(termino)
        );

        const resultadosDiv = document.getElementById("resultadosMateriales");
        resultadosDiv.innerHTML = "";
        if (resultados.length > 0) {
            resultadosDiv.style.display = "block";
            resultados.forEach(mat => {
                const div = document.createElement("div");
                div.textContent = `${mat.nombre} - ${mat.categoria}`;
                div.onclick = () => seleccionarMaterial(mat);
                resultadosDiv.appendChild(div);
            });
        } else {
            resultadosDiv.style.display = "none";
        }
    }

    // Seleccionar material
    function seleccionarMaterial(material) {
        document.getElementById("nombreMaterial").value = material.nombre;
        document.getElementById("resultadosMateriales").style.display = "none";
    }

    // Agregar material a la lista de materiales seleccionados
    function agregarMaterial() {
        const nombreMaterial = document.getElementById("nombreMaterial").value;
        const nivelEducativo = document.getElementById("nivelEducativo").value;
        const marca = document.getElementById("marca").value;
        const cantidad = document.getElementById("cantidad").value;
        const comentarios = document.getElementById("comentarios").value;

        // Validación
        if (!nombreMaterial || !cantidad || cantidad <= 0) {
            alert("Por favor, seleccione un material y especifique una cantidad válida.");
            return;
        }

        // Crear objeto material
        const material = {
            nombre: nombreMaterial,
            nivelEducativo: nivelEducativo,
            marca: marca,
            cantidad: parseInt(cantidad, 10),
            comentarios: comentarios
        };

        // Agregar a la lista y actualizar vista previa
        materialesSeleccionados.push(material);
        localStorage.setItem('materialesSeleccionados', JSON.stringify(materialesSeleccionados));
        mostrarVistaPrevia();

        // Limpiar campos
        document.getElementById("nombreMaterial").value = '';
        document.getElementById("nivelEducativo").value = '';
        document.getElementById("marca").value = '';
        document.getElementById("cantidad").value = '';
        document.getElementById("comentarios").value = '';
    }

    // Mostrar la vista previa de materiales seleccionados
    function mostrarVistaPrevia() {
        const listaPrevia = document.getElementById("lista-materiales"); // Ajustado el ID
        listaPrevia.innerHTML = ''; // Limpiar vista previa

        if (materialesSeleccionados.length === 0) {
            listaPrevia.innerHTML = '<li>No hay materiales seleccionados.</li>';
            return;
        }

        // Crear elementos de lista
        let currentNivel = '';
        materialesSeleccionados.forEach(material => {
            if (material.nivelEducativo !== currentNivel) {
                currentNivel = material.nivelEducativo;
                const encabezado = document.createElement("h3");
                encabezado.textContent = currentNivel;
                listaPrevia.appendChild(encabezado);
            }
            const li = document.createElement("li");
            li.textContent = `${material.cantidad} x ${material.nombre} ${
                material.marca ? `- ${material.marca}` : ""
            } ${material.comentarios ? `(${material.comentarios})` : ""}`;
            listaPrevia.appendChild(li);
        });
    }

    // Descargar lista en Excel
    function descargarLista() {
        const nombreCentro = document.getElementById("nombreCentro").value;
        const codigoBarra = document.getElementById("codigoBarra").value;
        const nivelEducativo = document.getElementById("nivelEducativo").value; // Obtener el grado

        if (!nombreCentro || !codigoBarra) {
            alert("Por favor, complete los datos del instituto antes de descargar.");
            return;
        }

        // Verifica que los materiales seleccionados estén correctos
        console.log(materialesSeleccionados); // Verifica si contiene más de un material

        const datos = {
            nombreCentro: nombreCentro,
            codigoBarra: codigoBarra,
            nivelEducativo: nivelEducativo, // Incluir el grado en los datos
            materiales: materialesSeleccionados
        };

        fetch('descargar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datos)
        })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.style.display = "none";
            a.href = url;
            a.download = `${nombreCentro}_${codigoBarra}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
        })
        .catch(error => console.error("Error al descargar el archivo:", error));
    }

    // Limpiar formulario
    function limpiarFormulario() {
        document.getElementById("codigoBarra").value = '';
        document.getElementById("nombreCentro").value = '';
        document.getElementById("lugarCentro").value = '';
        document.getElementById("busquedaInstituto").value = '';
        document.getElementById("imagen-instituto").innerHTML = '';
        document.getElementById("nombreMaterial").value = '';
        document.getElementById("nivelEducativo").value = '';
        document.getElementById("marca").value = '';
        document.getElementById("cantidad").value = '';
        document.getElementById("comentarios").value = '';
        materialesSeleccionados = [];
        localStorage.removeItem('materialesSeleccionados');
        localStorage.removeItem('institucionSeleccionada');
        mostrarVistaPrevia();
    }
    // Vincular funciones a eventos globales
    window.buscarInstituto = buscarInstituto;
    window.seleccionarInstituto = seleccionarInstituto;
    window.buscarMaterial = buscarMaterial;
    window.seleccionarMaterial = seleccionarMaterial;
    window.agregarMaterial = agregarMaterial;
    window.mostrarVistaPrevia = mostrarVistaPrevia;
    window.descargarLista = descargarLista;
    window.limpiarFormulario = limpiarFormulario;

    // Mostrar vista previa al cargar la página
    mostrarVistaPrevia();
});
</script>
</body>
</html>