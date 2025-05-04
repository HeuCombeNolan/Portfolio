<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="styles.css"> <!-- Importe le fichier CSS -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Formulaire de Connexion</title>
</head>
<body>

<!-- Titre de la page -->
<br><br><br> <h1>Bienvenue sur le site GestionNet</h1>

<!-- Formulaire de connexion -->
<form action="connexion.php" method="post">
    <h2>Formulaire de Connexion</h2>
    <div>
        <label for="login">Identifiant :</label> <br>
        <input type="text" id="login" name="login" required> <!-- Champ pour l'identifiant -->
    </div>
    <div>
        <label for="mdp">Mot de passe :</label><br>
        <input type="password" id="mdp" name="mdp" required> <!-- Champ pour le mot de passe -->
    </div>
    <button type="submit">Se connecter</button> <!-- Bouton de soumission du formulaire -->
</form>

<!-- Script JavaScript -->
<script>
    // Attend que la page soit complètement chargée
    window.onload = function() {
        // Récupère les paramètres d'URL
        const urlParams = new URLSearchParams(window.location.search);
        // Affiche un message d'erreur si la connexion a échoué
        if (urlParams.get('error') === 'login_failed') {
            const popup = createPopup('error', 'Identifiant ou mot de passe incorrect.');
            document.body.appendChild(popup);
            displayPopup(popup);
        }
    };

    // Fonction pour créer un message pop-up
    function createPopup(type, message) {
        const popup = document.createElement('div');
        popup.className = 'popup ' + type;
        popup.textContent = message;
        return popup;
    }

    // Fonction pour afficher le message pop-up
    function displayPopup(popup) {
        popup.style.display = 'block';
        setTimeout(function() {
            popup.style.display = 'none';
        }, 3000);
    }
</script>

</body>
</html>
