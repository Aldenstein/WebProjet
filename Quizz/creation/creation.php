<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Vérifier si toutes les données du formulaire sont présentes
if (!isset($_POST['title'], $_POST['question'], $_POST['option1'], $_POST['option2'], $_POST['option3'], $_POST['correct_option'])) {
    die("Erreur : Tous les champs du formulaire doivent être remplis.");
}

// Récupérer les données du formulaire
$title = $_POST['title'];
$question = $_POST['question'];
$option1 = $_POST['option1'];
$option2 = $_POST['option2'];
$option3 = $_POST['option3'];
$correct_option = $_POST['correct_option'];

// Insérer le titre du quiz dans la table quizzes
$sql_quiz = "INSERT INTO quizzes (title) VALUES (?)";
$stmt_quiz = $conn->prepare($sql_quiz);
$stmt_quiz->bind_param("s", $title); // "s" pour string

if ($stmt_quiz->execute()) {
    // Récupérer l'ID du quiz nouvellement inséré
    $quiz_id = $conn->insert_id;

    // Insérer la question dans la table questions
    $sql_question = "INSERT INTO questions (quiz_id, question, option1, option2, option3, correct_option) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_question = $conn->prepare($sql_question);

    // Vérifier le type de correct_option dans ta base de données
    // Si correct_option est un VARCHAR, on le traite comme une chaîne ("s")
    // Si c'est un INT, on le traite comme un entier ("i")
    // Ici, je suppose que correct_option est une chaîne (VARCHAR) car tu passes des valeurs comme '2'
    $stmt_question->bind_param("isssss", $quiz_id, $question, $option1, $option2, $option3, $correct_option);
    
    if ($stmt_question->execute()) {
        echo "Nouvelle question ajoutée avec succès";
    } else {
        echo "Erreur lors de l'insertion de la question : " . $conn->error;
    }

    $stmt_question->close();
} else {
    echo "Erreur lors de l'insertion du quiz : " . $conn->error;
}

$stmt_quiz->close();
$conn->close();
?>