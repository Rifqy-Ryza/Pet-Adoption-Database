<?php
// delete_admin.php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
include 'config.php';

if (!isset($_GET['adminID']) || !is_numeric($_GET['adminID'])) {
    $_SESSION['message'] = "❌ Invalid admin ID.";
    header("Location: admin_dashboard.php");
    exit();
}

$adminID = (int)$_GET['adminID'];

// Prevent self-deletion
if ($adminID == ($_SESSION['adminID'] ?? 0)) {
    $_SESSION['message'] = "❌ You cannot delete your own account.";
    header("Location: admin_dashboard.php");
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM Admin WHERE adminID = ?");
    $stmt->execute([$adminID]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = "✅ Admin deleted successfully.";
    } else {
        $_SESSION['message'] = "⚠️ Admin not found.";
    }
} catch (PDOException $e) {
    $_SESSION['message'] = "❌ Cannot delete: this admin may be in use.";
}

header("Location: admin_dashboard.php");
exit();
?>