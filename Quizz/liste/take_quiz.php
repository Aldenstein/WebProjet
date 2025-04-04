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

// récupérer questions du quiz
$sql_questions = "SELECT id, question_text, question_type, option1, option2, option3, correct_option FROM questions WHERE quiz_id = ?";
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

if ($total_questions === 0) {
    echo "<script>alert('Aucune question trouvée pour ce quiz.');</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ctrl+Quizz</title>
    <link rel="stylesheet" href="take_quiz.css">
    <link rel="icon" href="../images/icone.jpg">
    <style>
        .progress-bar-container {
            width: 100%;
            background-color: #e0e0e0;
            border-radius: 25px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .progress-bar {
            height: 20px;
            width: 0%;
            background-color: #76c7c0;
            transition: width 0.3s ease;
        }
    </style>
    <script>
        let currentQuestion = 0;
        let totalQuestions = <?php echo $total_questions; ?>;

        function startQuiz() {
            const progressBar = document.getElementById('progressBar');
            const timerElement = document.getElementById('timer');
            const form = document.getElementById('quizForm');

            function updateProgressBar() {
                const progress = ((currentQuestion + 1) / totalQuestions) * 100;
                progressBar.style.width = `${progress}%`;
            }

            function showNextQuestion() {
                const questions = document.querySelectorAll('.question');
                questions.forEach((question, index) => {
                    question.style.display = index === currentQuestion ? 'block' : 'none';
                });
                updateProgressBar();
            }

            function startTimer() {
                let timer = 8; // 8 secondes par question
                timerElement.textContent = `Temps restant : ${timer} secondes`;

                const interval = setInterval(() => {
                    if (timer > 0) {
                        timer--;
                        timerElement.textContent = `Temps restant : ${timer} secondes`;
                    } else {
                        clearInterval(interval);
                        currentQuestion++;
                        if (currentQuestion < totalQuestions) {
                            showNextQuestion();
                            startTimer();
                        } else {
                            form.submit(); // Soumettre le formulaire automatiquement à la fin
                            window.location.href = "liste.html";
                        }
                    }
                }, 1000);
            }

            showNextQuestion();
            startTimer();
        }

        window.onload = startQuiz;
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
    <div class="card">
        <h1>Questions du Quizz</h1>
        <div class="progress-bar-container">
            <div id="progressBar" class="progress-bar"></div>
        </div>
        <p id="timer">Temps restant : 8 secondes</p>
        <form id="quizForm" action="take_quiz_submit.php?quiz_id=<?php echo $quiz_id; ?>" method="POST">
            <?php while ($question = $result_questions->fetch_assoc()): ?>
                <div class="question" style="display: none;">
                    <p><strong><?php echo htmlspecialchars($question['question_text']); ?></strong></p>
                    <?php if ($question['question_type'] === "QCM"): ?>
                        <label>
                            <input type="radio" name="answer[<?php echo $question['id']; ?>]" value="<?php echo htmlspecialchars($question['option1']); ?>" required>
                            <?php echo htmlspecialchars($question['option1']); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="answer[<?php echo $question['id']; ?>]" value="<?php echo htmlspecialchars($question['option2']); ?>">
                            <?php echo htmlspecialchars($question['option2']); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="answer[<?php echo $question['id']; ?>]" value="<?php echo htmlspecialchars($question['option3']); ?>">
                            <?php echo htmlspecialchars($question['option3']); ?>
                        </label><br>
                    <?php elseif ($question['question_type'] === "Vrai/Faux"): ?>
                        <label>
                            <input type="radio" name="answer[<?php echo $question['id']; ?>]" value="vrai" required>
                            Vrai
                        </label><br>
                        <label>
                            <input type="radio" name="answer[<?php echo $question['id']; ?>]" value="faux">
                            Faux
                        </label><br>
                    <?php elseif ($question['question_type'] === "Ouverte"): ?>
                        <label>
                            <input type="text" name="answer[<?php echo $question['id']; ?>]" placeholder="Votre réponse" required>
                        </label><br>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
            <button type="submit" class="btn"><img src="../images/save.png" alt="envoyer les réponses" width="50px"></button>
        </form>
    </div>
</body>
</html>