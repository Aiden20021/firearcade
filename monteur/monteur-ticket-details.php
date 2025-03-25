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

// Check of er een ticket ID is meegegeven
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: monteur-dashboard.php');
    exit();
}

$ticket_id = $_GET['id'];
$message = '';

// Ticket voltooien verwerken
if (isset($_POST['voltooien'])) {
    $werkzaamheden = $_POST['werkzaamheden'];
    $nieuwe_status = $_POST['status'];
    
    // Valideer status
    $geldige_statussen = ['open', 'in_behandeling', 'afgerond'];
    if (!in_array($nieuwe_status, $geldige_statussen)) {
        $nieuwe_status = 'afgerond'; // Default is afgerond
    }
    
    if (empty($werkzaamheden)) {
        $message = "Vul de uitgevoerde werkzaamheden in";
    } else {
        // Update ticket in database
        $update_sql = "UPDATE tickets 
                      SET status = '$nieuwe_status', 
                          werkzaamheden = '$werkzaamheden', 
                          afgerond_op = NOW()
                      WHERE ticket_id = $ticket_id";
        
        if ($conn->query($update_sql) === TRUE) {
            header('Location: monteur-dashboard.php?status=afgerond');
            exit();
        } else {
            $message = "Fout bij voltooien ticket: " . $conn->error;
        }
    }
}

// Ticket details ophalen
$sql = "SELECT 
            t.ticket_id, 
            t.type, 
            t.beschrijving, 
            t.status, 
            t.werkzaamheden,
            k.naam as klantnaam,
            k.adres,
            k.postcode,
            k.woonplaats,
            s.naam as spelkast,
            b.verlengde_garantie
        FROM 
            tickets t
        JOIN 
            klanten k ON t.klant_id = k.klant_id
        JOIN 
            bestellingen b ON t.bestelling_id = b.bestelling_id
        JOIN 
            spelkasten s ON b.spelkast_id = s.spelkast_id
        WHERE 
            t.ticket_id = $ticket_id";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    header('Location: monteur-dashboard.php');
    exit();
}

$ticket = $result->fetch_assoc();
$volledig_adres = $ticket['adres'] . ', ' . $ticket['postcode'] . ' ' . $ticket['woonplaats'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ticketdetails - Monteurportaal FireArcade</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="container">
        <h1>Ticket #<?php echo $ticket_id; ?></h1>
        
        <?php if(!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <div class="info-group">
                <label>Klantnaam:</label>
                <span><?php echo htmlspecialchars($ticket['klantnaam']); ?></span>
            </div>
            <div class="info-group">
                <label>Adres:</label>
                <span><?php echo htmlspecialchars($volledig_adres); ?></span>
            </div>
            <div class="info-group">
                <label>Spelkast:</label>
                <span><?php echo htmlspecialchars($ticket['spelkast']); ?></span>
            </div>
            <div class="info-group">
                <label>Probleemomschrijving:</label>
                <span><?php echo nl2br(htmlspecialchars($ticket['beschrijving'])); ?></span>
            </div>
            <div class="info-group">
                <label>Onderhoudscontract:</label>
                <span><?php echo $ticket['verlengde_garantie'] ? 'Ja' : 'Nee'; ?></span>
            </div>
            
            <!-- Ticket voltooien formulier -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status" class="form-input">
                        <option value="open" <?php echo ($ticket['status'] == 'open') ? 'selected' : ''; ?>>Open</option>
                        <option value="in_behandeling" <?php echo ($ticket['status'] == 'in_behandeling') ? 'selected' : ''; ?>>In behandeling</option>
                        <option value="afgerond" <?php echo ($ticket['status'] == 'afgerond') ? 'selected' : ''; ?>>Afgerond</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="werkzaamheden">Werkzaamheden:</label>
                    <textarea id="werkzaamheden" name="werkzaamheden" class="form-input textarea" required><?php echo htmlspecialchars($ticket['werkzaamheden'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="voltooien" class="submit-button">Ticket voltooien</button>
                    <a href="monteur-dashboard.php" class="nav-button">Terug naar overzicht</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Form validatie
        document.querySelector('form').addEventListener('submit', function(e) {
            const werkzaamheden = document.getElementById('werkzaamheden').value;
            if (werkzaamheden.trim() === '') {
                e.preventDefault();
                alert('Vul de uitgevoerde werkzaamheden in');
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?> 