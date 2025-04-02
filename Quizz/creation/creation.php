<?php
// Connexion à la base de données
$host = 'localhost';
$dbname = 'projetweb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Gestion de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $question = trim($_POST['question']);
    $question_type = trim($_POST['question_type']);
    $correct_option = isset($_POST['correct_option']) ? trim($_POST['correct_option']) : null;
    $formatted_answer = isset($_POST['formatted_answer']) ? trim($_POST['formatted_answer']) : null;

    // Vérifier si un quiz avec ce titre existe déjà
    $stmt = $pdo->prepare("SELECT quiz_id FROM quizzes WHERE title = ?");
    $stmt->execute([$title]);
    $quiz = $stmt->fetch();

    if ($quiz) {
        $quiz_id = $quiz['quiz_id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO quizzes (title) VALUES (?)");
        $stmt->execute([$title]);
        $quiz_id = $pdo->lastInsertId();
    }

    // Insertion de la question dans la table questions
    try {
        $stmt = $pdo->prepare("
            INSERT INTO questions (quiz_id, question, question_type, correct_option)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$quiz_id, $question, $question_type, $correct_option]);
        echo "<p>Question ajoutée avec succès au quiz '$title' !</p>";
    } catch (PDOException $e) {
        die("Erreur lors de l'insertion de la question : " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ctrl+Quizz - Création</title>
    <link rel="stylesheet" href="creation.css">
    <link rel="icon" href="../images/icone.jpg">
    <script src="creation.js" defer></script>
</head>
<body>
    <div class="card">
        <h1>Créer un Quiz</h1>
        <form id="quizz-form" action="creation.php" method="POST">
            <!-- Titre du Quiz -->
            <div class="input-container">
                <input placeholder="Titre du Quiz" name="title" id="title" class="input-fichier" type="text" required>
                <label for="title" class="input-label">Titre du Quiz :</label>
                <span class="input-aesthetic"></span>
            </div>

            <!-- Question -->
            <div class="input-container">
                <input placeholder="Question" name="question" id="question" class="input-fichier" type="text" required>
                <label for="question" class="input-label">Question :</label>
                <span class="input-aesthetic"></span>
            </div>

            <!-- Type de Question -->
            <div class="input-container">
                <label for="question_type" class="input-label">Type de Question :</label>
                <select name="question_type" id="question_type" class="input-fichier" required>
                    <option value="">Choisir Type</option>
                    <option value="QCM">QCM</option>
                    <option value="Vrai/Faux">Vrai/Faux</option>
                    <option value="Ouverte">Ouverte</option>
                </select>
                <span class="input-aesthetic"></span>
            </div>

            <!-- Options pour QCM -->
            <div id="qcm-options" style="display: none;">
                <div class="input-container">
                    <input placeholder="Option 1" name="option1" id="option1" class="input-fichier" type="text">
                    <label for="option1" class="input-label">Option 1 :</label>
                    <span class="input-aesthetic"></span>
                </div>
                <div class="input-container">
                    <input placeholder="Option 2" name="option2" id="option2" class="input-fichier" type="text">
                    <label for="option2" class="input-label">Option 2 :</label>
                    <span class="input-aesthetic"></span>
                </div>
                <div class="input-container">
                    <input placeholder="Option 3" name="option3" id="option3" class="input-fichier" type="text">
                    <label for="option3" class="input-label">Option 3 :</label>
                    <span class="input-aesthetic"></span>
                </div>
                <div class="input-container">
                    <input placeholder="Option correcte (1, 2 ou 3)" name="correct_option" id="correct_option" class="input-fichier" type="number" min="1" max="3">
                    <label for="correct_option" class="input-label">Option correcte :</label>
                    <span class="input-aesthetic"></span>
                </div>
            </div>

            <!-- Options pour Vrai/Faux -->
            <div id="true-false-options" style="display: none;">
                <div class="input-container">
                    <input placeholder="Réponse correcte (Vrai ou Faux)" name="correct_option" id="true_false_correct" class="input-fichier" type="text">
                    <label for="true_false_correct" class="input-label">Réponse correcte :</label>
                    <span class="input-aesthetic"></span>
                </div>
            </div>

            <!-- Réponse pour Question Ouverte -->
            <div id="open-answer" style="display: none;">
                <div class="input-container">
                    <textarea placeholder="Réponse préformatée" name="formatted_answer" id="formatted_answer" class="input-fichier"></textarea>
                    <label for="formatted_answer" class="input-label">Réponse préformatée :</label>
                    <span class="input-aesthetic"></span>
                </div>
            </div>

            <!-- Bouton Soumettre -->
            <button type="submit" class="btn">
                <img src="../images/plus.png" alt="add-question" width="40px" height="40px">
            </button>
        </form>
    </div>
    <script>
        document.getElementById('question_type').addEventListener('change', function() {
            const qcmOptions = document.getElementById('qcm-options');
            const trueFalseOptions = document.getElementById('true-false-options');
            const openAnswer = document.getElementById('open-answer');

            qcmOptions.style.display = 'none';
            trueFalseOptions.style.display = 'none';
            openAnswer.style.display = 'none';

            if (this.value === 'QCM') {
                qcmOptions.style.display = 'block';
            } else if (this.value === 'Vrai/Faux') {
                trueFalseOptions.style.display = 'block';
            } else if (this.value === 'Ouverte') {
                openAnswer.style.display = 'block';
            }
        });
    </script>
</body>
</html>