<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();

// Vérifie si l'utilisateur est connecté, sinon le redirige vers la page de connexion
if (!isset($_SESSION['loggedin'])) {
    header('Location: form-connexion.php');
    exit;
}

// Inclut le fichier debut_code_html.php pour commencer la section HTML
include 'debut_code_html.php';

// Vérification si la variable de session 'login' est définie
if (isset($_SESSION['login'])) {
    // Récupération du login de l'utilisateur connecté depuis la session
    $login = $_SESSION['login'];
} else {
    // Redirection vers le formulaire de connexion si la variable de session 'login' n'est pas définie
    header('Location: form-connexion.php');
    exit;
}

// Connexion à la base de données
$conn = pg_connect("host=localhost dbname=postgres user=postgres password=nolan");

// Vérification de la connexion
if (!$conn) {
    echo "Erreur de connexion à la base de données.";
    exit;
}

// Récupération des clients avec leurs plages d'adresses
$query = "
    SELECT c.id_client, c.nom, p.debut_plage, p.fin_plage 
    FROM public.client c 
    LEFT JOIN public.plage_ip p ON c.id_client = p.id_client
";
$result = pg_query($conn, $query);

// Vérification si la requête a échoué
if (!$result) {
    echo "Une erreur s'est produite lors de la récupération des clients.";
    exit;
}
?>

<!-- Début de la section HTML -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord</title>
    <!-- Lien pour se déconnecter -->
    <a href="logout.php" class="logout-btn"></a>
    
    <!-- Script JavaScript pour afficher les messages pop-up -->
    <script>
    // Attend que la page soit complètement chargée
    window.onload = function() {
        // Récupère les paramètres d'URL
        const urlParams = new URLSearchParams(window.location.search);
        // Affiche un message pop-up en fonction des paramètres d'URL
        if (urlParams.get('success') === '1') {
            const popup = createPopup('success', 'Client ajouté avec succès');
            document.body.appendChild(popup);
            displayPopup(popup);
        } 
        else if (urlParams.get('delete_success') === '1') {
            const popup = createPopup('success', 'Client supprimé avec succès');
            document.body.appendChild(popup);
            displayPopup(popup);
        } 
        else if (urlParams.get('error') === 'id_exists') {
            const popup = createPopup('error', 'Veuillez utiliser un autre ID client.');
            document.body.appendChild(popup);
            displayPopup(popup);
        } 
        else if (urlParams.get('error') === 'no_more_ranges') {
            const popup = createPopup('error', 'Plus de plage IP disponible');
            document.body.appendChild(popup);
            displayPopup(popup);
        } 
        else if (urlParams.get('error') === 'id_not_numeric') {
            const popup = createPopup('error', 'Veuillez utiliser un ID client valide');
            document.body.appendChild(popup);
            displayPopup(popup);
        }
        else if (urlParams.get('error') === 'delete') {
            const popup = createPopup('error', "Erreur lors de la supression d'un client");
            document.body.appendChild(popup);
            displayPopup(popup);
        }
        else if (urlParams.get('error') === 'no_access') {
            const popup = createPopup('error', "Vous n'avez pas les droits d'administrateur");
            document.body.appendChild(popup);
            displayPopup(popup);
        }
        
    };

    // Fonction pour créer un message pop-up
    function createPopup(type, message) {
        const popup = document.createElement('div');
        popup.className = 'popup ' + type;
        popup.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'times-circle') + '"></i> ' + message;
        return popup;
    }

    // Fonction pour afficher un message pop-up
    function displayPopup(popup) {
        popup.style.display = 'block';
        setTimeout(function() {
            popup.style.display = 'none';
        }, 3000);
    }
</script>
</head>
<body>

<!-- Affichage du message de bienvenue -->
<div class="welcome-message">
    Bienvenue, <?php echo htmlspecialchars($login); ?> sur le site GestionNet!
</div>

<div class="container">
    <!-- Affichage de la liste des clients -->
    <h2>Liste des Clients</h2>
    <table>
        <tr>
            <th>ID Client</th>
            <th>Nom</th>
            <th>Début Plage d'Adresse</th>
            <th>Fin Plage d'Adresse</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = pg_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['id_client']); ?></td>
            <td><?php echo htmlspecialchars($row['nom']); ?></td>
            <td><?php echo htmlspecialchars($row['debut_plage'] ?? 'Non attribuée'); ?></td>
            <td><?php echo htmlspecialchars($row['fin_plage'] ?? 'Non attribuée'); ?></td>
            <td>
                <!-- lien pour supprimer un client -->
                <a href="delete_client.php?id=<?php echo $row['id_client']; ?>">Supprimer</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <!-- Formulaire pour ajouter un nouveau client -->
    <h2>Ajouter un Nouveau Client</h2>
    <form method="post" action="add_client.php">
        <label for="id_client">ID Client:</label>
        <input type="text" id="id_client" name="id_client" required><br>
        <label for="nom">Nom du Client:</label>
        <input type="text" id="nom" name="nom" required><br>
        <button type="submit">Ajouter</button>
    </form>
</div>

<?php
// Fermeture de la connexion à la base de données
pg_close($conn);

// Inclut le fichier fin_code_html.php pour terminer la section HTML
include 'fin_code_html.php'; 
?>
</body>
</html>
