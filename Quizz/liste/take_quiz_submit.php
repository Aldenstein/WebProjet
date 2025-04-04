<?php
session_start(); // Démarrer la session

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Vérifier si le quiz_id est présent
if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    die("Quiz ID manquant ou invalide.");
}

$quiz_id = intval($_GET['quiz_id']);
$user_answers = isset($_POST['answer']) ? $_POST['answer'] : []; // Récupérer les réponses de l'utilisateur

// Récupérer les questions et les réponses correctes
$sql = "SELECT id, question_text, correct_option FROM questions WHERE quiz_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();

$score = 0;
$total_questions = $result->num_rows;
$feedback = []; // Stocker les résultats pour chaque question

while ($row = $result->fetch_assoc()) {
    $question_id = $row['id'];
    $question_text = $row['question_text']; // Texte de la question
    $correct_answer = trim(strtolower($row['correct_option'])); // Réponse correcte

    // Vérifier si l'utilisateur a répondu à cette question
    if (isset($user_answers[$question_id])) {
        $user_answer = trim(strtolower($user_answers[$question_id])); // Réponse de l'utilisateur

        if ($user_answer === $correct_answer) {
            $score++;
            $feedback[] = [
                'question_text' => $question_text,
                'result' => 'Correct'
            ]; // Réponse correcte
        } else {
            $feedback[] = [
                'question_text' => $question_text,
                'result' => 'Incorrect'
            ]; // Réponse incorrecte
        }
    } else {
        $feedback[] = [
            'question_text' => $question_text,
            'result' => 'Pas de réponse'
        ]; // Pas de réponse
    }
}

// Fermer la connexion
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ctrl+Quizz</title>
    <link rel="stylesheet" href="take_quiz.css">
    <link rel="icon" href="../images/icone.jpg">
</head>
<body>
    <div class="card">
        <h1>Résultats du Quiz</h1>
        <p>Score : <?php echo $score; ?> / <?php echo $total_questions; ?></p>
        <div class="feedback">
            <?php foreach ($feedback as $item): ?>
                <div class="question-feedback" style="background-color: <?php echo $item['result'] === 'Correct' ? 'lightgreen' : ($item['result'] === 'Incorrect' ? 'lightcoral' : 'lightgray'); ?>;">
                    <p><strong>Question :</strong> <?php echo htmlspecialchars($item['question_text']); ?></p>
                    <p><strong>Résultat :</strong> <?php echo ucfirst($item['result']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="submit" id="btn" class="btn">
            <a href="../liste/liste.html">
                <img src="../images/pin.jpg" alt="Envoyer" width="40px" height="40px">
            </a>
        </button>
    </div>
</body>
</html>