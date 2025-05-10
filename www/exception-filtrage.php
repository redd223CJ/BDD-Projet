<!DOCTYPE html>
<html>

<head>
    <title>Filtrer les exceptions</title>
</head>

<body>
    <form method="post" action="exception-filtrage.php">
        <p>Filtrer les exceptions selon un ou plusieurs critères (recherche insensible à la casse).</p>
        <input type="hidden" name="action" value="filtrer" />
        <input type="text" name="service_id" placeholder="ID service">
        <input type="date" name="date" placeholder="Date">
        <input type="text" name="code" placeholder="Code">
        <input type="submit" value="Filtrer">
    </form>

    <h1>Résultats</h1>

<?php
    $bdd = new PDO('mysql:host=db;dbname=group22;charset=utf8', 'group22', 'ulgfsa');
  
    if ($_POST['action'] == 'filtrer') {
        $conditions = [];
        $params = [];

        if (!empty($_POST['service_id'])) {
            $conditions[] = "LOWER(SERVICE_ID) = :service_id";
            $params[':service_id'] = strtolower($_POST['service_id']);
        }

        if (!empty($_POST['date'])) {
            $conditions[] = "LOWER(DATE) = :date";
            $params[':date'] = strtolower($_POST['date']);
        }

        if (!empty($_POST['code'])) {
            $conditions[] = "LOWER(CODE) = :code";
            $params[':code'] = strtolower($_POST['code']);
        }


        
        $sql = "SELECT * FROM EXCEPTION";
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
                    <th>ID Service</th>
                    <th>Date</th>
                    <th>Code</th>
                </tr>
            <?php
            foreach ($results as $tuple) {
                echo "<tr>";
                echo "<td>" . $tuple['SERVICE_ID'] . "</td>";
                echo "<td>" . $tuple['DATE'] . "</td>";
                echo "<td>" . $tuple['CODE'] . "</td>";
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