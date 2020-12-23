-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Hôte : db115427.sql-pro.online.net
-- Généré le : mer. 23 déc. 2020 à 14:22
-- Version du serveur :  5.7.19-0ubuntu0.16.04.1
-- Version de PHP : 7.0.32-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `db38127_poguet_gazette`
--

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `arID` int(11) UNSIGNED NOT NULL,
  `arTitre` char(250) COLLATE utf8_unicode_ci NOT NULL,
  `arResume` text COLLATE utf8_unicode_ci NOT NULL,
  `arTexte` text COLLATE utf8_unicode_ci NOT NULL,
  `arDatePublication` bigint(12) UNSIGNED NOT NULL,
  `arDateModification` bigint(12) UNSIGNED DEFAULT NULL,
  `arAuteur` char(20) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`arID`, `arTitre`, `arResume`, `arTexte`, `arDatePublication`, `arDateModification`, `arAuteur`) VALUES
(1, 'Sondage : allez-vous réussir votre année ?', 'Comme chaque année, retrouvez les résultats de notre sondage de la rentrée, dont les résultats ont enfin été dépouillés par nos mains expertes.', '[p]Avec plus de 500 réponses, et après dépouillement des votes, \r\n voici les résultats de notre grand sondage : allez-vous réussir votre année ? [/p][liste][item] Oui, j\'y crois, je suis motivé \r\n comme jamais et j\'ai super-bien bossé (55%)[/item] [item]Bof, je sais pas trop, on verra bien (42%)[/item][item]Peut-être mais uniquement grâce aux compensations (8%)[/item][item]Oui, car je vais glisser quelques billets dans ma copie (4%)[/item][item]Sûrement, j\'ai des vidéo compromettantes du prof d\'algo (0.2%) [/item][/liste] [p] Les résultats ont été commentés par Frédéric Dadeau, responsable de la filière informatique :[/p] [citation]    Mouais bof, à part la dernière réponse, ce n\'est pas vraiment différent des années précédentes.[/citation][p]Nous ne pouvons que le remercier pour son analyse. [/p]', 201911021056, NULL, 'jbigoude'),
(2, 'Il leur avait annoncé \"Je vais vous défoncer\" l\'enseignant relaxé', 'Jugé vendredi dernier pour avoir proféré des menaces un peu rudes, le professeur d\'algorithmique et structures de données à obtenu le relaxe dans une affaire qui devrait faire jurisprudence. ', '[p]Pour Julien, cette journée de septembre était comme les autres. Après avoir démarré son TD de Théorie des Langages à 9h30, le brillant trentenaire a, d\'un coup, \"pété un plomb\" quand il a constaté, 15 secondes après avoir distribué le sujet, que seulement 90% des étudiants s\'étaient mis au travail et commençaient à chercher le premier exercice.[/p][citation]C\'est à ce demander ce que les autres attendaient. Du coup, mon sang n\'a fait qu\'un tour et je me suis laissé emporter.[/citation][p]reconnait l\'accusé dans son box.[/p][p]Déjà sous le coup d\'une mise à l\'épreuve pour avoir, l\'an passé, été reconnu responsable d\'un pantalon souillé à la simple vue de son sujet d\'Algorithmique et Structure de Données, Julien leur a asséné cette phrase qui en a choqué plus d\'un :[/p] [citation] Vous allez voir, à l\'exam, je vais vous défoncer. [/citation] [p] Des mots rudes, qui ont été pris personnellement par William (le prénom a été modifié pour préserver son anonymat). Ce dernier a été retrouvé, après 3 jours d\'inquiétante disparition, prostré au fond de la salle 501C, en boule et dos au mur.[/p][p]Absent pour ne pas avoir à croiser le regard de son tortionnaire, le témoignage de William aurait peut-être pu changer l\'issue du procès. Le juge a en effet estimé qu\'en l\'état, [it]\"il n\'y a pas mort d\'homme\"[/it] et que [it]\"ça ne leur fait pas de mal à ces petits jeunes de se faire malmener un peu de temps en temps\"[/it].[/p] [p] Malgré le réquisitoire du procureur de la république, qui demandait 6 mois de TIG au service des emplois du temps, Julien est sorti libre. [/p]', 201911131247, NULL, 'kdiot'),
(3, 'Le Président Macron obtient sa Licence d\'Informatique en EAD', 'Et si les apparences étaient finalement trompeuses ? Découvrez dans cet article les dessous d\'une image, désormais devenue mondialement célèbre, du Président Macron capturée lors de la finale de la coupe du monde 2018.', '[p]L\'image a fait le tour du monde : Emmanuel Macron debout, le poing levé, victorieux, lors de la finale de la Coupe du Monde de football 2018 à Moscou le 15 juillet dernier. Sur le moment, tout le monde a cru qu\'il s\'agissait de la réaction du Président au but de Killian M\'Bappé. Mais la vérité est toute autre.[/p]\r\n\r\n[citation]Nous venions tout juste de finir le jury de 1ère session de Licence Info EAD. Il faisait partie des diplômés. Le sachant loin, je me suis dit que j\'allais l\'appeler pour l\'informer de la bonne nouvelle. A peine raccroché, je l\'ai vu exulter sur l\'écran de mon téléviseur. [br] Tout le monde a cru qu\'il était content parce que l\'Equipe de France avait marqué, mais moi, je connais la vraie raison ![/citation]\r\n\r\n[p]commente Isabelle Jacques, responsable de la 3e année. Le Président, qui a fait de l\'Intelligence Artificielle son cheval de bataille, avait entrepris à l\'automne 2017 de suivre une Licence d\'Informatique en EAD à l\'Université de Franche-Comté.[/p]\r\n\r\n[citation]Il a eu du mal au début, et c\'était sûrement très difficile pour lui de jongler avec son emploi du temps de ministre (sic) et les devoirs à rendre. Il peut être fier de lui. [/citation]\r\n\r\n[p]Sur cette image désormais historique, M. Macron exprime donc sa joie d\'avoir validé son diplôme, malgré des résultats, il faut bien l\'admettre, un peu limites en TLSP et en Méthodes Formelles.[/p]\r\n\r\n[p]Joint par téléphone, le secrétariat de l\'Elysée s\'est, dans un premier temps, refusé à tous commentaires. Mis en confiance par notre reporter, il s\'est finalement laissé aller à la confidence suivante :[/p]\r\n\r\n[citation]L\'image était parfaite, on a en a même fait d\'ailleurs un [a:https://boutique.elysee.fr/mode/410-t-shirt-champions-du-monde.html]tee-shirt[/a]. De quoi aurait-on l\'air si le public découvrait que le Président ne suivait même pas le match et qu\'il s\'enflammait pour une banale Licence obtenue dans une petite université de province ?[/citation]\r\n\r\n[p]Une information qui nous apporte un éclairage nouveau sur ce cliché historique.[/p]', 201911270655, 201911271235, 'akuz'),
(4, 'Donald Trump veut importer les CMI aux Etats-Unis', 'Récemment en visite en France, D. Trump a été très impressionné par les étudiants du Cursus Master en Ingénierie. A tel point qu\'il a décidé d\'en importer quelques uns sur le territoire américain pour lancer quelques essais cliniques. ', '[p]En marge de son dernier déplacement en France, le président des Etats-Unis, Donald Trump, est venu visiter en catimini l\'Université de Franche-Comté et notamment la filière informatique. A peine de retour dans le bureau ovale, il a décidé d\'inscrire dans la constitution américaine un nouvel amendement. Celui-ci aura pour objectif d\'imposer à toutes les universités américaines de remplacer les cursus universitaires traditionnels par des Cursus Master en Ingénierie à la Française.[/p]\r\n\r\n[p]Le directeur du CMI, Maxime Jaqcuot, a accueilli cette décision avec le plus grand enthousiasme :[/p]\r\n\r\n[citation]Donald [Trump, ndlr] m\'a appelé depuis la Maison Blanche, alors que j\'étais en train de tremper ma biscotte beurrée dans mon thé à la bergamotte. J\'étais très supris. Et voilà qu\'il se met à me parler du Réseau FIGURE et des CMI ![br]\r\nIl m\'a dit qu\'il trouvait l\'idée excellente et qu\'il souhaitait l\'imposer dans les université Américaines, rendez-vous compte ! Quelle belle récompense pour le CMI ![/citation]\r\n\r\n[p]Des émissaires des prestigieuses universités de Harvard, Yale, ou encore Berkeley sont attendus à la rentrée prochaine sur le campus de la Bouloie pour tout apprendre des CMI, et importer outre-atlantique la recette qui a fait le succès que l\'on lui connaît aujourd\'hui. Ils comptent bien repartir avec quelques spécimens d\'étudiants CMI dans leurs valises. Le pot de départ de Valentin, Fabian et Nathanaël aura donc lieu en 401C, le vendredi 22 décembre prochain. [/p]', 201911301058, NULL, 'jbigoude'),
(5, 'Le calendier des Dieux de la Licence bientôt disponible', 'Comme chaque année à la même période, le traditionnel calendrier des Dieux de la Licence Info va bientôt atterrir dans les kiosques. Son objectif : financer le coût de la formation, plombé par les trop nombreux redoublements des étudiants en 1ère et 2e année.', '[p]À l\'instar du calendrier des Dieux du Stade dont les bénéfices sont reversés à diverses associations caritatives, l\'EAD a choisi un moyen original de renflouer ses caisses.[/p]\r\n\r\n[p]Frédéric Dadeau, directeur de la Licence, est à l\'origine de cette initiative :[/p]\r\n\r\n[citation]En ces temps de situation budgétaire serrée, c\'est vraiment à nous, enseignants-chercheurs d\'aller chercher les financements là où ils sont. Voyant l\'engouement pour des sportifs dénudés, on s\'est dit que l\'on pourrait très bien, nous aussi, exhiber nos muscles les plus développés, c\'est-à-dire nos cerveaux, dans le plus simple appareil. [/citation]\r\n\r\n[p]Le résultat est étonnant : au fil des pages du calendrier, des crânes chauves, dégarnis, ou rasés pour l\'occasion, s\'enchaînent dans des poses toutes plus suggestives les unes que les autres.[/p]\r\n\r\n[p]Malgré l\'excellence de cette idée, le calendrier a pris du retard dans sa réalisation. A qui la faute ? A une bande de frondeurs, menée par notre typographe Yves Jourdelesse, qui n\'étaient pas d\'accord pour se faire tondre spécialement pour l\'occasion :[/p]\r\n\r\n[citation]Vous êtes gentils, mais moi, mes cheveux, j\'y tiens. Les autres, ils ont déjà perdus les leurs, ils s\'en fichent, mais moi j\'ai encore des poils sur le caillou, j\'aimerais bien les garder ! [/citation]\r\n\r\n[p]Remis dans le droit chemin, Yves et ses petits amis ont été sévèrement punis et le calendrier pourra être édité pour les fêtes de fin d\'année. Il sera en vente au prix de 18 euros, avec une offre promotionnelle spéciale pour les étudiants de Licence, qui le recevront automatiquement avec leur supplément au diplôme en fin d\'année. [/p]', 201912141028, NULL, 'jbigoude'),
(6, 'Une arnarque au corrigé de TL mise à jour', 'Notre reporter a suivi la piste d\'une arnaque bien huilée qui a cours depuis plusieurs années en 3e année de Licence Informatique. Contre monnaie sonnante et trébuchante, un faussaire revendait des faux-corrigés des devoirs de Théorie des Langages aux étudiants.', '[p] Depuis 4 ans, une bande d\'arnaqueurs bien organisée sévit dans la filière informatique de l\'université de Franche-Comté.[/p][p]La procédure est simple : un faux étudiant prend contact individuellement avec les membres d\'une promotion de Licence Info 3e année, en se faisant passer pour un redoublant. Il appâte les primo-entrants avec une proposition alléchante qui ne peut pas se refuser : l\'intégralité des devoirs corrigés de Théorie des Langages, en échange d\'une somme rondelette.[/p][p]Intéressé par se faire bien voir de ses enseignants, Jean-Luc (le prénom a été changé pour préserver son anonymat) a cédé à la tentation :[/p][citation]J\'ai déboursé 3000 euros, déposés sur un compte aux îles Caïman. Ils disaient que j\'allais recevoir tous les corrigés des examens de TL de cette année.[/citation][p]Cette matière, très théorique, est en effet la bête noire des étudiants de L3 Informatique. Ces arnaqueurs l\'ont bien compris. Pourtant Jean-Luc a vite déchanté :[/p][citation] J\'ai reçu un corrigé très incomplet de l\'examen de 2003. Quand j\'ai voulu me plaindre, c\'était trop tard. J\'ai vraiment gaspillé mon argent. [/citation] [p]Jean-Luc n\'est malheureusement pas le seul à s\'être fait arnaquer ainsi. Au fil des années, on estime à plus de 400.000 euros la fortune amassée par ces brigands. Même les profs se sont faits avoir :[/p][citation] Je n\'arrivais plus à déterminiser l\'automate de la question 3 de mon propre sujet. Du coup, j\'ai choisi la facilité : j\'ai payé... J\'ai honte.[/citation] [p]nous confie Julien B. qui a souhaité conserver l\'anonymat.[/p][p]Pour démanteler cette économie parallèle de contrebande, nous avons travaillé en étroite collaboration avec les responsables de la filière. Bruno Tatibouët, directeur du département info, nous a donné accès au matériel de pointe. Nous nous sommes fait passer pour un étudiant fraîchement inscrit dans le cursus, et nous avons commencé à nous faire remarquer par quelques questions pointues sur le forum Moodle de TL.[/p][p]La réaction ne s\'est pas fait tarder. A peine quelques heures après avoir demandé si les automates du cours pouvaient jouer de la musique ou faire la vaisselle, nous avons été contactés en message privé sur Moodle par un des membres du-dit gang.[/p][citation] Si ça tintéresse, je te propose le corrigé du premier exo, gratuit, pour essayer. [/citation][p]Pour gagner la confiance de notre interlocuteur, nous sommes rentrés dans son jeu. Pendant 3 semaines, il nous a fourni, en augmentant la somme à chaque proposition, les corrigés des exercices de TL d\'une prétendue interro à venir.[/p][p]Cherchant à le coincer, nous lui avons donné rendez-vous au square Saint-Amour à Besançon, au pied de la Citadelle, pour conclure une importante transaction : la remise contre euros sonnants et trébuchants le sujet et le corrigé de l\'examen de TL de 1ère session.[/p][p]Malheureusement, le jour du RDV, à l\'heure convenue, personne ne s\'est présenté. Peut-être ont-ils pris peur en voyant arriver Fabien Peureux, qui passait là [it]\"par pur hasard, vraiment\"[/it]. Gageons que sa présence aura découragé les agissements de ces voyous, dont nous n\'avons plus jamais entendu parler par la suite. [/p]', 202001020755, 202001020815, 'kdiot'),
(7, 'Une famille de pingouins s\'installe dans l\'amphi B', 'Les températures glaciales relevées au retour des vacances de fin d\'année dans l\'amphi B n\'ont pas fait que des malheureux. Une famille de pinguoins a décidé de s\'installer dans cet amphithéâtre et d\'y élire domicile en vue de fonder un foyer.', '[p]Ce n\'est un secret pour personne, le système de chauffage de l\'UFR ST est possédé par un esprit farceur et semble hors de tout contrôle : bâtiments surchauffés en plein été, climatisation activée au mois de Décembre, nombreuses sont les incohérences de fonctionnement.[/p][citation] On a refusé d\'essayer de comprendre. Chacun a désormais une seconde garde-robe, dans son bureau, pour être prêt en toutes circonstances tout au long de l\'année.[/citation] [p]commente le directeur adjoint aux locaux de l\'UFR.[/p] [p] L\'amphithéatre B dans lequel les étudiants de L2 ont souvent cours n\'échappe pas à cette règle. Victime continuelle d\'un froid polaire toute l\'année, celui-ci a été le théatre d\'une découverte inattendue. Eric Merlet nous raconte :[/p] [citation] J\'étais en train de remballer vite-fait mes affaires à la fin du cours de Langages du Web de 2e année. Mon attention a été attirée par un petit couinement venant de derrière les tableaux. Je me suis alors approché, j\'ai fait remonter les tableaux, je me suis penché et c\'est là que je les ai vus.[/citation] [p] Au milieu des restes de craies tombées au champ d\'honneur, toute une famille de pingouins avait en effet élu domicile dans cette zone glaciale et bien à l\'abri des regards.[/p][citation] Ces animaux sont les premiers à souffrir du réchauffement climatique.[/citation] [p]commente un chercheur du Laboratoire Chrono-Environnement, dépêché sur place pour l\'occasion. Il poursuit :[/p][citation]Il va falloir s\'attendre à voir débarquer dans nos régions de plus en plus d\'espèces menacées, chassées par les conséquences écologiques désastreuses de l\'activité humaine.[/citation] [p]Comment ces animaux sont-ils arrivés là ? Mystère. En attendant, la zone a été protégée, et un soigneur a été spécialement détaché du parc zoologique de la Citadelle de Besançon pour veiller aux bons soins de ces locataires inattendus. Celui-ci a par ailleurs observé que la femelle semble être en gestation et pourrait ainsi mettre bas pendant la période des examens de 2e chance en juin prochain. Le plus grand silence et le plus grand calme sera alors exigé des étudiants pour ne pas déranger la naissance des bébés pingouins.[/p]', 202001170906, NULL, 'jbigoude'),
(8, 'L\'amphi Sciences Naturelles bientôt renommé Amphi Mélenchon', 'L\'un des derniers amphis \"anonymes\" de l\'UFR ST est en passe d\'être baptisé par un patronyme des plus inattendus. En effet, Jean-Luc Mélenchon donnerait son nom à l\'actuel Amphi Sciences Naturelles, pour son plus grand plaisir.', '[p] Après Croisot, Jacquemain et Duffieux, seul l\'amphi Sciences Naturelles du Hall de Propédeutique restait pour l\'instant anonyme. Ce sera désormais chose du passé.[/p][p]En souvenir de la jeunesse Bisontine du leader de la France Insoumise, l\'amphithéatre Sciences Naturelles sera rebaptisé Amphithéatre Jean-Luc Mélenchon.[/p][citation] C\'est un honneur pour moi. J\'ai toujours su que l\'université, et en particulier celle de Besançon, que j\'ai fréquentée dans ma jeunesse, était un repaire de dangereux gauchistes. Mais de là à l\'afficher aussi clairement, c\'est très courageux de leur part par les temps qui courrent.[/citation][p]\r\nEn préambule de la cérémonie qui aura lieu le 12 octobre, une semaine complète de festivités sera organisée. Des portraits de Jean-Luc seront placés dans toutes les salles de TD, et les étudiants devront chanter l\'Internationale, poing levé, à chaque début de cours.[/p][p]Il se murmure même que Jean-Luc Mélenchon pourrait établir son QG de campagne pour 2022 dans les locaux de l\'Aqua. [/p]', 202001271058, 202001271244, 'kdiot'),
(9, 'Votez pour l\'hymne de la Licence', 'En ce début d\'année, les responsables de la Licence Info invitent leurs étudiants à élire la chanson qui deviendra l\'hymne de leur diplôme. La compétition fait rage entre les morceaux choisis. ', '[p] Le jeu concours de ce mois de février propose de choisir l\'hymne de la Licence Informatique. A l\'initiative de cette élection, son directeur nous explique : [/p] [citation] Je pense que c\'est important d\'avoir un hymne derrière lequel tout le monde peut se retrouver. Regardez les Anglais avec [it]\"God Save the Queen\"[/it]. Ils peuvent être en désaccord sur tout, mais dès que les premières notes retentissent, ils ne font qu\'un. Pour la Licence Info, c\'est la même idée. Cela participera à la cohésion du groupe. [/citation] [p]La sélection de cette année, comme il nous le précise, n\'a rien du hasard. [/p][citation] On a effectué une pré-sélection importante, et finalement, on arrive avec 3 morceaux représentatifs, qui proposent des lectures à plusieurs niveaux. Tout d\'abord, il était important que ce soit un hymne en anglais, les étudiants étant attendus d\'avoir le niveau C1 en fin de cursus. Pour varier, on a donc choisi des Australiens, des Anglais et des Américains. On a ensuite sélectionné des groupes, et non des artistes solo, puisque la filière info se veut renforcer l\'esprit de groupe et non juxtaposer des individus. Concernant le type de musique, le choix s\'est porté sur des morceaux plutôt rock, voire métal. L\'informaticien de base étant un g33k solitaire, c\'est le type de musique, relativement recherché du point de vue des harmonies, avec des paroles profondes, qu\'il affectionne tout particulièrement. [br] Pour finir, nous laissons aux étudiants le choix entre des chansons qui peuvent sembler négatives au premier abord, mais se révèlent finalement, à l\'écoute des paroles, être d\'un optimisme débordant. [/citation] [youtube:250:148:https://www.youtube.com/embed/l482T0yNkeo AC/DC - Highway To Hell] [youtube:250:148:https://www.youtube.com/embed/Ij99dud8-0A Iron Maiden - Wasted Years] [youtube:250:148:https://www.youtube.com/embed/Tj75Arhq5ho Metallica - Nothing Else Matters] [p] Si cette initiative peut sembler quelque peu farfelue, il faut savoir que les équipes pédagogiques prennent très au sérieux l\'investissement et l\'adhésion des étudiants à cette nouveauté : [/p] [citation] Vous remarquerez que chacune de ces chansons, comme tout hymne qui se respecte, est reconnaissable dès les premières notes. On effectuera des tests en balançant à l\'improviste cet hymne dans les amphis pour voir si les étudiants se lèvent, la main sur le coeur et se mettent à chanter d\'une seule voix. On mesurera le temps de réaction et on punira sévèrement tous ceux qui feront de la résistance. [/citation] [p] Pour voter, cliquez sur les liens ci-dessous pour envoyer un mail prérempli avec votre choix (un seul mail par personne sera pris en compte) : [/p] [liste][item][a:mailto:contact@gazette-linfo.fr?subject=Vote%20hymne%20Licence&body=Je%20vote%20pour%20le%201]Voter pour AC/DC - Highway To Hell[/a][/item] [item][a:mailto:contact@gazette-linfo.fr?subject=Vote%20hymne%20Licence&body=Je%20vote%20pour%20le%202]Voter pour Iron Maiden - Wasted Years[/a][/item] [item][a:mailto:contact@gazette-linfo.fr?subject=Vote%20hymne%20Licence&body=Je%20vote%20pour%20le%203]Voter pour Metallica - Nothing Else Matters[/a][/item][/liste] [p] Pour rappel, notre dernier jeu concours consistait à trouver une chanson associée à Julien Bernard. Notre sondage a enregistré un taux de participation record (plus de 400 réponses). Plus des [#xBE] des participants ont voté pour le titre de [a:https://www.youtube.com/watch?v=ClQcUyhoxTg]Blue Oyster Cult [it]\"Don\'t fear the reaper\"[/it][/a].[/p]', 202001281423, NULL, 'jbigoude'),
(10, 'Un mouchard dans un corrigé de Langages du Web', 'En ce début de semaine, un corrigé frauduleux a été diffusé sur le Moodle du cours de Langages du Web. S\'il pouvait à première vue sembler s\'agir de pages web tout à fait classiques, la réalité est toute autre. Le code contenait un très discret logiciel espion destiné à dérober les données les plus sensibles des étudiants.', '[p] Une bien curieuse affaire a secoué la semaine dernière le module de Langages du Web. En effet, un fichier diffusé en guise de correction espionnait en cachette le contenu des ordinateurs personnels des étudiants. [/p] [p] La nouvelle a fait grand bruit dans une période où le respect des données personnelles et de la confidentialité était au centre des débats. Le responsable de l\'UE, Eric Merlet, nous assure : [/p] [citation] Je peux vous garantir que je n\'y suis pour rien, et je ne comprends pas ce qui a pu se passer ! [/citation] [p]En cause, un banal fichier HTML diffusé aux étudiants comme solution au TP1. Sans crier gare, à l\'ouverture de ce script, un message d\'alerte s\'affichait indiquant [it]Vous avez été hacké ![/it] <script>alert(\"Vous avez été hacké !\"); document.location.replace(\"https://fr.wikipedia.org/wiki/Cross-site_scripting#Protection_contre_les_XSS\");</script>.[/p] [p]Les chercheurs en sécurité informatique du DISC se sont penchés sur ce script, qui, s\'il semble inoffensif au premier abord, révèle un lourd secret. Julien Bernard, expert en sécurité, nous explique : [/p] [citation]On pourrait penser que ce programme n\'est qu\'une menace en l\'air, comme on aime à en prononcer, mais il n\'en est rien. Le script effectue un scan minutieux des cookies de l\'utilisateur et profite d\'une faille de sécurité au niveau des instructions bas niveau du processeur pour ouvrir une backdoor qui sert ensuite à un troyan qui et envoie toutes les données de l\'étudiant vers un site en Corée du Nord. Nous avons remonté la piste et récupéré les données qui ont été collectées durant plusieurs mois. Nous sommes en train de les analyser. On verra bien ce qu\'il en ressort.[/citation] [p]Plusieurs étudiants, blêmes, nous ont confié leurs inquiétudes :[/p] [citation]C\'est à dire que... euh... je garde sur mon ordi des vidéos un peu...personnelles... J\'espère qu\'elles ne sont pas parties en Corée du Nord...[/citation] [p]A l\'heure où des activistes Russes ont revendiqué être à l\'origine de la diffusion d\'une vidéo intime d\'un candidat aux élections municipales de la ville de Paris, il serait peut-être bon de réfléchir aux usages du numérique dans nos interactions sociales. [/p]', 202002101115, NULL, 'jbigoude');

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `catID` tinyint(3) UNSIGNED NOT NULL,
  `catLibelle` char(100) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`catID`, `catLibelle`) VALUES
(1, 'rédacteur en chef'),
(2, 'premier violon'),
(3, 'sous-fifre');

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

CREATE TABLE `commentaire` (
  `coID` int(11) UNSIGNED NOT NULL,
  `coAuteur` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `coTexte` tinytext COLLATE utf8_unicode_ci NOT NULL,
  `coDate` bigint(12) UNSIGNED NOT NULL,
  `coArticle` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `commentaire`
--

INSERT INTO `commentaire` (`coID`, `coAuteur`, `coTexte`, `coDate`, `coArticle`) VALUES
(1, 'jbernard', 'Ouf, j\'ai eu chaud sur ce coup-là...', 201911131312, 1),
(2, 'emerlet', 'Du coup, ça veut dire qu\'on peut menacer de défoncer les étudiants, sans risquer de représailles ?', 201911131426, 1),
(3, 'freddd', 'Oui, c\'est l\'idée. Et c\'est très efficace.', 201911131512, 1),
(4, 'jbernard', 'On dit merci qui ?', 201911131517, 1),
(5, 'emerlet', 'Ah mince, il va falloir éviter d\'attribuer cet amphi pour les L2...', 202001170919, 7),
(6, 'freddd', 'De toutes façon l\'amphi B, en L2, il est un peu juste... ', 202001170928, 7),
(7, 'jbernard', 'Ca dépend, pour les cours d\'algo, en général il n\'y vite plus grand monde, et pour le coup, on a de la place...', 202001170943, 7),
(8, 'jbernard', 'Si j\'étais intervenu en EAD, jamais il n\'aurait eu sa Licence ! (gniak gniak gniak)', 202001180932, 3),
(9, 'freddd', 'C\'est peut-être pas plus mal : s\'il n\'avait pas eu sa Licence, il n\'y aurait plus d\'unversité...', 202001181010, 3),
(10, 'jbernard', 'Non mais c\'est n\'importe quoi ! Le choix des chansons c\'est de la [#128169]', 202001281743, 9),
(11, 'freddd', 'Non mais oh ! Reste poli ! [#129304]', 202001282358, 9),
(12, 'noussachons', 'C\'est bien évident, on nous cache des choses...', 202002101116, 10);

-- --------------------------------------------------------

--
-- Structure de la table `redacteur`
--

CREATE TABLE `redacteur` (
  `rePseudo` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `reBio` text COLLATE utf8_unicode_ci NOT NULL,
  `reCategorie` tinyint(3) UNSIGNED NOT NULL,
  `reFonction` char(100) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `redacteur`
--

INSERT INTO `redacteur` (`rePseudo`, `reBio`, `reCategorie`, `reFonction`) VALUES
('akuz', '[p]Sans cesse sur les théatres d\'opération aux 4 coins du monde, Alex prête régulièrement sa plume à la Gazette de L-INFO pour nous raconter les trépidentes aventures de nos étudiants de Licence en stage à l\'étranger.[/p]\r\n\r\n[p]Il a récemment suivi la trace d\'un groupe d\'étudiants de L3 en Angleterre et décroché une révélation tout à fait étonnante qui lui vaudra très certainement le prix Pullitzer l\'année prochaine.[/p]\r\n\r\n[p]Equipé des derniers gadgets à la mode dans le domaine des technologies mobiles (très envié de nos sous-fifres [a:#pheupakeur]Pete[/a] et [a:#yjourdelesse]Yves[/a]), Alex s\'infiltre partout, et approvisionne la rédaction en images les plus époustouflantes venues du monde entier.[/p]\r\n\r\n[p]Membre co-fondateur de la rédaction avec [a:#jbigoude]Johnny[/a], le duo a su imposer la présence de cet OVNI journalistique qu\'est notre Gazette au sein du département informatique. [/p] ', 2, 'Correspondant à l\'étranger'),
('jbigoude', '[p]Récemment débarqué de la rédaction d\'iTélé suite au scandale Morandini, Johnny insuffle une vision nouvelle et moderne du journalisme au sein de notre rédaction. Leader charismatique et figure incontournable de l\'information en France et à l\'étranger, il est diplômé de la Harvard Business School of Bullshit, promotion 1997.[/p]\r\n\r\n[p]Véritable puits de sagesse sans fond, Johnny est LA référence dans la rédaction. Présent dans les locaux du département info, il suit au plus près l\'actualité de la Licence, et signe la majorité des articles du journal, en plus d\'en tracer la ligne éditoriale.[/p]', 1, NULL),
('kdiot', '[p]Ancienne détective privé, Kelly a rejoint l\'équipe l\'été dernier. Mettant à profit ses acquis d\'expérience de sa vie professionnelle antérieure, elle est tout particulièrement attachée aux enquêtes spéciales.[/p]\r\n\r\n[p]Si ses articles sont rares, ce sont de petits bijoux d\'investigation qui sont régulièrement cités en exemple dans toutes les bonnes écoles de journalisme.[/p]\r\n\r\n[p]Son meilleur article à ce jour reste son enquête sur une filière clandestine d\'approvisionnements de sujets d\'examens, qui a permis de mettre au jour des pratiques plus que douteuses au sein du département informatique.[/p]\r\n\r\n[p]Véritable Elise Lucet de notre rédaction, elle n\'hésite pas à faire preuve d\'une ingéniosité sans égale pour pièger ses cibles et obtenir les confessions de leurs plus noirs secrets.[/p]', 2, 'Journaliste d\'investigation'),
('pheupakeur', '[p]Equipé de son reflex dernier cri, Pete est l\'oeil de la Gazette de L-INFO. Ses clichés originaux viennent parfaitement illustrer les articles magistrement écrits par nos collaborateurs.[/p]\r\n\r\n[p]Son meilleur cliché reste celui du Président Macron juste après avoir appris qu\'il validait sa Licence d\'Informatique.[/p]', 3, 'Photographe officiel'),
('yjourdelesse', '[p]Responsable de l\'édition numérique du journal, Yves donne vie à nos articles dans un style CSS inimitable. Ancien étudiant de Licence Informatique (comme le laisse deviner son style vestimentaire et capillaire négligé), Yves travaille d\'arrache-pied pour offrir au monde extérieur un contenu d\'un rendu impeccable.[/p]\r\n\r\n[p]Puni suite à un choix d\'illustration [#128286], Yves passe désormais la moitié de son temps de travail au pilori, devant l\'entrée ouest du bâtiment Propédeutique.[/p]', 3, 'Typographe et webmaster');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `utPseudo` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `utNom` char(50) COLLATE utf8_unicode_ci NOT NULL,
  `utPrenom` char(60) COLLATE utf8_unicode_ci NOT NULL,
  `utEmail` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `utPasse` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `utDateNaissance` int(8) UNSIGNED NOT NULL,
  `utStatut` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `utCivilite` enum('h','f') COLLATE utf8_unicode_ci NOT NULL,
  `utMailsPourris` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`utPseudo`, `utNom`, `utPrenom`, `utEmail`, `utPasse`, `utDateNaissance`, `utStatut`, `utCivilite`, `utMailsPourris`) VALUES
('akuz', 'Kuzbidon', 'Alex', 'alex.kuzbidon@gazette-linfo.fr', '$2y$10$hJm2k.p8vz65DKpcAeNUAe0wv7QHifUzeNmInBAdVGDhK6MdYXTB2', 19740627, 1, 'h', 0),
('emerlet', 'Merlet', 'Eric', 'eric.merlet@univ-fcomte.fr', '$2y$10$hJm2k.p8vz65DKpcAeNUAe0wv7QHifUzeNmInBAdVGDhK6MdYXTB2', 19700528, 0, 'h', 0),
('freddd', 'Dadeau', 'Fred', 'frederic.dadeau@univ-fcomte.fr', '$2y$10$hJm2k.p8vz65DKpcAeNUAe0wv7QHifUzeNmInBAdVGDhK6MdYXTB2', 19800630, 0, 'h', 0),
('hpotter', 'Harry', 'Potter', 'potter@poudlard.com', '$2y$10$QeJ4EGdp353mhYr5v1RkKeXWH2TB96scr9Ci3Tj/quXJZ2dU3gzmW', 19950718, 0, 'h', 0),
('jbernard', 'Bernard', 'Julien', 'julien.bernard@univ-fcomte.fr', '$2y$10$CaOMMrYi6hwgmCL5g8CxVexwyXwOLmweO3byxfAfcDnHW/GQHBswO', 19810126, 0, 'h', 1),
('jbigoude', 'Bigoude', 'Johnny', 'johnny.bigoude@gazette-linfo.fr', '$2y$10$hJm2k.p8vz65DKpcAeNUAe0wv7QHifUzeNmInBAdVGDhK6MdYXTB2', 19630217, 3, 'h', 0),
('kdiot', 'Diot', 'Kelly', 'kelly.diot@gazette-linfo.fr', '$2y$10$hJm2k.p8vz65DKpcAeNUAe0wv7QHifUzeNmInBAdVGDhK6MdYXTB2', 19870917, 1, 'f', 0),
('noussachons', 'Sachons', 'Nous', 'nous.sachons@complots-faciles.com', '$2y$10$hJm2k.p8vz65DKpcAeNUAe0wv7QHifUzeNmInBAdVGDhK6MdYXTB2', 19900317, 0, 'h', 0),
('pheupakeur', 'Heupakeur', 'Pete', 'pete.heupakeur@gazette-linfo.fr', '$2y$10$hJm2k.p8vz65DKpcAeNUAe0wv7QHifUzeNmInBAdVGDhK6MdYXTB2', 19890503, 1, 'h', 1),
('yjourdelesse', 'Jourdelesse', 'Yves', 'yves.jourdelesse@gazette-linfo.fr', '$2y$10$hJm2k.p8vz65DKpcAeNUAe0wv7QHifUzeNmInBAdVGDhK6MdYXTB2', 19911209, 2, 'h', 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`arID`),
  ADD KEY `arAuteur` (`arAuteur`);

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`catID`);

--
-- Index pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`coID`),
  ADD KEY `coAuteur` (`coAuteur`),
  ADD KEY `coArticle` (`coArticle`);

--
-- Index pour la table `redacteur`
--
ALTER TABLE `redacteur`
  ADD PRIMARY KEY (`rePseudo`),
  ADD KEY `reCategorie` (`reCategorie`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`utPseudo`),
  ADD UNIQUE KEY `utEmail` (`utEmail`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
  MODIFY `arID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `catID` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `coID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `article`
--
ALTER TABLE `article`
  ADD CONSTRAINT `article_ibfk_1` FOREIGN KEY (`arAuteur`) REFERENCES `utilisateur` (`utPseudo`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD CONSTRAINT `commentaire_ibfk_1` FOREIGN KEY (`coAuteur`) REFERENCES `utilisateur` (`utPseudo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `commentaire_ibfk_2` FOREIGN KEY (`coArticle`) REFERENCES `article` (`arID`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `redacteur`
--
ALTER TABLE `redacteur`
  ADD CONSTRAINT `redacteur_ibfk_1` FOREIGN KEY (`rePseudo`) REFERENCES `utilisateur` (`utPseudo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `redacteur_ibfk_2` FOREIGN KEY (`reCategorie`) REFERENCES `categorie` (`catID`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
