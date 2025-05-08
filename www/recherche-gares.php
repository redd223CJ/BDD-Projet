<!DOCTYPE html>
<html>
<head>
    <title>Recherche de gares</title>
</head>
<body>
    <h1>Recherche de gares</h1>
    <form method="get">
        <label>Nom contient :</label>
        <input type="text" name="nom" required>
        <label>Nombre min d’arrets/departs/arrivees (facultatif) :</label>
        <input type="number" name="min">
        <input type="submit" value="Rechercher">
    </form>

<?php
if (!empty($_GET['nom']) && trim($_GET['nom']) !== '')
{
    $nom = strtolower(trim($_GET['nom']));           //si y a juste un espace, on l'enleve sinon ca nous donnera toutes les gares
    $min = isset($_GET['min']) && is_numeric($_GET['min']) ? intval($_GET['min']) : 0;      // minimum

    try
    {
        $pdo = new PDO('mysql:host=ms8db;dbname=group22;charset=utf8', 'group22', 'ulgfsa');

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "
            SELECT 
                A.NOM AS gare,                          -- nom gare
                IFNULL(S.NOM, '-') AS service,          -- nom service ('-' si y a rien)
                COUNT(H.ARRET_ID) AS total_arrets,      -- nb total d'apparitions de la gare dans les horaires
                SUM(H.HEURE_ARRIVEE IS NOT NULL) AS arrivees, -- nb d'arrivees
                SUM(H.HEURE_DEPART IS NOT NULL) AS departs    -- nb de departs

            FROM ARRET A
            LEFT JOIN HORAIRE H ON A.ID = H.ARRET_ID         -- lien gare-horaires (si y en a)
            LEFT JOIN TRAJET T ON H.TRAJET_ID = T.TRAJET_ID  -- horaire-trajet
            LEFT JOIN SERVICE S ON T.SERVICE_ID = S.ID       -- trajet-service

            WHERE LOWER(A.NOM) LIKE ?                   -- filtre
            GROUP BY A.NOM, S.NOM

            HAVING total_arrets >= ?                    -- garde les gares % au min
            OR arrivees >= ?
            OR departs >= ?

            ORDER BY total_arrets DESC, arrivees DESC, departs DESC -- trie du plus actif au moins actif
        ";


        $stmt = $pdo->prepare($sql);
        $likeNom = "%$nom%";

        $stmt->execute([$likeNom, $min, $min, $min]);       // execution avec valeurs

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);          // recup resultat en tableau assoc

        if (count($rows) > 0)
        {
            echo "<table border='1'><tr><th>Gare</th><th>Service</th><th>Arrets</th><th>Arrivees</th><th>Departs</th></tr>";
            foreach ($rows as $row)
            {                                               // tableau
                echo "<tr>
                    <td>{$row['gare']}</td>
                    <td>{$row['service']}</td>
                    <td>{$row['total_arrets']}</td>
                    <td>{$row['arrivees']}</td>
                    <td>{$row['departs']}</td>
                </tr>";
            }
            
            echo "</table>";
        }
        
        else
        {
            echo "<p>Aucun resultat trouve.</p>";           // si aucun resultat, on renvoit un msg
        }
    }
    
    catch (Exception $e)
    {
        echo "<p style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

elseif (isset($_GET['nom']) && $_SERVER['REQUEST_METHOD'] === 'GET')
{
    echo "<p style='color:red;'>La chaine de recherche ne peut pas etre vide.</p>";         // pour les noms vides
}
?>

</body>
</html>
