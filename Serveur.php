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

        $role = role_Token($bearer_token);

        //séparation des droits
        if($role == 'publisher') {

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


    }
?>