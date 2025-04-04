<?php
session_start(); // démarrer la session

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

// vérifier connexion
if ($conn->connect_error) {
    echo "<script>alert('Erreur de connexion à la base de données : " . $conn->connect_error . "');</script>";
    exit;
}

// vérifier si user connecté
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Pas d'utilisateur connecté');</script>";
    exit;
}

$user_id = $_SESSION['user_id']; // récupérer id user

// vérifier si quiz_id présent
if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    echo "<script>alert('Quiz ID manquant ou invalide');</script>";
    exit;
}

$quiz_id = (int)$_GET['quiz_id'];
$score = 0;

// récupérer questions du quiz
$sql_questions = "SELECT id, question_type, correct_option FROM questions WHERE quiz_id = ?";
$stmt_questions = $conn->prepare($sql_questions);

// vérifier requête
if (!$stmt_questions) {
    echo "<script>alert('Erreur lors de la préparation de la requête : " . $conn->error . "');</script>";
    exit;
}

$stmt_questions->bind_param("i", $quiz_id);
$stmt_questions->execute();
$result_questions = $stmt_questions->get_result();

$total_questions = $result_questions->num_rows;

// boucle sur les questions
while ($question = $result_questions->fetch_assoc()) {
    $question_id = $question['id'];
    $question_type = $question['question_type'];
    $correct_option = strtolower(trim($question['correct_option'])); // bonne réponse

    if ($question_type === "QCM" || $question_type === "Vrai/Faux") {
        // vérifier réponse utilisateur
        if (isset($_POST['answer'][$question_id])) {
            $user_answer = strtolower(trim($_POST['answer'][$question_id]));
            if ($user_answer === $correct_option) {
                $score++;
            }
        }
    } elseif ($question_type === "Ouverte") {
        // vérifier réponse ouverte
        if (isset($_POST['answer'][$question_id])) {
            $user_answer = strtolower(trim($_POST['answer'][$question_id]));
            if ($user_answer === $correct_option) {
                $score++;
            }
        }
    }
}

// mettre à jour score utilisateur
$stmt_score = $conn->prepare("UPDATE users SET score = COALESCE(score, 0) + ? WHERE id = ?");
if (!$stmt_score) {
    echo "<script>alert('Erreur lors de la mise à jour du score : " . $conn->error . "');</script>";
    exit;
}

$increment = $score; // ajouter score calculé
$stmt_score->bind_param("ii", $increment, $user_id);
$stmt_score->execute();

if ($stmt_score->affected_rows === 0) {
    echo "<script>alert('Score non mis à jour');</script>";
    exit;
}

$stmt_score->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ctrl+Quizz - Résultats</title>
    <link rel="stylesheet" href="take_quiz_submit.css"> 
    <link rel="icon" href="../images/icone.jpg">
</head>
<body>
    <div class="navbar">
        <button name="timer" value="timer" id="homebtn" class="btn">    
            <a href="../liste/liste.html">        
                <img src="../images/home.jpg" alt="send" width="40px" height="40px">
            </a> 
        </button>
        <h1 align="center">Bienvenue sur Ctrl+Quizz !</h1>
        <button type="submit" name="deconnexion" value="deco" id="decobtn" class="btn">
            <a href="../deco/deco.html">            
                <img src="../images/deco.jpg" alt="send" width="40px" height="40px">
            </a>
        </button>
    </div>
    <div class="card">
        <h1>Résultats de votre Quizz</h1>
        <p>Quiz id : <?php echo htmlspecialchars($quiz_id); ?></p>
        <p>Score : <?php echo htmlspecialchars($score); ?> / <?php echo htmlspecialchars($total_questions); ?></p>
        <button id="btnautrequiz" class="btn">
            <a href="../liste/liste.html">
                <img src="../images/pin.jpg" alt="Rejoindre un autre quizz" width="40px" height="40px">
            </a>
        </button>
    </div>
</body>
</html>