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

$action = $_GET['action'] ?? '';

if ($action === 'getQuizzes') {
    $result = $conn->query("SELECT id, title FROM quizzes");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
} elseif ($action === 'deleteQuiz') {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM quizzes WHERE id = $id");
} elseif ($action === 'getQuestions') {
    $quizId = intval($_GET['quizId']);
    $result = $conn->query("SELECT id, question AS text FROM questions WHERE quiz_id = $quizId");
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
} elseif ($action === 'deleteQuestion') {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM questions WHERE id = $id");
} elseif ($action === 'saveQuestions') {
    $questions = json_decode(file_get_contents("php://input"), true);
    foreach ($questions as $question) {
        $id = intval($question['id']);
        $text = $conn->real_escape_string($question['text']);
        $conn->query("UPDATE questions SET question = '$text' WHERE id = $id");
    }
}

$conn->close();
?>