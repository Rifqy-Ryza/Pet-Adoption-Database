<?php
session_start();
if(!isset($_SESSION['adopterID'])){ header("Location:index.php"); exit(); }
include 'config.php';

// Fetch dynamic event name
$stmt = $pdo->query("SELECT event_name FROM settings WHERE id = 1");
$setting = $stmt->fetch();
$eventName = $setting ? $setting['event_name'] : 'Happy Paws Shelter';

// Fetch available pets
$stmt = $pdo->query("SELECT * FROM Pet WHERE status='Available'");
$pets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($eventName) ?> - Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body.dashboard-page { font-family:'Segoe UI',sans-serif; margin:0; background:#f5f7fa; }
        #bg-video { position:fixed; top:0; left:0; width:100%; height:100%; object-fit:cover; z-index:-2; }
        .overlay { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.35); z-index:-1; }

        header.dashboard-header { text-align:center; padding:60px 20px 20px; color:white; }
        header.dashboard-header h1 { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            font-size: 3rem; 
            background: linear-gradient(to right, #FFD700, #FFA500); 
            -webkit-background-clip: text; 
            color: transparent; 
            text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
            margin-bottom:10px;
        }
        header.dashboard-header p { font-size:1.2rem; opacity:0.9; font-weight:600; color:#FFFACD; margin-top:6px; }

        .filters { display:flex; justify-content:center; gap:15px; margin:20px; flex-wrap:wrap; }
        .filters select { padding:10px 15px; border-radius:12px; border:1px solid #ccc; font-size:1rem; }
        .filters button { padding:10px 20px; border:none; border-radius:12px; background: linear-gradient(120deg,#4CAF50,#FF6B6B); color:white; font-weight:700; cursor:pointer; }

        .pet-list { display:flex; flex-wrap:wrap; gap:20px; justify-content:center; margin:30px auto; max-width:1200px; }
        .pet-card { background:white; border-radius:18px; padding:20px; width:230px; box-shadow:0 4px 15px rgba(0,0,0,0.1); text-align:center; transition: transform 0.2s; }
        .pet-card:hover { transform: scale(1.05); }
        .pet-card img { width:100%; height:180px; object-fit:cover; border-radius:12px; margin-bottom:12px; }
        .pet-card h4 { margin:0 0 6px; font-size:1.1rem; font-weight:600; color:#333; }
        .pet-card p { margin:3px 0; font-size:0.95rem; color:#555; }
        .pet-card a { display:inline-block; margin-top:10px; padding:8px 15px; border-radius:12px; background: linear-gradient(120deg,#4CAF50,#FF6B6B); color:white; text-decoration:none; font-weight:600; transition: opacity 0.2s; }
        .pet-card a:hover { opacity:0.85; }

        /* Collapsible panels */
        .top-right-panels { position: fixed; top: 20px; right: 20px; display: flex; gap:10px; z-index: 10; }
        .panel-box { width: 50px; height: 50px; border-radius:12px; background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 8px rgba(0,0,0,0.2); transition: 0.3s; }
        .panel-box:hover { transform: scale(1.1); }
        .panel-content { position: absolute; top: 60px; right: 0; width: 350px; max-height: 0; overflow: hidden; background: #fff; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,0.25); transition: max-height 0.5s ease, padding 0.5s ease; z-index: 11; }
        .panel-content.open { max-height: 700px; padding: 15px; }
        .panel-content h3 { margin-top:0; color:#333; }
        .panel-content p, .panel-content li { color: #000; font-size:0.95rem; line-height:1.4; }
        .panel-content ul { padding-left: 20px; margin: 5px 0 10px; }
        .panel-content li { margin-bottom: 5px; }

        .team-member { display: flex; align-items: center; gap:10px; margin-bottom:8px; }
        .team-member img { width: 35px; height:35px; border-radius:50%; object-fit:cover; border:1px solid #ccc; }
        .panel-title { font-size:1.2rem; font-weight:bold; margin-bottom:10px; color:#333; }
    </style>
</head>
<body class="dashboard-page">
    <video autoplay muted loop id="bg-video">
        <source src="assets/video/background.mp4" type="video/mp4">
    </video>
    <div class="overlay"></div>

    <header class="dashboard-header">
        <h1><?= htmlspecialchars($eventName) ?></h1>
        <p>"Every adoption brings a new spark of joy ✨"</p>
        <a href="my_requests.php" style="color:white; text-decoration:underline; margin-right:15px;">My Requests</a>
        <a href="logout.php" style="color:white; text-decoration:underline;">Logout</a>
    </header>

    <!-- Top-right collapsible panels -->
    <div class="top-right-panels">
        <div class="panel-box" onclick="togglePanel('team')">👥</div>
        <div class="panel-box" onclick="togglePanel('info')">ℹ️</div>
    </div>

    <!-- Team Member Panel -->
    <div class="panel-content" id="team">
        <div class="panel-title">Team Members</div>
        <div class="team-member"><img src="assets/images/rifqy.jpg" alt="Rifqy">Rifqy Nazhan Ryza - Project Manager</div>
        <div class="team-member"><img src="assets/images/franz.jpg" alt="Franz">Franz Linggi Kaiser - Data Analyst</div>
        <div class="team-member"><img src="assets/images/daphne.jpg" alt="Daphne">Daphne Anak Andrus - Support</div>
        <div class="team-member"><img src="assets/images/arabella.jpg" alt="Arabella">Arabella Grace - Support</div>
        <div class="team-member"><img src="assets/images/eunice.jpg" alt="Eunice">Eunice Supang - Support</div>
    </div>

    <!-- Information Panel -->
    <div class="panel-content" id="info">
        <div class="panel-title">Information</div>
        <p>We are a dedicated team of tech enthusiasts who created an innovative Pet Adoption System designed to make adopting your perfect pet easier than ever. Our goal is simple: to bring pets and loving homes together at the tip of your fingers.</p>
        <ul>
            <li>Browse available pets with detailed profiles, photos, and essential information.</li>
            <li>Submit adoption requests seamlessly, without any paperwork hassles.</li>
            <li>Track the status of their applications in real-time.</li>
        </ul>
        <p>Behind the scenes, our team led by a Director and supported by a skilled development team worked tirelessly to ensure the system is user-friendly, efficient, and secure. Every feature is carefully crafted to provide a smooth adoption experience for both pets and adopters.</p>
        <p>With our system, adopting a pet is no longer complicated or time-consuming. A loving companion is just a few clicks away.</p>
    </div>

    <div class="filters">
        <select id="filter-type">
            <option value="">All Types</option>
            <option value="Dog">Dog</option>
            <option value="Cat">Cat</option>
            <option value="Rabbit">Rabbit</option>
            <option value="Bird">Bird</option>
            <option value="Hamster">Hamster</option>
        </select>
        <select id="filter-age">
            <option value="">All Ages</option>
            <?php
            $ages = array_unique(array_map(fn($p)=>$p['age'],$pets));
            sort($ages);
            foreach($ages as $age){ echo "<option value='$age'>$age</option>"; }
            ?>
        </select>
        <button onclick="applyFilter()">Apply Filter</button>
    </div>

    <div class="pet-list">
        <?php foreach($pets as $pet): 
            $type = strtolower($pet['type']);
            $imgFile = "assets/images/".$type."1.jpg";
            if(!file_exists($imgFile)) $imgFile = "assets/images/dog1.jpg";
        ?>
        <div class="pet-card" data-type="<?= htmlspecialchars($pet['type']) ?>" data-age="<?= $pet['age'] ?>">
            <img src="<?= $imgFile ?>" alt="<?= htmlspecialchars($pet['name']) ?>">
            <h4><?= htmlspecialchars($pet['name']) ?> (<?= htmlspecialchars($pet['type']) ?>)</h4>
            <p>Age: <?= $pet['age'] ?></p>
            <p>Status: <?= htmlspecialchars($pet['status']) ?></p>
            <a href="adopt.php?petID=<?= $pet['petID'] ?>">Adopt Me!</a>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
        function applyFilter(){
            const type = document.getElementById('filter-type').value;
            const age = document.getElementById('filter-age').value;
            document.querySelectorAll('.pet-card').forEach(card=>{
                const matchesType = !type || card.dataset.type===type;
                const matchesAge = !age || card.dataset.age===age;
                card.style.display = (matchesType && matchesAge)?'block':'none';
            });
        }

        function togglePanel(id){
            const panel = document.getElementById(id);
            panel.classList.toggle('open');
        }
    </script>
</body>
</html>
