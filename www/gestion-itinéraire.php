<!DOCTYPE html>
<html>

<head>
    <title>Gestion d'itinéraire</title>
</head>

<body>
    <!-- Formulaire de suppression d'itinéraire -->
    <h1> Suppression d'itinéraire </h1>
    <form method ="post" action="gestion-itinéraire.php">

        <label for="liste_supp">Itinéraire à supprimer :</label>
        <select name ="liste_supp">
            <?php
            $bdd = new PDO('mysql:host=db;dbname=group22;charset=utf8', 'group22', 'ulgfsa');
            $req = $bdd->query("SELECT ID,NOM FROM ITINERAIRE ORDER BY ID");
            foreach ($req as $row) { ?>
                <option value="<?= $row['ID']; ?>"><?= $row['NOM']; ?></option>
            <?php } ?>
        </select>

        <input type="submit" value="Supprimer">
    </form>

    <?php
        if (isset($_POST['liste_supp'])) {
            try {
                // Début transaction suppression itinéraire, trajet, horaire, arrêt desservi
                $bdd->beginTransaction();
                $id = $_POST['liste_supp'];

                $suppHoraire = $bdd->prepare("DELETE FROM HORAIRE WHERE ITINERAIRE_ID = ?");
                $suppHoraire->execute([$id]);

                $suppArretDesservi = $bdd->prepare("DELETE FROM ARRET_DESSERVI WHERE ITINERAIRE_ID = ?");
                $suppArretDesservi->execute([$id]);

                $suppTrajet = $bdd->prepare("DELETE FROM TRAJET WHERE ITINERAIRE_ID = ?");
                $suppTrajet->execute([$id]);

                $suppItineraire = $bdd->prepare("DELETE FROM ITINERAIRE WHERE ID = ?");
                $suppItineraire->execute([$id]); 

                if ($suppHoraire && $suppArretDesservi && $suppTrajet && $suppItineraire) { ?>
                    <p>Vous avez supprimé l'itinéraire <?= $id ?> et ses trajets correspondants. Veuillez actualiser pour mettre les changements à jour sur cette page.</p>
                    <?php
                    $bdd->commit();
                }
                else {
                    $bdd->rollBack();
                    throw new Exception("Erreur lors de la suppression de l'itinéraire. Aucun changement effectué.");
                }
            }
            catch (Exception $e)
            {
                $bdd->rollBack();
                if ($e instanceof \PDOException) {
                    die("Une erreur interne est survenue.");
                } else {
                    die($e->getMessage());
                }
            }
        }
    ?>

    <!-- Formulaire d'ajout de trajet -->
    <h1> Ajout de trajet </h1>
    <form method ="post" action="gestion-itinéraire.php">

        <label for="liste_itinéraire">Itinéraire du trajet à ajouter :</label>
        <select name ="liste_itinéraire">
            <?php
            $ls_itin = $bdd->query("SELECT ID, NOM FROM ITINERAIRE ORDER BY ID");
            foreach ($ls_itin as $row) { ?>
                <option value="<?= $row['ID']; ?>"><?= $row['NOM']; ?></option>
            <?php } ?>
        </select><br>

        <label for="liste_direction">Direction du trajet à ajouter :</label>
        <select name ="liste_direction">
            <option value="0">0</option>
            <option value="1">1</option> 
        </select><br>

        <label for="ID_trajet">ID du trajet à ajouter :</label>
        <input type="text" name="IDtrajet" placeholder="88____:007::8891702:8844628:40:843:20250314" required><br>

        <label for="liste_services">Service du trajet à ajouter :</label>
        <select name ="liste_serv">
            <?php
            $serv = $bdd->query("SELECT ID,NOM FROM SERVICE ORDER BY ID");
            foreach ($serv as $row) { ?>
                <option value="<?= $row['ID']; ?>"><?= $row['NOM']; ?></option>
            <?php } ?>
        </select>

        <input type="submit" value="Soumettre">
    </form>

    <!-- Formulaire horaire du trajet à ajouter -->
    <?php if(isset($_POST['liste_itinéraire']) && isset($_POST['liste_direction']) && isset($_POST['IDtrajet']) && isset($_POST['liste_serv']) && !isset($_POST['horaire'])) { ?>
        <form method="post" action="gestion-itinéraire.php">
            <label for="textarea"> <strong>Horaire du trajet à ajouter :</strong> </label>
            [Format : Arrêt, Heure d'arrivée, Heure de départ]<br>
            <textarea rows="30" cols="70" name="horaire" placeholder=
            "Eupen, , 8:00:00
Liège-Guillemins, 08:30:00, 08:35:00
Namur, 10:00:00, 10:05:00
Charleroi, 10:45:00, "></textarea>

            <!-- Hidden inputs pour passer les données du formulaire précédent. -->
            <input type="hidden" name="IDtrajet" value="<?= htmlentities($_POST['IDtrajet']) ?>">
            <input type="hidden" name="liste_itinéraire" value="<?= $_POST['liste_itinéraire'] ?>">
            <input type="hidden" name="liste_direction" value="<?= $_POST['liste_direction'] ?>">
            <input type="hidden" name="liste_serv" value="<?= $_POST['liste_serv'] ?>">


            <input type="submit" value="Ajouter le trajet">
        </form>
    <?php } ?>

    <!-- Insertion aux tables -->
    <?php 
    if (isset($_POST['horaire'])) {
        try 
        {   
            $bdd->beginTransaction();

            $trajet_ID = $_POST['IDtrajet'];
            $service_ID = $_POST['liste_serv'];
            $itineraire_ID = $_POST['liste_itinéraire'];
            $direction = $_POST['liste_direction'];

            $arretExistant = $bdd->prepare("SELECT COUNT(*) FROM TRAJET WHERE TRAJET_ID = ?");
            $arretExistant->execute([$_POST['IDtrajet']]);
            if ($arretExistant->fetchColumn() > 0) { 
                throw new Exception("ID de trajet déjà utilisé.");
            }

            // Insertion à la table trajet
            $insertTraj = $bdd->prepare("INSERT INTO TRAJET (TRAJET_ID, SERVICE_ID, ITINERAIRE_ID, DIRECTION) VALUES (?, ?, ?, ?)");
            $insertTraj->execute([$trajet_ID, $service_ID, $itineraire_ID, $direction]);

            $lines = explode("\n", $_POST['horaire']);
            
            if($lines){
                foreach($lines as $line) {
                    if (trim($line) == '') continue;
                    $ln = explode(',', $line);
                    $l = array_map('trim', $ln);
                    $arret = $l[0];
                    $hArriv = $l[1] ?? null;
                    $hDep = $l[2] ?? null;

                    $idArret = $bdd->prepare("SELECT ID FROM ARRET WHERE NOM = ?");
                    $idArret->execute([$arret]);
                    $arretRow = $idArret->fetch();

                    if (!$arretRow) { 
                        throw new Exception("Arrêt inexistant ou mal orthographié.");
                    } 
                    $arretID = $arretRow['ID'];

                    if ($hArriv && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $hArriv))
                    { 
                        throw new Exception("Erreur : format HH:MM:SS attendu pour l'heure d'arrivée.");
                    }

                    if ($hDep && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $hDep))
                    { 
                        throw new Exception("Erreur : format HH:MM:SS attendu pour l'heure de de départ.");
                    }

                    if ($hArriv && $hDep && strtotime($hArriv) > strtotime($hDep)) { 
                        throw new Exception("Erreur : l'heure de départ doit arriver plus tard que l'heure d'arrivée à l'arrêt.");
                    }

                    // Insertion à la table horaire
                    $insertHoraire = $bdd->prepare("INSERT INTO HORAIRE (TRAJET_ID, ITINERAIRE_ID, ARRET_ID, HEURE_ARRIVEE, HEURE_DEPART)
                    VALUES (?, ?, ?, ?, ?)");
                    $insertHoraire->execute([$trajet_ID, $itineraire_ID, $arretID, $hArriv ?: null, $hDep ?: null]);
                }
            } 
            $bdd->commit();
            ?>
            <p>Horaire et trajet <?= htmlentities($trajet_ID)?> ajoutés avec succès.</p>
        <?php 
        }
        catch (Exception $e)
            {
                $bdd->rollBack();
                if ($e instanceof \PDOException) {
                    die("Une erreur interne est survenue.");
                } else {
                    die($e->getMessage());
                }
            }
    }
    ?>
</body>
</html>

