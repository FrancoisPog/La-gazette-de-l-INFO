<?php 

require_once('bibli_gazette.php');

/*################################################################
 *
 *              Generic function librarie          
 * 
 *      > My libraries functions are prefixed by fp_
 *      > My local functions are prefixed by fpl_
 * 
 *      > I organize my functions in several domains :
 *         _print_  -> functions that display html code
 *         _html_   -> processing functions returning html code
 *         _db_     -> interactions functions with database
 *         _str_    -> string processing functions
 *
 ###############################################################*/


// BD

/** 
 *  Ouverture de la connexion à la base de données
 *  En cas d'erreur de connexion le script est arrêté.
 *
 *  @return objet 	connecteur à la base de données
 */
function fp_db_connecter() {
    $conn = mysqli_connect(BD_SERVER, BD_USER, BD_PASS, BD_NAME);
    if ($conn !== FALSE) {
        //mysqli_set_charset() définit le jeu de caractères par défaut à utiliser lors de l'envoi
        //de données depuis et vers le serveur de base de données.
        mysqli_set_charset($conn, 'utf8') 
        or fp_db_erreur_exit('<h4>Erreur lors du chargement du jeu de caractères utf8</h4>');
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
    fp_db_erreur_exit($msg);
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
function fp_db_erreur_exit($msg) {
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
function fp_db_erreur($bd, $sql) {
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

    fp_db_erreur_exit($msg);	// ==> ARRET DU SCRIPT
}

/**
 * Execution of database query. 
 * @param Object $db    The databasse connecter 
 * @param String $query The query 
 * @return Array        The result in an array
 */
function fp_db_execute($db,$query,$protect = true,$insert = false){
    $query = mysqli_query($db,$query) or fp_db_erreur($db,$query);
    
    if($insert){
        mysqli_free_result($query);
        return $query;
    }

    $array = null;
    while($data = mysqli_fetch_assoc($query)){
        $array[] = ($protect) ? fp_db_protect_exits($data):$data;
    }

    mysqli_free_result($query);
    return $array;
}

/**
 * Protection of database outputs.
 * @param mixed $content    The array or string to protect
 * @return mixed            The array or string protected
 */
function fp_db_protect_exits($content) {
    if (is_array($content)) {
        foreach ($content as &$value) { 
            $value = fp_db_protect_exits($value);   
        }
        unset ($value);
        return $content;
    }
    if (is_string($content)){
        $protected_content = htmlentities($content);
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
function fp_db_protect_entries($db,$content) {
    if (is_array($content)) {
        foreach ($content as &$value) { 
            $value = fp_db_protect_entries($db,$value);   
        }
        unset ($value); // à ne pas oublier (de façon générale)
        return $content;
    }
    if (is_string($content)){
        $protected_content = mysqli_real_escape_string($db,$content);
        return $protected_content;
    }
    return $content;
}

// HTML

/**
 * Parsing bbcode text to html code.
 * @param String $arg   The bbcode text to parse
 * @return String       The html text
 */
function fp_html_parseBbCode($arg){
    $exp = array();
    $exp[] = '/\[(p|citation|gras|it|item|liste)\](.+?)\[\/\1\]/i';
    $exp[] = '/\[(br|youtube:\d+:\d+:(https:\/\/)?(www\.)?youtube\.com\/[^ \]]+( [^\]]*)?)\]/i';
    $exp[] = '/\[(a):([^\]]*)\][^\[]+\[\/\1\]/i';
    $exp[] = '/\[#x?[0-9a-fA-F]+\]/';
    
    if(is_string($arg)){
        return preg_replace_callback($exp,'fp_html_parseBbCode',$arg);
    }
    
    // youtube avec legende
    if(preg_match('/^\[youtube:\d+:\d+:(https:\/\/)?(www\.)?youtube\.com\/[^ \]]+ [^\]]+\]$/i',$arg[0])){
        return preg_replace('/youtube:(\d+):(\d+):((https:\/\/)?(www\.)?youtube\.com\/[^ \]]+) ([^\]]*)$/i','<figure><iframe width="\1" height="\2" src="\3" allowfullscreen></iframe><figcaption>\6</figcaption></figure>',$arg[1]);
        
    }

    // youtube sans légende
    if(preg_match('/^\[youtube:\d+:\d+:(https:\/\/)?(www\.)?youtube\.com\/[^ \]]+\]/i',$arg[0])){
        return preg_replace('/youtube:(\d+):(\d+):((https:\/\/)?(www\.)?youtube\.com\/[^ \]]+)/i','<iframe width="\1" height="\2" src="\3" allowfullscreen></iframe>',$arg[1]);
        
    }

    // Lien
    if(preg_match('/^\[a:([^\]])*\][^\[]+\[\/a\]$/i',$arg[0])){
        return preg_replace_callback($exp,'fp_html_parseBbCode',preg_replace('/\[a:([^\]]*)\]([^\[]+)\[\/a\]/i','<a href="\1">\2</a>',$arg[0]));
       
    }

    // Code unicode 
    if(preg_match('/^\[#x?[0-9a-fA-F]+\]$/',$arg[0])){
        return preg_replace('/\[#(x)?([0-9a-fA-F]+)\]/','&#\1\2;',$arg[0]);
    }

    
    // Balises simples
    switch ($arg[1]) {
        case "br":
            return "<br>";
        case 'p' :
            return preg_replace_callback($exp,'fp_html_parseBbCode','<p>'.$arg[2].'</p>');
        case 'citation' : 
            return preg_replace_callback($exp,'fp_html_parseBbCode','<blockquote>'.$arg[2].'</blockquote>');
        case 'liste' : 
            return preg_replace_callback($exp,'fp_html_parseBbCode','<ul>'.$arg[2].'</ul>');
        case 'item' : 
            return preg_replace_callback($exp,'fp_html_parseBbCode','<li>'.$arg[2].'</li>');
        case 'it' : 
            return preg_replace_callback($exp,'fp_html_parseBbCode','<em>'.$arg[2].'</em>');
        case 'gras' : 
            return preg_replace_callback($exp,'fp_html_parseBbCode','<strong>'.$arg[2].'</strong>');
    }

    echo 'erreur';
    
}

// STR

/**
 * Check if a string is an int
 * @param String $str The string to test
 * @return boolean    True if the string is an int, else false
 */
function fp_str_isInt($str){
    return preg_match('/^[[:digit:]]+$/',$str);
}

/**
 * Parsing date from database to string 
 * @param int $date The date to parse
 * @return String   The date in correct format
 */
function fp_str_toDate($date){
    setlocale(LC_TIME, "fr_FR");
    return utf8_encode(strftime("%e %B %G &agrave; %Hh%M",strtotime($date)));
}

// OTHER

/**
 * Checking parameters validity
 * @param Array array               The array containing the parameters
 * @param Array $mandatory_keys     The array containing the mandatory keys
 * @param Array $optional_keys      The array containing the optional keys
 * @return boolean                  True if the parameters are correct, else false
 */
function fp_check_param($array, $mandatory_keys, $optional_keys = array()){
    $array = array_keys($array);
    if (count(array_diff($mandatory_keys, $array)) > 0){
        return false;
    }
    if (count(array_diff($array, array_merge($mandatory_keys,$optional_keys))) > 0){
        return false;
    }
    
    return true;
}