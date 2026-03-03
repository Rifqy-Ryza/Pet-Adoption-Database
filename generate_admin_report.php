<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Admin access only.");
}
include 'config.php';
require('fpdf/fpdf.php');

// Fetch dynamic shelter/event name
$stmt = $pdo->query("SELECT event_name FROM settings WHERE id = 1");
$setting = $stmt->fetch();
$shelterName = $setting ? $setting['event_name'] : 'Animal Shelter';

// Fetch all pets
$petStmt = $pdo->query("SELECT name, type, breed, age, status, created_at FROM Pet ORDER BY created_at DESC");
$pets = $petStmt->fetchAll();

// Fetch ALL adoption decisions
$reqStmt = $pdo->query("
    SELECT p.name AS pet_name, a.name AS adopter_name, a.email AS adopter_email,
           ar.status, ar.requestDate
    FROM AdoptionRequest ar
    JOIN Pet p ON ar.petID = p.petID
    JOIN Adopter a ON ar.adopterID = a.adopterID
    ORDER BY ar.requestDate DESC
");
$allRequests = $reqStmt->fetchAll();

// Fetch ONLY APPROVED adoptions
$adoptedStmt = $pdo->query("
    SELECT p.name AS pet_name, p.type AS pet_type, p.breed,
           a.name AS adopter_name, a.email AS adopter_email,
           ar.requestDate
    FROM AdoptionRequest ar
    JOIN Pet p ON ar.petID = p.petID
    JOIN Adopter a ON ar.adopterID = a.adopterID
    WHERE ar.status = 'Approved'
    ORDER BY ar.requestDate DESC
");
$adoptedPets = $adoptedStmt->fetchAll();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 12, strtoupper($shelterName) . ' - ADMIN REPORT', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, 'Generated on: ' . date('F j, Y \a\t g:i A'), 0, 1, 'C');
$pdf->Ln(10);

// Section 1: All Pets
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, '1. All Registered Pets', 0, 1);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(40, 7, 'Pet Name', 1);
$pdf->Cell(25, 7, 'Type', 1);
$pdf->Cell(30, 7, 'Breed', 1);
$pdf->Cell(15, 7, 'Age', 1);
$pdf->Cell(25, 7, 'Status', 1);
$pdf->Cell(50, 7, 'Added On', 1);
$pdf->Ln();

foreach ($pets as $pet) {
    $pdf->Cell(40, 6, substr($pet['name'], 0, 25), 1);
    $pdf->Cell(25, 6, $pet['type'], 1);
    $pdf->Cell(30, 6, $pet['breed'] ?: 'N/A', 1);
    $pdf->Cell(15, 6, $pet['age'] ?? 'N/A', 1);
    $pdf->Cell(25, 6, $pet['status'], 1);
    $pdf->Cell(50, 6, $pet['created_at'], 1);
    $pdf->Ln();
}

$pdf->Ln(10);

// Section 2: All Requests
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, '2. All Adoption Requests (All Statuses)', 0, 1);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(25, 7, 'Pet', 1);
$pdf->Cell(70, 7, 'Adopter Name', 1);
$pdf->Cell(50, 7, 'Adopter Email', 1);
$pdf->Cell(25, 7, 'Status', 1);
$pdf->Cell(30, 7, 'Date', 1);
$pdf->Ln();

foreach ($allRequests as $req) {
    $pdf->Cell(25, 6, substr($req['pet_name'], 0, 20), 1);
    $pdf->Cell(70, 6, substr($req['adopter_name'], 0, 25), 1);
    $pdf->Cell(50, 6, substr($req['adopter_email'], 0, 30), 1);
    $pdf->Cell(25, 6, $req['status'], 1);
    $pdf->Cell(30, 6, $req['requestDate'], 1);
    $pdf->Ln();
}

$pdf->Ln(10);

// Section 3: Adopted Pets
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, '3. Successfully Adopted Pets (Approved)', 0, 1);
$pdf->SetFont('Arial', '', 9);

if ($adoptedPets) {
    $pdf->Cell(25, 7, 'Pet Name', 1);
    $pdf->Cell(25, 7, 'Type', 1);
    $pdf->Cell(30, 7, 'Breed', 1);
    $pdf->Cell(70, 7, 'Adopter Name', 1);
    $pdf->Cell(50, 7, 'Adopter Email', 1);
    $pdf->Cell(30, 7, 'Adoption Date', 1);
    $pdf->Ln();

    foreach ($adoptedPets as $pet) {
        $pdf->Cell(25, 6, substr($pet['pet_name'], 0, 20), 1);
        $pdf->Cell(25, 6, $pet['pet_type'], 1);
        $pdf->Cell(30, 6, $pet['breed'] ?: 'N/A', 1);
        $pdf->Cell(70, 6, substr($pet['adopter_name'], 0, 25), 1);
        $pdf->Cell(50, 6, substr($pet['adopter_email'], 0, 30), 1);
        $pdf->Cell(30, 6, $pet['requestDate'], 1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(0, 6, 'No pets have been adopted yet.', 0, 1);
}

$pdf->Ln(15);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 6, "This report is generated automatically from the {$shelterName} Management System.", 0, 1, 'C');
$pdf->Cell(0, 6, '© ' . date('Y') . " {$shelterName} - For Internal Use Only", 0, 1, 'C');

$pdf->Output('D', 'Admin_Report_' . date('Ymd_His') . '.pdf');
?>