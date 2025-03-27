<?php
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
        $question_id = $question['question_id'];
        $correct_option = $question['correct_option'];
        if (isset($_POST['answer'][$question_id])) {
            $user_answer = $_POST['answer'][$question_id];
            if ($user_answer === $correct_option) {
                $score++;
            }
        }
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
    <script src="script.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="icon" href="../images/icone.jpg">
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
                        $question_id = $question['question_id'];
                    ?>
                        <h3>Question <?php echo $question_number; ?> : <?php echo htmlspecialchars($question['question']); ?></h3>
                        <div class="input-container">
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