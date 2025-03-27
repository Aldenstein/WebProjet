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

<!-- Formulaire HTML pour ajouter une question -->
<form method="POST" action="">
    <label for="question_text">Question :</label><br>
    <textarea id="question_text" name="question_text" required></textarea><br><br>

    <label for="option1">Option 1 :</label><br>
    <input type="text" id="option1" name="option1" required><br><br>

    <label for="option2">Option 2 :</label><br>
    <input type="text" id="option2" name="option2" required><br><br>

    <label for="option3">Option 3 :</label><br>
    <input type="text" id="option3" name="option3" required><br><br>

    <label for="correct_option">Option correcte (1, 2 ou 3) :</label><br>
    <input type="number" id="correct_option" name="correct_option" min="1" max="3" required><br><br>

    <button type="submit">Ajouter la question</button>
</form>