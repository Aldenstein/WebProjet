<?php
// demarre session
session_start();

// connexion a la bdd
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

// verifier connexion
if ($conn->connect_error) {
    die("erreur connexion : " . $conn->connect_error);
}

// verifier si quiz_id present
if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    die("quiz id manquant ou invalide");
}

$quiz_id = (int)$_GET['quiz_id'];

// recup titre du quiz
$sql_quiz = "SELECT title FROM quizzes WHERE id = ?";
$stmt_quiz = $conn->prepare($sql_quiz);
$stmt_quiz->bind_param("i", $quiz_id);
$stmt_quiz->execute();
$result_quiz = $stmt_quiz->get_result();

// verifier si quiz existe
if ($result_quiz->num_rows === 0) {
    die("quiz non trouvé");
}

$quiz = $result_quiz->fetch_assoc();
$quiz_title = $quiz['title'];

// recup questions du quiz
$sql_questions = "SELECT * FROM questions WHERE quiz_id = ?";
$stmt_questions = $conn->prepare($sql_questions);
$stmt_questions->bind_param("i", $quiz_id);
$stmt_questions->execute();
$result_questions = $stmt_questions->get_result();

$stmt_quiz->close();
$stmt_questions->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ctrl+Quizz - <?php echo htmlspecialchars($quiz_title); ?></title>
    <link rel="stylesheet" href="take_quiz.css">
    <link rel="icon" href="../images/icone.jpg">
    <script>
        // timer pour le quiz
        let timePerQuestion = 8; // secondes par question
        let totalQuestions = <?php echo $result_questions->num_rows; ?>;
        let timeLeft = totalQuestions * timePerQuestion; // temps total
        let timer;

        // demarre le timer
        function startTimer() {
            const timerElement = document.getElementById('timer');

            timer = setInterval(() => {
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    alert('Temps épuisé !'); // afficher une alerte
                    window.location.href = '../liste/liste.html'; // rediriger vers liste.html
                } else {
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    timerElement.textContent = `Temps restant : ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                    timeLeft--;
                }
            }, 1000);
        }

        window.onload = startTimer;
    </script>
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
    <div id="timer"></div>
    <div class="card">
        <h1>Quiz : <?php echo htmlspecialchars($quiz_title); ?></h1>

        <?php if ($result_questions->num_rows > 0): ?>
            <form id="quiz-form" method="POST" action="take_quiz_submit.php?quiz_id=<?php echo $quiz_id; ?>">
                <?php
                $question_number = 1;
                while ($question = $result_questions->fetch_assoc()):
                    $question_id = $question['id'];
                    $question_type = $question['question_type'];
                ?>
                    <div class="question">
                        <h3>Question <?php echo $question_number; ?> : <?php echo htmlspecialchars($question['question_text']); ?></h3>
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
                    </div>
                <?php
                    $question_number++;
                endwhile;
                ?>
                <button type="submit" name="action" value="register" id="btn" class="btn">             
                    <img src="../images/save.png" alt="adduser" width="40px" height="40px">
                </button>
            </form>
        <?php else: ?>
            <p>Aucune question trouvée pour ce quiz.</p>
            <button id="btnautrequiz" class="btn">
            <a href="../liste/liste.html">
                <img src="../images/plus.jpg" alt="Rejoindre un autre quizz" width="40px" height="40px">
            </a>
        </button>
        <?php endif; ?>
    </div>
</body>
</html>