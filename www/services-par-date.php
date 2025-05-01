<!DOCTYPE html>
<html>

<head>
    <title>Affichage des services selon la date</title>
</head>

<body>
    <p><strong>Entrez une date au format AAAA-MM-JJ</strong> (exemple : 2025-04-21) ou laissez vide pour tout afficher :</p>
    <form method="post" action="services-par-date.php">
        <label for="date">Date :</label>
        <input type="text" name="date" id="date" placeholder="AAAA-MM-JJ">
        <button type="submit" name="filtrer">Filtrer par date</button>
        <button type="submit" name="tout">Tout afficher</button>
    </form>

    <h1>Résultat :</h1>
    <table border = "1">
        <tr>
            <th>Date</th>
            <th>Services</th>
        </tr>

        <?php
        $bdd = new PDO('mysql:host=db;dbname=group22;charset=utf8', 'group22', 'ulgfsa');

        if (isset($_POST['tout'])) {
            $req = $bdd->query("
                SELECT DATE_ACTUELLE, GROUP_CONCAT(NOM_SERVICE ORDER BY NOM_SERVICE) AS SERVICES
                FROM DatesWithExceptions
                GROUP BY DATE_ACTUELLE
                ORDER BY DATE_ACTUELLE");

                foreach ($req as $row) {
                    echo "<tr><td>" . htmlentities($row['DATE_ACTUELLE']) . "</td><td>" . htmlentities($row['SERVICES']) . "</td></tr>";
                }
        }
        if (isset($_POST['filtrer'])) {
            $sth = $bdd->prepare("
                SELECT DATE_ACTUELLE, GROUP_CONCAT(NOM_SERVICE ORDER BY NOM_SERVICE) AS SERVICES
                FROM DatesWithExceptions
                WHERE DATE_ACTUELLE = :date
                GROUP BY DATE_ACTUELLE");
            $sth->execute(['date' => $_POST['date']]);
            $red = $sth->fetch();

            if ($red) {
                echo "<tr><td>" . htmlentities($red['DATE_ACTUELLE']) . "</td><td>" . htmlentities($red['SERVICES']) . "</td></tr>";
            } else {
                echo "<tr><td>colspan='2'Aucun service assuré ce jour.</td></tr>";
            }
        }
        ?>
    </table>
</body>
</html>