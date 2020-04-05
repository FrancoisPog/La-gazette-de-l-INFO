<?php 


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

// Includes
require_once('bibli_formulaire.php');
require_once('bibli_database.php');

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
    
    // youtube with legende
    if(preg_match('/^\[youtube:\d+:\d+:(https:\/\/)?(www\.)?youtube\.com\/[^ \]]+ [^\]]+\]$/i',$arg[0])){
        return preg_replace('/youtube:(\d+):(\d+):((https:\/\/)?(www\.)?youtube\.com\/[^ \]]+) ([^\]]*)$/i','<figure><iframe width="\1" height="\2" src="\3" allowfullscreen></iframe><figcaption>\6</figcaption></figure>',$arg[1]);
        
    }

    // youtube without légende
    if(preg_match('/^\[youtube:\d+:\d+:(https:\/\/)?(www\.)?youtube\.com\/[^ \]]+\]/i',$arg[0])){
        return preg_replace('/youtube:(\d+):(\d+):((https:\/\/)?(www\.)?youtube\.com\/[^ \]]+)/i','<iframe width="\1" height="\2" src="\3" allowfullscreen></iframe>',$arg[1]);
        
    }

    // Link
    if(preg_match('/^\[a:([^\]])*\][^\[]+\[\/a\]$/i',$arg[0])){
        return preg_replace_callback($exp,'fp_html_parseBbCode',preg_replace('/\[a:([^\]]*)\]([^\[]+)\[\/a\]/i','<a href="\1">\2</a>',$arg[0]));
       
    }

    // Unicode
    if(preg_match('/^\[#x?[0-9a-fA-F]+\]$/',$arg[0])){
        return preg_replace('/\[#(x)?([0-9a-fA-F]+)\]/','&#\1\2;',$arg[0]);
    }

    
    // Basic tags
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

    echo 'error';
    
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

// PARAMETERS

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

// SESSION

/**
 * Correctly end a session and redirect to the given page.
 * @param String $page  The page for the redirection
 */
function fp_session_exit($page){
    session_destroy();
    session_unset();

    // deleting session cookie
    $cookie_session_data = session_get_cookie_params();
    setcookie(session_name(), 
                '', 
                time() - 86400,
                $cookie_session_data['path'], 
                $cookie_session_data['domain'],
                $cookie_session_data['secure'],
                $cookie_session_data['httponly']
            );
        
    header("Location: $page");
    exit(0);

}

/**
 * Check if the user is logged in.
 * @param mixed $page_to_go_if_not  If you want to redirect the user if he's not logged in, you must specify a page
 * @return boolean True if he's logged in, else false (only if he's not redirected)
 */
function fp_is_logged($page_to_go_if_not = false){
    $isLogged = (isset($_SESSION['pseudo']) && isset($_SESSION['statut']));
    if($isLogged){
        return true;
    }
    
    if(!$page_to_go_if_not){
        return false;
    }
    
    fp_session_exit($page_to_go_if_not);

}