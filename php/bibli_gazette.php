<?php 

define("BD_SERVER","localhost");
define("BD_NAME","poguet_gazette");
define("BD_USER","poguet_u");
define("BD_PASS","poguet_p");

/**
 * Affiche le menu de navigation
 * @param int $deepness : La profondeur du fichier par rapport au dossier gazette 
 * @param int $status : Le status de l'utilisateur connecté (-1 si non authentifié)
 */
function fp_make_gaz_nav(int $deepness, int $status){
    $path="";
    for($i = 0 ; $i < $deepness ; $i++){
        $path.="../";
    }
    
    echo    '<nav>',
                '<ul>',
                    '<li><a href="',$path,'index.php">Accueil</a></li>',
                    '<li><a href="',$path,'html/actus.html">Toute l\'actu</a></li>',
                    '<li><a href="',$path,'php/recherche.php">Recherche</a></li>',
                    '<li><a href="',$path,'html/redaction.html">La rédac\'</a></li>';

    if($status == -1){
        echo        '<li><a href="',$path,'php/connexion.php">Se connecter</a></li>';
    }else{
        echo        '<li><a href="',$path,'php/compte.php">jbigoude</a>',
                        '<ul>',
                            '<li><a href="',$path,'php/compte.php">Mon profil</a></li>',
                            ($status > 0 && $status != 2) ? "<li><a href=\"'$path'php/nouveau.php\">Nouvel article</a></li>":'',
                            ($status > 1) ? "<li><a href=\"$path"."php/administration.php\">Administration</a></li>":'',
                            '<li><a href="',$path,'php/deconnexion.php">Se deconnecter</a></li>',
                        '</ul>',
                    '</li>';
    }
                    
    echo        '</ul>',
            '</nav>';
}

/**
 * Affiche le header du site
 * @param int $deepness : La profondeur du fichier par rapport au dossier gazette 
 * @param String $title : Le titre de la page
 */
function fp_make_gaz_header($deepness, $title){
    $path="";
    for($i = 0 ; $i < $deepness ; $i++){
        $path.="../";
    }
    echo    '<header>',
                '<img src="',$path,'images/titre.png" alt="Titre : La gazette de l\'info">',
                '<h1>',$title,'</h1>',
            '</header>';   
}

/**
 * Affiche le footer de la gazette de l'info
 */
function fp_make_gaz_footer(){
    echo    '<footer>',
                '<p> &copy; Licence Informatique - Janvier 2020 - Tous droits réservés</p>',
            '</footer>';
}

/**
 * Affiche le début d'une page du site (head+nav+header)
 * @param String $titleHead : Le titre de la page (head)
 * @param String $titleHeader : Le titre de la page (header)
 * @param int $deepness : La profondeur du fichier par rapport au dossier gazette
 * @param String $stylesheet : Le lien vers la feuille de style
 * @param int $status : Le status de l'utilisateur connecté (-1 si non authentifié)
 */
function fp_begin_gaz_page($titleHead,$titleHeader,$deepness,$stylesheet,$status){
    fp_begin_tag('!DOCTYPE html');
    fp_begin_tag('html',['lang'=>'fr']);
    fp_make_head($titleHead,$stylesheet);
    fp_begin_tag('body');
    fp_make_gaz_nav($deepness,$status);
    fp_make_gaz_header($deepness,$titleHeader);
}

/**
 * Affiche la fin d'une page du site
 */
function fp_end_gaz_page(){
    fp_make_gaz_footer();
    fp_end_tag('body');
    fp_end_tag('html');
}

/**
 * Affiche le debut d'une section et son titre
 * @param String $title : Le titre de la section
 * @param Array $attributs : La liste des attributs et leur valeurs de la section
 */
function fp_begin_gaz_section($title,$attributs=[]){
    fp_begin_tag('section',$attributs);
    echo '<h2>',$title,'</h2>';
}

/**
 * Affiche la fin d'une section
 */
function fp_end_gaz_section(){
    fp_end_tag('section');
}