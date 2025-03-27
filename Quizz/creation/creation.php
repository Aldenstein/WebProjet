<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titre = $_POST['titre'];

    if (!empty($titre)) {
        $stmt = $conn->prepare("INSERT INTO quiz (titre) VALUES (?)");
        $stmt->bind_param("s", $titre);

        if ($stmt->execute()) {
            $quiz_id = $conn->insert_id;
            echo "Le titre du quiz a été inséré avec succès. ID du quiz : " . $quiz_id;
            session_start();
            $_SESSION['quiz_id'] = $quiz_id;
        } else {
            echo "Erreur lors de l'insertion : " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Le champ titre est vide.";
    }
}

$conn->close();
?>

