<?php
session_start(); // demarre session

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

// verifier connexion
if ($conn->connect_error) {
    die("erreur connexion : " . $conn->connect_error);
}

// verifier si user connecte
if (!isset($_SESSION['user_id'])) {
    die("pas d'utilisateur connecte");
}

$user_id = $_SESSION['user_id']; // recup id user

// verifier si quiz_id present
if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    die("quiz id manquant ou invalide");
}

$quiz_id = (int)$_GET['quiz_id'];
$score = 0;

// recup questions du quiz
$sql_questions = "SELECT id, question_type, correct_option FROM questions WHERE quiz_id = ?";
$stmt_questions = $conn->prepare($sql_questions);

// verifier requete
if (!$stmt_questions) {
    die("erreur requete : " . $conn->error);
}

$stmt_questions->bind_param("i", $quiz_id);
$stmt_questions->execute();
$result_questions = $stmt_questions->get_result();

$total_questions = $result_questions->num_rows;

// boucle sur les questions
while ($question = $result_questions->fetch_assoc()) {
    $question_id = $question['id'];
    $question_type = $question['question_type'];
    $correct_option = strtolower(trim($question['correct_option'])); // bonne reponse

    if ($question_type === "QCM" || $question_type === "Vrai/Faux") {
        // verifier reponse user
        if (isset($_POST['answer'][$question_id]) && strtolower(trim($_POST['answer'][$question_id])) === $correct_option) {
            $score++;
        }
    } elseif ($question_type === "Ouverte") {
        // verifier reponse ouverte
        if (isset($_POST['answer'][$question_id]) && strtolower(trim($_POST['answer'][$question_id])) === $correct_option) {
            $score++;
        }
    }
}

// mettre a jour score user
$stmt_score = $conn->prepare("UPDATE users SET score = COALESCE(score, 0) + ? WHERE id = ?");
if (!$stmt_score) {
    die("erreur mise a jour score : " . $conn->error);
}

$increment = $score; // ajouter score calcule
$stmt_score->bind_param("ii", $increment, $user_id);
$stmt_score->execute();

if ($stmt_score->affected_rows === 0) {
    die("score pas mis a jour");
}

$stmt_score->close();

// afficher resultats
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>resultats quiz</title>
    <link rel="stylesheet" href="take_quiz.css"> <!-- Appliquer le style -->
</head>
<body>
    <div class="card">
        <h1>resultats quiz</h1>
        <p>quiz id : <?php echo htmlspecialchars($quiz_id); ?></p>
        <p>score : <?php echo htmlspecialchars($score); ?> / <?php echo htmlspecialchars($total_questions); ?></p>
        <a href="liste.html" class="btn">rejoindre un autre quiz</a>
    </div>
</body>
</html>