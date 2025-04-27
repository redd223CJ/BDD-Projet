<!DOCTYPE html>
<html>

<head>
    <title>Filtrer les horaires</title>
</head>

<body>
    <form method="post" action="horaire-filtrage.php">
        <p>Filtrer les horaires selon un ou plusieurs critères (recherche insensible à la casse).</p>
        <input type="hidden" name="action" value="filtrer" />
        <input type="text" name="trajet_id" placeholder="ID trajet">
        <input type="text" name="itineraire_id" placeholder="ID itinéraire">
        <input type="text" name="arret_id" placeholder="ID arrêt">
        <input type="text" name="heure_arrivee" placeholder="Heure d'arrivée">
        <input type="text" name="heure_depart" placeholder="Heure de départ">
        <input type="submit" value="Filtrer">
    </form>

    <h1>Résultats</h1>

    <table border="1">
      <tr>
          <th>ID Trajet</th>
          <th>ID itinéraire</th>
          <th>ID arrêt</th>
          <th>Heure d'arrivée</th>
          <th>Heure de départ</th>
      </tr>

<?php
    $bdd = new PDO('mysql:host=db;dbname=group22;charset=utf8', 'group22', 'ulgfsa');
  
    if ($_POST['action'] == 'filtrer') {
        $conditions = [];
        $params = [];

        if (!empty($_POST['trajet_id'])) {
            $conditions[] = "LOWER(TRAJET_ID) LIKE :trajet_id";
            $params[':trajet_id'] = '%' . strtolower($_POST['trajet_id']) . '%';
        }

        if (!empty($_POST['itineraire_id'])) {
            $conditions[] = "LOWER(ITINERAIRE_ID) = :itineraire_id";
            $params[':itineraire_id'] = strtolower($_POST['itineraire_id']);
        }

        if (!empty($_POST['arret_id'])) {
            $conditions[] = "LOWER(ARRET_ID) = :arret_id";
            $params[':arret_id'] = strtolower($_POST['arret_id']);
        }

        if (!empty($_POST['heure_arrivee'])) {
            $conditions[] = "LOWER(HEURE_ARRIVEE) LIKE :heure_arrivee";
            $params[':heure_arrivee'] = '%' . strtolower($_POST['heure_arrivee']) . '%';
        }

        if (!empty($_POST['heure_depart'])) {
            $conditions[] = "LOWER(HEURE_DEPART) LIKE :heure_depart";
            $params[':heure_depart'] = '%' . strtolower($_POST['heure_depart']) . '%';
        }


        
        $sql = "SELECT * FROM HORAIRE";
        if (!empty($conditions)) {
            $sql .= " WHERE ";
        
            $index = 0;
            foreach ($conditions as $condition) {
                if($index != 0)
                    $sql .= " AND ";
                $sql .= $condition;
                $index++;
            }
        }

        $statement = $bdd->prepare($sql);                
        $res = $statement->execute($params);

        while ($tuple = $statement->fetch()) {
            echo "<tr>";
            echo "<td>" . $tuple['TRAJET_ID'] . "</td>";
            echo "<td>" . $tuple['ITINERAIRE_ID'] . "</td>";
            echo "<td>" . $tuple['ARRET_ID'] . "</td>";
            echo "<td>" . $tuple['HEURE_ARRIVEE'] . "</td>";
            echo "<td>" . $tuple['HEURE_DEPART'] . "</td>";
            echo "</tr>";
        }
    }
?>


    </table>


</body>

</html>