<?php
// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Récupérer les utilisateurs et leurs scores, triés par score décroissant, en excluant l'admin
$sql = "SELECT pseudo, score FROM users WHERE active = '0' ORDER BY score DESC";
$result = $conn->query($sql);

// Vérifier si la requête a échoué
if (!$result) {
    die("Erreur dans la requête SQL : " . $conn->error);
}

// Stocker les résultats dans un tableau
$users = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Fermer la connexion
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ctrl+Quizz</title>
    <link rel="stylesheet" href="classement.css">
    <link rel="icon" href="../images/icone.jpg">
</head>
<body>
    <div class="navbar">
        <button id="homebtn" class="btn">
            <a href="../index.html">
                <img src="../images/home.jpg" alt="Accueil" width="40px" height="40px">
            </a>
        </button>
        <h1 align="center">Classement des Utilisateurs !</h1>
        <button id="decobtn" class="btn">
            <a href="../deco/deco.html">
                <img src="../images/deco.jpg" alt="Déconnexion" width="40px" height="40px">
            </a>
        </button>
    </div>
    <div class="card">
        <h2>Classement</h2>
        <table>
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Nom d'utilisateur</th>
                    <th>Score</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php $position = 1; ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $position++; ?></td>
                            <td><?php echo htmlspecialchars($user['pseudo']); ?></td>
                            <td><?php echo htmlspecialchars($user['score']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Aucun joueur trouvé.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>