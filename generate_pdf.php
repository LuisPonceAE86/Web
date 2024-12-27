<?php
require('fpdf/fpdf.php');
require('phpqrcode/qrlib.php');

// Manejo de errores
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

// Datos enviados desde el formulario
$title = $_POST['title'];
$filter = $_POST['filter'];
$extraComment = trim($_POST['extra_comment']); // Elimina espacios extra

// Comentarios predeterminados
$comments = [
    "opcion1" => "Este es un comentario predeterminado para la Opción 1.",
    "opcion2" => "Este es un comentario predeterminado para la Opción 2."
];

// Generar un identificador único
$uniqueId = uniqid();

// Crear el código QR
$qrFilePath = "qr_$uniqueId.png";
QRcode::png($uniqueId, $qrFilePath, QR_ECLEVEL_L, 4);

// Crear el PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);

// Título
$pdf->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1', $title), 0, 1, 'C');
$pdf->Ln(10);

// Comentarios predeterminados
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 10, iconv('UTF-8', 'ISO-8859-1', $comments[$filter]));
$pdf->Ln(10);

// Comentario adicional (si se proporcionó)
if (!empty($extraComment)) {
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell(0, 10, 'Comentario adicional:', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->MultiCell(0, 10, iconv('UTF-8', 'ISO-8859-1', $extraComment));
    $pdf->Ln(10);
}

// Agregar el código QR
$pdf->Image($qrFilePath, 10, $pdf->GetY(), 50, 50);
$pdf->Ln(60);

// Código único
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, "ID único: $uniqueId", 0, 1, 'C');

// Eliminar el QR temporal
unlink($qrFilePath);

// Limpiar el buffer de salida
ob_clean();

// Descargar el archivo PDF
$pdf->Output("D", "documento_$uniqueId.pdf");
