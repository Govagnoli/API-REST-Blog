<?php
    include 'fonctions_Serveur.php';
    include 'jwt_utils.php';
    include 'Connexion.php';
    include 'Code_Erreurs.php';
    $linkpdo = Connexion::getConnexion();

    /// Paramétrage de l'entête HTTP (pour la réponse au Client)
    header("Content-Type:application/json");

    /// Identification du type de méthode HTTP envoyée par le client
    $http_method = $_SERVER['REQUEST_METHOD'];
    switch ($http_method){
    case "GET" :
        $bearer_token = '';
        $bearer_token = get_bearer_token();

        //Vérifie si le jwt est valides
        if(!is_jwt_valid($bearer_token)) {
            deliver_response(500, "Erreur de Token", NULL);
            break;   
        }

        $role = getPropertyFromToken($bearer_token, 'role');

        //séparation des droits
        if($role == 'publisher') {
            echo 'mahh';
        } elseif($role == 'moderator') {
            //Traitement pour récupérer un Article
            if(!empty($_GET['id'])) {
                if(!isID($linkpdo, $_GET['id'])) {
                    deliver_response(400, "L'identifiant renseigné n'existe pas.", NULL);
                    break;
                }
                $code = getArticle($linkpdo, $_GET['id']);
                testsErreurs($code, "Résultat de la recherche de l'identifiant : ".$_GET['id'], $code);
            }
        }

        break;
    case "POST":
        $postedData = file_get_contents('php://input');
        $postData = json_decode($postedData, true);

        $bearer_token = '';
        $bearer_token = get_bearer_token();

        //Vérifie si le jwt est valides
        if(!is_jwt_valid($bearer_token)) {
            deliver_response(500, "Erreur de Token", NULL);
            break;   
        }
        $auteur = getPropertyFromToken($bearer_token, 'username');//récupère l'username présent dans le payload du token
        $role = getPropertyFromToken($bearer_token, 'role');////récupère le rôle présent dans le payload du token
        echo $role; 
        if ($role = 'moderator' || $role == 'anonyme'){
            deliver_response(401, "Permission non accordée", NULL);
            break;
        }
        if ($role = 'publisher'){
            if (!empty($postData['contenu'])) {
                $code = ajoutArticle($linkpdo, $postData['contenu'], $auteur);
                if (!is_int($code)) {     
                    testsErreurs($code, "Article crée avec succès", "ID de l'article : ".$code);
                } else {
                    testsErreurs($code, "<<<< Erreur >>>>", NULL);
                }
            } else {
                deliver_response(400, "<<<< Données non valides >>>>", NULL);
            }
        }
        break;

    case "DELETE":
        $bearer_token = '';
        $bearer_token = get_bearer_token();

        //Vérifie si le jwt est valides
        if(!is_jwt_valid($bearer_token)) {
            deliver_response(500, "Erreur de Token", NULL);
            break;   
        }

        $role = getPropertyFromToken($bearer_token, 'role');
        if ($role = 'anonyme'){ //Empêche la suppression pour les utilisateurs "anonyme"
            deliver_response(401, "Permission non accordée", NULL);
            break;
        }

        if($role == 'publisher') {// Limite la suppression d'articles ?
            echo 'mahh';
        } elseif($role == 'moderator') {
            //Traitement pour la suppression d'un Article
            if(!empty($_GET['id'])) {
                $code = deleteArticle($linkpdo, $_GET['id']);
                testsErreurs($code, "Suppression validee", "ID : ".$code);
            } 
        }
        break;
    }

    
?>