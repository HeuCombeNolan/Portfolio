<?php
session_start();

// Vérification des données de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'];
    $mdp = $_POST['mdp'];

    // Connexion à la base de données
    $conn = pg_connect("host=localhost dbname=postgres user=postgres password=nolan");

    if (!$conn) {
        echo "Erreur de connexion à la base de données.";
        exit;
    }

    // Vérification des identifiants
    $result = pg_query_params($conn, "SELECT * FROM utilisateurs WHERE login = $1 AND mdp = $2", array($login, $mdp));

    if (pg_num_rows($result) === 1) {
        $_SESSION['loggedin'] = true;
        $_SESSION['login'] = $login;
        header('Location: dashboard.php'); // Rediriger vers le tableau de bord en cas de succès
    } else {
        header('Location: form-connexion.php?error=login_failed'); // Rediriger vers le formulaire de connexion avec un message d'erreur
        exit;
    }

    pg_close($conn);
}
?>
