<!-- signup.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - Pet Adoption</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Create an Account</h2>
        <?php
        session_start();
        include 'config.php';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $address = trim($_POST['address']); // ← NEW
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            // Check if email exists
            $stmt = $pdo->prepare("SELECT adopterID FROM Adopter WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                echo "<p style='color:red;'>Email already registered!</p>";
            } else {
                $stmt = $pdo->prepare("INSERT INTO Adopter (...) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$name, $email, $phone, $address, $_POST['password']]); // plain text
                echo "<p style='color:green;'>Account created! <a href='index.php'>Login here</a></p>";
            }
        }
        ?>
        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="text" name="phone" placeholder="Phone Number" required><br>
            <textarea name="address" placeholder="Full Address" rows="3" required></textarea><br> <!-- ← NEW -->
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="index.php">Login</a></p>
    </div>
</body>
</html>
