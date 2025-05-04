<!DOCTYPE html>
<html>

<head>
    <title>Gestion d'itinéraire</title>
</head>

<body>
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
        $erreur = 0;
        if (isset($_POST['liste_supp'])) {
            $id = $_POST['liste_supp'];

            $suppHoraire = $bdd->prepare("DELETE FROM HORAIRE WHERE ITINERAIRE_ID = ?");
            $suppHoraire->execute([$id]);

            $suppArretDesservi = $bdd->prepare("DELETE FROM ARRET_DESSERVI WHERE ITINERAIRE_ID = ?");
            $suppArretDesservi->execute([$id]);

            $suppTrajet = $bdd->prepare("DELETE FROM TRAJET WHERE ITINERAIRE_ID = ?");
            $suppTrajet->execute([$id]);

            $suppItineraire = $bdd->prepare("DELETE FROM ITINERAIRE WHERE ID = ?");
            $suppItineraire->execute([$id]); ?>

            <p>Vous avez supprimé l'itinéraire <?= $id ?> et ses trajets correspondants.</p>
        <?php }
    ?>

    <h1> Ajout de trajet </h1>
    <form method ="post" action="gestion-itinéraire.php">

        <label for="liste_itinéraire">Itinéraire du trajet à ajouter :</label>
        <select name ="liste_itinéraire">
            <?php
            $ls_itin = $bdd->query("SELECT ID, NOM FROM ITINERAIRE ORDER BY ID");
            foreach ($ls_itin as $row) { ?>
                <option value="<?= $row['ID']; ?>"><?= $row['NOM']; ?></option>
            <?php } ?>
        </select>

        <label for="liste_direction">Direction du trajet à ajouter :</label>
        <select name ="liste_direction">
            <option value="0">0</option>
            <option value="1">1</option> 
        </select>

        <label for="ID_trajet">ID du trajet à ajouter :</label>
        <input type="text" name="IDtrajet" placeholder="88____:007::8891702:8844628:40:843:20250314" required>
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

    <!-- Ajouter à la table TRAJET qui contient TRAJET_ID,SERVICE_ID,ITINERAIRE_ID,DIRECTION  -->
    <?php if (isset($_POST['liste_itinéraire']) && isset($_POST['liste_direction']) && isset($_POST['IDtrajet']) && isset($_POST['liste_serv']) && !isset($_POST['horaire'])) {
        $insertTraj = $bdd->prepare("INSERT INTO TRAJET (TRAJET_ID, SERVICE_ID, ITINERAIRE_ID, DIRECTION) VALUES (?,?,?,?)");
        $insertTraj->execute([$_POST['IDtrajet'], $_POST['liste_serv'], $_POST['liste_itinéraire'], $_POST['liste_direction']]); ?>
        <p>Trajet ajouté : itinéraire <?= $_POST['liste_itinéraire']?>, direction <?= $_POST['liste_direction']?>.</p>
    <?php }  ?>

    <?php if(isset($_POST['liste_itinéraire']) && isset($_POST['liste_direction']) && isset($_POST['IDtrajet']) && isset($_POST['liste_serv']) && !isset($_POST['horaire'])) { ?>
        <form method="post" action="gestion-itineraire.php">
            <label for="textarea"> Horaire du trajet à ajouter : </label><br><br>
            <small>Arrêt, Heure d'arrivée, Heure de départ</small><br>
            <textarea rows="50" cols="50" name="horaire" placeholder="Eupen, , 8:00:00\nLiège-Guillemins, 08:30:00, 08:35:00\nNamur, 10:00:00, 10:05:00\nCharleroi, 10:45:00, "></textarea>
            <input type="submit" value="Ajouter le trajet">
        </form>
    <?php } ?>

    <!-- Ajouter contenu du textarea à la table HORAIRE qui contient TRAJET_ID,ITINERAIRE_ID,ARRET_ID,HEURE_ARRIVEE,HEURE_DEPART -->
    <?php if (isset($_POST['horaire'])) {
        $lines = explode("\n", $_POST['horaire']);
        
        if($lines){
            foreach($lines as $line) {
                $ln = explode(',', $line);
                $l = array_map('trim', $ln);
                $arret = $l[0];
                $hArriv = $l[1] ?? null;
                $hDep = $l[2] ?? null;

                $idArret = $bdd->prepare("SELECT ID FROM ARRET WHERE NOM = ?");
                $idArret->execute([$arret]);
                $arretRow = $idArret->fetch();

                if (!$arretRow) {
                    $erreur = 1; ?>
                    <p> Erreur : arrêt inexistant ou mal orthographié.<p>
                    <?php continue;
                } 
                $arretID = $arretRow['ID'];

                if((!$hArriv && !$hDep) || (strtotime($hArriv) > strtotime($hDep))) {
                    $erreur = 1; ?>
                    <p> Erreur : l'heure d'arrivée doit être inférieure ou égale à l'heure de départ (sauf si une des deux est vide).<p>
                    <?php continue;
                }

                if ($hArriv && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $hArriv))
                {
                    $erreur = 1; ?>
                    <p>Erreur : format HH:MM:SS attendu pour l'heure d'arrivée.<p>
                <?php }

                if ($hDep && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $hDep))
                {
                    $erreur = 1; ?>
                    <p>Erreur : format HH:MM:SS attendu pour l'heure de départ.<p>
                <?php }

                $insertHoraire = $bdd->prepare("INSERT INTO HORAIRE (TRAJET_ID, ITINERAIRE_ID, ARRET_ID, HEURE_ARRIVEE, HEURE_DEPART)
                VALUES (?, ?, ?, ?, ?)");
                $insertHoraire->execute([$_POST['IDtrajet'], $_POST['liste_itinéraire'], $arretID, $hArriv ?: null, $hDep ?: null]);
            }
        } 
        if ($erreur == 0) { ?>
        <p>Horaire ajouté pour le trajet <?= htmlentities($_POST['IDtrajet'])?>.</p>
    <?php 
        }
    }
    ?>
</body>
</html>

