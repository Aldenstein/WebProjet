<?php
session_start();

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

// Initialiser les variables de session si elles n'existent pas
if (!isset($_SESSION['quiz_id'])) {
    $_SESSION['quiz_id'] = null;
}
if (!isset($_SESSION['current_question'])) {
    $_SESSION['current_question'] = 0;
}
if (!isset($_SESSION['score'])) {
    $_SESSION['score'] = 0;
}
if (!isset($_SESSION['total_questions'])) {
    $_SESSION['total_questions'] = 0;
}

// Si l'utilisateur soumet un quiz_id pour commencer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['start_quiz']) && isset($_POST['quiz_id'])) {
    $quiz_id = intval($_POST['quiz_id']);
    
    // Vérifier si le quiz existe
    $stmt = $conn->prepare("SELECT title FROM quizzes WHERE quiz_id = ?");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $_SESSION['quiz_id'] = $quiz_id;
        $_SESSION['current_question'] = 0;
        $_SESSION['score'] = 0;
        
        // Compter le nombre total de questions
        $stmt_count = $conn->prepare("SELECT COUNT(*) FROM questions WHERE quiz_id = ?");
        $stmt_count->bind_param("i", $quiz_id);
        $stmt_count->execute();
        $stmt_count->bind_result($total_questions);
        $stmt_count->fetch();
        $_SESSION['total_questions'] = $total_questions;
        $stmt_count->close();
    } else {
        echo "<p style='color: red;'>Quiz non trouvé avec l'ID $quiz_id.</p>";
    }
    $stmt->close();
}

// Si l'utilisateur soumet une réponse
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_answer']) && $_SESSION['quiz_id']) {
    $user_answer = intval($_POST['answer']);
    $current_question_id = intval($_POST['question_id']);
    
    // Vérifier la bonne réponse
    $stmt = $conn->prepare("SELECT qcm_rep FROM questions WHERE question_id = ? AND quiz_id = ?");
    $stmt->bind_param("ii", $current_question_id, $_SESSION['quiz_id']);
    $stmt->execute();
    $stmt->bind_result($correct_answer);
    $stmt->fetch();
    
    if ($user_answer === $correct_answer) {
        echo "<p style='color: green;'>Bonne réponse !</p>";
        $_SESSION['score']++;
    } else {
        echo "<p style='color: red;'>Mauvaise réponse. La bonne réponse était l'option $correct_answer.</p>";
    }
    $_SESSION['current_question']++;
    $stmt->close();
}

// Afficher la question actuelle ou le résultat final
if ($_SESSION['quiz_id']) {
    $quiz_id = $_SESSION['quiz_id'];
    $current_question = $_SESSION['current_question'];
    
    if ($current_question < $_SESSION['total_questions']) {
        // Récupérer toutes les questions pour ce quiz
        $stmt = $conn->prepare("SELECT question_id, question, option1, option2, option3 FROM questions WHERE quiz_id = ? ORDER BY question_id LIMIT 1 OFFSET ?");
        $stmt->bind_param("ii", $quiz_id, $current_question);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $question_id = $row['question_id'];
            $question = $row['question'];
            $option1 = $row['option1'];
            $option2 = $row['option2'];
            $option3 = $row['option3'];
            
            // Afficher la question
            echo "<h3>Question " . ($current_question + 1) . " / " . $_SESSION['total_questions'] . "</h3>";
            echo "<p>" . htmlspecialchars($question) . "</p>";
            echo "<form method='post'>";
            echo "<input type='hidden' name='question_id' value='$question_id'>";
            echo "<label><input type='radio' name='answer' value='1' required> " . htmlspecialchars($option1) . "</label><br>";
            echo "<label><input type='radio' name='answer' value='2'> " . htmlspecialchars($option2) . "</label><br>";
            echo "<label><input type='radio' name='answer' value='3'> " . htmlspecialchars($option3) . "</label><br>";
            echo "<input type='submit' name='submit_answer' value='Valider'>";
            echo "</form>";
        }
        $stmt->close();
    } else {
        // Quiz terminé
        echo "<h3>Quiz terminé !</h3>";
        echo "<p>Votre score : " . $_SESSION['score'] . " / " . $_SESSION['total_questions'] . "</p>";
        echo "<form method='post'><input type='submit' name='reset' value='Recommencer un autre quiz'></form>";
        
        // Réinitialiser la session si demandé
        if (isset($_POST['reset'])) {
            session_unset();
            session_destroy();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }
} else {
    // Afficher le formulaire pour choisir un quiz
    echo "<h3>Choisir un quiz</h3>";
    echo "<form method='post'>";
    echo "<label>Entrez l'ID du quiz : <input type='number' name='quiz_id' required></label>";
    echo "<input type='submit' name='start_quiz' value='Commencer le quiz'>";
    echo "</form>";
}

$conn->close();
?>