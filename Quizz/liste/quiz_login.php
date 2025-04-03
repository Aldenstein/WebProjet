<?php
// connexion a la bdd
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

// verifier si connexion marche
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// demarrer session
session_start();

// verifier si formulaire soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pin = $_POST['pin'];

    // verifier si quiz existe
    $sql = "SELECT id FROM quizzes WHERE id = ?";
    $stmt = $conn->prepare($sql);

    // si erreur dans la requete
    if (!$stmt) {
        die("Erreur requête : " . $conn->error);
    }

    $stmt->bind_param("i", $pin); // associer pin
    $stmt->execute();
    $stmt->store_result();

    // si quiz trouvé
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($quiz_id);
        $stmt->fetch();

        // sauvegarder id dans session
        $_SESSION['quiz_id'] = $quiz_id;

        // rediriger vers quiz
        header("Location: take_quiz.php?quiz_id=" . $quiz_id);
        exit();
    } else {
        // si pin invalide
        echo "Code PIN invalide.";
    }

    $stmt->close();
}

// fermer connexion
$conn->close();
?>