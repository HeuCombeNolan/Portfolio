<?php
// Démarre une nouvelle session ou reprend une session existante
session_start();

// Vérifie si l'utilisateur est connecté, sinon redirige vers la page de connexion
if (!isset($_SESSION['loggedin'])) {
    header('Location: form-connexion.php');
    exit;
}

// Inclut le fichier début_code_html.php pour commencer la section HTML
include 'debut_code_html.php'; 

// Connexion à la base de données
$conn = pg_connect("host=localhost dbname=postgres user=postgres password=nolan");

// Récupération des clients et de leurs plages d'IP
$result = pg_query($conn, "SELECT c.id_client, c.nom, p.debut_plage, p.fin_plage, p.mask FROM public.client c LEFT JOIN public.plage_ip p ON c.id_client = p.id_client");

// Vérifie si la requête a échoué
if (!$result) {
    echo "Une erreur s'est produite.\n";
    exit;
}

// Stockage des adresses IP dans un tableau
$ip_addresses = array();
while ($row = pg_fetch_assoc($result)) {
    $ip_addresses[] = $row['debut_plage'];
}

// Fonction de comparaison pour trier les adresses IP
function compareIP($ip1, $ip2) {
    return ip2long($ip1) - ip2long($ip2);
}

// Trier les adresses IP
usort($ip_addresses, 'compareIP');

// Récupérer les informations des clients dans l'ordre trié des adresses IP
$sorted_clients = array();
foreach ($ip_addresses as $ip) {
    pg_result_seek($result, 0); // Réinitialiser le curseur de résultat à chaque itération
    while ($row = pg_fetch_assoc($result)) {
        if ($row['debut_plage'] === $ip) {
            $sorted_clients[] = $row;
            break;
        }
    }
}

?>

<!-- Affichage du tableau des clients et de leurs plages d'IP -->
<h2>Liste des Clients et leurs Plages d'IP (Triées)</h2>
<table>
    <tr>
        <th>ID Client</th>
        <th>Nom</th>
        <th>Début Plage</th>
        <th>Fin Plage</th>
        <th>Masque</th>
    </tr>
    <?php foreach ($sorted_clients as $row) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['id_client']); ?></td>
        <td><?php echo htmlspecialchars($row['nom']); ?></td>
        <td><?php echo htmlspecialchars($row['debut_plage']); ?></td>
        <td><?php echo htmlspecialchars($row['fin_plage']); ?></td>
        <td><?php echo htmlspecialchars($row['mask']); ?></td>
    </tr>
    <?php } ?>
</table>

<?php
// Inclut le fichier fin_code_html.php pour terminer la section HTML
include 'fin_code_html.php';
?>
