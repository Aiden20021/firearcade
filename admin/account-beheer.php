<?php
session_start();

// Controleer of de gebruiker is ingelogd 
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
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

// Verwerk het aanmaken van een nieuw account
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_account'])) {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $name = $_POST['name'];
    $role = $_POST['role'];

    // Controleer of het e-mailadres al bestaat
    $check_sql = "SELECT id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message = "Dit e-mailadres is al in gebruik";
    } else {
        // Maak het nieuwe account aan
        $sql = "INSERT INTO users (email, password, name, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $email, $password, $name, $role);

        if ($stmt->execute()) {
            $message = "Account succesvol aangemaakt";
            header("Location: account-beheer.php");
            exit();
        } else {
            $message = "Er is een fout opgetreden bij het aanmaken van het account";
        }
    }
}

// Verwerk het verwijderen van een account
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_account'])) {
    $delete_id = $_POST['delete_id'];
    $sql = "DELETE FROM users WHERE id = ? AND role != 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $message = "Account succesvol verwijderd";
    } else {
        $message = "Er is een fout opgetreden bij het verwijderen van het account";
    }
}

// Haal alle accounts op
$sql = "SELECT id, email, name, role FROM users WHERE role != 'admin' ORDER BY name";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Beheer - FireArcade</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1>Account Beheer</h1>
            <div>
                <span>Welkom, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="../logout.php" class="nav-button">Uitloggen</a>
            </div>
        </div>
        
        <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-input" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Wachtwoord</label>
                    <input type="password" class="form-input" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="name">Naam</label>
                    <input type="text" class="form-input" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="role">Rol</label>
                    <select class="form-input" id="role" name="role" required>
                        <option value="">Selecteer een rol</option>
                        <option value="verkoper">Verkoper</option>
                        <option value="monteur">Monteur</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" name="create_account" class="submit-button">Account Aanmaken</button>
                </div>
            </form>
        </div>

        <h2>Geregistreerde Accounts</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Naam</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                            echo "<td>" . htmlspecialchars($row["role"]) . "</td>";
                            echo "<td>";
                            echo "<form style='display: inline;' method='POST' onsubmit='return confirm(\"Weet u zeker dat u dit account wilt verwijderen?\");'>";
                            echo "<input type='hidden' name='delete_id' value='" . $row["id"] . "'>";
                            echo "<button type='submit' name='delete_account' class='action-button delete'>Verwijderen</button>";
                            echo "</form>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>Geen accounts gevonden</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html> 