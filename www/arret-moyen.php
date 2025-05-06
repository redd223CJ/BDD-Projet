<!DOCTYPE html>
<html>
<head>
    <title>Temps d'arrêt moyen</title>
</head>
<body>

    <?php
        $bdd = new PDO('mysql:host=db;dbname=group22;charset=utf8', 'group22', 'ulgfsa');

        $bdd->beginTransaction();
        // temps moyen global
        $sql_global = "SELECT 
                          SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(h.HEURE_DEPART, h.HEURE_ARRIVEE)))) as temps_arret_moyen
                       FROM 
                          HORAIRE h
                       WHERE 
                          h.HEURE_ARRIVEE IS NOT NULL AND h.HEURE_DEPART IS NOT NULL";
        
        $statement_global = $bdd->prepare($sql_global);
        $statement_global->execute();
        $global_avg = $statement_global->fetch();
        
        // Temps moyen par itinéraire / trajet
        $sql = "SELECT 
                    i.ID as itineraire_id,
                    i.NOM as itineraire_nom,
                    t.TRAJET_ID,
                    SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(h.HEURE_DEPART, h.HEURE_ARRIVEE)))) as temps_arret_moyen
                FROM 
                    HORAIRE h
                JOIN 
                    TRAJET t ON h.TRAJET_ID = t.TRAJET_ID
                JOIN 
                    ITINERAIRE i ON t.ITINERAIRE_ID = i.ID
                WHERE 
                    h.HEURE_ARRIVEE IS NOT NULL AND h.HEURE_DEPART IS NOT NULL
                GROUP BY 
                    i.ID, i.NOM, t.TRAJET_ID
                ORDER BY 
                    i.NOM, temps_arret_moyen";
        
        $statement = $bdd->prepare($sql);
        
        // Temps moyen par itinéraire
        $sql_itineraire = "SELECT 
                              i.ID as itineraire_id,
                              i.NOM as itineraire_nom,
                              SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(h.HEURE_DEPART, h.HEURE_ARRIVEE)))) as temps_arret_moyen
                           FROM 
                              HORAIRE h
                           JOIN 
                              TRAJET t ON h.TRAJET_ID = t.TRAJET_ID
                           JOIN 
                              ITINERAIRE i ON t.ITINERAIRE_ID = i.ID
                           WHERE 
                    	      h.HEURE_ARRIVEE IS NOT NULL AND h.HEURE_DEPART IS NOT NULL
                           GROUP BY 
                              i.ID, i.NOM
                           ORDER BY 
                              i.NOM, temps_arret_moyen";
        
        $statement_itineraire = $bdd->prepare($sql_itineraire);
        $statement_itineraire->execute();
        $itineraires_avg = $statement_itineraire->fetchAll();
        
        echo "<h2>Temps d'arrêt moyen global: " . $global_avg['temps_arret_moyen'] . "</h2>";

        echo "<h2>Temps d'arrêt moyen par itinéraire et trajet</h2>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>ITINÉRAIRE</th><th>TRAJET</th><th>AVG_STOP_TIME</th></tr>";

        foreach ($itineraires_avg as $row) {
            $statement->execute();

            // ajout des temps moyens par trajet
            while ($trajet = $statement->fetch()) {
                if ($trajet['itineraire_id'] == $row['itineraire_id']) {
                    echo "<tr>";
                    echo "<td>" . $row['itineraire_nom'] . "</td>";
                    echo "<td>" . $trajet['TRAJET_ID'] . "</td>";
                    echo "<td>" . $trajet['temps_arret_moyen'] . "</td>";
                    echo "</tr>";
                }
            }

            // temps moyen par itinéraire
            echo "<tr>";
            echo "<td>" . $row['itineraire_nom'] . "</td>";
            echo "<td></td>";
            echo "<td>" . $row['temps_arret_moyen'] . "</td>";
            echo "</tr>";

        }

        $bdd->commit();
        
        echo "</table>";
    
    ?>
</body>
</html>
