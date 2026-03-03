<!-- my_requests.php -->
<?php
session_start();
if (!isset($_SESSION['adopterID'])) {
    header("Location: index.php");
    exit();
}
include 'config.php';

$adopterID = $_SESSION['adopterID'];

// Fetch all adoption requests with pet info
$stmt = $pdo->prepare("
    SELECT ar.requestID, ar.status, ar.requestDate,
           p.name AS pet_name, p.type AS pet_type
    FROM AdoptionRequest ar
    JOIN Pet p ON ar.petID = p.petID
    WHERE ar.adopterID = ?
    ORDER BY ar.requestDate DESC
");
$stmt->execute([$adopterID]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Adoption Requests</title>
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
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background: rgba(255, 245, 238, 0.95); /* light warm */
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        h2 {
            text-align: center;
            color: #4B2E2E; /* darker chocolate */
            margin-bottom: 10px;
        }

        .quote {
            text-align: center;
            font-style: italic;
            color: #6B4226;
            margin-bottom: 25px;
        }

        a.back-btn {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #fff;
            background: #8B4513;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: bold;
        }

        a.back-btn:hover {
            background: #5C2A0C;
        }

        .request-card {
            border: 2px solid #8B4513;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 12px;
            background: #FFF0E0; /* soft chocolate card */
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
            transition: transform 0.2s;
        }

        .request-card:hover {
            transform: translateY(-5px);
        }

        h4 {
            margin: 0 0 10px 0;
            color: #5C2A0C;
        }

        p {
            margin: 5px 0;
            color: #4B2E2E;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: bold;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: bold;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: bold;
        }

        .btn {
            display: inline-block;
            padding: 8px 15px;
            margin-top: 10px;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.95em;
            font-weight: bold;
        }

        .btn-certificate {
            background: #8B4513;
            color: white;
        }
        .btn-certificate:hover {
            background: #5C2A0C;
        }
    </style>
</head>
<body>
    <video autoplay muted loop id="bg-video">
        <source src="assets/video/background.mp4" type="video/mp4">
    </video>
    <div class="overlay"></div>

    <div class="container">
        <h2>My Adoption Requests</h2>
        <p class="quote">“Every paw has a story. Make yours a happy one.” 🐾</p>
        <a href="dashboard.php" class="back-btn">← Back to Dashboard</a>

        <?php if ($requests): ?>
            <?php foreach ($requests as $req): ?>
                <div class="request-card">
                    <h4><?= htmlspecialchars($req['pet_name']) ?> (<?= htmlspecialchars($req['pet_type']) ?>)</h4>
                    <p><strong>Date:</strong> <?= $req['requestDate'] ?></p>
                    <p><strong>Status:</strong> 
                        <?php
                        $status = $req['status'];
                        if ($status === 'Pending') {
                            echo "<span class='status-pending'>🟡 Pending</span>";
                        } elseif ($status === 'Approved') {
                            echo "<span class='status-approved'>🟢 Approved</span>";
                            echo ' <a href="generate_certificate.php?requestID=' . $req['requestID'] . '" class="btn btn-certificate">📄 Download Certificate</a>';
                        } elseif ($status === 'Rejected') {
                            echo "<span class='status-rejected'>🔴 Rejected</span>";
                        }
                        ?>
                    </p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>You haven't submitted any adoption requests yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
