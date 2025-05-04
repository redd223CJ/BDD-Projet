<!DOCTYPE html>
<html>

<head>
    <title>Affichage des services selon la date</title>
</head>

<body>
    <p><strong>Entrez une date au format AAAA-MM-JJ</strong> (exemple : 2025-04-21) ou laissez vide pour tout afficher :</p>
    <form method="post" action="services-par-date.php">
        <label for="date">Date :</label>
        <input type="date" name="date" id="date">
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
            $toutAfficher = $bdd->prepare("
                SELECT DATE_ACTUELLE, GROUP_CONCAT(NOM_SERVICE ORDER BY NOM_SERVICE) AS SERVICES
                FROM DatesWithExceptions
                GROUP BY DATE_ACTUELLE
                ORDER BY DATE_ACTUELLE");
            $toutAfficher->execute();
            foreach ($req as $row) { ?>
                <tr><td><?= htmlentities($row['DATE_ACTUELLE']);?> </td><td> <?= htmlentities($row['SERVICES']); ?> </td></tr> <?php
            }
        }
        if (isset($_POST['filtrer'])) {
            $filtrage = $bdd->prepare("
                SELECT DATE_ACTUELLE, GROUP_CONCAT(NOM_SERVICE ORDER BY NOM_SERVICE) AS SERVICES
                FROM DatesWithExceptions
                WHERE DATE_ACTUELLE = :date
                GROUP BY DATE_ACTUELLE");
            $filtrage->execute(['date' => $_POST['date']]);
            $filtragefetch = $sth->fetch();

            if ($filtragefetch) { ?>
                <tr><td><?= htmlentities($filtragefetch['DATE_ACTUELLE']); ?> </td><td> <?= htmlentities($filtragefetch['SERVICES']); ?> </td></tr> <?php
            } else { ?>
                <tr><td colspan='2'>Aucun service assuré ce jour ou aucune date entrée.</td></tr>
            <?php } 
        }
        ?>
    </table>
</body>
</html>