<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$numero_question = $_POST['numero_question'];
$question = $_POST['question'];
$reponse_v = $_POST['reponse_v'];
$reponse_f1 = $_POST['reponse_f1'];
$reponse_f2 = $_POST['reponse_f2'];

$sql = "INSERT INTO questions (numero_question, question, reponse_v, reponse_f1, reponse_f2) VALUES ('$numero_question', '$question', '$reponse_v', '$reponse_f1', '$reponse_f2')";

if ($conn->query($sql) === TRUE) {
    header("Location: index.html");
    exit();
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>