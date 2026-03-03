<?php
session_start();
include 'config.php';

if ($_POST['action'] ?? '' === 'add') {
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $age = (int)($_POST['age'] ?? 0);
    $breed = trim($_POST['breed'] ?? '');
    if ($name && $type) {
        $pdo->prepare("INSERT INTO Pet (name, type, age, breed, status) VALUES (?, ?, ?, ?, 'Available')")->execute([$name, $type, $age, $breed]);
        $_SESSION['message'] = "✅ Pet added successfully!";
        header("Location: admin_pets.php");
        exit();
    }
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM Pet WHERE petID = ?")->execute([$_GET['delete']]);
    $_SESSION['message'] = "✅ Pet deleted.";
    header("Location: admin_pets.php");
    exit();
}

$pets = $pdo->query("SELECT * FROM Pet ORDER BY created_at DESC")->fetchAll();
include 'admin_nav.php';
?>

<h2>🐾 Manage Pets</h2>

<?php if (!empty($_SESSION['message'])): ?>
    <div style="padding:12px; background:#d4edda; color:#155724; margin-bottom:20px; border-radius:6px;">
        <?= htmlspecialchars($_SESSION['message']) ?>
        <?php unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<form method="POST" style="background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:25px;">
    <input type="hidden" name="action" value="add">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
        <input type="text" name="name" placeholder="Pet Name" required>
        <input type="text" name="type" placeholder="Type (e.g., Dog)" required>
        <input type="number" name="age" placeholder="Age" min="0">
        <input type="text" name="breed" placeholder="Breed (optional)">
    </div>
    <button type="submit" style="margin-top:10px; padding:8px 20px; background:#28a745; color:white; border:none; border-radius:4px;">➕ Add Pet</button>
</form>

<?php if ($pets): ?>
    <?php foreach ($pets as $pet): ?>
        <div style="border:1px solid #ddd; padding:15px; margin:12px 0; border-radius:8px; background:#fafafa;">
            <strong><?= htmlspecialchars($pet['name']) ?></strong> (<?= htmlspecialchars($pet['type']) ?>, <?= $pet['age'] ?> yrs)<br>
            Breed: <?= htmlspecialchars($pet['breed'] ?: 'N/A') ?> | Status: <strong><?= $pet['status'] ?></strong><br>
            Added: <?= $pet['created_at'] ?>
            <br>
            <a href="?delete=<?= $pet['petID'] ?>" onclick="return confirm('Delete \"<?= addslashes($pet['name']) ?>\" and all its requests?');" style="color:red; text-decoration:underline;">❌ Delete Pet</a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No pets registered yet.</p>
<?php endif; ?>

<?php include 'admin_footer.php'; ?>