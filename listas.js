let institutos = <?php echo json_encode($institutos); ?>;
let materialesDisponibles = <?php echo json_encode($utiles); ?>;

// Función para buscar institutos
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

// Función para seleccionar un instituto
function seleccionarInstituto(instituto) {
    document.getElementById("codigoBarra").value = instituto.codigo_barra;
    document.getElementById("nombreCentro").value = instituto.nombre_instituto;
    document.getElementById("lugarCentro").value = instituto.lugar || "Sin lugar";
    document.getElementById("busquedaInstituto").value = "";
    document.getElementById("resultadosInstitutos").style.display = "none";

    // Mostrar la imagen del instituto desde la URL almacenada
    const imagenURL = instituto.imagen_url ? instituto.imagen_url : "imagenes/default.png"; // URL de la imagen
    const imgTag = `<img src="${imagenURL}" alt="Imagen del instituto" width="150" />`;
    document.getElementById("vista-previa").innerHTML = imgTag;
}

// Función para buscar materiales
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

// Función para seleccionar un material
function seleccionarMaterial(material) {
    // Aquí debes agregar el material a la lista de materiales seleccionados.
    console.log(material); // Para depuración, muestra el material seleccionado en la consola

    // Opcional: agregar el material seleccionado a un campo de entrada o una lista
    // Ejemplo: colocar el nombre del material en un campo de texto
    document.getElementById("nombreMaterial").value = material.nombre;

    // Si quieres mostrar detalles adicionales del material, también puedes hacerlo aquí
    const materialDetalles = `Nombre: ${material.nombre}\nCategoría: ${material.categoria}`;
    alert(materialDetalles); // Muestra los detalles en un cuadro de alerta o en el área deseada.

    // Opcional: ocultar los resultados de búsqueda después de seleccionar un material
    document.getElementById("resultadosMateriales").style.display = "none";
}
let materialesSeleccionados = [];  // Esta variable almacenará los materiales seleccionados.

// Función para agregar un material a la lista de materiales seleccionados y mostrarlo en la vista previa.
function agregarMaterial() {
    const nombreMaterial = document.getElementById("nombreMaterial").value;
    const nivelEducativo = document.getElementById("nivelEducativo").value;
    const marca = document.getElementById("marca").value;
    const cantidad = document.getElementById("cantidad").value;
    const comentarios = document.getElementById("comentarios").value;

    // Validar que se haya seleccionado un material
    if (!nombreMaterial || !cantidad) {
        alert("Por favor, seleccione un material y especifique la cantidad.");
        return;
    }

    // Crear un objeto de material con los datos
    const material = {
        nombre: nombreMaterial,
        nivelEducativo: nivelEducativo,
        marca: marca,
        cantidad: cantidad,
        comentarios: comentarios
    };

    // Agregar el material a la lista
    materialesSeleccionados.push(material);

    // Mostrar los materiales en la vista previa
    mostrarVistaPrevia();

    // Limpiar los campos después de agregar el material
    document.getElementById("nombreMaterial").value = '';
    document.getElementById("nivelEducativo").value = '';
    document.getElementById("marca").value = '';
    document.getElementById("cantidad").value = '';
    document.getElementById("comentarios").value = '';
}

// Función para mostrar los materiales en la vista previa
function mostrarVistaPrevia() {
    const listaPrevia = document.getElementById("lista-previa");
    listaPrevia.innerHTML = '';  // Limpiar la lista antes de agregar los nuevos materiales

    // Recorrer los materiales seleccionados y agregarlos a la lista de vista previa
    materialesSeleccionados.forEach((material, index) => {
        const li = document.createElement("li");
        li.textContent = `${material.cantidad} x ${material.nombre} - ${material.nivelEducativo} - ${material.marca} ${material.comentarios ? '(' + material.comentarios + ')' : ''}`;
        listaPrevia.appendChild(li);
    });
}

// Función para descargar la lista en formato Excel
function descargarLista() {
    const nombreCentro = document.getElementById("nombreCentro").value;
    const codigoBarra = document.getElementById("codigoBarra").value;

    const datos = {
        nombreCentro: nombreCentro,
        codigoBarra: codigoBarra,
        materiales: materialesSeleccionados
    };

    // Realizar una petición AJAX para descargar el archivo Excel
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
        a.download = 'Lista_Materiales.xlsx';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
    })
    .catch(error => {
        console.error("Error al descargar el archivo:", error);
    });
}
