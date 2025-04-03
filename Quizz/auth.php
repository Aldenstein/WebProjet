<?php
// demarre session
session_start();

// connexion a la bdd
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "projetweb";

$conn = new mysqli($servername, $username, $password, $dbname);

// verifier connexion
if ($conn->connect_error) {
    die("erreur connexion : " . $conn->connect_error);
}

// recupere pseudo et mdp
$pseudo = $_POST['pseudo'];
$mdp = $_POST['mdp'];
$action = $_POST['action'];

if ($action == 'register') {
    // verifier si user existe
    $sql = "SELECT * FROM users WHERE pseudo='$pseudo'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // user deja existant
        echo "User already exists!";
    } else {
        // creer user
        $hashed_password = password_hash($mdp, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (pseudo, mdp, active) VALUES ('$pseudo', '$hashed_password', false)";
        if ($conn->query($sql) === TRUE) {
            echo "<script>
                    alert('Compte créé avec succès, vous pouvez vous connecter');
                    document.getElementById('Pseudo').value = '';
                    document.getElementById('mdp').value = '';
                  </script>";
            header("Location: index.html");
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
} elseif ($action == 'login') {
    // verifier si user existe
    $sql = "SELECT * FROM users WHERE pseudo='$pseudo'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // verifier mdp
        if (password_verify($mdp, $row['mdp'])) {
            // stocker id user dans session
            $_SESSION['user_id'] = $row['id'];

            if ($row['active'] == 1) {
                // rediriger admin
                header("Location: admin/admin.html");
            } else {
                // rediriger user
                header("Location: liste/liste.html");
            }
            exit();
        } else {
            // mauvais mdp
            header("Location: invalide/invalide.html");
        }
    } else {
        // user pas trouvé
        echo "Invalid username or password!";
    }
}

// fermer connexion
$conn->close();
?>