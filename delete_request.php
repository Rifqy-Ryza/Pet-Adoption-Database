<?php
// delete_request.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
include 'config.php';

if (!isset($_GET['requestID']) || !is_numeric($_GET['requestID'])) {
    $_SESSION['message'] = "❌ Invalid request ID.";
    header("Location: admin_dashboard.php");
    exit();
}

$requestID = (int)$_GET['requestID'];

try {
    // Optional: If you want to revert pet status when deleting an Approved request
    $stmt = $pdo->prepare("SELECT petID, status FROM AdoptionRequest WHERE requestID = ?");
    $stmt->execute([$requestID]);
    $req = $stmt->fetch();

    if ($req) {
        // Delete the request
        $pdo->prepare("DELETE FROM AdoptionRequest WHERE requestID = ?")->execute([$requestID]);

        // If it was Approved, set pet back to Available
        if ($req['status'] === 'Approved') {
            $pdo->prepare("UPDATE Pet SET status = 'Available' WHERE petID = ?")->execute([$req['petID']]);
        }

        $_SESSION['message'] = "✅ Request deleted. Pet status updated if needed.";
    } else {
        $_SESSION['message'] = "⚠️ Request not found.";
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "❌ Database error during deletion.";
}

header("Location: admin_dashboard.php");
exit();
?>