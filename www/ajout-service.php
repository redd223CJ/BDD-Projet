<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajout de service</title>
</head>
<body>

<h1>Ajout d’un service</h1>

<form method="post" action="">
    <input type="text" name="nom" placeholder="Nom du service" required><br><br>

    <fieldset>
        <legend>Jours de fonctionnement :</legend>
        <?php
        $jours = ['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'];
        foreach ($jours as $jour) {
            echo "<label><input type='checkbox' name='jours[]' value='$jour'> " . ucfirst($jour) . "</label><br>";        //checkbox
        }
        ?>
    </fieldset><br>

    Date de début : <input type="date" name="date_debut" required><br><br>
    Date de fin : <input type="date" name="date_fin" required><br><br>

    Exceptions (une par ligne, format : YYYY-MM-DD INCLUS/EXCLUS) :<br>
    <textarea name="exceptions" rows="5" cols="40"></textarea><br><br>

    <input type="submit" value="Ajouter le service">
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $bdd = null;
    try 
    {
        $bdd = new PDO('mysql:host=ms8db;dbname=group22;charset=utf8', 'group22', 'ulgfsa');
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $bdd->beginTransaction();                 // debut de transaction

        $nom = $_POST['nom'];
        $joursCochés = $_POST['jours'] ?? [];     // recup jours coches (ou vide)
        $date_debut = $_POST['date_debut'];
        $date_fin = $_POST['date_fin'];

        // Récupérer un ID unique
        $sql_max_id = "SELECT MAX(ID) AS max_id FROM SERVICE";
        $result = $bdd->query($sql_max_id);
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $id_service = ($row['max_id'] ?? 0) + 1;

        $joursFinal = [];
        foreach (['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'] as $j)
        {
            $joursFinal[] = in_array($j, $joursCochés) ? 1 : 0;            // transforme jours en 0 ou 1
        }

        $sql = "INSERT INTO SERVICE (ID, NOM, LUNDI, MARDI, MERCREDI, JEUDI, VENDREDI, SAMEDI, DIMANCHE, DATE_DEBUT, DATE_FIN) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $bdd->prepare($sql);
        $stmt->execute(array_merge([$id_service, $nom], $joursFinal, [$date_debut, $date_fin]));        // insertion du service

        $exceptions = explode("\n", $_POST['exceptions']);
        $stmt_exc = $bdd->prepare("INSERT INTO EXCEPTION (SERVICE_ID, DATE, CODE) VALUES (?, ?, ?)");
        
        foreach ($exceptions as $line)
        {
            $line = trim($line);
            if ($line !== '')
            {
                [$date, $code] = explode(' ', $line);
                $code = strtoupper($code);
                $code_val = ($code === 'INCLUS') ? 1 : (($code === 'EXCLUS') ? 2 : null);
                if ($code_val === null)
                {
                    throw new Exception("Code d'exception invalide : $code");
                }
                
                $stmt_exc->execute([$id_service, $date, $code_val]);        // insert exception
            }
        }
        
        $bdd->commit();
        echo "<p>Service ajouté avec succès</p>";

    }
    
    catch (Exception $e)
    {
        if ($bdd)
        {
            $bdd->rollBack();        // annule la transaction si erreur (rollback)
        }
        
        echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
    }
}
?>

</body>
</html>
