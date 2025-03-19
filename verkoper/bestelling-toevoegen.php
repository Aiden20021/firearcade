<?php
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

// Haal alle klanten op voor de dropdown
$klanten_query = "SELECT klant_id, naam, email FROM klanten ORDER BY naam";
$klanten_result = $conn->query($klanten_query);

// Haal alle spelkasten op voor de dropdown
$spelkasten_query = "SELECT spelkast_id, naam, prijs FROM spelkasten ORDER BY naam";
$spelkasten_result = $conn->query($spelkasten_query);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Haal de ingevoerde waarden 
    $klant_id = $_POST['klant'];
    $spelkast_id = $_POST['spelkast'];
    $datum = $_POST['datum'];
    // Zet de waarde van verlengde garantie op 1 als het checkboxje is aangevinkt, anders 0
    $garantie = isset($_POST['garantie']) ? 1 : 0;

    // Maak de SQL-query voor het invoegen van de nieuwe bestelling
    $sql = "INSERT INTO bestellingen (klant_id, spelkast_id, besteldatum, verlengde_garantie) 
            VALUES ('$klant_id', '$spelkast_id', '$datum', '$garantie')";

    // Voer de query uit en controleer of het succesvol is
    if ($conn->query($sql) === TRUE) {
        header("Location: verkoop-dashboard.php");
        exit();
    } else {
        // Toon een foutmelding als de query niet succesvol is
        $message = "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Bestelling toevoegen - Verkoopmedewerkerportaal FireArcade</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Bestelling toevoegen</h1>

        <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <form id="bestellingForm" class="form-container" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="klant">Selecteer klant</label>
                <select id="klant" name="klant" class="form-input" required>
                    <option value="">-- Kies een klant --</option>
                    <?php while($klant = $klanten_result->fetch_assoc()): ?>
                        <option value="<?php echo $klant['klant_id']; ?>">
                            <?php echo htmlspecialchars($klant['naam'] . ' (' . $klant['email'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="spelkast">Selecteer spelkast</label>
                <select id="spelkast" name="spelkast" class="form-input" required>
                    <option value="">-- Maak een keuze --</option>
                    <?php while($spelkast = $spelkasten_result->fetch_assoc()): ?>
                        <option value="<?php echo $spelkast['spelkast_id']; ?>">
                            <?php echo htmlspecialchars($spelkast['naam'] . ' (€' . number_format($spelkast['prijs'], 2) . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="datum">Besteldatum</label>
                <input type="date" id="datum" name="datum" class="form-input" 
                       value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" id="garantie" name="garantie">
                    <span>Verlengde garantie</span>
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="submit-button">Bestelling opslaan</button>
                <button type="button" class="secondary-button" onclick="location.href='verkoop-dashboard.php'">Annuleren</button>
            </div>
        </form>
    </div>
</body>
</html>
