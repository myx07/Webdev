<?php
// Connect to MySQL Server (no database selected yet)
$conn = new mysqli("localhost", "root", "root");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create Database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS dbContacts");

// Select the database
$conn->select_db("dbContacts");

// Create Table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS tblSMS (
    sms_ID INT AUTO_INCREMENT PRIMARY KEY,
    studno VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    cpno VARCHAR(20) NOT NULL
)");

// Handle Form Actions
$action = $_GET['action'] ?? '';

$search_studno = $name = $cpno = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $studno = $_POST['studno'] ?? '';
    $name = $_POST['name'] ?? '';
    $cpno = $_POST['cpno'] ?? '';

    if (isset($_POST['search'])) {
        $stmt = $conn->prepare("SELECT * FROM tblSMS WHERE studno = ?");
        $stmt->bind_param("s", $studno);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $search_studno = $row['studno'];
            $name = $row['name'];
            $cpno = $row['cpno'];
        } else {
            echo "<script>alert('Student Number not found!');</script>";
        }
        $stmt->close();
    }

    if (isset($_POST['save'])) {
        $new_studno = $studno;
        $counter = 1;

        // Ensure unique Student Number
        while (true) {
            $stmt = $conn->prepare("SELECT 1 FROM tblSMS WHERE studno = ?");
            $stmt->bind_param("s", $new_studno);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 0) break;

            $new_studno = $studno . '-' . $counter++;
            $stmt->close();
        }

        $stmt = $conn->prepare("INSERT INTO tblSMS (studno, name, cpno) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $new_studno, $name, $cpno);

        if ($stmt->execute()) {
            echo "<script>alert('Record saved successfully! New Student Number: $new_studno');</script>";
        } else {
            echo "Error adding record: " . $conn->error;
        }
        $stmt->close();
    }

    if (isset($_POST['update'])) {
        $stmt = $conn->prepare("UPDATE tblSMS SET name = ?, cpno = ? WHERE studno = ?");
        $stmt->bind_param("sss", $name, $cpno, $studno);

        if ($stmt->execute()) {
            echo "<script>alert('Record updated successfully!');</script>";
        } else {
            echo "Error updating record: " . $conn->error;
        }
        $stmt->close();
    }

    if (isset($_POST['delete'])) {
        $stmt = $conn->prepare("DELETE FROM tblSMS WHERE studno = ?");
        $stmt->bind_param("s", $studno);

        if ($stmt->execute()) {
            echo "<script>alert('Record deleted successfully!');</script>";
        } else {
            echo "Error deleting record: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage SMS Records</title>
</head>
<body>

<h3>Menu Links:</h3>
<a href="?action=add">Add Record</a> |
<a href="?action=update">Update Record</a> |
<a href="?action=delete">Delete Record</a>

<?php if ($action == 'add'): ?>
    <!-- Add Record -->
    <h2>Add Record</h2>
    <form method="POST">
        Student Number: <input type="text" name="studno" required><br><br>
        Name: <input type="text" name="name" required><br><br>
        CP No.: <input type="text" name="cpno" required placeholder="639201234567"><br><br>
        <button type="submit" name="save">Save</button>
    </form>

<?php elseif ($action == 'update'): ?>
    <!-- Update Record -->
    <h2>Update Record</h2>
    <form method="POST">
        Student Number: <input type="text" name="studno" value="<?= htmlspecialchars($search_studno) ?>" required>
        <button type="submit" name="search">Search</button><br><br>
        Name: <input type="text" name="name" value="<?= htmlspecialchars($name) ?>"><br><br>
        CP No.: 63<input type="text" name="cpno" value="<?= htmlspecialchars($cpno) ?>"><br><br>
        <button type="submit" name="update">Update</button>
    </form>

<?php elseif ($action == 'delete'): ?>
    <!-- Delete Record -->
    <h2>Delete Record</h2>
    <form method="POST">
        Student Number: <input type="text" name="studno" value="<?= htmlspecialchars($search_studno) ?>" required>
        <button type="submit" name="search">Search</button><br><br>
        Name: <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" readonly><br><br>
        CP No.: 63<input type="text" name="cpno" value="<?= htmlspecialchars($cpno) ?>" readonly><br><br>
        <button type="submit" name="delete">Delete</button>
    </form>

<?php else: ?>
    <h2>Select an action from the menu.</h2>
<?php endif; ?>

</body>
</html>
