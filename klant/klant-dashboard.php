<?php
session_start();

// Controleer of de gebruiker is ingelogd 
if (!isset($_SESSION['klant_id']) || $_SESSION['user_role'] !== 'klant') {
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

$message = '';
$ticket_aangemaakt = false;

// Verwerk nieuwe ticket
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_ticket'])) {
    $bestelling_id = $_POST['bestelling_id'];
    $type = $_POST['type'];
    $beschrijving = $_POST['beschrijving'];
    $klant_id = $_SESSION['klant_id'];

    $sql = "INSERT INTO tickets (bestelling_id, klant_id, type, beschrijving) 
            VALUES ('$bestelling_id', '$klant_id', '$type', '$beschrijving')";

    if ($conn->query($sql) === TRUE) {
        $message = "Ticket succesvol aangemaakt!";
        $ticket_aangemaakt = true;
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Haal bestellingen op van de klant
$klant_id = $_SESSION['klant_id'];
$sql = "SELECT bestellingen.bestelling_id, spelkasten.naam as spelkast_naam, bestellingen.besteldatum, bestellingen.verlengde_garantie,
               tickets.ticket_id, tickets.type as ticket_type, tickets.status as ticket_status
        FROM bestellingen
        JOIN spelkasten ON bestellingen.spelkast_id = spelkasten.spelkast_id
        LEFT JOIN tickets ON bestellingen.bestelling_id = tickets.bestelling_id
        WHERE bestellingen.klant_id = $klant_id
        ORDER BY bestellingen.besteldatum DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Klant Dashboard - FireArcade</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <?php if ($ticket_aangemaakt): ?>
    <script>
        // Voorkom dubbele form submission bij vernieuwen pagina
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1>Welkom, <?php echo htmlspecialchars($_SESSION['klant_naam']); ?></h1>
            <a href="../logout.php" class="nav-button">Uitloggen</a>
        </div>

        <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <h2>Mijn Bestellingen</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Bestellingsnummer</th>
                        <th>Spelkast</th>
                        <th>Datum</th>
                        <th>Garantie</th>
                        <th>Status</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['bestelling_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['spelkast_naam']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($row['besteldatum'])); ?></td>
                                <td><?php echo $row['verlengde_garantie'] ? 'Ja' : 'Nee'; ?></td>
                                <td>
                                    <?php 
                                    if (isset($row['ticket_status'])) {
                                        $status_text = [
                                            'open' => 'Open',
                                            'in_behandeling' => 'In behandeling',
                                            'afgerond' => 'Afgerond'
                                        ];
                                        echo $status_text[$row['ticket_status']];
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if (!isset($row['ticket_id'])): ?>
                                    <button onclick="openTicketModal(<?php echo $row['bestelling_id']; ?>)" 
                                            class="action-button">Ticket aanmaken</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">Geen bestellingen gevonden</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Ticket Modal -->
    <div id="ticketModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Nieuwe Ticket Aanmaken</h2>
            <form method="POST" class="form-container">
                <input type="hidden" id="bestelling_id" name="bestelling_id">
                <div class="form-group">
                    <label for="type">Type Ticket</label>
                    <select class="form-input" id="type" name="type" required>
                        <option value="">Selecteer type</option>
                        <option value="montage">Installatie</option>
                        <option value="reparatie">Reparatie</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="beschrijving">Beschrijving</label>
                    <textarea class="form-input" id="beschrijving" name="beschrijving" required></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" name="create_ticket" class="submit-button">Ticket Aanmaken</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functionaliteit
        const modal = document.getElementById("ticketModal");
        const span = document.getElementsByClassName("close")[0];

        function openTicketModal(bestellingId) {
            document.getElementById("bestelling_id").value = bestellingId;
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
