<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
include 'config.php';

if (!isset($_GET['requestID']) || !isset($_GET['action'])) {
    die("Invalid action.");
}

$requestID = (int)$_GET['requestID'];
$action = $_GET['action'];

try {
    if ($action === 'approve') {
        $pdo->prepare("UPDATE AdoptionRequest SET status = 'Approved' WHERE requestID = ?")->execute([$requestID]);
        $pdo->prepare("UPDATE Pet SET status = 'Adopted' WHERE petID = (SELECT petID FROM AdoptionRequest WHERE requestID = ?)")->execute([$requestID]);
    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE AdoptionRequest SET status = 'Rejected' WHERE requestID = ?")->execute([$requestID]);
        $pdo->prepare("UPDATE Pet SET status = 'Available' WHERE petID = (SELECT petID FROM AdoptionRequest WHERE requestID = ?)")->execute([$requestID]);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

header("Location: admin_dashboard.php");
exit();
?>