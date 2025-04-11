<?php
// démarrer la session
session_start();

// connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

// vérifier la connexion
if ($conn->connect_error) {
    echo "<script>
            alert('Erreur de connexion à la base de données : " . $conn->connect_error . "');
            window.location.href = 'index.html';
          </script>";
    exit;
}

// récupérer pseudo, mot de passe et action
$pseudo = isset($_POST['pseudo']) ? $_POST['pseudo'] : '';
$mdp = isset($_POST['mdp']) ? $_POST['mdp'] : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action == 'register') {
    // vérifier si l'utilisateur existe déjà
    $sql = "SELECT * FROM users WHERE pseudo='$pseudo'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // utilisateur déjà existant
        echo "<script>
                alert('Utilisateur déjà existant !');
                window.location.href = 'index.html';
              </script>";
    } else {
        // créer un nouvel utilisateur
        $hashed_password = password_hash($mdp, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (pseudo, mdp, active) VALUES ('$pseudo', '$hashed_password', false)";
        if ($conn->query($sql) === TRUE) {
            echo "<script>
                    alert('Compte créé avec succès, vous pouvez vous connecter.');
                    window.location.href = 'index.html';
                  </script>";
        } else {
            echo "<script>
                    alert('Erreur lors de la création du compte : " . $conn->error . "');
                    window.location.href = 'index.html';
                  </script>";
        }
    }
} elseif ($action == 'login') {
    // vérifier si l'utilisateur existe
    $sql = "SELECT * FROM users WHERE pseudo='$pseudo'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // vérifier le mot de passe
        if (password_verify($mdp, $row['mdp'])) {
            // stocker l'ID utilisateur dans la session
            $_SESSION['user_id'] = $row['id'];

            if ($row['active'] == 1) {
                // rediriger l'administrateur
                header("Location: admin/admin.html");
            } else {
                // rediriger l'utilisateur
                header("Location: liste/liste.html");
            }
            exit();
        } else {
            // mot de passe incorrect
            echo "<script>
                    alert('Mot de passe incorrect.');
                    window.location.href = 'index.html';
                  </script>";
        }
    } else {
        // utilisateur non trouvé
        echo "<script>
                alert('Nom d\'utilisateur ou mot de passe invalide.');
                window.location.href = 'index.html';
              </script>";
    }
} else {
    // action non valide
    echo "<script>
            alert('Action non valide.');
            window.location.href = 'index.html';
          </script>";
}

// fermer la connexion
$conn->close();
?>