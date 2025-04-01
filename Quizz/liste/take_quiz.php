<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    die("Erreur : Code PIN du quiz manquant ou invalide.");
}

$quiz_id = (int)$_GET['quiz_id'];

$sql_quiz = "SELECT title FROM quizzes WHERE quiz_id = ?";
$stmt_quiz = $conn->prepare($sql_quiz);
$stmt_quiz->bind_param("i", $quiz_id);
$stmt_quiz->execute();
$result_quiz = $stmt_quiz->get_result();

if ($result_quiz->num_rows === 0) {
    die("Erreur : Quiz non trouvé avec ce code PIN.");
}

$quiz = $result_quiz->fetch_assoc();
$quiz_title = $quiz['title'];

$sql_questions = "SELECT * FROM questions WHERE quiz_id = ?";
$stmt_questions = $conn->prepare($sql_questions);
$stmt_questions->bind_param("i", $quiz_id);
$stmt_questions->execute();
$result_questions = $stmt_questions->get_result();
$score = 0;
$total_questions = $result_questions->num_rows;
$submitted = isset($_POST['submit']);

if ($submitted) {
    $result_questions->data_seek(0);

    while ($question = $result_questions->fetch_assoc()) {
        $question_id = $question['id'];
        $question_type = $question['question_type'];

        if ($question_type === "QCM") {
            $correct_option = $question['qcm_rep'];
            if (isset($_POST['answer'][$question_id])) {
                $user_answer = intval($_POST['answer'][$question_id]);
                if ($user_answer === $correct_option) {
                    $score++;
                }
            }
        } elseif ($question_type === "Vrai/Faux") {
            $correct_option = intval($question['qcm_rep']); // 1 pour Vrai, 0 pour Faux
            if (isset($_POST['answer'][$question_id])) {
                $user_answer = intval($_POST['answer'][$question_id]);
                if ($user_answer === $correct_option) {
                    $score++;
                }
            }
        } elseif ($question_type === "Ouverte") {
            $correct_answer = strtolower(trim($question['formatted_answer']));
            if (isset($_POST['answer'][$question_id])) {
                $user_answer = strtolower(trim($_POST['answer'][$question_id]));
                if ($user_answer === $correct_answer) {
                    $score++;
                }
            }
        }
    }

    // Ajouter le score à l'utilisateur connecté
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $stmt_score = $conn->prepare("UPDATE users SET score = score + ? WHERE id = ?");
        $stmt_score->bind_param("ii", $score, $user_id);
        $stmt_score->execute();
        $stmt_score->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ctrl+Quizz - <?php echo htmlspecialchars($quiz_title); ?></title>
    <link rel="stylesheet" href="liste.css">
</head>
<body>
    <div class="navbar">
        <button type="submit" name="accueil" value="accueil" id="homebtn" class="btn">    
            <a href="../index.html">        
                <img src="../images/home.jpg" alt="send" width="40px" height="40px">
            </a> 
        </button>
        <h1 align="center">Bienvenue sur Ctrl+Quizz</h1>
        <button type="submit" name="deconnexion" value="deco" id="decobtn" class="btn">
            <a href="../deco/deco.html">            
                <img src="../images/deco.jpg" alt="send" width="40px" height="40px">
            </a>
        </button>
    </div>
    <div class="card">
        <h1>Quiz : <?php echo htmlspecialchars($quiz_title); ?></h1>

        <?php if ($submitted): ?>
            <h2>Résultat</h2>
            <p>Votre score : <?php echo $score; ?> / <?php echo $total_questions; ?></p>
            <p>Pourcentage : <?php echo ($total_questions > 0) ? round(($score / $total_questions) * 100, 2) : 0; ?>%</p>
            <a href="liste.html">Rejoindre un autre quiz</a>
        <?php else: ?>
            <?php if ($result_questions->num_rows > 0): ?>
                <form method="POST" action="">
                    <?php
                    $question_number = 1;
                    while ($question = $result_questions->fetch_assoc()):
                        $question_id = $question['id'];
                        $question_type = $question['question_type'];
                    ?>
                        <h3>Question <?php echo $question_number; ?> : <?php echo htmlspecialchars($question['question']); ?></h3>
                        <div class="input-container">
                            <?php if ($question_type === "QCM"): ?>
                                <label>
                                    <input type="radio" name="answer[<?php echo $question_id; ?>]" value="1" required>
                                    <?php echo htmlspecialchars($question['option1']); ?>
                                </label><br>
                                <label>
                                    <input type="radio" name="answer[<?php echo $question_id; ?>]" value="2">
                                    <?php echo htmlspecialchars($question['option2']); ?>
                                </label><br>
                                <label>
                                    <input type="radio" name="answer[<?php echo $question_id; ?>]" value="3">
                                    <?php echo htmlspecialchars($question['option3']); ?>
                                </label><br>
                            <?php elseif ($question_type === "Vrai/Faux"): ?>
                                <label>
                                    <input type="radio" name="answer[<?php echo $question_id; ?>]" value="1" required>
                                    Vrai
                                </label><br>
                                <label>
                                    <input type="radio" name="answer[<?php echo $question_id; ?>]" value="0">
                                    Faux
                                </label><br>
                            <?php elseif ($question_type === "Ouverte"): ?>
                                <label>
                                    <input type="text" name="answer[<?php echo $question_id; ?>]" placeholder="Votre réponse" required>
                                </label><br>
                            <?php endif; ?>
                        </div>
                    <?php
                        $question_number++;
                    endwhile;
                    ?>
                    <button type="submit" name="submit" id="btn" class="btn">Soumettre mes réponses</button>
                </form>
            <?php else: ?>
                <p>Aucune question trouvée pour ce quiz.</p>
                <a href="liste.html">Rejoindre un autre quiz</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$stmt_quiz->close();
$stmt_questions->close();
$conn->close();
?>