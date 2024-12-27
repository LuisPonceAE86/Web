function createDocument() {
    const instituteName = document.getElementById('instituteName').value;
    const selectedOptions = Array.from(document.getElementById('documentType').selectedOptions).map(option => option.text);
    const quantity = document.getElementById('quantity').value;
    const includeQR = document.getElementById('includeQR').checked ? 'Sí' : 'No';
    const includeBarcode = document.getElementById('includeBarcode').checked ? 'Sí' : 'No';

    if (!instituteName || selectedOptions.length === 0 || !quantity) {
        alert('Por favor complete todos los campos obligatorios.');
        return;
    }

    const table = document.getElementById('documentTable').getElementsByTagName('tbody')[0];
    const newRow = table.insertRow();

    newRow.innerHTML = `
        <td>${instituteName}</td>
        <td>${selectedOptions.join(', ')}</td>
        <td>${quantity}</td>
        <td>${includeQR}</td>
        <td>${includeBarcode}</td>
        <td>
            <button onclick="editRow(this)">Editar</button>
            <button onclick="deleteRow(this)">Eliminar</button>
        </td>
    `;

    document.getElementById('documentForm').reset();
}

function editRow(button) {
    const row = button.parentElement.parentElement;
    const instituteName = row.cells[0].innerText;
    const documentTypes = row.cells[1].innerText.split(', ');
    const quantity = row.cells[2].innerText;
    const includeQR = row.cells[3].innerText === 'Sí';
    const includeBarcode = row.cells[4].innerText === 'Sí';

    document.getElementById('instituteName').value = instituteName;
    Array.from(document.getElementById('documentType').options).forEach(option => {
        option.selected = documentTypes.includes(option.text);
    });
    document.getElementById('quantity').value = quantity;
    document.getElementById('includeQR').checked = includeQR;
    document.getElementById('includeBarcode').checked = includeBarcode;

    row.remove();
}

function deleteRow(button) {
    const row = button.parentElement.parentElement;
    row.remove();
}

function showDownloadOptions() {
    const optionsDiv = document.getElementById('downloadOptions');
    optionsDiv.style.display = 'block';
}

function downloadDocument(format) {
    const tableData = Array.from(document.getElementById('documentTable').getElementsByTagName('tbody')[0].rows).map(row => {
        return {
            instituteName: row.cells[0].innerText,
            documentTypes: row.cells[1].innerText,
            quantity: row.cells[2].innerText,
            includeQR: row.cells[3].innerText,
            includeBarcode: row.cells[4].innerText
        };
    });

    if (format === 'pdf') {
        generatePDF(tableData);
    } else if (format === 'excel') {
        generateExcel(tableData);
    }

    const optionsDiv = document.getElementById('downloadOptions');
    optionsDiv.style.display = 'none';
}

function generatePDF(data) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    let y = 10;
    data.forEach((row, index) => {
        doc.text(`Documento ${index + 1}:`, 10, y);
        doc.text(`Nombre del Instituto: ${row.instituteName}`, 10, y + 10);
        doc.text(`Tipos de Documento: ${row.documentTypes}`, 10, y + 20);
        doc.text(`Cantidad: ${row.quantity}`, 10, y + 30);
        doc.text(`Incluye QR: ${row.includeQR}`, 10, y + 40);
        doc.text(`Incluye Código de Barras: ${row.includeBarcode}`, 10, y + 50);
        y += 60;
    });

    doc.save('documentos.pdf');
}

function generateExcel(data) {
    const worksheet = XLSX.utils.json_to_sheet(data);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Documentos");
    XLSX.writeFile(workbook, 'documentos.xlsx');
}
