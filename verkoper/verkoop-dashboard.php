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

// Variabele voor foutmeldingen of bevestigingen
$message = '';

// Haal bestellingen op
$search = $_GET['search'] ?? '';
$sql = "SELECT bestellingen.bestelling_id, klanten.naam klantnaam, spelkasten.naam spelkast_naam, 
               bestellingen.besteldatum, bestellingen.verlengde_garantie
        FROM bestellingen
        JOIN klanten ON bestellingen.klant_id = klanten.klant_id
        JOIN spelkasten ON bestellingen.spelkast_id = spelkasten.spelkast_id";

// Controleer of de gebruiker de zoekbalk ingevoerd heeft
if ($search !== '') { 
    $sql .= " WHERE klanten.naam LIKE '%$search%'";
}

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Verkoopmedewerkerportaal FireArcade</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Verkoop Dashboard</h1>
        
        <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="dashboard-actions">
            <button class="submit-button" onclick="location.href='bestelling-toevoegen.php'" style="max-width: 250px;">Nieuwe bestelling toevoegen</button>
            <button class="submit-button" onclick="location.href='klanten-beheer.php'" style="max-width: 250px;">Klantenbeheer</button>
        </div>
        
        <div class="search-container">
            <form method="GET" style="display: flex; gap: 10px; width: 100%;">
                <input type="text" name="search" class="search-input" 
                       placeholder="Zoek bestellingen op klantnaam..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="submit-button">Zoeken</button>
            </form>
        </div>
        
        <h2>Bestellingen</h2>
        <div class="table-container">
            <table id="bestellingen">
                <thead>
                    <tr>
                        <th>Bestellingsnummer</th>
                        <th>Klantnaam</th>
                        <th>Spelkast</th>
                        <th>Datum</th>
                        <th>Garantie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["bestelling_id"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["klantnaam"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["spelkast_naam"]) . "</td>";
                            echo "<td>" . htmlspecialchars(date('d-m-Y', strtotime($row["besteldatum"]))) . "</td>";
                            echo "<td>" . ($row["verlengde_garantie"] ? "Ja" : "Nee") . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>Geen recente bestellingen gevonden</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
