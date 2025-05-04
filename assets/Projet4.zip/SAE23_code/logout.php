<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();

// Vide toutes les variables de session
session_unset();

// Détruit la session actuelle
session_destroy();

// Redirige l'utilisateur vers la page de connexion
header('Location: form-connexion.php');

// Arrête l'exécution du script pour s'assurer que la redirection se fasse correctement
exit;
?>
