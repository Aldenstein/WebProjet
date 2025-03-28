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
    // Débogage : afficher les données reçues
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    // Récupérer le titre du quiz
    if (isset($_POST['title']) && !empty(trim($_POST['title']))) {
        $title = trim($_POST['title']);

        // Vérifier si le quiz existe déjà
        $stmt = $conn->prepare("SELECT quiz_id FROM quizzes WHERE title = ?");
        $stmt->bind_param("s", $title);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Le quiz existe déjà, récupérer son ID
            $stmt->bind_result($quiz_id);
            $stmt->fetch();
            $_SESSION['quiz_id'] = $quiz_id;
        } else {
            // Le quiz n'existe pas, le créer
            $stmt = $conn->prepare("INSERT INTO quizzes (title) VALUES (?)");
            $stmt->bind_param("s", $title);
            if ($stmt->execute()) {
                $_SESSION['quiz_id'] = $conn->insert_id; // Récupérer l'ID auto-incrémenté
                echo "<p style='color: green;'>Quiz créé avec succès : $title</p>";
            } else {
                echo "<p style='color: red;'>Erreur lors de la création du quiz : " . $stmt->error . "</p>";
                $stmt->close();
                $conn->close();
                exit;
            }
        }
        $stmt->close();
    } else {
        echo "<p style='color: red;'>Titre du quiz manquant. Veuillez fournir un titre.</p>";
        $conn->close();
        exit;
    }

    // Ajouter la question au quiz
    if (isset($_SESSION['quiz_id'])) {
        $quiz_id = $_SESSION['quiz_id'];
        $question = trim($_POST['question']);
        $question_type = trim($_POST['question_type']);

        if (!empty($question) && !empty($question_type)) {
            if ($question_type === "QCM") {
                if (!isset($_POST['option1'], $_POST['option2'], $_POST['option3'], $_POST['qcm_rep']) || empty(trim($_POST['qcm_rep']))) {
                    echo "<p style='color: red;'>Erreur : Les options et la réponse correcte sont obligatoires pour une question QCM.</p>";
                    $conn->close();
                    exit;
                }

                $option1 = trim($_POST['option1']);
                $option2 = trim($_POST['option2']);
                $option3 = trim($_POST['option3']);
                $qcm_rep = intval($_POST['qcm_rep']); // Convertir en entier

                // Vérifier que qcm_rep est valide (1, 2 ou 3)
                if ($qcm_rep < 1 || $qcm_rep > 3) {
                    echo "<p style='color: red;'>Erreur : La réponse correcte doit être 1, 2 ou 3.</p>";
                    $conn->close();
                    exit;
                }

                // Débogage : afficher les données avant l'insertion
                echo "<pre>";
                echo "Quiz ID: " . htmlspecialchars($quiz_id) . "\n";
                echo "Question: " . htmlspecialchars($question) . "\n";
                echo "Question Type: " . htmlspecialchars($question_type) . "\n";
                echo "Option 1: " . htmlspecialchars($option1) . "\n";
                echo "Option 2: " . htmlspecialchars($option2) . "\n";
                echo "Option 3: " . htmlspecialchars($option3) . "\n";
                echo "Correct Option (qcm_rep): " . htmlspecialchars($qcm_rep) . "\n";
                echo "</pre>";

                $stmt = $conn->prepare("INSERT INTO questions (quiz_id, question, question_type, option1, option2, option3, qcm_rep) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssssi", $quiz_id, $question, $question_type, $option1, $option2, $option3, $qcm_rep);

            } else {
                echo "<p style='color: red;'>Erreur : Type de question invalide.</p>";
                $conn->close();
                exit;
            }

            // Exécuter la requête pour insérer la question
            if (!$stmt->execute()) {
                echo "<p style='color: red;'>Erreur lors de l'insertion de la question : " . $stmt->error . "</p>";
            } else {
                echo "<p style='color: green;'>Nouvelle question ajoutée avec succès.</p>";
            }

            $stmt->close();
        } else {
            echo "<p style='color: red;'>Erreur : La question ou le type de question est manquant.</p>";
        }
    }
}

$conn->close();
?>