<?php
// Connexion à la base de données (à adapter selon votre configuration)
$host = 'localhost';
$dbname = 'projetweb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Création des tables si elles n'existent pas
$pdo->exec("
    CREATE TABLE IF NOT EXISTS quizzes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL
    )
");

$pdo->exec("
    CREATE TABLE IF NOT EXISTS questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        quiz_id INT NOT NULL,
        question_text TEXT NOT NULL,
        question_type VARCHAR(50) NOT NULL,
        option1 VARCHAR(255) DEFAULT NULL,
        option2 VARCHAR(255) DEFAULT NULL,
        option3 VARCHAR(255) DEFAULT NULL,
        correct_option VARCHAR(255) NOT NULL,
        FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
    )
");

// Récupération des données du formulaire
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$question = isset($_POST['question']) ? trim($_POST['question']) : '';
$question_type = isset($_POST['question_type']) ? trim($_POST['question_type']) : '';


// Vérifier si un quiz avec ce titre existe déjà
$stmt = $pdo->prepare("SELECT id FROM quizzes WHERE title = ?");
$stmt->execute([$title]);
$quiz = $stmt->fetch();

if ($quiz) {
    // Si le quiz existe, on récupère son ID
    $quiz_id = $quiz['id'];
} else {
    // Sinon, on crée un nouveau quiz
    $stmt = $pdo->prepare("INSERT INTO quizzes (title) VALUES (?)");
    $stmt->execute([$title]);
    $quiz_id = $pdo->lastInsertId();
}

// Gestion des différents types de questions
$option1 = $option2 = $option3 = $correct_option = null;

if ($question_type === "QCM") {
    $option1 = isset($_POST['option1']) ? trim($_POST['option1']) : '';
    $option2 = isset($_POST['option2']) ? trim($_POST['option2']) : '';
    $option3 = isset($_POST['option3']) ? trim($_POST['option3']) : '';
    $correct_option = isset($_POST['correct_option']) ? trim($_POST['correct_option']) : '';

    // On stocke l'index de l'option correcte (1, 2 ou 3)
    $correct_option = $correct_option;

} elseif ($question_type === "Vrai/Faux") {
    $correct_option = isset($_POST['correct_option']) ? trim($_POST['correct_option']) : '';

    if (empty($correct_option) || !in_array(strtolower($correct_option), ['vrai', 'faux'])) {
        die("La réponse correcte doit être 'Vrai' ou 'Faux'.");
    }
    $correct_option = strtolower($correct_option);

} elseif ($question_type === "Ouverte") {
    $correct_option = isset($_POST['formatted_answer']) ? trim($_POST['formatted_answer']) : '';

    if (empty($correct_option)) {
        die("Veuillez fournir une réponse préformatée pour la question ouverte.");
    }
}

// Insertion de la question dans la table questions
$stmt = $pdo->prepare("
    INSERT INTO questions (quiz_id, question_text, question_type, option1, option2, option3, correct_option)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([$quiz_id, $question, $question_type, $option1, $option2, $option3, $correct_option]);

// Redirection ou message de succès
header('Location: creation.html');
?>
