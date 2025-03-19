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
    // Haal de invoer van de gebruiker op
    $email = $_POST['email'];
    $wachtwoord = $_POST['wachtwoord'];

    // Zoek de klant op basis van het ingevoerde e-mailadres
    $sql = "SELECT klant_id, naam, wachtwoord FROM klanten WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Als klant wordt gevonden, verifieer het wachtwoord
        $klant = $result->fetch_assoc();
        if (password_verify($wachtwoord, $klant['wachtwoord'])) {
            // Start de sessie en sla klantgegevens op
            $_SESSION['klant_id'] = $klant['klant_id'];
            $_SESSION['klant_naam'] = $klant['naam'];
            header("Location: klant-dashboard.php");
            exit();
        } else {
            // Foutmelding als het wachtwoord onjuist is
            $message = "Onjuist wachtwoord";
        }
    } else {
        // Foutmelding als het e-mailadres niet bestaat
        $message = "E-mailadres niet gevonden";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Klant Login - FireArcade</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Klant Login</h1>
        
        <?php if ($message): ?>
        <div class="message error"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST" class="form-container">
            <div class="form-group">
                <label for="email">E-mailadres</label>
                <input type="email" id="email" name="email" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="wachtwoord">Wachtwoord</label>
                <div class="password-container">
                    <input type="password" id="wachtwoord" name="wachtwoord" class="form-input" required>
                    <button type="button" id="togglePassword" class="toggle-password">
                        &#128064;
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
            const wachtwoordInput = document.getElementById('wachtwoord');
            if (wachtwoordInput.type === 'password') {
                wachtwoordInput.type = 'text';
            } else {
                wachtwoordInput.type = 'password';
            }
        });
    </script>
</body>
</html>
