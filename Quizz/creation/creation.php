<<<<<<< Updated upstream
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

// Récupérer les données du formulaire
$title = $_POST['title'];
$question = $_POST['question'];
$option1 = $_POST['option1'];
$option2 = $_POST['option2'];
$option3 = $_POST['option3'];
$correct_option = $_POST['correct_option'];

// Insérer le titre du quiz dans la table quizzes
$sql_quiz = "INSERT INTO quizzes (title) VALUES ('$title')";
if ($conn->query($sql_quiz) === TRUE) {
    // Récupérer l'ID du quiz nouvellement inséré
    $quiz_id = $conn->insert_id;

    // Insérer la question dans la table questions
    $sql_question = "INSERT INTO questions (quiz_id, question, option1, option2, option3, correct_option)
                     VALUES ($quiz_id, '$question', '$option1', '$option2', '$option3', $correct_option)";

    if ($conn->query($sql_question) === TRUE) {
        echo "Nouvelle question ajoutée avec succès";
    } else {
        echo "Erreur: " . $sql_question . "<br>" . $conn->error;
    }
} else {
    echo "Erreur: " . $sql_quiz . "<br>" . $conn->error;
}

// Fermer la connexion
$conn->close();
?>
=======
>>>>>>> Stashed changes
