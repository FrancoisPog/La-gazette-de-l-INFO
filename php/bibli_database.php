<?php 

/*################################################################
 *
 *              Database functions librarie          
 *
 ###############################################################*/

/** 
 *  Ouverture de la connexion à la base de données
 *  En cas d'erreur de connexion le script est arrêté.
 *
 *  @return objet 	connecteur à la base de données
 */
function cp_db_connecter() {
    $conn = mysqli_connect(BD_SERVER, BD_USER, BD_PASS, BD_NAME);
    if ($conn !== FALSE) {
        //mysqli_set_charset() définit le jeu de caractères par défaut à utiliser lors de l'envoi
        //de données depuis et vers le serveur de base de données.
        mysqli_set_charset($conn, 'utf8') 
        or cp_db_error_exit('<h4>Erreur lors du chargement du jeu de caractères utf8</h4>');
        return $conn;     // ===> Sortie connexion OK
    }
    // Erreur de connexion
    // Collecte des informations facilitant le debugage
    $msg = '<h4>Erreur de connexion base MySQL</h4>'
            .'<div style="margin: 20px auto; width: 350px;">'
            .'BD_SERVER : '. BD_SERVER
            .'<br>BD_USER : '. BD_USER
            .'<br>BD_PASS : '. BD_PASS
            .'<br>BD_NAME : '. BD_NAME
            .'<p>Erreur MySQL numéro : '.mysqli_connect_errno()
            //appel de htmlentities() pour que les éventuels accents s'affiche correctement
            .'<br>'.htmlentities(mysqli_connect_error(), ENT_QUOTES, 'ISO-8859-1')  
            .'</div>';
    cp_db_error_exit($msg);
}

/**
 * Arrêt du script si erreur base de données 
 *
 * Affichage d'un message d'erreur, puis arrêt du script
 * Fonction appelée quand une erreur 'base de données' se produit :
 * 		- lors de la phase de connexion au serveur MySQL
 *		- ou indirectement lorsque l'envoi d'une requête échoue
 *
 * @param string	$msg	Message d'erreur à afficher
 */
function cp_db_error_exit($msg) {
    ob_end_clean();	// Suppression de tout ce qui a pu être déja généré

    echo    '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">',
            '<title>Erreur base de données</title>',
            '<style>',
                'table{border-collapse: collapse;}td{border: 1px solid black;padding: 4px 10px;}',
            '</style>',
            '</head><body>',
            $msg,
            '</body></html>';
    exit(1);		// ==> ARRET DU SCRIPT
}

/**
 * Gestion d'une erreur de requête à la base de données.
 *
 * A appeler impérativement quand un appel de mysqli_query() échoue 
 * Appelle la fonction xx_db_erreurExit() qui affiche un message d'erreur puis termine le script
 *
 * @param objet		$bd		Connecteur sur la bd ouverte
 * @param string	$sql	requête SQL provoquant l'erreur
 */
function cp_db_error($bd, $sql) {
    $errNum = mysqli_errno($bd);
    $errTxt = mysqli_error($bd);

    // Collecte des informations facilitant le debugage
    $msg =  '<h4>Erreur de requête</h4>'
            ."<pre><b>Erreur mysql :</b> $errNum"
            ."<br> $errTxt"
            ."<br><br><b>Requête :</b><br> $sql"
            .'<br><br><b>Pile des appels de fonction</b></pre>';

    // Récupération de la pile des appels de fonction
    $msg .= '<table>'
            .'<tr><td>Fonction</td><td>Appelée ligne</td>'
            .'<td>Fichier</td></tr>';

    $appels = debug_backtrace();
    for ($i = 0, $iMax = count($appels); $i < $iMax; $i++) {
        $msg .= '<tr style="text-align: center;"><td>'
                .$appels[$i]['function'].'</td><td>'
                .$appels[$i]['line'].'</td><td>'
                .$appels[$i]['file'].'</td></tr>';
    }

    $msg .= '</table>';

    cp_db_error_exit($msg);	// ==> ARRET DU SCRIPT
}

/**
 * Execution of database query. 
 * @param Object $db            The databasse connecter 
 * @param String $query         The query 
 * @param bool $protect_outputs If true, the outputs results will be protected (for 'select' query)
 * @param bool $insert          If true, is an insertion query
 * @param bool $multi           If there are severals query in $query
 * @return Array                The result in an array
 */
function cp_db_execute($db,$query,$protect_outputs = true,$insert = false,$multi = false){
    $array = null;
    if($multi){ // Multi query
        $res = mysqli_multi_query($db,$query);
        if(!$res){
            cp_db_error($db,$query);
        }

        if($insert){
            return $res;
        }

        $i = 0;
        do {
            if ($result = mysqli_store_result($db,0)) {
                while ($data = mysqli_fetch_assoc($result)) {
                    $array[$i][] = ($protect_outputs) ? cp_db_protect_outputs($data) : $data;
                }
                mysqli_free_result($result);
            }
            
            if (!mysqli_more_results($db)) {
                break;
            }
            $i++;
        } while (mysqli_next_result($db));

        return $array;
    }

    // Single query

    $res = mysqli_query($db,$query);

    if(!$res){
        cp_db_error($db,$query);
    }
    
    if($insert){
        return $res;
    }

    while($data = mysqli_fetch_assoc($res)){
        $array[] = ($protect_outputs) ? cp_db_protect_outputs($data) : $data;
    }

    mysqli_free_result($res);
    return $array;
}

/**
 * Protection of database outputs. (htmlentities())
 * @param mixed $content    The array or string to protect
 * @return mixed            The array or string protected
 */
function cp_db_protect_outputs($content) {
    if (is_array($content)) {
        foreach ($content as &$value) { 
            $value = cp_db_protect_outputs($value);   
        }
        unset ($value);
        return $content;
    }
    if (is_string($content)){
        $protected_content = htmlentities($content,ENT_QUOTES);
        return $protected_content;
    }
    return $content;
}

/**
 * Protection of database inputs.
 * @param Object $db        The database connecter
 * @param mixed $content    The array or string to protect
 * @return mixed            The array or string protected
 */
function cp_db_protect_inputs($db,$content) {
    if (is_array($content)) {
        foreach ($content as &$value) { 
            $value = cp_db_protect_inputs($db,$value);   
        }
        unset ($value);
        return $content;
    }
    if (is_string($content)){
        $protected_content = mysqli_real_escape_string($db,$content);
        return $protected_content;
    }
    return $content;
}
