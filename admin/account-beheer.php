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

// Verwerk het bewerken van een account
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_account'])) {
    $edit_id = $_POST['edit_id'];
    $email = $_POST['edit_email'];
    $name = $_POST['edit_name'];
    $role = $_POST['edit_role'];
    
    // Controleer of het wachtwoord is gewijzigd
    if (!empty($_POST['edit_password'])) {
        $password = password_hash($_POST['edit_password'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET email = ?, password = ?, name = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $email, $password, $name, $role, $edit_id);
    } else {
        // Update zonder wachtwoord te wijzigen
        $sql = "UPDATE users SET email = ?, name = ?, role = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $email, $name, $role, $edit_id);
    }
    
    if ($stmt->execute()) {
        $message = "Account succesvol bijgewerkt";
    } else {
        $message = "Er is een fout opgetreden bij het bijwerken van het account";
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
    <style>
        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 5px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }
    </style>
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
                            echo "<button type='button' class='action-button edit' onclick='openEditModal(" . $row["id"] . ", \"" . htmlspecialchars($row["email"], ENT_QUOTES) . "\", \"" . htmlspecialchars($row["name"], ENT_QUOTES) . "\", \"" . htmlspecialchars($row["role"], ENT_QUOTES) . "\")'>Bewerken</button> ";
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

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Account Bewerken</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" id="edit_id" name="edit_id">
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" class="form-input" id="edit_email" name="edit_email" required>
                </div>
                <div class="form-group">
                    <label for="edit_password">Wachtwoord</label>
                    <input type="password" class="form-input" id="edit_password" name="edit_password" placeholder="Laat leeg om ongewijzigd te laten">
                </div>
                <div class="form-group">
                    <label for="edit_name">Naam</label>
                    <input type="text" class="form-input" id="edit_name" name="edit_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_role">Rol</label>
                    <select class="form-input" id="edit_role" name="edit_role" required>
                        <option value="">Selecteer een rol</option>
                        <option value="verkoper">Verkoper</option>
                        <option value="monteur">Monteur</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" name="edit_account" class="submit-button">Opslaan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functies
        function openEditModal(id, email, name, role) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_role').value = role;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Sluit de modal als er buiten wordt geklikt
        window.onclick = function(event) {
            var modal = document.getElementById('editModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>