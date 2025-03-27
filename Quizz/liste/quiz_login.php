<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pin = $_POST['pin'];

    $sql = "SELECT id FROM quizzes WHERE quizz_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $pin);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($quiz_id);
        $stmt->fetch();

        $_SESSION['quiz_id'] = $quiz_id;

        header("Location: quiz.php");
        exit();
    } else {
        echo "Code PIN invalide.";
    }
}
?>
