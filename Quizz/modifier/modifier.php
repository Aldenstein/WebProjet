<?php
// filepath: c:\Users\Devia\Desktop\WebProjet\Quizz\modifier\modifier.php

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrage de la session
session_start();

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Initialisation des variables
$action = isset($_GET['action']) ? $_GET['action'] : '';
$quizzes = [];
$questions = [];
$message = '';

// Gestion des actions
if ($action === 'getQuizzes') {
    // Récupérer tous les quizzes
    $result = $conn->query("SELECT id, title FROM quizzes");
    if ($result && $result->num_rows > 0) {
        while ($quiz = $result->fetch_assoc()) {
            $quizzes[] = $quiz;
        }
    } else {
        $message = "Aucun quiz trouvé.";
    }
} elseif ($action === 'getQuestions' && isset($_GET['quizId'])) {
    // Récupérer les questions d'un quiz
    $quizId = intval($_GET['quizId']);
    $stmt = $conn->prepare("SELECT id, question_text, option1, option2, option3, correct_option FROM questions WHERE quiz_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $quizId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($question = $result->fetch_assoc()) {
                $questions[] = $question;
            }
        } else {
            $message = "Aucune question trouvée pour ce quiz.";
        }
        $stmt->close();
    } else {
        $message = "Erreur lors de la préparation de la requête.";
    }
} elseif ($action === 'deleteQuiz' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Supprimer un quiz
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Quiz supprimé avec succès.";
        } else {
            $message = "Erreur lors de la suppression du quiz.";
        }
        $stmt->close();
    } else {
        $message = "Erreur lors de la préparation de la requête.";
    }
} elseif ($action === 'saveQuestion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mettre à jour une question
    $id = intval($_POST['id']);
    $question_text = $_POST['question_text'];
    $option1 = $_POST['option1'];
    $option2 = $_POST['option2'];
    $option3 = $_POST['option3'];
    $correct_option = $_POST['correct_option'];

    $stmt = $conn->prepare("
        UPDATE questions 
        SET question_text = ?, option1 = ?, option2 = ?, option3 = ?, correct_option = ? 
        WHERE id = ?
    ");
    if ($stmt) {
        $stmt->bind_param("sssssi", $question_text, $option1, $option2, $option3, $correct_option, $id);
        if ($stmt->execute()) {
            $message = "Question mise à jour avec succès.";
        } else {
            $message = "Erreur lors de la mise à jour de la question.";
        }
        $stmt->close();
    } else {
        $message = "Erreur lors de la préparation de la requête.";
    }
} elseif ($action === 'deleteQuestion' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Supprimer une question
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Question supprimée avec succès.";
        } else {
            $message = "Erreur lors de la suppression de la question.";
        }
        $stmt->close();
    } else {
        $message = "Erreur lors de la préparation de la requête.";
    }
}

// Fermer la connexion à la base de données
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier les Quizzes</title>
    <link rel="stylesheet" href="modifier.css">
</head>
<body>
    <div class="navbar">
        <button id="homebtn" class="btn">
            <a href="../admin/admin.html">
                <img src="../images/home.jpg" alt="Accueil" width="40px" height="40px">
            </a>
        </button>
        <h1 align="center">Modifier les Quizzes</h1>
        <button id="decobtn" class="btn">
            <a href="../deco/deco.html">
                <img src="../images/deco.jpg" alt="Déconnexion" width="40px" height="40px">
            </a>
        </button>
    </div>
    <div class="card">
        <?php if ($action === 'getQuizzes'): ?>
            <h2>Liste des Quizzes</h2>
            <?php if (!empty($quizzes)): ?>
                <?php foreach ($quizzes as $quiz): ?>
                    <div>
                        <form method="GET" action="modifier.php" style="display:inline;">
                            <input type="hidden" name="action" value="getQuestions">
                            <input type="hidden" name="quizId" value="<?php echo $quiz['id']; ?>">
                            <button type="submit" class="button"><?php echo htmlspecialchars($quiz['title']); ?></button>
                        </form>
                        <form method="POST" action="modifier.php?action=deleteQuiz" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $quiz['id']; ?>">
                            <button type="submit" class="btn"><img src="../images/poubelle.png" width="20px" height="20px"></button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun quiz trouvé.</p>
            <?php endif; ?>
        <?php elseif ($action === 'getQuestions'): ?>
            <h2>Questions du Quiz</h2>
            <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $question): ?>
                    <div>
                        <form method="POST" action="modifier.php?action=saveQuestion">
                            <input type="hidden" name="id" value="<?php echo $question['id']; ?>">
                            <label>Question :</label>
                            <input type="text" name="question_text" value="<?php echo htmlspecialchars($question['question_text']); ?>">
                            <label>Option 1 :</label>
                            <input type="text" name="option1" value="<?php echo htmlspecialchars($question['option1']); ?>">
                            <label>Option 2 :</label>
                            <input type="text" name="option2" value="<?php echo htmlspecialchars($question['option2']); ?>">
                            <label>Option 3 :</label>
                            <input type="text" name="option3" value="<?php echo htmlspecialchars($question['option3']); ?>">
                            <label>Réponse correcte :</label>
                            <input type="text" name="correct_option" value="<?php echo htmlspecialchars($question['correct_option']); ?>">
                            <button type="submit">Mettre à jour</button>
                        </form>
                        <form method="POST" action="modifier.php?action=deleteQuestion" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $question['id']; ?>">
                            <button type="submit" class="btn"><img src="../images/poubelle.png" width="20px" height="20px"></button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucune question trouvée pour ce quiz.</p>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>