<?php
session_start();

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['loggedin'])) {
    header('Location: form-connexion.php');
    exit;
}

// Connexion à la base de données
$conn = pg_connect("host=localhost dbname=postgres user=postgres password=nolan");

// Vérifie si la connexion à la base de données a échoué
if (!$conn) {
    header('Location: dashboard.php?error=connection');
    exit;
}

// Récupère le login de l'utilisateur connecté
$login = $_SESSION['login'];

// Vérifie si l'utilisateur est un administrateur
$admin_check = pg_query_params($conn, "SELECT 1 FROM public.utilisateurs WHERE login = $1 AND mdp = 'admin'", array($login));
if (pg_num_rows($admin_check) === 0) {
    // Redirige vers le tableau de bord avec un message d'erreur
    header('Location: dashboard.php?error=no_access');
    exit;
}

// Vérifie si un identifiant de client a été passé en paramètre GET
if (isset($_GET['id'])) {
    $id_client = $_GET['id'];

    // Met à jour les plages IP pour marquer comme disponibles (id_client et id_vlan sont mis à NULL)
    $ip_update = pg_query_params($conn, "UPDATE public.plage_ip SET id_client = NULL, id_vlan = NULL WHERE id_client = $1", array($id_client));
    // Supprime les enregistrements du VLAN associés à ce client
    $vlan_delete = pg_query_params($conn, "DELETE FROM public.vlan WHERE id_client = $1", array($id_client));
    // Supprime le client de la base de données
    $client_delete = pg_query_params($conn, "DELETE FROM public.client WHERE id_client = $1", array($id_client));

    // Vérifie si une des requêtes a échoué
    if (!$ip_update || !$vlan_delete || !$client_delete) {
        header('Location: dashboard.php?error=delete'); // Message d'erreur si la suppression n'a pas fonctionnée
        exit;
    }

    // Redirige vers le tableau de bord avec un message de succès
    header('Location: dashboard.php?delete_success=1');
    exit;
}

?>