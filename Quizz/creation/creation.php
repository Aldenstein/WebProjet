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

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Vérifier si le formulaire pour ajouter une question a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['question'])) {
    // Si aucun quiz n'est sélectionné, enregistrer le titre automatiquement
    if (!isset($_SESSION['quiz_id'])) {
        if (isset($_POST['title']) && !empty(trim($_POST['title']))) {
            $title = trim($_POST['title']);
            $stmt = $conn->prepare("INSERT INTO quizzes (title) VALUES (?)");
            if ($stmt) {
                $stmt->bind_param("s", $title);
                if ($stmt->execute()) {
                    $_SESSION['quiz_id'] = $conn->insert_id;
                    echo "<p style='color: green;'>Quiz créé avec succès : $title</p>";
                } else {
                    echo "<p style='color: red;'>Erreur lors de la création du quiz : " . $stmt->error . "</p>";
                    $stmt->close();
                    $conn->close();
                    exit;
                }
                $stmt->close();
            } else {
                echo "<p style='color: red;'>Erreur lors de la préparation de la requête pour le quiz : " . $conn->error . "</p>";
                $conn->close();
                exit;
            }
        } else {
            echo "<p style='color: red;'>Titre du quiz manquant. Veuillez fournir un titre.</p>";
            $conn->close();
            exit;
        }
    }

    // Ajouter la question au quiz
    if (isset($_SESSION['quiz_id'])) {
        $quiz_id = $_SESSION['quiz_id'];
        $question = trim($_POST['question']);
        $question_type = trim($_POST['question_type']);

        if (!empty($question) && !empty($question_type)) {
            if ($question_type === "QCM") {
                if (!isset($_POST['option1'], $_POST['option2'], $_POST['option3'], $_POST['correct_option'])) {
                    echo "<p style='color: red;'>Erreur : Les options et la réponse correcte sont obligatoires pour une question QCM.</p>";
                    $conn->close();
                    exit;
                }

                $option1 = trim($_POST['option1']);
                $option2 = trim($_POST['option2']);
                $option3 = trim($_POST['option3']);
                $correct_option = trim($_POST['correct_option']);

                $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question, question_type, option1, option2, option3, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssss", $quiz_id, $question, $question_type, $option1, $option2, $option3, $correct_option);

            } elseif ($question_type === "Vrai/Faux") {
                if (!isset($_POST['correct_option'])) {
                    echo "<p style='color: red;'>Erreur : La réponse correcte est obligatoire pour une question Vrai/Faux.</p>";
                    $conn->close();
                    exit;
                }

                $correct_option = trim($_POST['correct_option']);

                $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question, question_type, correct_option) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $quiz_id, $question, $question_type, $correct_option);

            } elseif ($question_type === "Ouverte") {
                if (!isset($_POST['formatted_answer'])) {
                    echo "<p style='color: red;'>Erreur : La réponse préformatée est obligatoire pour une question ouverte.</p>";
                    $conn->close();
                    exit;
                }

                $formatted_answer = trim($_POST['formatted_answer']);

                $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question, question_type, formatted_answer) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $quiz_id, $question, $question_type, $formatted_answer);

            } else {
                echo "<p style='color: red;'>Erreur : Type de question invalide.</p>";
                $conn->close();
                exit;
            }

            // Exécuter la requête pour insérer la question
            if ($stmt->execute()) {
                echo "<p style='color: green;'>Nouvelle question ajoutée avec succès.</p>";
            } else {
                echo "<p style='color: red;'>Erreur lors de l'insertion de la question : " . $stmt->error . "</p>";
            }

            $stmt->close();
        } else {
            echo "<p style='color: red;'>Erreur : La question ou le type de question est manquant.</p>";
        }
    }
}

$conn->close();
?>