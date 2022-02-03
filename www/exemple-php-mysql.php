<html>
<!-- connexion a la base de donnees -->
<?php
$bdd = new PDO('mysql:host=ms8db;dbname=groupXX;charset=utf8', 'groupXX', 'secret');
?>

<head>
    <title>Départements</title>
</head>

<body>
    <h1>Départements</h1>
    <?php
    /*$req contient les tuples de la requete*/
    $req = $bdd->query('SELECT * FROM department');
    /*On affiche tous les resultats de la requete*/
    while ($tuple = $req->fetch()) {
        echo "<p>" . $tuple['DNO'] . " " . $tuple['DNAME'] . "</p>";
    }
    ?>
</body>

</html>