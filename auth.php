<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$pseudo = $_POST['pseudo'];
$mdp = $_POST['mdp'];
$action = $_POST['action'];

if ($action == 'register') {
    $sql = "SELECT * FROM users WHERE pseudo='$pseudo'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "User already exists!";
    } else {
        $hashed_password = password_hash($mdp, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (pseudo, mdp, active) VALUES ('$pseudo', '$hashed_password', false)";
        if ($conn->query($sql) === TRUE) {
            echo "<script>
                    alert('Compte créé avec succès, vous pouvez vous connecter');
                    document.getElementById('Pseudo').value = '';
                    document.getElementById('mdp').value = '';
                  </script>";
                  header("Location: index.html");
                  exit();
      } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
} elseif ($action == 'login') {
    $sql = "SELECT * FROM users WHERE pseudo='$pseudo'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($mdp, $row['mdp'])) {
if ($row['active'] == 1) {
                header("Location: creation/creation.html");
            } else {
            header("Location: liste/liste.html");
}
            exit();
        } else {
            header("Location: invalide/invalide.html");
        }
    } else {
        echo "Invalid username or password!";
    }
}
$conn->close();
?>