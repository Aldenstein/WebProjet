<?php
session_start(); // Démarrer la session

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    echo "<script>alert('Erreur de connexion à la base de données : " . $conn->connect_error . "');</script>";
    exit;
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Pas d\'utilisateur connecté');</script>";
    exit;
}

$user_id = $_SESSION['user_id']; // Récupérer l'ID utilisateur

// Vérifier si quiz_id est présent
if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    echo "<script>alert('Quiz ID manquant ou invalide');</script>";
    exit;
}

$quiz_id = (int)$_GET['quiz_id'];

// Récupérer les questions du quiz
$sql_questions = "SELECT id, question_text, question_type, option1, option2, option3, correct_option FROM questions WHERE quiz_id = ?";
$stmt_questions = $conn->prepare($sql_questions);

// Vérifier la requête
if (!$stmt_questions) {
    echo "<script>alert('Erreur lors de la préparation de la requête : " . $conn->error . "');</script>";
    exit;
}

$stmt_questions->bind_param("i", $quiz_id);
$stmt_questions->execute();
$result_questions = $stmt_questions->get_result();

$total_questions = $result_questions->num_rows;

// Vérifier si des questions existent
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
    <link rel="icon" href="../images/icone.jpg">
    <style>
        /* Styles pour la barre de progression */
        .progress-bar-container {
            width: 100%;
            background-color: #f3f3f3;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .progress-bar {
            height: 20px;
            width: 0;
            background-color: #184766;
            border-radius: 5px;
            transition: width 0.5s;
        }
        .btn{
            background-color:transparent;
            border: none;
            padding: 10px;
            border-radius: 50px;
            margin-top: 1.5em;
            font-size: 1em;
            justify-content: center;
            align-items: center;
        }
        
        .btn img{
            display: inline-block;
            justify-content: center;
            align-items: center;
        }
        /* Styles pour la navbar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 98%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background: transparent;
        }
        .navbar button {
            background: none;
            border: none;
            color: black;
            font-size: 16px;
            cursor: pointer;
        }
        .navbar h1 {
            margin: 0;
            font-size: 2rem;
        }
        * {
            font-family: "Open Sans", sans-serif;
            font-optical-sizing: auto;
            font-weight: 500;
            font-style: normal;
            font-variation-settings:
            "wdth" 100;
        }
        body{
            background-color: #e6e6fa;
            justify-content: center;
            align-items: center;
            display: flex;
            height: 100vh;
            flex-direction: column;
        }
        .card{
            max-width: 800px;
            margin: 20px auto;
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 20px;
            background-color: #f0f8ff;
            border-radius: 10px;
            width: 800px;
            text-align: center;
        }

        .card h1{
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
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

    <!-- Carte contenant le quiz -->
    <div class="card">
        <h1>Questions du Quizz</h1>
        <!-- Barre de progression -->
        <div class="progress-bar-container">
            <div id="progressBar" class="progress-bar"></div>
        </div>
        <!-- Timer -->
        <p id="timer">Temps restant : 8 secondes</p>
        <!-- Formulaire du quiz -->
        <form id="quizForm" action="take_quiz_submit.php?quiz_id=<?php echo $quiz_id; ?>" method="POST" data-total-questions="<?php echo $total_questions; ?>">
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

    <!-- Script pour gérer le quiz -->
    <script>
        let currentQuestion = 0; // Question actuelle
        let totalQuestions = parseInt(document.getElementById('quizForm').dataset.totalQuestions, 10); // Nombre total de questions
        let timerInterval;

        function startQuiz() {
            const progressBar = document.getElementById('progressBar');
            const timerElement = document.getElementById('timer');
            const form = document.getElementById('quizForm');
            const submitButton = document.querySelector('button[type="submit"]');

            // Met à jour la barre de progression
            function updateProgressBar() {
                const progress = ((currentQuestion + 1) / totalQuestions) * 100;
                progressBar.style.width = `${progress}%`;
            }

            // Affiche la question suivante
            function showNextQuestion() {
                const questions = document.querySelectorAll('.question');
                questions.forEach((question, index) => {
                    question.style.display = index === currentQuestion ? 'block' : 'none';
                });
                updateProgressBar();
            }

            // Gère le timer
            function startTimer() {
                let timer = 8;
                timerElement.textContent = `Temps restant : ${timer} secondes`;

                timerInterval = setInterval(() => {
                    if (timer > 0) {
                        timer--;
                        timerElement.textContent = `Temps restant : ${timer} secondes`;
                    } else {
                        clearInterval(timerInterval);
                        currentQuestion++;
                        if (currentQuestion < totalQuestions) {
                            showNextQuestion();
                            startTimer();
                        } else {
                            progressBar.style.width = '100%';
                            form.submit();
                        }
                    }
                }, 1000);
            }

            // Gère le clic sur le bouton de soumission
            submitButton.addEventListener('click', (event) => {
                event.preventDefault();
                clearInterval(timerInterval);

                if (currentQuestion < totalQuestions - 1) {
                    currentQuestion++;
                    showNextQuestion();
                    startTimer();
                } else {
                    form.submit();
                }
            });

            // Si une seule question, remplir la barre immédiatement
            if (totalQuestions === 1) {
                progressBar.style.width = '100%';
            }

            showNextQuestion();
            startTimer();
        }

        window.onload = startQuiz; // Démarrer le quiz au chargement de la page
    </script>
</body>
</html>