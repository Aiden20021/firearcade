<?php
session_start();

// Controleer of de gebruiker is ingelogd 
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'monteur') {
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

// Filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'open';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$zoek_term = isset($_GET['zoek']) ? $_GET['zoek'] : '';

// Basisquery
$sql = "SELECT 
            t.ticket_id, 
            t.type, 
            t.beschrijving, 
            t.status, 
            k.naam as klantnaam,
            s.naam as spelkast
        FROM 
            tickets t
        JOIN 
            klanten k ON t.klant_id = k.klant_id
        JOIN 
            bestellingen b ON t.bestelling_id = b.bestelling_id
        JOIN 
            spelkasten s ON b.spelkast_id = s.spelkast_id
        WHERE 1=1";

// Filters toepassen
if ($status_filter != '') {
    $sql .= " AND t.status = '$status_filter'";
}

if ($type_filter != '') {
    $sql .= " AND t.type = '$type_filter'";
}

if ($zoek_term != '') {
    $sql .= " AND (t.ticket_id LIKE '%$zoek_term%' OR k.naam LIKE '%$zoek_term%')";
}

$sql .= " ORDER BY t.ticket_id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Monteurportaal FireArcade</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1>Openstaande Tickets</h1>
            <a href="../logout.php" class="nav-button">Uitloggen</a>
        </div>
        <form method="GET" action="" class="form-container">
            <div class="form-group">
                <label for="status">Status:</label>
                <select id="status" name="status" class="form-input" onchange="this.form.submit()">
                    <option value="" <?php echo ($status_filter == '') ? 'selected' : ''; ?>>Alle statussen</option>
                    <option value="open" <?php echo ($status_filter == 'open') ? 'selected' : ''; ?>>Open</option>
                    <option value="in_behandeling" <?php echo ($status_filter == 'in_behandeling') ? 'selected' : ''; ?>>In behandeling</option>
                    <option value="afgerond" <?php echo ($status_filter == 'afgerond') ? 'selected' : ''; ?>>Afgerond</option>
                </select>
            </div>
            <div class="form-group">
                <label for="type">Type:</label>
                <select id="type" name="type" class="form-input" onchange="this.form.submit()">
                    <option value="" <?php echo ($type_filter == '') ? 'selected' : ''; ?>>-- Alle types --</option>
                    <option value="montage" <?php echo ($type_filter == 'montage') ? 'selected' : ''; ?>>Installatie</option>
                    <option value="reparatie" <?php echo ($type_filter == 'reparatie') ? 'selected' : ''; ?>>Reparatie</option>
                </select>
            </div>
            <div class="form-group">
                <label for="zoek">Zoeken:</label>
                <div style="display: flex;">
                    <input type="text" id="zoek" name="zoek" class="form-input" placeholder="Zoek op ticketnummer of klantnaam" value="<?php echo htmlspecialchars($zoek_term); ?>">
                    <button type="submit" class="action-button" style="margin-left: 10px;">Zoeken</button>
                </div>
            </div>
        </form>
        
        <div class="table-container">
            <table id="tickets">
                <thead>
                    <tr>
                        <th>Ticketnummer</th>
                        <th>Klantnaam</th>
                        <th>Spelkast</th>
                        <th>Status</th>
                        <th>Details</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $status_class = 'status-' . $row['status'];
                            $status_text = '';
                            
                            switch($row['status']) {
                                case 'open':
                                    $status_text = 'Open';
                                    break;
                                case 'in_behandeling':
                                    $status_text = 'In behandeling';
                                    break;
                                case 'afgerond':
                                    $status_text = 'Afgerond';
                                    break;
                                default:
                                    $status_text = $row['status'];
                            }
                            
                            $type_text = ucfirst($row['type']);
                            
                            echo "<tr>
                                <td>" . $row['ticket_id'] . "</td>
                                <td>" . htmlspecialchars($row['klantnaam']) . "</td>
                                <td>" . htmlspecialchars($row['spelkast']) . "</td>
                                <td class='" . $status_class . "'>" . $status_text . "</td>
                                <td><a href='monteur-ticket-details.php?id=" . $row['ticket_id'] . "' class='action-button'>Details</a></td>
                                <td>" . $type_text . "</td>
                              </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>Geen tickets gevonden</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?> 