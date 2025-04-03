<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pin = $_POST['pin'];

    // Correction de la requête SQL : utilisation de la colonne `id` au lieu de `quizz_id`
    $sql = "SELECT id FROM quizzes WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erreur lors de la préparation de la requête : " . $conn->error);
    }

    $stmt->bind_param("i", $pin); // Assurez-vous que $pin est un entier
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($quiz_id);
        $stmt->fetch();

        $_SESSION['quiz_id'] = $quiz_id;

        // Redirection vers la page du quiz
        header("Location: take_quiz.php?quiz_id=" . $quiz_id);
        exit();
    } else {
        echo "Code PIN invalide.";
    }

    $stmt->close();
}
$conn->close();
?>