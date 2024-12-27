<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Obtener datos de la solicitud
    $nombreCentro = $data['nombreCentro'] ?? 'C.E.B. PERFECTO H. BOBADILLA';
    $materiales = $data['materiales'] ?? [];
    $codigoBarra = $data['codigoBarra'] ?? '';
    $nivelEducativo = $data['nivelEducativo'] ?? '';

    // Obtener la URL de la imagen del código de barras desde la base de datos
    $query = "SELECT imagen_url FROM institutos WHERE codigo_barra = :codigo_barra";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':codigo_barra', $codigoBarra);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        echo "Error: No se encontró el código de barras en la base de datos.";
        exit;
    }

    $barcodeImageUrl = $result['imagen_url'];

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Encabezados principales
    $sheet->mergeCells('A1:D1');
    $sheet->setCellValue('A1', '¡45% DE DESCUENTO!');
    $sheet->getStyle('A1')->applyFromArray([
        'font' => ['bold' => true, 'size' => 14],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);

    $sheet->mergeCells('A2:D2');
    $sheet->setCellValue('A2', $nombreCentro);

    $sheet->mergeCells('A3:D3');
    $sheet->setCellValue('A3', 'LISTA DE MATERIALES 2025');

   // Combina las celdas desde A4 hasta D4
    $sheet->mergeCells('A4:D4');
    $sheet->setCellValue('A4', $data['nivelEducativo']); // Aquí se muestra el grado
    
    $headerStyle = [
        'font' => ['bold' => true, 'size' => 12],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ];
    $sheet->getStyle('A2:D4')->applyFromArray($headerStyle);

    // Inicializar filas y columnas
    $row = 6;

    // Procesar los materiales
    foreach ($materiales as $index => $material) {
        $cantidad = $material['cantidad'] ?? '';
        $nombre = $material['nombre'] ?? '';
        $comentarios = $material['comentarios'] ?? '';
        $marca = $material['marca'] ?? ''; // Agregar marca si está presente

        $comentariosTexto = $comentarios ? " ($comentarios)" : ''; // Solo agrega paréntesis si hay comentarios

        // Determinar las columnas a usar
        if ($index % 2 == 0) {
            $colA = 'A';
            $colB = 'B';
        } else {
            $colA = 'C';
            $colB = 'D';
        }

        // Establecer valores en las celdas
        $sheet->setCellValue($colA . $row, $cantidad);
        $sheet->getStyle($colA . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue($colB . $row, $nombre . ($marca ? " - $marca" : "") . $comentariosTexto);

        // Incrementar la fila para el siguiente material
        if ($index % 2 != 0) {
            $row++;
        }
    }

    // Ajustar anchos de columna
    $sheet->getColumnDimension('A')->setWidth(4);
    $sheet->getColumnDimension('B')->setWidth(60);
    $sheet->getColumnDimension('C')->setWidth(4);
    $sheet->getColumnDimension('D')->setWidth(60);

    // Aplicar estilo a las celdas con bordes
    $sheet->getStyle('A6:D' . ($row - 1))->applyFromArray([
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
        ],
        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
    ]);

    // Texto adicional
    $row = max($row, 16); // Asegurar espacio para el texto después de los productos
    $sheet->mergeCells('A' . ($row + 1) . ':D' . ($row + 1));
    $sheet->setCellValue('A' . ($row + 1), 'USO CORRECTO DEL UNIFORME DIARIO YA ESTABLECIDO.');
    $sheet->mergeCells('A' . ($row + 2) . ':D' . ($row + 2));
    $sheet->setCellValue('A' . ($row + 2), 'Todos los materiales deben venir rotulados con el nombre completo del alumno.');
    $sheet->mergeCells('A' . ($row + 3) . ':D' . ($row + 3));
    $sheet->setCellValue('A' . ($row + 3), '¡RECUERDA PEDIR QUE ESCANEEN EL CÓDIGO DE BARRA EN CAJA!');
    $sheet->getStyle('A' . ($row + 1) . ':D' . ($row + 3))->applyFromArray([
        'font' => ['italic' => true],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    ]);

    // Insertar el código de barras desde la URL
    if ($barcodeImageUrl) {
        $barcodeImage = new Drawing();
        $barcodeImage->setName('Código de barras');
        $barcodeImage->setDescription('Código de barras');
        $barcodeImage->setPath($barcodeImageUrl); // Ruta del archivo de código de barras desde la base de datos
        $barcodeImage->setHeight(50);
        $barcodeImage->setWidth(200);
        $barcodeImage->setCoordinates('A' . ($row + 4)); // Posición del código de barras
        $barcodeImage->setWorksheet($sheet);
    } else {
        echo "Error: No se encontró la imagen del código de barras.";
        exit;
    }

    // Guardar y enviar el archivo
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Lista_' . urlencode($nombreCentro) . '.xlsx"');
    header('Cache-Control: max-age=0');

    if (ob_get_contents()) {
        ob_end_clean();
    }
    flush();

    $writer->save('php://output');
    exit;
} else {
    echo "No se recibieron datos para generar el archivo.";
}
?>