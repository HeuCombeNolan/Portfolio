<?php
session_start(); // Démarre ou reprend une session existante
if (!isset($_SESSION['loggedin'])) { // Vérifie si l'utilisateur est connecté
    header('Location: form-connexion.php'); // Redirige vers la page de connexion si l'utilisateur n'est pas connecté
    exit; // Arrête l'exécution du script
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Vérifie si le formulaire a été soumis avec la méthode POST
    $id_client = $_POST['id_client']; // Récupère l'ID du client depuis le formulaire
    $nom = $_POST['nom']; // Récupère le nom du client depuis le formulaire

    // Vérification que id_client contient uniquement des chiffres
    if (!ctype_digit($id_client)) { // Vérifie si l'ID client est numérique
        header('Location: dashboard.php?error=id_not_numeric'); // Redirige avec un message d'erreur si l'ID n'est pas numérique
        exit; // Arrête l'exécution du script
    }

    // Connexion à la base de données
    $conn = pg_connect("host=localhost dbname=postgres user=postgres password=nolan"); // Connecte à la base de données PostgreSQL

    if (!$conn) { // Vérifie si la connexion à la base de données a échoué
        header('Location: dashboard.php?error=connection'); // Redirige avec un message d'erreur si la connexion a échoué
        exit; // Arrête l'exécution du script
    }

    // Vérification de l'ID du client pour éviter les duplications
    $id_check = pg_query_params($conn, "SELECT 1 FROM public.client WHERE id_client = $1", array($id_client)); // Vérifie si l'ID client existe déjà dans la base de données
    if (pg_num_rows($id_check) > 0) { // Vérifie s'il y a des résultats de la requête
        pg_close($conn); // Ferme la connexion à la base de données
        header('Location: dashboard.php?error=id_exists'); // Redirige avec un message d'erreur si l'ID client existe déjà
        exit; // Arrête l'exécution du script
    }

    // Fonction pour incrémenter une adresse IP
    function increment_ip($ip, $increment) {
        $ip_parts = array_map('intval', explode('.', $ip)); // Divise l'adresse IP en parties
        $ip_parts[3] += $increment; // Incrémente la dernière partie de l'adresse IP

        for ($i = 3; $i >= 0; $i--) { // Boucle à travers les parties de l'adresse IP
            if ($ip_parts[$i] > 255) { // Vérifie si la partie de l'adresse IP est supérieure à 255
                $ip_parts[$i] = 0; // Réinitialise la partie de l'adresse IP à 0
                if ($i > 0) { // Vérifie si ce n'est pas la première partie de l'adresse IP
                    $ip_parts[$i - 1] += 1; // Incrémente la partie précédente de l'adresse IP
                }
            }
        }

        return implode('.', $ip_parts); // Reconstitue l'adresse IP à partir des parties modifiées
    }

    // Fonction pour obtenir la prochaine plage IP disponible
    function get_next_ip_range($conn) {
        // Vérifier d'abord les plages disponibles
        $available_ip_result = pg_query($conn, "SELECT debut_plage, fin_plage FROM public.plage_ip WHERE id_client IS NULL ORDER BY id_plage LIMIT 1"); // Sélectionne la première plage IP disponible
        if ($available_ip_result && pg_num_rows($available_ip_result) > 0) { // Vérifie s'il y a des résultats de la requête
            $available_ip_row = pg_fetch_assoc($available_ip_result); // Récupère la première plage IP disponible
            return array($available_ip_row['debut_plage'], $available_ip_row['fin_plage']); // Retourne la plage IP disponible
        }

        // Si aucune plage disponible, en créer une nouvelle
        $ip_result = pg_query($conn, "SELECT fin_plage FROM public.plage_ip ORDER BY id_plage DESC LIMIT 1"); // Sélectionne la dernière plage IP enregistrée
        if (!$ip_result || pg_num_rows($ip_result) == 0) { // Vérifie s'il y a des résultats de la requête
            return array("164.166.1.1", "164.166.1.5"); // Plage initiale si aucune plage IP n'est enregistrée
        }
        $ip_row = pg_fetch_assoc($ip_result); // Récupère la dernière plage IP enregistrée
        $last_ip = $ip_row ? $ip_row['fin_plage'] : "164.166.1.0"; // Récupère la dernière adresse IP de la plage
        $new_start_ip = increment_ip($last_ip, 1); // Calcule la prochaine adresse IP disponible
        $new_end_ip = increment_ip($new_start_ip, 4); // Calcule la fin de la plage IP (5 adresses)

        if ($new_end_ip > "164.166.1.253") { // Vérifie si la fin de la plage dépasse la limite
            return null; // Plus de plage IP disponible
        }
        return array($new_start_ip, $new_end_ip); // Retourne la nouvelle plage IP
    }

    // Obtenir la prochaine plage IP disponible
    $next_ip_range = get_next_ip_range($conn);

    if ($next_ip_range === null) { // Vérifie s'il n'y a pas de plage IP disponible
        // Plus de plage IP disponible
        header('Location: dashboard.php?error=no_more_ranges'); // Redirige avec un message d'erreur
        exit; // Arrête l'exécution du script
    }

    list($new_start_ip, $new_end_ip) = $next_ip_range; // Récupère la nouvelle plage IP
    $mask = "255.255.255.248"; // Masque de sous-réseau pour 5 adresses

    // Insertion du nouveau client dans la base de données
    $result = pg_query_params($conn, "INSERT INTO public.client (id_client, nom) OVERRIDING SYSTEM VALUE VALUES ($1, $2)", array($id_client, $nom));

    if (!$result) { // Vérifie si l'insertion du client a échoué
        pg_close($conn); // Ferme la connexion à la base de données
        header('Location: dashboard.php?error=insert_client'); // Redirige avec un message d'erreur
        exit; // Arrête l'exécution du script
    }

    // Mise à jour de la plage d'IP avec id_vlan = 1 et association de la plage au nouveau client
    $ip_query = pg_query_params($conn, "UPDATE public.plage_ip SET id_vlan = 1, id_client = $1 WHERE debut_plage = $2 AND fin_plage = $3", array($id_client, $new_start_ip, $new_end_ip));

    if (!$ip_query || pg_affected_rows($ip_query) == 0) { // Vérifie si la mise à jour de la plage IP a échoué
        // Si aucune plage n'a été mise à jour, insérer une nouvelle plage IP
        $ip_insert_query = pg_query_params($conn, "INSERT INTO public.plage_ip (debut_plage, fin_plage, id_vlan, id_client, mask) VALUES ($1, $2, 1, $3, $4)", array($new_start_ip, $new_end_ip, $id_client, $mask));
        if (!$ip_insert_query) { // Vérifie si l'insertion de la plage IP a échoué
            pg_close($conn); // Ferme la connexion à la base de données
            header('Location: dashboard.php?error=insert_vlan_ip'); // Redirige avec un message d'erreur
            exit; // Arrête l'exécution du script
        }
    }

    pg_close($conn); // Ferme la connexion à la base de données
    header('Location: dashboard.php?success=1'); // Redirige avec un message de succès
    exit; // Arrête l'exécution du script
}
?>
