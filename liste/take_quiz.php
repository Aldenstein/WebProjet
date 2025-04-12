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
$sql_questions = "SELECT id, question_text, question_type, option1, option2, option3, correct_option 
                  FROM questions 
                  WHERE quiz_id = ? 
                  ORDER BY id ASC"; // Tri par ID croissant
$stmt_questions = $conn->prepare($sql_questions);

// Vérifier la requête
if (!$stmt_questions) {
    echo "<script>alert('Erreur lors de la préparation de la requête : " . $conn->error . "');</script>";
    exit;
}

$stmt_questions->bind_param("i", $quiz_id);
$stmt_questions->execute();
$result_questions = $stmt_questions->get_result();

error_log("Nombre de questions récupérées : " . $result_questions->num_rows);

$total_questions = $result_questions->num_rows;

// Vérifier si des questions existent
if ($total_questions === 0) {
    echo "<script>alert('Aucune question trouvée pour ce quiz.');</script>";
    exit;
}

// Récupérer une question spécifique
$sql_specific_question = "SELECT * FROM questions WHERE id = 20 AND quiz_id = ?";
$stmt_specific_question = $conn->prepare($sql_specific_question);

if (!$stmt_specific_question) {
    echo "<script>alert('Erreur lors de la préparation de la requête spécifique : " . $conn->error . "');</script>";
    exit;
}

$stmt_specific_question->bind_param("i", $quiz_id);
$stmt_specific_question->execute();
$result_specific_question = $stmt_specific_question->get_result();
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

        .question {
            display: none;
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
                <?php error_log("Question ID: " . $question['id']); // Log pour chaque question ?>
                <div class="question" style="display: none;">
                    <p><strong><?php echo htmlspecialchars($question['question_text']); ?></strong></p>
                    <?php if ($question['question_type'] === "QCM"): ?>
                        <label>
                            <input type="radio" name="answer[<?php echo $question['id']; ?>]" value="1" required>
                            <?php echo htmlspecialchars($question['option1']); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="answer[<?php echo $question['id']; ?>]" value="2">
                            <?php echo htmlspecialchars($question['option2']); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="answer[<?php echo $question['id']; ?>]" value="3">
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

<?php
// Vérifier si le résultat des questions est défini
if (isset($result_questions)) {
    while ($row = $result_questions->fetch_assoc()) {
        $question_id = $row['id'];
        $correct_answer = trim($row['correct_option']); // Réponse correcte (1, 2 ou 3)

        // Vérifier si l'utilisateur a répondu à cette question
        if (isset($user_answers[$question_id])) {
            $user_answer = trim($user_answers[$question_id]); // Réponse de l'utilisateur

            if ($user_answer === $correct_answer) {
                $score++;
                $feedback[] = [
                    'question_id' => $question_id,
                    'result' => 'Correct'
                ]; // Réponse correcte
            } else {
                $feedback[] = [
                    'question_id' => $question_id,
                    'result' => 'Incorrect'
                ]; // Réponse incorrecte
            }
        } else {
            $feedback[] = [
                'question_id' => $question_id,
                'result' => 'Pas de réponse'
            ]; // Pas de réponse
        }
    }
}

// Vérifier le type de question pour le QCM
if (isset($question_type) && $question_type === "QCM") {
    // Récupérer les options QCM
    $option1 = isset($_POST['option1']) ? trim(htmlspecialchars($_POST['option1'])) : '';
    $option2 = isset($_POST['option2']) ? trim(htmlspecialchars($_POST['option2'])) : '';
    $option3 = isset($_POST['option3']) ? trim(htmlspecialchars($_POST['option3'])) : '';
    $correct_option = isset($_POST['correct_option']) ? trim($_POST['correct_option']) : '';

    // Vérifier que la réponse correcte est un indice valide (1, 2 ou 3)
    if (!in_array($correct_option, ['1', '2', '3'])) {
        die("La réponse correcte doit être 1, 2 ou 3.");
    }
}
?>