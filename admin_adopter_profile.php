<!-- admin_adopter_profile.php -->
<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
include 'config.php';

$adopterID = $_GET['adopterID'] ?? 0;
if (!is_numeric($adopterID)) {
    die("Invalid adopter ID.");
}

$stmt = $pdo->prepare("SELECT name, email, phone, password, address FROM Adopter WHERE adopterID = ?");
$stmt->execute([$adopterID]);
$adopter = $stmt->fetch();

if (!$adopter) {
    die("Adopter not found.");
}

// Fetch adoption history
$stmt = $pdo->prepare("
    SELECT p.name AS pet_name, p.type AS pet_type, p.age, p.breed, ar.status, ar.requestDate
    FROM AdoptionRequest ar
    JOIN Pet p ON ar.petID = p.petID
    WHERE ar.adopterID = ? AND ar.status = 'Approved'
    ORDER BY ar.requestDate DESC
");
$stmt->execute([$adopterID]);
$history = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Adopter Profile - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            color: #022B3A;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .video-bg {
            position: fixed;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -2;
            filter: brightness(0.7);
        }

        .overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 40, 70, 0.3);
            z-index: -1;
        }

        .container {
            max-width: 800px;
            margin: 60px auto;
            padding: 35px;
            background: rgba(255, 255, 255, 0.85);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 60, 120, 0.25);
            backdrop-filter: blur(10px);
        }

        h2 {
            text-align: center;
            color: #004E89;
            font-size: 2rem;
            margin-bottom: 25px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.1);
        }

        .profile-card {
            background: linear-gradient(145deg, #E1F5FE, #B3E5FC);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #81D4FA;
            margin-bottom: 18px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .profile-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 18px rgba(0,0,0,0.15);
        }

        .profile-label {
            font-weight: bold;
            color: #01579B;
            margin-bottom: 4px;
            font-size: 1.05em;
        }

        .profile-value {
            color: #023047;
            font-size: 1em;
            line-height: 1.5;
        }

        .back-btn {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 25px;
            background: linear-gradient(120deg, #0288D1, #26C6DA, #80DEEA);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .back-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        small {
            color: #01579B;
        }

        /* History table */
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .history-table th, .history-table td {
            padding: 10px;
            border: 1px solid rgba(0,0,0,0.1);
            text-align: left;
        }
        .history-table th {
            background: linear-gradient(90deg, #80DEEA, #26C6DA);
            color: white;
        }
    </style>
</head>
<body>

<video autoplay muted loop class="video-bg">
    <source src="assets/video/ocean_background.mp4" type="video/mp4">
</video>
<div class="overlay"></div>

<div class="container">
    <h2>👤 Adopter Profile</h2>

    <div class="profile-card">
        <div class="profile-label">Full Name:</div>
        <div class="profile-value"><?= htmlspecialchars($adopter['name']) ?></div>
    </div>

    <div class="profile-card">
        <div class="profile-label">Email:</div>
        <div class="profile-value"><?= htmlspecialchars($adopter['email']) ?></div>
    </div>

    <div class="profile-card">
        <div class="profile-label">Phone Number:</div>
        <div class="profile-value"><?= htmlspecialchars($adopter['phone']) ?></div>
    </div>

    <div class="profile-card">
        <div class="profile-label">Address:</div>
        <div class="profile-value"><?= nl2br(htmlspecialchars($adopter['address'] ?: 'Not provided')) ?></div>
    </div>

    <div class="profile-card">
        <div class="profile-label">Password:</div>
        <div class="profile-value" style="font-family: monospace; font-size: 0.9em; color: #01579B;">
            <?= htmlspecialchars($adopter['password']) ?>
        </div>
        <small>(Stored as plain text)</small>
    </div>

    <?php if ($history): ?>
    <div class="profile-card">
        <div class="profile-label">Adoption History:</div>
        <table class="history-table">
            <thead>
                <tr>
                    <th>Pet Name</th>
                    <th>Type</th>
                    <th>Age</th>
                    <th>Breed</th>
                    <th>Adoption Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $h): ?>
                <tr>
                    <td><?= htmlspecialchars($h['pet_name']) ?></td>
                    <td><?= htmlspecialchars($h['pet_type']) ?></td>
                    <td><?= htmlspecialchars($h['age']) ?></td>
                    <td><?= htmlspecialchars($h['breed'] ?: 'N/A') ?></td>
                    <td><?= htmlspecialchars($h['requestDate']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <a href="admin_dashboard.php" class="back-btn">← Back to Dashboard</a>
</div>
</body>
</html>
