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
    <link rel="stylesheet" type="text/css" href="../css/login.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="login-page">
    <a href="../index.html" class="back-to-home">
        <i class="fas fa-arrow-left"></i>
        Terug naar home
    </a>

    <div class="login-container">
        <div class="login-header">
            <h1>Inloggen</h1>
            <p>Welkom terug bij FireArcade</p> 
        </div>
        
        <?php if ($message): ?>
        <div class="message error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo $message; ?>
        </div>
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
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="submit-button">
                    <i class="fas fa-sign-in-alt"></i>
                    Inloggen
                </button>
            </div>
        </form>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');
        
        // Initially hide the toggle button
        toggleButton.classList.remove('visible');
        
        // Check if password field has content on input
        passwordInput.addEventListener('input', function() {
            if (this.value.length > 0) {
                toggleButton.classList.add('visible');
            } else {
                toggleButton.classList.remove('visible');
            }
        });
        
        // Toggle password visibility when eye icon is clicked
        toggleButton.addEventListener('click', function() {
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>