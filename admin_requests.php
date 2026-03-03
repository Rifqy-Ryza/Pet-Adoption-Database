<?php
session_start();
include 'config.php';

if (isset($_GET['action']) && isset($_GET['requestID'])) {
    $requestID = (int)$_GET['requestID'];
    if ($_GET['action'] === 'approve') {
        $pdo->prepare("UPDATE AdoptionRequest SET status = 'Approved' WHERE requestID = ?")->execute([$requestID]);
        $pdo->prepare("UPDATE Pet SET status = 'Adopted' WHERE petID = (SELECT petID FROM AdoptionRequest WHERE requestID = ?)")->execute([$requestID]);
    } elseif ($_GET['action'] === 'reject') {
        $pdo->prepare("UPDATE AdoptionRequest SET status = 'Rejected' WHERE requestID = ?")->execute([$requestID]);
        $pdo->prepare("UPDATE Pet SET status = 'Available' WHERE petID = (SELECT petID FROM AdoptionRequest WHERE requestID = ?)")->execute([$requestID]);
    }
    $_SESSION['message'] = "✅ Request updated.";
    header("Location: admin_requests.php");
    exit();
}

if (isset($_GET['delete'])) {
    $requestID = (int)$_GET['delete'];
    // Revert pet status if approved
    $stmt = $pdo->prepare("SELECT petID, status FROM AdoptionRequest WHERE requestID = ?");
    $stmt->execute([$requestID]);
    $req = $stmt->fetch();
    if ($req && $req['status'] === 'Approved') {
        $pdo->prepare("UPDATE Pet SET status = 'Available' WHERE petID = ?")->execute([$req['petID']]);
    }
    $pdo->prepare("DELETE FROM AdoptionRequest WHERE requestID = ?")->execute([$requestID]);
    $_SESSION['message'] = "✅ Request deleted.";
    header("Location: admin_requests.php");
    exit();
}

$requests = $pdo->query("
    SELECT ar.requestID, ar.status, ar.requestDate,
           p.name AS pet_name, p.type AS pet_type,
           a.name AS adopter_name, a.email AS adopter_email
    FROM AdoptionRequest ar
    JOIN Pet p ON ar.petID = p.petID
    JOIN Adopter a ON ar.adopterID = a.adopterID
    ORDER BY ar.requestDate DESC
")->fetchAll();

include 'admin_nav.php';
?>

<h2>📬 Adoption Requests</h2>

<?php if (!empty($_SESSION['message'])): ?>
    <div style="padding:12px; background:#d4edda; color:#155724; margin-bottom:20px; border-radius:6px;">
        <?= htmlspecialchars($_SESSION['message']) ?>
        <?php unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<?php if ($requests): ?>
    <?php foreach ($requests as $req): ?>
        <div style="border:1px solid #ddd; padding:15px; margin:12px 0; border-radius:8px; background:#fafafa;">
            <strong><?= htmlspecialchars($req['pet_name']) ?></strong> (<?= $req['pet_type'] ?>)<br>
            Adopter: <?= htmlspecialchars($req['adopter_name']) ?> (<?= $req['adopter_email'] ?>)<br>
            Status: 
            <?php if ($req['status'] === 'Approved'): ?>
                <span style="color:green; font-weight:bold;">🟢 Approved</span>
            <?php elseif ($req['status'] === 'Rejected'): ?>
                <span style="color:red; font-weight:bold;">🔴 Rejected</span>
            <?php else: ?>
                <span style="color:orange; font-weight:bold;">🟡 Pending</span>
            <?php endif; ?>
            <br>Date: <?= $req['requestDate'] ?>
            <br>
            <?php if ($req['status'] === 'Pending'): ?>
                <a href="?action=approve&requestID=<?= $req['requestID'] ?>" style="margin-right:10px; padding:6px 12px; background:#28a745; color:white; text-decoration:none; border-radius:4px;">Approve</a>
                <a href="?action=reject&requestID=<?= $req['requestID'] ?>" style="padding:6px 12px; background:#dc3545; color:white; text-decoration:none; border-radius:4px;">Reject</a>
            <?php endif; ?>
            <a href="?delete=<?= $req['requestID'] ?>" onclick="return confirm('Delete this request record?');" style="margin-left:10px; color:red; text-decoration:underline;">❌ Delete</a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No adoption requests yet.</p>
<?php endif; ?>

<?php include 'admin_footer.php'; ?>