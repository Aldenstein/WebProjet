<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier que les champs nécessaires existent
    if (!isset($_POST['quiz-title']) || !isset($_POST['questions']) || !isset($_POST['options']) || !isset($_POST['correct-options'])) {
        die("Erreur : Certains champs du formulaire sont manquants.");
    }

    $quizTitle = $_POST['quiz-title'];
    $questions = $_POST['questions'];
    $options = $_POST['options'];
    $correctOptions = $_POST['correct-options'];

    // Insérer le titre du quiz dans la table quizzes
    $sql = "INSERT INTO quizzes (title) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $quizTitle);
    if (!$stmt->execute()) {
        die("Erreur lors de l'insertion du quiz : " . $stmt->error);
    }
    $quizId = $stmt->insert_id;
    $stmt->close();

    // Insérer les questions et options dans la table questions
    $sql = "INSERT INTO questions (quiz_id, question_text, option1, option2, option3, correct_option) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    foreach ($questions as $index => $question) {
        // Vérifier que les options et l'option correcte existent
        if (!isset($options[$index]) || count($options[$index]) < 3) {
            die("Erreur : Les options pour la question $index ne sont pas correctement définies.");
        }
        if (!isset($correctOptions[$index])) {
            die("Erreur : L'option correcte pour la question $index n'est pas définie.");
        }

        $option1 = $options[$index][0];
        $option2 = $options[$index][1];
        $option3 = $options[$index][2];
        $correctOption = (int)$correctOptions[$index];

        // Assurez-vous que les variables sont correctement liées
        $stmt->bind_param("iisssi", $quizId, $question, $option1, $option2, $option3, $correctOption);
        if (!$stmt->execute()) {
            die("Erreur lors de l'insertion de la question $index : " . $stmt->error);
        }
    }

    $stmt->close();
    $conn->close();

    echo "<h1>Quiz Enregistré avec Succès !</h1>";
} else {
    echo "Erreur : La requête doit être de type POST.";
}
?>