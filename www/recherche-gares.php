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
        <label>Nombre min d’arrêts/départs/arrivées (facultatif) :</label>
        <input type="number" name="min">
        <input type="submit" value="Rechercher">
    </form>

<?php
if (!empty($_GET['nom']))
{
    $nom = strtolower(trim($_GET['nom']));           // minuscule et espaces en moins
    $min = isset($_GET['min']) && is_numeric($_GET['min']) ? intval($_GET['min']) : 0; // minimum (pas obligatoir)

    try
    {
        $pdo = new PDO('mysql:host=ms8db;dbname=group22;charset=utf8', 'group22', 'ulgfsa'); // connexion BDD

        $sql = "
            SELECT 
                A.NOM AS gare,
                S.NOM AS service,
                COUNT(*) AS total_arrets,
                SUM(HEURE_ARRIVEE IS NOT NULL) AS arrivées,      -- nb arrivees
                SUM(HEURE_DEPART IS NOT NULL) AS departs        
            FROM ARRET A
            JOIN HORAIRE H ON A.ID = H.ARRET_ID
            JOIN TRAJET T ON H.TRAJET_ID = T.TRAJET_ID
            JOIN SERVICE S ON T.SERVICE_ID = S.ID
            WHERE LOWER(A.NOM) LIKE ?                            -- filtre avec le nom de la gare
            GROUP BY A.NOM, S.NOM
            HAVING total_arrets >= ?
               OR arrivées >= ?
               OR departs >= ?                                   -- applique min si on en a mis un
            ORDER BY total_arrets DESC, arrivées DESC, departs DESC
        ";

        $stmt = $pdo->prepare($sql);                              // preparation requete
        $likeNom = "%$nom%";
        $stmt->execute([$likeNom, $min, $min, $min]);             // execution avec valeurs

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);                // recup resultat en tableau assoc

        if (count($rows) > 0)
        {
            echo "<table border='1'><tr><th>Gare</th><th>Service</th><th>Arrêts</th><th>Arrivées</th><th>Départs</th></tr>";
            foreach ($rows as $row)
            {
                echo "<tr>
                    <td>{$row['gare']}</td>
                    <td>{$row['service']}</td>
                    <td>{$row['total_arrets']}</td>
                    <td>{$row['arrivées']}</td>
                    <td>{$row['departs']}</td>
                </tr>";
            }
            echo "</table>";
        }
        
        else
        {
            echo "<p>Aucun resultat trouve.</p>";                 // si aucun resultat
        }
    }
    
    catch (Exception $e)
    {
        echo "<p style='color:red;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>"; // gestion erreur
    }
}

elseif (isset($_GET['nom']))
{
    echo "<p style='color:red;'>La chaine de recherche ne peut pas etre vide.</p>"; // nom vide
}

?>

</body>
</html>
