<?php
session_start();

// Database connectie
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "firearcade";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Eerst zoeken in de users tabel (voor admin, verkoper, monteur)
    $sql = "SELECT id, name, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Gebruiker gevonden in users tabel
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Start de sessie en sla gebruikersgegevens op
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            // Redirect op basis van rol
            switch($user['role']) {
                case 'admin':
                    header("Location: account-beheer.php");
                    break;
                case 'verkoper':
                    header("Location: ../verkoper/verkoop-dashboard.php");
                    break;
                case 'monteur':
                    header("Location: ../monteur/monteur-dashboard.php");
                    break;
                default:
                    $message = "Ongeldige rol";
            }
            exit();
        } else {
            $message = "Onjuist wachtwoord";
        }
    } else {
        // Als geen gebruiker gevonden in users tabel, zoek in klanten tabel
        $sql = "SELECT klant_id, naam, wachtwoord FROM klanten WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Klant gevonden
            $klant = $result->fetch_assoc();
            if (password_verify($password, $klant['wachtwoord'])) {
                // Start de sessie en sla klantgegevens op
                $_SESSION['klant_id'] = $klant['klant_id'];
                $_SESSION['klant_naam'] = $klant['naam'];
                $_SESSION['user_role'] = 'klant';
                header("Location: ../klant/klant-dashboard.php");
                exit();
            } else {
                $message = "Onjuist wachtwoord";
            }
        } else {
            $message = "E-mailadres niet gevonden";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inloggen - FireArcade</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Inloggen</h1>
        
        <?php if ($message): ?>
        <div class="message error"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" class="form-container">
            <div class="form-group">
                <label for="email">E-mailadres</label>
                <input type="email" id="email" name="email" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="password">Wachtwoord</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" class="form-input" required>
                    <button type="button" id="togglePassword" class="toggle-password">
                        👁️
                    </button>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="submit-button">Inloggen</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        });
    </script>
</body>
</html> 