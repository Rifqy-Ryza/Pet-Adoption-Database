<?php
session_start();
include 'config.php';

// === Handle Login ===
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check Admin
    $stmt = $pdo->prepare("SELECT * FROM Admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && $password == $admin['password']) {
        $_SESSION['adminID'] = $admin['adminID'];
        $_SESSION['name'] = $admin['name'];
        $_SESSION['role'] = 'admin';
        header("Location: admin_dashboard.php");
        exit();
    }

    // Check Adopter
    $stmt = $pdo->prepare("SELECT * FROM Adopter WHERE email = ?");
    $stmt->execute([$email]);
    $adopter = $stmt->fetch();

    if ($adopter && $password == $adopter['password']) {
        $_SESSION['adopterID'] = $adopter['adopterID'];
        $_SESSION['name'] = $adopter['name'];
        $_SESSION['role'] = 'adopter';
        header("Location: dashboard.php");
        exit();
    }

    $error = "Invalid email or password.";
}

// === Handle Signup ===
if (isset($_POST['signup'])) {
    $fullname = trim($_POST['fullname']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($fullname && $address && $phone && $email && $password) {
        $stmt = $pdo->prepare("SELECT adopterID FROM Adopter WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $signup_error = "Email already registered.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO Adopter (name, address, phone, email, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$fullname, $address, $phone, $email, $password]);
            $signup_success = "Account created! You can now log in.";
        }
    } else {
        $signup_error = "All fields are required.";
    }
}

// === Fetch Event Name ===
$stmt = $pdo->query("SELECT event_name FROM settings WHERE id = 1");
$setting = $stmt->fetch();
$eventName = $setting ? $setting['event_name'] : 'HAPPY PAWS';

// === Quote under event name ===
$quote = "Every paw deserves a loving home.";
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($eventName) ?> - Login / Sign Up</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0; padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
            position: relative;
            height: 100vh;
            overflow: hidden;
        }
        #bg-video {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            object-fit: cover;
            z-index: -1;
        }
        .overlay {
            position: fixed;
            top:0; left:0;
            width:100%; height:100%;
            background: rgba(210,105,30,0.6); /* chocolate overlay */
            z-index: -1;
        }
        .login-container, .signup-container {
            max-width: 400px;
            margin: 50px auto;
            background: rgba(255, 248, 220, 0.95);
            color: #4b2e2e;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            text-align: center;
        }
        h1 { margin-bottom: 5px; }
        h2 { margin-bottom: 20px; }
        p.quote { font-style: italic; margin-bottom: 20px; }
        input[type="text"], input[type="email"], input[type="password"], input[type="tel"] {
            width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #8b4513; border-radius: 6px;
        }
        button {
            width: 100%; padding: 12px; margin: 10px 0; background: #8b4513;
            border: none; border-radius: 6px; font-weight: bold; color: #fff; cursor: pointer;
        }
        button:hover { background: #a0522d; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 6px; margin-bottom: 10px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 6px; margin-bottom: 10px; }
        .tabs { display: flex; justify-content: center; margin-bottom: 20px; }
        .tab { padding: 10px 20px; cursor: pointer; border-bottom: 3px solid transparent; }
        .tab.active { border-bottom: 3px solid #fff; font-weight: bold; }
        .hidden { display: none; }
    </style>
    <script>
        function showTab(tab) {
            if(tab==='login') {
                document.getElementById('login-form').classList.remove('hidden');
                document.getElementById('signup-form').classList.add('hidden');
                document.getElementById('tab-login').classList.add('active');
                document.getElementById('tab-signup').classList.remove('active');
            } else {
                document.getElementById('login-form').classList.add('hidden');
                document.getElementById('signup-form').classList.remove('hidden');
                document.getElementById('tab-login').classList.remove('active');
                document.getElementById('tab-signup').classList.add('active');
            }
        }
    </script>
</head>
<body>
    <video autoplay muted loop id="bg-video">
    <source src="assets/video/login_bg.mp4" type="video/mp4">
</video>
    <div class="overlay"></div>

    <div class="login-container">
        <h1>🐾 <?= htmlspecialchars($eventName) ?></h1>
        <p class="quote"><?= htmlspecialchars($quote) ?></p>

        <div class="tabs">
            <div id="tab-login" class="tab active" onclick="showTab('login')">Log In</div>
            <div id="tab-signup" class="tab" onclick="showTab('signup')">Sign Up</div>
        </div>

        <!-- Login Form -->
        <form id="login-form" method="POST">
            <?php if(isset($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">Log In</button>
        </form>

        <!-- Sign Up Form -->
        <form id="signup-form" method="POST" class="hidden">
            <?php if(isset($signup_error)): ?><div class="error"><?= htmlspecialchars($signup_error) ?></div><?php endif; ?>
            <?php if(isset($signup_success)): ?><div class="success"><?= htmlspecialchars($signup_success) ?></div><?php endif; ?>
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="text" name="address" placeholder="Address" required>
            <input type="tel" name="phone" placeholder="Phone Number" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="signup">Sign Up</button>
        </form>
    </div>
</body>
</html>
