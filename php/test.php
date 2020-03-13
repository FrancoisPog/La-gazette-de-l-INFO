<?php

require_once("bibli_generale.php");








// Texte
$txt = "[p]Avec plus de 500 réponses, et après dépouillement des votes, voici les résultats de notre grand sondage : allez-vous réussir votre année ? [/p][liste][item] Oui, j'y crois, je suis motivé comme jamais et j'ai super-bien bossé (55%)[/item] [item]Bof, je sais pas trop, on verra bien (42%)[/item][item]Peut-être mais uniquement grâce aux compensations (8%)[/item][item]Oui, car je vais glisser quelques billets dans ma copie (4%)[/item][item]Sûrement, j'ai des vidéo compromettantes du prof d'algo (0.2%) [/item][/liste] [p] Les résultats ont été commentés par Frédéric Dadeau, responsable de la filière informatique :[/p] [citation]    Mouais bof, à part la dernière réponse, ce n'est pas vraiment différent des années précédentes.[/citation][p]Nous ne pouvons que le remercier pour son analyse. [/p]";
// Remplacement
$html = parseBbCode($txt);

echo $html;