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
            } else {
                //Traitement pour consulter ses propres articles
                $username = getPropertyFromToken($bearer_token, 'username');
                $code = sesArticles($linkpdo, $username);
                testsErreurs($code, "Voici tous les articles de l'utilisateur : $username", $code, $codeHTTP=200);
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
            } else {
                //Traitement Erreur pour consulter ses propres articles
                deliver_response(401, "Permission non accordée, il faut être publisher pour consulter ses articles", "Rôle : ".$role);
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
            } else {
                //Traitement Erreur pour consulter ses propres articles
                deliver_response(401, "Permission non accordée. Veuillez vous connecter.", "Rôle : ".$role);
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
            if(!empty($_GET['id'])) {
                //Vérification de l'identifiant
                if(!isID($linkpdo, $_GET['id'])) {
                    deliver_response(404, "Veuillez renseigner un ID existant", "ID : ".$_GET['id']." introuvable");
                    break;
                }
                //Pour savoir s'il aime ou pas son propre poste.
                $usernameVotant = getPropertyFromToken($bearer_token, 'username');
                $code = getArticle($linkpdo, $_GET['id']);
                if(!testsErreursSansSucces($code)) {break;}
                if($usernameVotant == $code[0]['auteur']) {
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
                //S'il like
                if($data->aimer) {
                    $code = incrementerLikes($linkpdo, $_GET['id'], $usernameVotant);
                    testsErreurs($code, "Votre like est bien pris en compte.", $varARetourner=null, $codeHTTP=200);
                    break;
                }
                //S'il dislike
                $code = incrementerDisLikes($linkpdo, $_GET['id'], $usernameVotant);
                testsErreurs($code, "Votre dislike est bien pris en compte.", $varARetourner=null, $codeHTTP=200);
                break;
            }
            
            if(!empty($_GET['id']) && !empty($postedData)) {
                $code = getAuteurArticle($linkpdo, $_GET['id']);
                if(!testsErreursSansSucces($code)) { break; }
                
                if (!($code == getPropertyFromToken($bearer_token, 'username'))){
                    deliver_response(401, "Permission non accordée, un utilisateur ne peut modifier un article dont il n'est pas l'auteur", NULL);
                    break;
                }
                $data = (array)$postedData;
                $phrase = $data['contenu'];
                if (getArticle($linkpdo, $_GET['id'])){
                    $code = modifierArticle($linkpdo, $_GET['id'], $phrase);
                    testsErreurs($code, "L'article a bien été modifié, ID : ".$_GET['id'], $code);
                }
            }
        //Un modérateur ou un anonyme ne peut pas voter.
        } elseif($role == 'moderator' || $role == 'anonyme') {
            deliver_response(401, "Permission non accordée", NULL);
        }
    }
?>
