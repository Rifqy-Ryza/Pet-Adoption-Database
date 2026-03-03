<?php
session_start();
if (!isset($_SESSION['adopterID'])) {
    die("Access denied.");
}
include 'config.php';

$requestID = $_GET['requestID'] ?? 0;

// Fetch approved adoption data
$stmt = $pdo->prepare("
    SELECT a.name AS adopter_name, p.name AS pet_name, ar.requestDate
    FROM AdoptionRequest ar
    JOIN Adopter a ON ar.adopterID = a.adopterID
    JOIN Pet p ON ar.petID = p.petID
    WHERE ar.requestID = ? AND ar.status = 'Approved' AND ar.adopterID = ?
");
$stmt->execute([$requestID, $_SESSION['adopterID']]);
$data = $stmt->fetch();

if (!$data) {
    die("No approved adoption found.");
}

// Fetch dynamic shelter/event name
$stmt = $pdo->query("SELECT event_name FROM settings WHERE id = 1");
$setting = $stmt->fetch();
$shelterName = $setting ? $setting['event_name'] : 'Animal Shelter';

$adopterName = htmlspecialchars($data['adopter_name']);
$petName      = htmlspecialchars($data['pet_name']);
$date         = date('F j, Y', strtotime($data['requestDate']));

// Load FPDF
require('fpdf/fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 12, 'PET ADOPTION CERTIFICATE', 0, 1, 'C');
$pdf->Ln(8);
$pdf->SetFont('Arial', '', 12);

$text = "This certificate proudly acknowledges that $adopterName has taken the meaningful step of adopting an animal, demonstrating care and responsibility.\n\n";
$text .= "The beloved companion who has found a new home through this adoption is affectionately named $petName.\n\n";
$text .= "This adoption was formally completed and recognized on the $date, marking the beginning of a lasting bond.\n\n";
$text .= "This certificate is presented as a lasting symbol of compassion and responsibility. By choosing adoption, the adopter has given $petName not only a safe home, but also love, care, and a future filled with kindness. This act reflects the values of empathy, respect for life, and dedication to the welfare of animals.\n\n";
$text .= "May this bond between $adopterName and $petName grow stronger each day, bringing joy, companionship, and cherished memories for years to come.";

$pdf->SetLeftMargin(25);
$pdf->SetRightMargin(25);
$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 6, $text, 0, 'C');

$pdf->Ln(15);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, "Issued by: $shelterName", 0, 1, 'C');
$pdf->Cell(0, 10, 'This is generated certificate and doesnt need signature', 0, 1, 'C');

$pdf->Output('D', 'Adoption_Certificate_' . $requestID . '.pdf');
?>