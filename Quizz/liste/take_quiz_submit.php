<?php
// filepath: c:\Users\Devia\Desktop\WebProjet\Quizz\liste\take_quiz_submit.php
session_start(); // Démarrer la session

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Erreur : Aucun utilisateur connecté. Veuillez vous connecter.");
}

$user_id = $_SESSION['user_id']; // Récupérer l'ID de l'utilisateur connecté

// Vérifier si le quiz_id est présent dans l'URL
if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    die("Erreur : Code PIN du quiz manquant ou invalide.");
}

$quiz_id = (int)$_GET['quiz_id'];
$score = 0;

// Récupérer les questions du quiz
$sql_questions = "SELECT id, question_type, correct_option FROM questions WHERE quiz_id = ?";
$stmt_questions = $conn->prepare($sql_questions);

if (!$stmt_questions) {
    die("Erreur lors de la préparation de la requête : " . $conn->error);
}

$stmt_questions->bind_param("i", $quiz_id);
$stmt_questions->execute();
$result_questions = $stmt_questions->get_result();

$total_questions = $result_questions->num_rows;

while ($question = $result_questions->fetch_assoc()) {
    $question_id = $question['id'];
    $question_type = $question['question_type'];
    $correct_option = strtolower(trim($question['correct_option'])); // Réponse correcte

    if ($question_type === "QCM" || $question_type === "Vrai/Faux") {
        // Comparer la réponse utilisateur avec la réponse correcte
        if (isset($_POST['answer'][$question_id]) && strtolower(trim($_POST['answer'][$question_id])) === $correct_option) {
            $score++;
        }
    } elseif ($question_type === "Ouverte") {
        // Comparer la réponse utilisateur avec la réponse correcte pour les questions ouvertes
        if (isset($_POST['answer'][$question_id]) && strtolower(trim($_POST['answer'][$question_id])) === $correct_option) {
            $score++;
        }
    }
}

// Mettre à jour le score de l'utilisateur
$stmt_score = $conn->prepare("UPDATE users SET score = COALESCE(score, 0) + ? WHERE id = ?");
if (!$stmt_score) {
    die("Erreur lors de la préparation de la requête de mise à jour du score : " . $conn->error);
}

$increment = $score + 1; // Ajouter 1 point au score calculé
$stmt_score->bind_param("ii", $increment, $user_id);
$stmt_score->execute();

if ($stmt_score->affected_rows === 0) {
    die("Erreur : Le score n'a pas été mis à jour. Vérifiez que l'utilisateur existe.");
}

$stmt_score->close();

// Afficher les résultats directement
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats du Quiz</title>
    <link rel="stylesheet" href="take_quiz.css">
</head>
<body>
    <div class="card">
        <h1>Résultats du Quiz</h1>
        <p>Quiz ID : <?php echo htmlspecialchars($quiz_id); ?></p>
        <p>Score : <?php echo htmlspecialchars($score); ?> / <?php echo htmlspecialchars($total_questions); ?></p>
        <p>Votre score total a été mis à jour avec 1 point supplémentaire.</p>
        <a href="liste.html" class="btn">Rejoindre un autre quiz</a>
    </div>
</body>
</html>
