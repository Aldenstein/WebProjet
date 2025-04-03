<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'getQuizzes') {
    $result = $conn->query("SELECT id, title FROM quizzes");
    if ($result) {
        if ($result->num_rows > 0) {
            while ($quiz = $result->fetch_assoc()) {
                echo '<div class="quiz-item">';
                echo '<span class="quiz-title" data-id="' . $quiz['id'] . '">' . htmlspecialchars($quiz['title']) . '</span>';
                echo '<button onclick="deleteQuiz(' . $quiz['id'] . ')">Supprimer</button>';
                echo '</div>';
            }
        } else {
            echo '<p>Aucun quiz trouvé.</p>';
        }
    } else {
        echo '<p>Erreur lors de la récupération des quizzes : ' . $conn->error . '</p>';
    }
} elseif ($action === 'deleteQuiz') {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "Quiz supprimé avec succès.";
    } else {
        echo "Erreur lors de la suppression du quiz : " . $stmt->error;
    }
    $stmt->close();
} elseif ($action === 'getQuestions') {
    $quizId = intval($_GET['quizId']);
    $stmt = $conn->prepare("SELECT id, question, question_type, option1, option2, option3, correct_option, formatted_answer FROM questions WHERE quiz_id = ?");
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($question = $result->fetch_assoc()) {
            echo '<div class="question-item">';
            echo '<input type="text" value="' . htmlspecialchars($question['question']) . '" data-id="' . $question['id'] . '" class="question-text" />';
            
            if ($question['question_type'] === 'QCM') {
                echo '<input type="text" value="' . htmlspecialchars($question['option1']) . '" placeholder="Option 1" class="option" data-option="1" data-id="' . $question['id'] . '" />';
                echo '<input type="text" value="' . htmlspecialchars($question['option2']) . '" placeholder="Option 2" class="option" data-option="2" data-id="' . $question['id'] . '" />';
                echo '<input type="text" value="' . htmlspecialchars($question['option3']) . '" placeholder="Option 3" class="option" data-option="3" data-id="' . $question['id'] . '" />';
                echo '<input type="text" value="' . htmlspecialchars($question['correct_option']) . '" placeholder="Réponse correcte" class="correct-option" data-id="' . $question['id'] . '" />';
            } elseif ($question['question_type'] === 'Ouverte') {
                echo '<textarea placeholder="Réponse préformatée" class="formatted-answer" data-id="' . $question['id'] . '">' . htmlspecialchars($question['formatted_answer']) . '</textarea>';
            }

            echo '<button onclick="deleteQuestion(' . $question['id'] . ')">Supprimer</button>';
            echo '</div>';
        }
    } else {
        echo '<p>Aucune question trouvée pour ce quiz.</p>';
    }
    $stmt->close();
} elseif ($action === 'deleteQuestion') {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "Question supprimée avec succès.";
    } else {
        echo "Erreur lors de la suppression de la question : " . $stmt->error;
    }
    $stmt->close();
} elseif ($action === 'saveQuestions') {
    $questions = json_decode(file_get_contents("php://input"), true);
    foreach ($questions as $question) {
        $id = intval($question['id']);
        $text = $conn->real_escape_string($question['text']);
        $type = $conn->real_escape_string($question['type']);

        if ($type === 'QCM') {
            $option1 = $conn->real_escape_string($question['option1']);
            $option2 = $conn->real_escape_string($question['option2']);
            $option3 = $conn->real_escape_string($question['option3']);
            $correct_option = $conn->real_escape_string($question['correct_option']);
            $stmt = $conn->prepare("UPDATE questions SET question = ?, option1 = ?, option2 = ?, option3 = ?, correct_option = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $text, $option1, $option2, $option3, $correct_option, $id);
        } elseif ($type === 'Ouverte') {
            $formatted_answer = $conn->real_escape_string($question['formatted_answer']);
            $stmt = $conn->prepare("UPDATE questions SET question = ?, formatted_answer = ? WHERE id = ?");
            $stmt->bind_param("ssi", $text, $formatted_answer, $id);
        }

        if (!$stmt->execute()) {
            echo "Erreur lors de la mise à jour de la question ID $id : " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>