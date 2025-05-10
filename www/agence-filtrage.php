<!DOCTYPE html>
<html>

<head>
    <title>Filtrer les agences</title>
</head>

<body>
    <form method="post" action="agence-filtrage.php">
        <p>Filtrer les agences selon un ou plusieurs critères (recherche insensible à la casse).</p>
        <input type="hidden" name="action" value="filtrer" />
        <input type="text" name="nom" placeholder="Nom">
        <input type="text" name="url" placeholder="URL">
        <input type="text" name="fuseau-horaire" placeholder="Fuseau horaire">
        <input type="text" name="telephone" placeholder="Telephone">
        <input type="text" name="siege" placeholder="Siège">
        <input type="submit" value="Filtrer">
    </form>

    <h1>Résultats</h1>

<?php
    $bdd = new PDO('mysql:host=db;dbname=group22;charset=utf8', 'group22', 'ulgfsa');
  
    if ($_POST['action'] == 'filtrer') {
        $conditions = [];
        $params = [];

        if (!empty($_POST['nom'])) {
            $conditions[] = "LOWER(NOM) LIKE :nom";
            $params[':nom'] = '%' . strtolower($_POST['nom']) . '%';
        }

        if (!empty($_POST['url'])) {
            $conditions[] = "LOWER(URL) LIKE :url";
            $params[':url'] = '%' . strtolower($_POST['url']) . '%';
        }

        if (!empty($_POST['fuseau-horaire'])) {
            $conditions[] = "LOWER(FUSEAU_HORAIRE) LIKE :fuseau";
            $params[':fuseau'] = '%' . strtolower($_POST['fuseau-horaire']) . '%';
        }

        if (!empty($_POST['telephone'])) {
            $conditions[] = "LOWER(TELEPHONE) LIKE :telephone";
            $params[':telephone'] = '%' . strtolower($_POST['telephone']) . '%';
        }

        if (!empty($_POST['siege'])) {
            $conditions[] = "LOWER(SIEGE) LIKE :siege";
            $params[':siege'] = '%' . strtolower($_POST['siege']) . '%';
        }


        
        $sql = "SELECT * FROM AGENCE";
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
        $results = $statement->fetchAll();

        if(count($results) > 0) {
            ?>
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>URL</th>
                    <th>Fuseau Horaire</th>
                    <th>Téléphone</th>
                    <th>Siège</th>
                </tr>
            <?php
            foreach ($results as $tuple) {
                echo "<tr>";
                echo "<td>" . $tuple['ID'] . "</td>";
                echo "<td>" . $tuple['NOM'] . "</td>";
                echo "<td>" . $tuple['URL'] . "</td>";
                echo "<td>" . $tuple['FUSEAU_HORAIRE'] . "</td>";
                echo "<td>" . $tuple['TELEPHONE'] . "</td>";
                echo "<td>" . $tuple['SIEGE'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<h2>Aucun résultats</h2>";
        }

    }
?>


    </table>


</body>

</html>