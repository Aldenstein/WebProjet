<?php
$servername = "localhost";
$username = "root"; // Remplacez par votre nom d'utilisateur MySQL
$password = ""; // Remplacez par votre mot de passe MySQL
$dbname = "projetweb";

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quizTitle = $_POST['quiz-title'];
    $questions = $_POST['questions'];
    $options = $_POST['options'];
    $correctOptions = $_POST['correct-options'];

    // Insérer le titre du quiz dans la table quizzes
    $sql = "INSERT INTO quizzes (title) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $quizTitle);
    $stmt->execute();
    $quizId = $stmt->insert_id;
    $stmt->close();

    // Insérer les questions et options dans la table questions
    $sql = "INSERT INTO questions (quiz_id, question_text, option1, option2, correct_option) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    foreach ($questions as $index => $question) {
        $option1 = $options[$index][0];
        $option2 = $options[$index][1];
        $correctOption = $correctOptions[$index];

        // Assurez-vous que les variables sont correctement liées
        $stmt->bind_param("iissi", $quizId, $question, $option1, $option2, $correctOption);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();

    echo "<h1>Quiz Enregistré avec Succès !</h1>";
}
?>