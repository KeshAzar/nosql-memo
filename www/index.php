<script>

    
    if(typeof window.history.pushState == 'function') {
        window.history.pushState({}, "Hide", '<?php echo $_SERVER['PHP_SELF'];?>');
    }

</script>

<?php
	function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
	}

/*************************************************
 * 
 *   0) Récupérer les tâches du fichier de données
 * 
 * **********************************************/

// Connexion à la base de données PostgreSQL

$config = "pgsql:host=postgresql-memo;port=5432;dbname=postgres";
$username = "admin";
$password = "password";

$db_connection = new PDO($config, $username, $password);

// Connexion à la base de données Redis

$redis = new Redis();
$redis->connect('redis-memo', 6379);


$tachesFichier = "data/memo.json";
$tachesJSON = file_get_contents($tachesFichier);


$tachesArray = json_decode($tachesJSON, true);
$tachesFilter = $tachesArray;

$sql = "SELECT * FROM taches";
$stmt = $db_connection->prepare($sql);
$stmt->execute();
$tachesArrayDb = $stmt->fetchAll(PDO::FETCH_ASSOC);


/***********************
 *
 *  1) Ajouter une tâche
 * 
 * ********************/



if (isset($_POST["texteTache"])) {
    $texte = $_POST["texteTache"];

    
    $idTache = uniqid();
    
    
    $dateHeureTache = gmdate('Y-m-d H:i:s');
       

    $tachesArray[$idTache] = [
        "texte" => $texte,
        "accomplie" => false,
        "dateAjout" => $dateHeureTache,
    ];

      
    $tachesJSON = json_encode($tachesArray);

    file_put_contents($tachesFichier, $tachesJSON);

		// Insert dans postgresql

		$sql = "INSERT INTO taches (id, texte, accomplie, date_ajout) VALUES (:id, :texte, :accomplie, :date_ajout)";
		$stmt = $db_connection->prepare($sql);
		$stmt->execute([
			':id' => $idTache,
			':texte' => $texte,
			':accomplie' => 0,
			':date_ajout' => $dateHeureTache,
		]);


		// Insert dans redis


	}


/*************************************************************
 *
 *  2) Afficher les tâches : Voir ci-dessous dans le code HTML
 * 
 * **********************************************************/


/************************
 * 
 *  3) Filtrer les tâches
 * 
 * *********************/




if (isset($_GET["action"]) && $_GET["action"] == "filtrer") {
    
    
    if(isset($_GET["accomplie"]) && $_GET["accomplie"]==="1"){
       $tachesArrayDb=array_filter($tachesArrayDb,function($p){
           return ($p["accomplie"] == true);
       });
    }
    if(isset($_GET["accomplie"]) && $_GET["accomplie"]==="0"){
        $tachesArrayDb=array_filter($tachesArrayDb,function($p){
            return ($p["accomplie"] == false);
        });
     }
}

/*********************************
 *  
 *  4) Basculer l'état d'une tâche
 * 
 * ******************************/


if (isset($_GET["action"]) && $_GET["action"]=="basculer" && isset($_GET["id"])) {
    
    $tachesArray[$_GET["id"]]["accomplie"] = !$tachesArray[$_GET["id"]]["accomplie"];

		$sql = "SELECT * FROM taches WHERE id = :id";
		$stmt = $db_connection->prepare($sql);
		$stmt->execute([
			':id' => $_GET["id"],
		]);
		$tache = $stmt->fetch(PDO::FETCH_ASSOC);

		// Basculer dans postgresql

		$sql = "UPDATE taches SET accomplie = :accomplie WHERE id = :id";
		$stmt = $db_connection->prepare($sql);
		$stmt->execute([
			':accomplie' => $tache["accomplie"] ? 0 : 1,
			':id' => $_GET["id"],
		]);

		// Basculer dans redis

    
    $tachesJSON = json_encode($tachesArray);

    file_put_contents($tachesFichier, $tachesJSON);
}

if (isset($_GET["action"]) && $_GET["action"]=="supprimer" && isset($_GET["id"])) {
    
    unset($tachesArray[$_GET["id"]]);
    
		// Supprimer dans la db postgresql

		$sql = "DELETE FROM taches WHERE id = :id";
		$stmt = $db_connection->prepare($sql);
		$stmt->execute([
			':id' => $_GET["id"],
		]);

		// Supprimer dans redis

    
  
    $tachesJSON = json_encode($tachesArray);

    
    file_put_contents($tachesFichier, $tachesJSON);
}

$sql = "SELECT * FROM taches";
$stmt = $db_connection->prepare($sql);
$stmt->execute();
$tachesArrayDb = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>MEMO | Liste de tâches à compléter</title>
    <meta name="description" content="Application Web de gestion de tâches à compléter.">
    <link rel="stylesheet" href="ressources/css/styles.css">
</head>

<body>
    <div class="conteneur">
        <a href="index.php">
            <h1>MEMO</h1>
        </a>
        <form method="post" autocomplete="off">
            <input autofocus class="quoi-faire" type="text" name="texteTache" placeholder="Tâche à accomplir ...">
        </form>
        <div class="filtres">
            <!-- Les liens suivants permettent de filtrer les tâches -->
            <a href="index.php?action=filtrer&accomplie=1">Complétées</a>
            <a href="index.php?action=filtrer&accomplie=0">Non-complétées</a>
            <a href="index.php">Toutes</a>
        </div>
        <ul class="liste-taches">
            <!-- 
            Utilisez les éléments LI suivants comme gabarits pour l'affichage
            des "tâches".
            
            Remarquez la présence de la classe "accomplie" sur l'élément LI pour le montrer 
            biffé (complété) ou non (dépend de la valeur du champ "accomplie" dans le fichier JSON).
            -->

            <?php
            foreach ($tachesArrayDb as $infoTache) :

                ?>

                <li class="<?= ($infoTache["accomplie"] === true)?"accomplie":""; ?>">
                    <span class="coche done"><a href="?action=basculer&id=<?= $infoTache["id"]; ?>" title="Cliquez pour faire basculer l'état de cette tâche."><img src="ressources/images/coche.svg" alt=""></a></span>
		    <div class="memo">    
			<span class="texte"><?= $infoTache["texte"]; ?></span>
			<span class="ajout"><?= $infoTache["date_ajout"]; ?></span>
		    </div>
                    <span class="coche"><a href="?action=supprimer&id=<?= $infoTache["id"]; ?>" title="Cliquez pour supprimer cette tâche."><img src="ressources/images/delete.svg" alt=""></a></span>
                </li>


            <?php endforeach; ?>

        </ul>
    </div>
</body>

</html>
