<?php
// delete_pet.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
include 'config.php';

if (!isset($_GET['petID']) || !is_numeric($_GET['petID'])) {
    $_SESSION['message'] = "❌ Invalid pet ID.";
    header("Location: admin_dashboard.php");
    exit();
}

$petID = (int)$_GET['petID'];

try {
    $stmt = $pdo->prepare("DELETE FROM Pet WHERE petID = ?");
    $stmt->execute([$petID]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = "✅ Pet and all its requests deleted.";
    } else {
        $_SESSION['message'] = "⚠️ Pet not found.";
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "❌ Cannot delete: database error.";
}

header("Location: admin_dashboard.php");
exit();
?>