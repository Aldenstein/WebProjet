<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Vérifier si le formulaire pour le titre a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['titre'])) {
    $titre = trim($_POST['titre']);

    if (!empty($titre)) {
        $stmt = $conn->prepare("INSERT INTO quiz (titre) VALUES (?)");
        if ($stmt) {
            $stmt->bind_param("s", $titre);
            if ($stmt->execute()) {
                $_SESSION['quiz_id'] = $conn->insert_id;
                header("Location: creation.html");
                exit();
            }
            $stmt->close();
        }
    }
}

// Vérifier si le formulaire pour ajouter une question a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['question_text'])) {
    if (isset($_SESSION['quiz_id'])) {
        $quiz_id = $_SESSION['quiz_id'];
        $question_text = $_POST['question_text'];
        $option1 = $_POST['option1'];
        $option2 = $_POST['option2'];
        $option3 = $_POST['option3'];
        $correct_option = $_POST['correct_option'];

        if (!empty($question_text) && !empty($option1) && !empty($option2) && !empty($option3) && !empty($correct_option)) {
            $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question_text, option1, option2, option3, correct_option) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("issssi", $quiz_id, $question_text, $option1, $option2, $option3, $correct_option);
                $stmt->execute();
                $stmt->close();
            }
        }
    } else {
        echo "Aucun quiz sélectionné. Veuillez d'abord enregistrer un titre.";
    }
}

$conn->close();
?>