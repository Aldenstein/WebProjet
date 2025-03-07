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
        $sql = "INSERT INTO users (pseudo, mdp) VALUES ('$pseudo', '$mdp')";
        if ($conn->query($sql) === TRUE) {
            echo "New user created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
} elseif ($action == 'login') {
    $sql = "SELECT * FROM users WHERE pseudo='$pseudo' AND mdp='$mdp'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "Login successful!";
    } else {
        echo "Invalid username or password!";
    }
}
$conn->close();
?>