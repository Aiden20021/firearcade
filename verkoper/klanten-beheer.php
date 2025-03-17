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
$edit_klant = null;

// Verwerk het bijwerken van een klant
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_klant'])) {
    $klant_id = $conn->real_escape_string($_POST['klant_id']);
    $naam = $conn->real_escape_string($_POST['naam']);
    $adres = $conn->real_escape_string($_POST['adres']);
    $postcode = $conn->real_escape_string($_POST['postcode']);
    $woonplaats = $conn->real_escape_string($_POST['woonplaats']);
    $email = $conn->real_escape_string($_POST['email']);
    $telefoon = $conn->real_escape_string($_POST['telefoon']);
    $bankrekening = $conn->real_escape_string($_POST['bankrekening']);
    $klantType = $conn->real_escape_string($_POST['klantType']);

    $sql = "UPDATE klanten SET 
            naam = '$naam',
            adres = '$adres',
            postcode = '$postcode',
            woonplaats = '$woonplaats',
            email = '$email',
            telefoon = '$telefoon',
            bankrekening = '$bankrekening',
            klant_type = '$klantType'
            WHERE klant_id = '$klant_id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: klanten-beheer.php");
        exit();
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Verwerk het verwijderen van een klant
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_klant'])) {
    $klant_id = $conn->real_escape_string($_POST['klant_id']);
    
    // Controleer eerst of er bestellingen zijn
    $check_sql = "SELECT COUNT(*) as count FROM bestellingen WHERE klant_id = '$klant_id'";
    $check_result = $conn->query($check_sql);
    $row = $check_result->fetch_assoc();
    
    if ($row['count'] > 0) {
        $message = "Kan klant niet verwijderen: er zijn nog bestellingen gekoppeld aan deze klant.";
    } else {
        $sql = "DELETE FROM klanten WHERE klant_id = '$klant_id'";
        if ($conn->query($sql) === TRUE) {
            header("Location: verkoop-dashboard.php");
            exit();
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}

// Laad klantgegevens voor bewerken
if (isset($_GET['edit'])) {
    $klant_id = $conn->real_escape_string($_GET['edit']);
    $sql = "SELECT * FROM klanten WHERE klant_id = '$klant_id'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $edit_klant = $result->fetch_assoc();
    }
}

// Haal alle klanten op
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$sql = "SELECT klant_id, naam, email, woonplaats, klant_type FROM klanten";
if ($search) {
    $sql .= " WHERE naam LIKE '%$search%'";
}
$sql .= " ORDER BY naam";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Klantenbeheer - Verkoopmedewerkerportaal FireArcade</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Klantenbeheer</h1>
        
        <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="dashboard-actions">
            <button class="submit-button" onclick="location.href='klant-toevoegen.php'" style="max-width: 250px;">Nieuwe klant toevoegen</button>
            <button class="nav-button" onclick="location.href='verkoop-dashboard.php'" style="max-width: 250px;">Terug naar dashboard</button>
        </div>

        <div class="search-container">
            <form method="GET" style="display: flex; gap: 10px; width: 100%;">
                <input type="text" name="search" class="search-input" 
                       placeholder="Zoek op klantnaam..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="submit-button">Zoeken</button>
            </form>
        </div>

        <?php if ($edit_klant): ?>
        <div id="editForm" class="edit-form">
            <h2>Klant bewerken</h2>
            <form class="form-container" method="POST">
                <input type="hidden" name="klant_id" value="<?php echo htmlspecialchars($edit_klant['klant_id']); ?>">
                <input type="hidden" name="update_klant" value="1">
                
                <div class="form-group">
                    <label for="naam">Naam</label>
                    <input type="text" id="naam" name="naam" class="form-input" 
                           value="<?php echo htmlspecialchars($edit_klant['naam']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="adres">Adres</label>
                    <input type="text" id="adres" name="adres" class="form-input" 
                           value="<?php echo htmlspecialchars($edit_klant['adres']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="postcode">Postcode</label>
                    <input type="text" id="postcode" name="postcode" class="form-input" 
                           pattern="[1-9][0-9]{3}\s?[A-Za-z]{2}" 
                           value="<?php echo htmlspecialchars($edit_klant['postcode']); ?>"
                           title="Voer een geldige postcode in (bijv. 1234 AB)" required>
                </div>
                
                <div class="form-group">
                    <label for="woonplaats">Woonplaats</label>
                    <input type="text" id="woonplaats" name="woonplaats" class="form-input" 
                           value="<?php echo htmlspecialchars($edit_klant['woonplaats']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">E-mailadres</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           value="<?php echo htmlspecialchars($edit_klant['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="telefoon">Telefoonnummer</label>
                    <input type="tel" id="telefoon" name="telefoon" class="form-input" 
                           value="<?php echo htmlspecialchars($edit_klant['telefoon']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="bankrekening">Bankrekeningnummer (IBAN)</label>
                    <input type="text" id="bankrekening" name="bankrekening" class="form-input" 
                           pattern="NL[0-9]{2}[A-Z]{4}[0-9]{10}"
                           value="<?php echo htmlspecialchars($edit_klant['bankrekening']); ?>"
                           title="Voer een geldig IBAN nummer in" required>
                </div>
                
                <div class="form-group">
                    <label for="klantType">Type klant</label>
                    <select id="klantType" name="klantType" class="form-input" required>
                        <option value="particulier" <?php echo $edit_klant['klant_type'] == 'particulier' ? 'selected' : ''; ?>>Particulier</option>
                        <option value="zakelijk" <?php echo $edit_klant['klant_type'] == 'zakelijk' ? 'selected' : ''; ?>>Zakelijk</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="submit-button">Wijzigingen opslaan</button>
                    <button type="button" class="secondary-button" onclick="location.href='klanten-beheer.php'">Annuleren</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <div class="table-container">
            <table id="klanten">
                <thead>
                    <tr>
                        <th>Klantnummer</th>
                        <th>Naam</th>
                        <th>E-mail</th>
                        <th>Woonplaats</th>
                        <th>Type</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["klant_id"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["naam"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["woonplaats"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["klant_type"]) . "</td>";
                            echo "<td>";
                            echo "<a href='?edit=" . $row["klant_id"] . "' class='action-button'>Bewerken</a> ";
                            echo "<form style='display: inline;' method='POST' onsubmit='return confirm(\"Weet u zeker dat u deze klant wilt verwijderen?\");'>";
                            echo "<input type='hidden' name='klant_id' value='" . $row["klant_id"] . "'>";
                            echo "<input type='hidden' name='delete_klant' value='1'>";
                            echo "<button type='submit' class='action-button delete'>Verwijderen</button>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>Geen klanten gevonden</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
