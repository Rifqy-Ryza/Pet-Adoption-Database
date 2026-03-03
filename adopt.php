<!-- adopt.php -->
<?php
session_start();
if (!isset($_SESSION['adopterID'])) {
    header("Location: index.php");
    exit();
}
include 'config.php';

if (!isset($_GET['petID']) || !is_numeric($_GET['petID'])) {
    die("Invalid pet.");
}

$petID = (int)$_GET['petID'];

// Check if pet is still available
$stmt = $pdo->prepare("SELECT * FROM Pet WHERE petID = ? AND status = 'Available'");
$stmt->execute([$petID]);
$pet = $stmt->fetch();

if (!$pet) {
    die("Pet not available for adoption.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Insert adoption request
    $stmt = $pdo->prepare("INSERT INTO AdoptionRequest (petID, adopterID, status) VALUES (?, ?, 'Pending')");
    $stmt->execute([$petID, $_SESSION['adopterID']]);
    
    // Update pet status to Pending
    $pdo->prepare("UPDATE Pet SET status = 'Pending' WHERE petID = ?")->execute([$petID]);
    
    echo "<p style='color:green; text-align:center; font-weight:bold;'>Adoption request submitted! Shelter will contact you soon.</p>";
    echo "<p style='text-align:center;'><a href='dashboard.php' class='btn'>Back to Dashboard</a></p>";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Adopt <?= htmlspecialchars($pet['name']) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #D2691E; /* chocolate background */
            margin: 0;
            padding: 0;
        }

        #bg-video {
            position: fixed;
            top: 0;
            left: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            object-fit: cover;
            opacity: 0.4;
        }

        .overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.25);
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 600px;
            margin: 60px auto;
            padding: 30px;
            background: rgba(255, 245, 238, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            text-align: center;
        }

        h2 {
            color: #4B2E2E;
            margin-bottom: 15px;
        }

        p {
            color: #5C2A2A;
            font-size: 1.1em;
            margin: 10px 0;
        }

        .btn {
            display: inline-block;
            background: #8B4513;
            color: white;
            padding: 10px 20px;
            margin: 15px 5px 0 5px;
            border-radius: 8px;
            font-weight: bold;
            text-decoration: none;
            font-size: 1em;
            transition: all 0.2s;
        }

        .btn:hover {
            background: #5C2A0C;
        }

        form p {
            margin: 20px 0 10px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <video autoplay muted loop id="bg-video">
        <source src="assets/video/background.mp4" type="video/mp4">
    </video>
    <div class="overlay"></div>

    <div class="container">
        <h2>Confirm Adoption: <?= htmlspecialchars($pet['name']) ?></h2>
        <p>Type: <?= htmlspecialchars($pet['type']) ?> | Age: <?= htmlspecialchars($pet['age']) ?></p>
        <form method="POST">
            <p>Are you sure you want to adopt this pet?</p>
            <button type="submit" class="btn">Yes, Submit Request</button>
        </form>
        <a href="dashboard.php" class="btn">Cancel</a>
    </div>
</body>
</html>
