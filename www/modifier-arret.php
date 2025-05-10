<!DOCTYPE html>
<html>

<head>
    <title>Modifier les arrêts</title>
</head>

<body>
    <form method="post" action="modifier-arret.php">
        <p>Filtrer les arrêts selon un ou plusieurs critères (recherche insensible à la casse).</p>
        <input type="hidden" name="action" value="filtrer" />
        <input type="number" name="id" placeholder="ID">
        <input type="text" name="nom" placeholder="Nom">
        <input type="number" step="any" name="latitude" placeholder="Latitude">
        <input type="number" step="any" name="longitude" placeholder="Longitude">
        <input type="submit" value="Filtrer">
    </form>

<?php
    $bdd = new PDO('mysql:host=db;dbname=group22;charset=utf8', 'group22', 'ulgfsa');
  
    if ($_POST['action'] == 'filtrer') {
        $conditions = [];
        $params = [];

?>
        <h1>Résultats</h1>

        <table border="1">
          <tr>
              <th>ID</th>
              <th>Nom</th>
              <th>Latitude</th>
              <th>Longitude</th>
              <th>-</th>
          </tr>
<?php
        if (!empty($_POST['id'])) {
            $conditions[] = "LOWER(ID) = :id";
            $params[':id'] = strtolower($_POST['id']);
        }

        if (!empty($_POST['nom'])) {
            $conditions[] = "LOWER(NOM) LIKE :nom";
            $params[':nom'] = '%' . strtolower($_POST['nom']) . '%';
        }

        if (!empty($_POST['latitude'])) {
            $conditions[] = "LOWER(LATITUDE) LIKE :latitude";
            $params[':latitude'] = '%' . strtolower($_POST['latitude']) . '%';
        }

        if (!empty($_POST['longitude'])) {
            $conditions[] = "LOWER(LONGITUDE) LIKE :longitude";
            $params[':longitude'] = '%' . strtolower($_POST['longitude']) . '%';
        }

        
        $sql = "SELECT * FROM ARRET";
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
            echo "<td>" . $tuple['ID'] . "</td>";
            echo "<td>" . $tuple['NOM'] . "</td>";
            echo "<td>" . $tuple['LATITUDE'] . "</td>";
            echo "<td>" . $tuple['LONGITUDE'] . "</td>";
            echo "<td> <a href='modifier-arret.php?id=" . $tuple['ID'] . "'>Modifier</a> </td>";
            echo "</tr>";
        }
    }



    if (!empty($_GET['id'])) {
        $id = $_GET['id'];

        $statement = $bdd->prepare("SELECT * FROM ARRET WHERE ID = :id");
        $statement->bindParam(':id', $id);
        $statement->execute();
        $tuple = $statement->fetch();
?>
        <form method="post" action="modifier-arret.php">
            <h1>Modifier l'arrêt</h1>
            <input type="hidden" name="action" value="modifier">
            <input type="text" name="id" placeholder="ID" value="<?php echo $tuple['ID']; ?>">
            <input type="text" name="nom" placeholder="Nom" value="<?php echo $tuple['NOM']; ?>">
            <input type="text" name="latitude" placeholder="Latitude" value="<?php echo $tuple['LATITUDE']; ?>"> 
            <input type="text" name="longitude" placeholder="Longitude" value="<?php echo $tuple['LONGITUDE']; ?>">

            <input type="hidden" name="originalId" value=" <?php echo $id ?> ">

            <input type="submit" value="Modifier">
        </form>
<?php
    }


    if ($_POST['action'] == 'modifier') {

        $sql = "UPDATE ARRET
                SET ID = :newId,
                    NOM = :newNom,
                    LATITUDE = :newLatitude,
                    LONGITUDE = :newLongitude
                WHERE ID = :originalId";


        $statement = $bdd->prepare($sql);                
        
        $statement->bindParam(':newId', $_POST['id']);
        $statement->bindParam(':newNom', $_POST['nom']);
        $statement->bindParam(':newLatitude', $_POST['latitude']);
        $statement->bindParam(':newLongitude', $_POST['longitude']);
        $statement->bindParam(':originalId', $_POST['originalId']);

        $statement->execute();

        echo $originalID;
        echo "<h2>L'arrêt avec l'ID " . $_POST['originalId'] . " a bien été modifié !</h2>";

        if ((int)$_POST['id'] !== (int)$_POST['originalId']) {
            echo "<h2>Nouvel ID " . $_POST['id'] . "</h2>";
        }

    }


?>


    </table>


</body>

</html>