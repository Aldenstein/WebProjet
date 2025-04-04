<?php
// Assurez-vous qu'il n'y a pas d'espaces ou de caractères avant cette ligne
ob_start(); // Démarre la mise en tampon de sortie
session_start(); // Démarrage de la session

// Activer l'affichage des erreurs pour le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Récupérer l'action demandée
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'getQuizzes') {
    // Récupérer tous les quizzes
    $result = $conn->query("SELECT id, title FROM quizzes");
    if ($result && $result->num_rows > 0) {
        while ($quiz = $result->fetch_assoc()) {
            echo '<div>';
            // Bouton pour afficher les questions du quiz
            echo '<form method="GET" action="modifier.php" style="display:inline;">';
            echo '<input type="hidden" name="action" value="getQuestions">';
            echo '<input type="hidden" name="quizId" value="' . $quiz['id'] . '">';
            echo '<button type="submit" class="button">' . htmlspecialchars($quiz['title']) . '</button>';
            echo '</form>';
            // Bouton pour supprimer le quiz
            echo '<form method="POST" action="modifier.php?action=deleteQuiz" style="display:inline;">';
            echo '<input type="hidden" name="id" value="' . $quiz['id'] . '">';
            echo '<button type="submit" class="btn"><img src="../images/poubelle.png" width="20px" height="20px"></button>';
            echo '</form>';
            echo '</div>';
        }
    } else {
        echo '<p>Aucun quiz trouvé.</p>';
    }
} elseif ($action === 'deleteQuiz') {
    // Supprimer un quiz
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "<script>alert('Quiz supprimé avec succès.'); window.location.href='modifier.html';</script>";
            } else {
                echo "<script>alert('Erreur lors de la suppression du quiz.'); window.location.href='modifier.html';</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Erreur lors de la préparation de la requête.'); window.location.href='modifier.html';</script>";
        }
    }
} elseif ($action === 'getQuestions') {
    // Récupérer les questions d'un quiz
    if (isset($_GET['quizId'])) {
        $quizId = intval($_GET['quizId']);
        $stmt = $conn->prepare("SELECT id, question_text, option1, option2, option3, correct_option FROM questions WHERE quiz_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $quizId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($question = $result->fetch_assoc()) {
                    echo '<div>';
                    echo '<form method="POST" action="modifier.php?action=saveQuestion">';
                    echo '<input type="hidden" name="id" value="' . $question['id'] . '">';
                    echo '<label>Question :</label>';
                    echo '<input type="text" name="question_text" value="' . htmlspecialchars($question['question_text']) . '">';
                    echo '<label>Option 1 :</label>';
                    echo '<input type="text" name="option1" value="' . htmlspecialchars($question['option1']) . '">';
                    echo '<label>Option 2 :</label>';
                    echo '<input type="text" name="option2" value="' . htmlspecialchars($question['option2']) . '">';
                    echo '<label>Option 3 :</label>';
                    echo '<input type="text" name="option3" value="' . htmlspecialchars($question['option3']) . '">';
                    echo '<label>Réponse correcte :</label>';
                    echo '<input type="text" name="correct_option" value="' . htmlspecialchars($question['correct_option']) . '">';
                    echo '<button type="submit">Mettre à jour</button>';
                    echo '</form>';
                    echo '<form method="POST" action="modifier.php?action=deleteQuestion" style="display:inline;">';
                    echo '<input type="hidden" name="id" value="' . $question['id'] . '">';
                    echo '<button type="submit" class="btn"><img src="../images/poubelle.png" width="20px" height="20px"></button>';
                    echo '</form>';
                    echo '</div>';
                }
            } else {
                echo '<p>Aucune question trouvée pour ce quiz.</p>';
            }
            $stmt->close();
        } else {
            echo "Erreur lors de la préparation de la requête.";
        }
    }
} elseif ($action === 'saveQuestion') {
    // Mettre à jour une question
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                echo "<script>alert('Question mise à jour avec succès.'); window.location.href='modifier.html';</script>";
            } else {
                echo "<script>alert('Erreur lors de la mise à jour de la question.'); window.location.href='modifier.html';</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Erreur lors de la préparation de la requête.'); window.location.href='modifier.html';</script>";
        }
    }
} elseif ($action === 'deleteQuestion') {
    // Supprimer une question
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "<script>alert('Question supprimée avec succès.'); window.location.href='modifier.html';</script>";
            } else {
                echo "<script>alert('Erreur lors de la suppression de la question.'); window.location.href='modifier.html';</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Erreur lors de la préparation de la requête.'); window.location.href='modifier.html';</script>";
        }
    }
}

// Fermer la connexion à la base de données
$conn->close();

// Envoyer la sortie tamponnée
ob_end_flush();
?>