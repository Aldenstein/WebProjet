<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$title = $_POST['title'];
$question = $_POST['question'];
$option1 = $_POST['option1'];
$option2 = $_POST['option2'];
$option3 = $_POST['option3'];
$correct_option = $_POST['correct_option'];

$sql_quiz = "INSERT INTO quizzes (title) VALUES (?)";
$stmt_quiz = $conn->prepare($sql_quiz);
$stmt_quiz->bind_param("s", $title); 

if ($stmt_quiz->execute()) {
    $quiz_id = $conn->insert_id;

    $sql_question = "INSERT INTO questions (quiz_id, question, option1, option2, option3, correct_option) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_question = $conn->prepare($sql_question);

    $stmt_question->bind_param("isssss", $quiz_id, $question, $option1, $option2, $option3, $correct_option);

    if ($stmt_question->execute()) {
        echo "Nouvelle question ajoutée avec succès";
    } else {
        echo "Erreur lors de l'insertion de la question : " . $conn->error;
    }

    $stmt_question->close();
} else {
    echo "Erreur lors de l'insertion du quiz : " . $conn->error;
}

$stmt_quiz->close();
$conn->close();
?>