<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
include 'config.php';

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// === Update Event Name ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event_name'])) {
    $eventName = trim($_POST['event_name']);
    if ($eventName) {
        $stmt = $pdo->prepare("UPDATE settings SET event_name = ? WHERE id = 1");
        $stmt->execute([$eventName]);
        $_SESSION['message'] = "✅ Shelter name updated!";
        header("Location: admin_dashboard.php");
        exit();
    }
}

// === Add New Admin ===
// === Add New Admin ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_admin'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $name = trim($_POST['admin_name']);
    $password = $_POST['password']; // stored as plain text

    if ($username && $email && $password) {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT adminID FROM Admin WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->rowCount() > 0) {
                $message = "⚠️ Admin with this username or email already exists.";
            } else {
                // Insert new admin without hashing
                $stmt = $pdo->prepare("INSERT INTO Admin (username, email, name, password) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $name, $password]);
                $_SESSION['message'] = "✅ New admin added!";
                header("Location: admin_dashboard.php");
                exit();
            }
        } catch (PDOException $e) {
            $message = "❌ Database error: " . $e->getMessage();
        }
    } else {
        $message = "⚠️ All admin fields are required.";
    }
}

// === Add New Pet ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_pet'])) {
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $age = (int)$_POST['age'];
    $breed = trim($_POST['breed'] ?? '');

    if ($name && $type) {
        $stmt = $pdo->prepare("INSERT INTO Pet (name, type, age, breed, status) VALUES (?, ?, ?, ?, 'Available')");
        $stmt->execute([$name, $type, $age, $breed]);
        $_SESSION['message'] = "✅ Pet added successfully!";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $message = "⚠️ Pet name and type are required.";
    }
}

// === Fetch Event Name ===
$stmt = $pdo->query("SELECT event_name FROM settings WHERE id = 1");
$setting = $stmt->fetch();
$eventName = $setting ? $setting['event_name'] : 'Happy Paws Animal Shelter';

// === Fetch Admins ===
$stmt = $pdo->query("SELECT adminID, username, name, email FROM Admin ORDER BY name");
$admins = $stmt->fetchAll();

// === Fetch Pending Requests ===
$stmt = $pdo->prepare("
    SELECT ar.requestID, ar.adopterID, ar.requestDate,
           p.name AS pet_name, p.type AS pet_type,
           a.name AS adopter_name, a.email AS adopter_email
    FROM AdoptionRequest ar
    JOIN Pet p ON ar.petID = p.petID
    JOIN Adopter a ON ar.adopterID = a.adopterID
    WHERE ar.status = 'Pending'
    ORDER BY ar.requestDate ASC
");
$stmt->execute();
$pendingRequests = $stmt->fetchAll();

// === Fetch All Pets ===
$stmt = $pdo->query("SELECT petID, name, type, age, breed, status, created_at FROM Pet ORDER BY created_at DESC");
$allPets = $stmt->fetchAll();

// === Fetch All Requests ===
$stmt = $pdo->prepare("
    SELECT ar.requestID, ar.status, ar.requestDate, p.name AS pet_name, p.type AS pet_type, a.name AS adopter_name
    FROM AdoptionRequest ar
    JOIN Pet p ON ar.petID = p.petID
    JOIN Adopter a ON ar.adopterID = a.adopterID
    ORDER BY ar.requestDate DESC
");
$stmt->execute();
$allRequests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Admin Dashboard - <?= htmlspecialchars($eventName) ?></title>
<link rel="stylesheet" href="style.css">
<style>
    :root {
        --ocean-1:#0077b6;
        --ocean-2:#00b4d8;
        --ocean-3:#48cae4;
        --panel-bg:rgba(224,247,250,0.92);
        --card-bg:rgba(255,255,255,0.12);
    }
    *{box-sizing:border-box;}
    body{margin:0;padding:0;font-family:'Segoe UI',sans-serif;background:#e6f7fb;color:#012a4a;}
    #bg-video{position:fixed;top:0;left:0;width:100%;height:100%;object-fit:cover;z-index:-2;}
    .overlay{position:fixed;inset:0;background:rgba(0,30,60,0.25);z-index:-1;}
    .wrap{max-width:1200px;margin:32px auto;padding:24px;background:var(--panel-bg);border-radius:16px;box-shadow:0 12px 32px rgba(0,0,0,0.14);position:relative;z-index:1;}
    header.top{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:22px;}
    .top-left{display:flex;align-items:center;gap:14px;}
    .brand{font-weight:800;font-size:1.6rem;color:var(--ocean-1);}
    .subtitle{color:#034f84;font-weight:600;}
    .top-right a{color:#d90429;text-decoration:none;font-weight:700;margin-left:12px;}
    .message{margin:12px 0;padding:14px;border-radius:10px;font-weight:700;box-shadow:0 3px 8px rgba(0,0,0,0.12);}
    .msg-success{background:#d4f1f9;color:#005b79;}
    .msg-warning{background:#fff4d6;color:#70550a;}
    .msg-error{background:#ffd5db;color:#7a0016;}
    .toolbar{display:flex;gap:12px;flex-wrap:wrap;align-items:center;margin-bottom:18px;}
    .report-btn{padding:10px 18px;background:linear-gradient(135deg,var(--ocean-2),var(--ocean-1));color:white;border-radius:10px;text-decoration:none;font-weight:700;box-shadow:0 6px 14px rgba(0,0,0,0.12);}
    .section{background:var(--card-bg);border-radius:14px;margin-bottom:20px;overflow:hidden;border:1px solid rgba(255,255,255,0.08);}
    .section-header{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;background:linear-gradient(90deg,rgba(0,0,0,0.03),rgba(0,0,0,0.02));cursor:pointer;}
    .section-title{font-weight:700;color:var(--ocean-1);font-size:1.1rem;}
    .toggle-btn{background:linear-gradient(120deg,var(--ocean-1),var(--ocean-2));color:white;border:none;padding:8px 14px;border-radius:8px;cursor:pointer;font-weight:700;}
.section-body {
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    transition: max-height 0.4s ease, opacity 0.5s ease;
    padding: 0 16px; /* add horizontal padding */
}
.section-body.open {
    opacity: 1;
    padding: 16px; /* more vertical padding when open */
}
.card {
    padding: 16px; /* increase from 12px to 16px for vertical space */
    border-radius: 12px;
    background: linear-gradient(180deg, rgba(255,255,255,0.12), rgba(255,255,255,0.06));
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
}
    form.inline-form{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;align-items:center;}
    input[type="text"],input[type="email"],input[type="number"],input[type="password"],select,textarea{padding:10px;border-radius:8px;border:1px solid rgba(0,0,0,0.08);background:white;font-size:0.95rem;}
    form button{padding:10px 14px;border-radius:8px;border:none;background:linear-gradient(120deg,var(--ocean-1),var(--ocean-2));color:white;font-weight:700;cursor:pointer;}
    .cards-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
    @media (max-width:980px){.cards-grid{grid-template-columns:repeat(2,1fr);}}
    @media (max-width:640px){.cards-grid{grid-template-columns:1fr;}}
    .card{background:linear-gradient(180deg,rgba(255,255,255,0.12),rgba(255,255,255,0.08));padding:14px;border-radius:12px;border:1px solid rgba(255,255,255,0.05);box-shadow:0 6px 18px rgba(0,0,0,0.08);}
    .card strong{display:block;color:var(--ocean-2);margin-bottom:6px;}
    .card small{color:#013a63;display:block;margin-top:6px;}
    .action-links{margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;}
    .action-links a{padding:8px 10px;border-radius:8px;text-decoration:none;font-weight:700;color:white;}
    .approve{background:#00b074;}
    .reject{background:#d90429;}
    .profile{background:var(--ocean-1);}
    .card .action-links a.delete {color: #d90429 !important; /* force red color */background: transparent;border: 2px solid rgba(217,4,41,0.12);padding: 6px 8px;border-radius: 8px;text-decoration: none;}
    .table{width:100%;border-collapse:collapse;margin-top:10px;background:rgba(255,255,255,0.1);border-radius:8px;overflow:hidden;}
    .table th,.table td{padding:10px;border-bottom:1px solid rgba(255,255,255,0.08);text-align:left;color:#012a4a;}
    .table th{background:linear-gradient(90deg,var(--ocean-3),var(--ocean-2));color:white;}
    .status-pill{padding:6px 10px;border-radius:999px;font-weight:700;display:inline-block;}
    .s-pending{background:#ffd166;color:#663c00;}
    .s-approved{background:#d4edda;color:#155724;}
    .s-rejected{background:#f8d7da;color:#721c24;}
    .filters-inline{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:12px;}
    .filters-inline select{padding:8px;border-radius:8px;border:1px solid rgba(0,0,0,0.08);}
</style>
</head>
<body>
<video autoplay muted loop id="bg-video">
    <source src="assets/video/ocean_background.mp4" type="video/mp4">
</video>
<div class="overlay"></div>

<div class="wrap">
<header class="top">
    <div class="top-left">
        <div class="brand">🐾 <?= htmlspecialchars($eventName) ?></div>
        <div class="subtitle">Admin Dashboard — Manage pets, requests & admins</div>
    </div>
  <div class="top-right">
    <a href="generate_admin_report.php" style="display:inline-block;padding:10px 18px;border-radius:10px;background:#00b4d8;color:white;text-decoration:none;font-weight:700;margin-right:8px;">📄 Generate Report</a>
    <a href="logout.php" style="display:inline-block;padding:10px 18px;border-radius:10px;background:#d90429;color:white;text-decoration:none;font-weight:700;">Logout</a>
</div>
</header>

<?php if($message): ?>
<div class="message <?= strpos($message,'✅')!==false?'msg-success':(strpos($message,'⚠️')!==false?'msg-warning':'msg-error') ?>">
    <?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>

<!-- Event Name -->
<section class="section">
    <div class="section-header"><div class="section-title">🏢 Shelter / Event Name</div><button class="toggle-btn" data-target="event-section">Show</button></div>
    <div id="event-section" class="section-body">
        <form method="POST" class="inline-form">
            <input type="text" name="event_name" value="<?= htmlspecialchars($eventName) ?>" required>
            <button type="submit" name="update_event_name">Update Name</button>
        </form>
    </div>
</section>

<!-- Add Pet -->
<section class="section">
    <div class="section-header"><div class="section-title">➕ Add New Pet</div><button class="toggle-btn" data-target="add-pet-section">Show</button></div>
    <div id="add-pet-section" class="section-body">
        <form method="POST" class="inline-form">
            <input type="text" name="name" placeholder="Pet Name" required>
            <input type="text" name="type" placeholder="Type (Dog, Cat...)" required>
            <input type="number" name="age" min="0" placeholder="Age" required>
            <input type="text" name="breed" placeholder="Breed (optional)">
            <div></div>
            <button type="submit" name="add_pet">Add Pet</button>
        </form>
    </div>
</section>

<!-- Add Admin -->
<section class="section">
    <div class="section-header"><div class="section-title">👥 Add New Admin</div><button class="toggle-btn" data-target="add-admin-section">Show</button></div>
    <div id="add-admin-section" class="section-body">
        <form method="POST" class="inline-form">
            <input type="text" name="username" placeholder="Admin Username" required>
            <input type="email" name="email" placeholder="Admin Email" required>
            <input type="text" name="admin_name" placeholder="Full Name" required>
            <input type="password" name="password" placeholder="Password" required>
            <div></div>
            <button type="submit" name="add_admin">Add Admin</button>
        </form>
    </div>
</section>

<!-- Manage Admins -->
<section class="section">
    <div class="section-header"><div class="section-title">🔐 Manage Admin Accounts</div><button class="toggle-btn" data-target="manage-admins-section">Show</button></div>
    <div id="manage-admins-section" class="section-body">
        <div class="cards-grid">
        <?php foreach($admins as $admin): ?>
            <div class="card">
                <strong><?= htmlspecialchars($admin['name']) ?></strong>
                <small>@<?= htmlspecialchars($admin['username']) ?></small>
                <small><?= htmlspecialchars($admin['email']) ?></small>
                <div class="action-links">
                    <?php if ($admin['adminID'] != ($_SESSION['adminID'] ?? 0)): ?>
                    <a href="delete_admin.php?adminID=<?= $admin['adminID'] ?>" class="delete" onclick="return confirm('Delete admin <?= addslashes($admin['name']) ?>?');">❌ Delete</a>
                    <?php else: ?>
                    <small style="color:#013a63">(You)</small>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Pending Requests -->
<section class="section">
    <div class="section-header"><div class="section-title">⏳ Pending Adoption Requests</div><button class="toggle-btn" data-target="pending-requests-section">Show</button></div>
    <div id="pending-requests-section" class="section-body">
        <div class="cards-grid">
        <?php if($pendingRequests): ?>
            <?php foreach($pendingRequests as $req): ?>
                <div class="card">
                    <strong><?= htmlspecialchars($req['pet_name']) ?> (<?= htmlspecialchars($req['pet_type']) ?>)</strong>
                    <small>Adopter: <?= htmlspecialchars($req['adopter_name']) ?> (<?= htmlspecialchars($req['adopter_email']) ?>)</small>
                    <small>Requested on: <?= htmlspecialchars($req['requestDate']) ?></small>
                    <div class="action-links">
                        <a href="admin_action.php?requestID=<?= $req['requestID'] ?>&action=approve" class="approve">Approve</a>
                        <a href="admin_action.php?requestID=<?= $req['requestID'] ?>&action=reject" class="reject">Reject</a>
                        <a href="admin_adopter_profile.php?adopterID=<?= $req['adopterID'] ?>" class="profile">👤 View Profile</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No pending requests.</p>
        <?php endif; ?>
        </div>
    </div>
</section>

<!-- All Pets Table -->
<section class="section">
    <div class="section-header"><div class="section-title">🐾 All Pets</div><button class="toggle-btn" data-target="all-pets-section">Show</button></div>
    <div id="all-pets-section" class="section-body">
        <div class="filters-inline">
            <label>Status:</label>
            <select id="filter-status-pets">
                <option value="">All Status</option>
                <option value="Available">Available</option>
                <option value="Pending">Pending</option>
                <option value="Adopted">Adopted</option>
            </select>
            <label>Type:</label>
            <select id="filter-type-pets">
                <option value="">All Types</option>
                <option value="Dog">Dog</option>
                <option value="Cat">Cat</option>
                <option value="Rabbit">Rabbit</option>
                <option value="Bird">Bird</option>
                <option value="Hamster">Hamster</option>
            </select>
            <button class="toggle-btn" onclick="applyPetFilter();return false" style="background:linear-gradient(120deg,var(--ocean-3),var(--ocean-2))">Apply Filter</button>
        </div>
        <table class="table" id="all-pets-table">
            <thead>
                <tr><th>Name</th><th>Type</th><th>Age</th><th>Breed</th><th>Status</th><th>Added On</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach($allPets as $pet): ?>
                <tr>
                    <td><?= htmlspecialchars($pet['name']) ?></td>
                    <td><?= htmlspecialchars($pet['type']) ?></td>
                    <td><?= htmlspecialchars($pet['age']) ?></td>
                    <td><?= htmlspecialchars($pet['breed']) ?></td>
                    <td><?= htmlspecialchars($pet['status']) ?></td>
                    <td><?= htmlspecialchars($pet['created_at']) ?></td>
                    <td>
                        <a href="delete_pet.php?petID=<?= $pet['petID'] ?>" class="delete" onclick="return confirm('Delete <?= addslashes($pet['name']) ?>?')">❌ Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- All Adoption Requests Table -->
<section class="section">
    <div class="section-header"><div class="section-title">📋 All Adoption Requests</div><button class="toggle-btn" data-target="all-requests-section">Show</button></div>
    <div id="all-requests-section" class="section-body">
        <div class="filters-inline">
            <label>Status:</label>
            <select id="filter-status-requests">
                <option value="">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Approved">Approved</option>
                <option value="Rejected">Rejected</option>
            </select>
            <label>Pet Type:</label>
            <select id="filter-type-requests">
                <option value="">All Types</option>
                <option value="Dog">Dog</option>
                <option value="Cat">Cat</option>
                <option value="Rabbit">Rabbit</option>
                <option value="Bird">Bird</option>
                <option value="Hamster">Hamster</option>
            </select>
            <button class="toggle-btn" onclick="applyRequestFilter();return false" style="background:linear-gradient(120deg,var(--ocean-3),var(--ocean-2))">Apply Filter</button>
        </div>
        <table class="table" id="all-requests-table">
            <thead>
                <tr><th>Pet Name</th><th>Pet Type</th><th>Adopter</th><th>Status</th><th>Request Date</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach($allRequests as $req): ?>
                <tr>
                    <td><?= htmlspecialchars($req['pet_name']) ?></td>
                    <td><?= htmlspecialchars($req['pet_type']) ?></td>
                    <td><?= htmlspecialchars($req['adopter_name']) ?></td>
                    <td><span class="status-pill <?= $req['status']=='Pending'?'s-pending':($req['status']=='Approved'?'s-approved':'s-rejected') ?>"><?= htmlspecialchars($req['status']) ?></span></td>
                    <td><?= htmlspecialchars($req['requestDate']) ?></td>
                    <td>
                        <a href="delete_request.php?requestID=<?= $req['requestID'] ?>" class="delete" onclick="return confirm('Delete request?')">❌ Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
</div>

<script>
document.querySelectorAll('.toggle-btn[data-target]').forEach(btn => {
    btn.addEventListener('click', function () {
        const targetId = btn.getAttribute('data-target');
        const el = document.getElementById(targetId);
        if (!el) return;

        const isOpen = el.classList.contains('open');

        // close all other sections
        document.querySelectorAll('.section-body').forEach(b => {
            b.classList.remove('open');
            b.style.maxHeight = null;
        });
        document.querySelectorAll('.toggle-btn').forEach(b => { if (b !== btn) b.textContent = 'Show'; });

        if (!isOpen) {
            el.classList.add('open');
            el.style.maxHeight = el.scrollHeight + "px"; // smooth slide
            btn.textContent = 'Hide';
            setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'center' }), 100);
        } else {
            el.classList.remove('open');
            el.style.maxHeight = null;
            btn.textContent = 'Show';
        }
    });
});


// Optional: open the first section by default
document.addEventListener('DOMContentLoaded', function(){
    const firstBtn = document.querySelector('.toggle-btn[data-target]');
    if(firstBtn){ firstBtn.click(); }
});


</script>
<script>


// Pet Filters
function applyPetFilter(){
    let status = document.getElementById('filter-status-pets').value.toLowerCase();
    let type = document.getElementById('filter-type-pets').value.toLowerCase();
    document.querySelectorAll('#all-pets-table tbody tr').forEach(row=>{
        let rowStatus=row.cells[4].innerText.toLowerCase();
        let rowType=row.cells[1].innerText.toLowerCase();
        row.style.display = ((status==""||rowStatus==status) && (type==""||rowType==type))?'':'none';
    });
}

// Request Filters
function applyRequestFilter(){
    let status = document.getElementById('filter-status-requests').value.toLowerCase();
    let type = document.getElementById('filter-type-requests').value.toLowerCase();
    document.querySelectorAll('#all-requests-table tbody tr').forEach(row=>{
        let rowStatus=row.cells[3].innerText.toLowerCase();
        let rowType=row.cells[1].innerText.toLowerCase();
        row.style.display = ((status==""||rowStatus==status) && (type==""||rowType==type))?'':'none';
    });
}
</script>
</body>
</html>
