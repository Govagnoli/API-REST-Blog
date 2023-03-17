<?php
    include 'fonctions_Serveur.php';
    include 'jwt_utils.php';
    include 'Connexion.php';
    include 'Code_Erreurs.php';
    include 'Vote.php';
    $linkpdo = Connexion::getConnexion();

    /// Paramétrage de l'entête HTTP (pour la réponse au Client)
    header("Content-Type:application/json");

    /// Identification du type de méthode HTTP envoyée par le client
    $http_method = $_SERVER['REQUEST_METHOD'];
    switch ($http_method){
    case "GET" :
        $bearer_token = '';
        $bearer_token = get_bearer_token();

        //définie le rôle de l'utilisateur
        $role = verifRole($bearer_token);
        if(is_null($role)) { deliver_response(500, "Erreur de Token.", NULL); break; }

        //séparation des droits
        if($role == 'publisher') {
            //Traitement pour récupérer un Article
            if(!empty($_GET['id'])) {
                if(!isID($linkpdo, $_GET['id'])) {
                    deliver_response(400, "L'identifiant renseigné n'existe pas.", NULL);
                    break;
                }
                $articles = getArticle($linkpdo, $_GET['id']); 
                if(!testsErreursSansSucces($articles)) {break;}
                $nbrLikes = NbrLikes($linkpdo, $_GET['id'], true);
                if(!testsErreursSansSucces($nbrLikes)) {break;}
                $nbrDisLikes = NbrLikes($linkpdo, $_GET['id'], false);
                if(!testsErreursSansSucces($nbrDisLikes)) {break;}
                $valeursRetour = array(
                    'auteur' => $articles[0]['auteur'],
                    'datePublication' => $articles[0]['date_publication'],
                    'contenu' => $articles[0]['contenu'],
                    'nbrLikes' => $nbrLikes,
                    'nbrDisLikes' => $nbrDisLikes
                );
                deliver_response(200, "Résultat de la recherche de l'identifiant ".$_GET['id'].":", $valeursRetour);
            }
        } elseif($role == 'moderator') {
            //Traitement pour récupérer un Article
            if(!empty($_GET['id'])) {
                if(!isID($linkpdo, $_GET['id'])) {
                    deliver_response(400, "L'identifiant renseigné n'existe pas.", NULL);
                    break;
                }
                $articles = getArticle($linkpdo, $_GET['id']); 
                if(!testsErreursSansSucces($articles)) {break;}
                $listeLikes = allVotes($linkpdo, $_GET['id'], true);
                if(!testsErreursSansSucces($listeLikes)) {break;}
                $nbrLikes = NbrLikes($linkpdo, $_GET['id'], true);
                if(!testsErreursSansSucces($nbrLikes)) {break;}
                $listeDislikes = allVotes($linkpdo, $_GET['id'], false);
                if(!testsErreursSansSucces($listeDislikes)) {break;}
                $nbrDisLikes = NbrLikes($linkpdo, $_GET['id'], false);
                if(!testsErreursSansSucces($nbrDisLikes)) {break;}
                $valeursRetour = array(
                    'auteur' => $articles[0]['auteur'],
                    'datePublication' => $articles[0]['date_publication'],
                    'contenu' => $articles[0]['contenu'],
                    'listeLikes' => $listeLikes,
                    'nbrLikes' => $nbrLikes,
                    'listeDislikes' => $listeDislikes,
                    'nbrDisLikes' => $nbrDisLikes
                );
                deliver_response(200, "Résultat de la recherche de l'identifiant ".$_GET['id'].":", $valeursRetour);
            }
        } else {
            //Cas si utilisateur non authentifié. (Anonymous)
            //Traitement pour récupérer un Article
            if(!empty($_GET['id'])) {
                if(!isID($linkpdo, $_GET['id'])) {
                    deliver_response(400, "L'identifiant renseigné n'existe pas.", NULL);
                    break;
                }
                $articles = getArticle($linkpdo, $_GET['id']);
                testsErreurs($articles, "Résultat de la recherche de l'identifiant ".$_GET['id'].":", $varARetourner=$articles, $codeHTTP=200);
            }
        }
        break;
    case "POST":
        $postedData = file_get_contents('php://input');
        $postData = json_decode($postedData, true);

        $bearer_token = '';
        $bearer_token = get_bearer_token();

        //définie le rôle de l'utilisateur
        $role = verifRole($bearer_token);
        if(is_null($role)) { deliver_response(500, "Erreur de Token.", NULL); break; }

        $auteur = getPropertyFromToken($bearer_token, 'username');//récupère l'username présent dans le payload du token
        $role = getPropertyFromToken($bearer_token, 'role');//récupère le rôle présent dans le payload du token
        if ($role == 'moderator' || $role == 'anonyme'){
            deliver_response(401, "Permission non accordée", "Rôle : ".$role);
            break;
        }
        if ($role == 'publisher'){
            if (!empty($postData['contenu'])) {
                $code = ajoutArticle($linkpdo, $postData['contenu'], $auteur);
                if (!is_numeric($code)) {     
                    testsErreurs($code, "<<<< Erreur >>>>", NULL);
                    break;
                }
                testsErreurs($code, "Article crée avec succès", "ID de l'article : ".$code);
            }
        }
        break;

    case "DELETE":
        $bearer_token = '';
        $bearer_token = get_bearer_token();

        //définie le rôle de l'utilisateur
        $role = verifRole($bearer_token);
        if(is_null($role)) { deliver_response(500, "Erreur de Token.", NULL); break; }

        if ($role == 'anonyme'){ //Empêche la suppression pour les utilisateurs "anonyme"
            deliver_response(401, "Permission non accordée", NULL);
            break;
        }

        if($role == 'publisher') { // Limite la suppression d'articles, le publisher peut supprimer que ses articles
            $auteur = getAuteurArticle($linkpdo, $_GET['id']);
            if(is_numeric($auteur)) {
                deliver_response(400, "La syntaxe de la requête est erronée", null);
                break;
            }
            if (!($auteur == getPropertyFromToken($bearer_token, 'username'))){
                deliver_response(401, "Permission non accordée", NULL);
                break;
            }
            $code = deleteArticle($linkpdo, $_GET['id']);
            testsErreurs($code, "Suppression validee", "ID : ".$code);     
        } elseif($role == 'moderator') {
            //Traitement pour la suppression d'un Article
            if(empty($_GET['id'])) {
                deliver_response(400, "La syntaxe de la requête est erronée", null);
                break;
            }
            $code = deleteArticle($linkpdo, $_GET['id']);
            testsErreurs($code, "Suppression validee", "ID : ".$code); 
        }
        break;
    case "PATCH":
        $bearer_token = '';
        $bearer_token = get_bearer_token();

        //définie le rôle de l'utilisateur
        $role = verifRole($bearer_token);
        if(is_null($role)) { deliver_response(500, "Erreur de Token.", NULL); break; }
        if($role == 'publisher') {  
            //traitement du like ou dislike
            if(!empty($_GET['id']) && !empty($_GET['username'])) {
                if(!isID($linkpdo, $_GET['id'])) {
                    deliver_response(404, "Veuillez renseigner un ID existant", "ID : ".$_GET['id']." introuvable");
                    break;
                }
                $usernameVotant = getPropertyFromToken($bearer_token, 'username');
                if($usernameVotant == $_GET['username']) {
                    deliver_response(401, "Permission non accordée, un utilisateur ne peut pas liker ou disliker ses posts.", NULL);
                    break;
                }   
                //récupération du body
                $postedData = file_get_contents('php://input');
                $data = json_decode($postedData);
                //vérif si le body contient le bool aimer
                if(is_null($data->aimer)) {
                    deliver_response(400, "Il manque des données dans le body. Veuillez préciser si vous avez liké ou disliké un article.", null);
                    break;
                }
                //isUsername
                if($data->aimer) {
                    $code = incrementerLikes($linkpdo, $_GET['id'], $usernameVotant);
                    testsErreurs($code, "Votre like est bien pris en compte.", $varARetourner=null, $codeHTTP=200);
                    break;
                }
                $code = incrementerDisLikes($linkpdo, $_GET['id'], $usernameVotant);
                testsErreurs($code, "Votre dislike est bien pris en compte.", $varARetourner=null, $codeHTTP=200);
                break;
            }
        } elseif($role == 'moderator' || $role == 'anonyme') {
            deliver_response(401, "Permission non accordée", NULL);
        }
    }
?>
