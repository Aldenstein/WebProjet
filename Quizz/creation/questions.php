<?php
session_start();

// Vérifier si l'ID du quiz est disponible dans la session
if (!isset($_SESSION['quiz_id'])) {
    die("Aucun quiz sélectionné. Veuillez créer un quiz d'abord.");
}

$quiz_id = $_SESSION['quiz_id'];


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question_text = $_POST['question_text'];
    $option1 = $_POST['option1'];
    $option2 = $_POST['option2'];
    $option3 = $_POST['option3'];
    $correct_option = $_POST['correct_option'];

    if (!empty($question_text) && !empty($option1) && !empty($option2) && !empty($option3) && !empty($correct_option)) {
        $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, option1, option2, option3, correct_option) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $quiz_id, $question_text, $option1, $option2, $option3, $correct_option);

        if ($stmt->execute()) {
            echo "Question ajoutée avec succès.";
        } else {
            echo "Erreur lors de l'ajout de la question : " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Tous les champs sont obligatoires.";
    }
}

$conn->close();
?>