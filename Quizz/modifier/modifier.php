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

// recup action
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'getQuizzes') {
    // recup tous les quizzes
    $result = $conn->query("SELECT id, title FROM quizzes");
    if ($result && $result->num_rows > 0) {
        while ($quiz = $result->fetch_assoc()) {
            echo '<div>';
            // bouton pour voir questions
            echo '<form method="GET" action="modifier.php" style="display:inline;">';
            echo '<input type="hidden" name="action" value="getQuestions">';
            echo '<input type="hidden" name="quizId" value="' . $quiz['id'] . '">';
            echo '<button type="submit">' . htmlspecialchars($quiz['title']) . '</button>';
            echo '</form>';
            // bouton pour supprimer quiz
            echo '<form method="POST" action="modifier.php?action=deleteQuiz" style="display:inline;">';
            echo '<input type="hidden" name="id" value="' . $quiz['id'] . '">';
            echo '<button type="submit">Supprimer</button>';
            echo '</form>';
            echo '</div>';
        }
    } else {
        echo '<p>Aucun quiz trouvé.</p>';
    }
} elseif ($action === 'deleteQuiz') {
    // supprimer un quiz
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
    // recup questions d'un quiz
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
                    // formulaire pour modifier question
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
                    // bouton pour supprimer question
                    echo '<form method="POST" action="modifier.php?action=deleteQuestion" style="display:inline;">';
                    echo '<input type="hidden" name="id" value="' . $question['id'] . '">';
                    echo '<button type="submit">Supprimer</button>';
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
    // mettre a jour une question
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
    // supprimer une question
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

// fermer connexion
$conn->close();

// envoyer sortie tamponnée
ob_end_flush();
?>