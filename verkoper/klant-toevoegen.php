
<?php
session_start();

// Controleer of de gebruiker is ingelogd 
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'verkoper') {
    header("Location: ../admin/login.php");
    exit();
}

// Database connectie
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "firearcade";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';  // Variabele voor foutmeldingen of bevestigingen


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $naam = $_POST['naam'];
    $adres = $_POST['adres'];
    $postcode = $_POST['postcode'];
    $woonplaats = $_POST['woonplaats'];
    $email = $_POST['email'];
    
    // Het wachtwoord veilig hashen
    $wachtwoord = password_hash($_POST['wachtwoord'], PASSWORD_DEFAULT);
    
    $telefoon = $_POST['telefoon'];
    $bankrekening = $_POST['bankrekening'];
    $klantType = $_POST['klantType'];

    $sql = "INSERT INTO klanten (naam, adres, postcode, woonplaats, email, wachtwoord, telefoon, bankrekening, klant_type) 
            VALUES ('$naam', '$adres', '$postcode', '$woonplaats', '$email', '$wachtwoord', '$telefoon', '$bankrekening', '$klantType')";

    if ($conn->query($sql) === TRUE) {
        header("Location: klanten-beheer.php");
        exit();
    } else {
        $message = "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Klant toevoegen - Verkoopmedewerkerportaal FireArcade</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Klant <span id="formTitle">toevoegen</span></h1>
        
        <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form id="klantForm" class="form-container" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="naam">Naam</label>
                <input type="text" id="naam" name="naam" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="adres">Adres</label>
                <input type="text" id="adres" name="adres" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="postcode">Postcode</label>
                <input type="text" id="postcode" name="postcode" class="form-input" 
                       pattern="[1-9][0-9]{3}\s?[A-Za-z]{2}" 
                       title="Voer een geldige postcode in (bijv. 1234 AB)" required>
            </div>
            
            <div class="form-group">
                <label for="woonplaats">Woonplaats</label>
                <input type="text" id="woonplaats" name="woonplaats" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="email">E-mailadres</label>
                <input type="email" id="email" name="email" class="form-input" 
                       pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                       title="Voer een geldig e-mailadres in" required>
            </div>
            
            <div class="form-group">
                <label for="wachtwoord">Wachtwoord</label>
                <div class="password-container">
                    <input type="password" id="wachtwoord" name="wachtwoord" class="form-input" 
                           pattern=".{8,}" 
                           title="Wachtwoord moet minimaal 8 karakters bevatten" required>
                    <button type="button" id="togglePassword" class="toggle-password">
                        &#128064;
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="telefoon">Telefoonnummer</label>
                <input type="tel" id="telefoon" name="telefoon" class="form-input" 
                       pattern="^(?:0|\+31|0031)?(?:6[\s-]?[1-9][0-9]{7}|[1-7][\s-]?[0-9]{8})$"
                       title="Voer een geldig Nederlands telefoonnummer in" required>
            </div>
            
            <div class="form-group">
                <label for="bankrekening">Bankrekeningnummer (IBAN)</label>
                <input type="text" id="bankrekening" name="bankrekening" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="klantType">Type klant</label>
                <select id="klantType" name="klantType" class="form-input" required>
                    <option value="">Selecteer type klant</option>
                    <option value="particulier">Particulier</option>
                    <option value="zakelijk">Zakelijk</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="submit-button">Opslaan</button>
                <button type="button" class="secondary-button" onclick="location.href='klanten-beheer.php'">Annuleren</button>
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
