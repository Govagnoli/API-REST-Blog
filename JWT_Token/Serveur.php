<?php
    include 'fonctions_Serveur.php';
    include 'jwt_utils.php';
    include('fonctionErreur.php');
    $linkedPDO = connectionBD();

    /// Paramétrage de l'entête HTTP (pour la réponse au Client)
    header("Content-Type:application/json");

    /// Identification du type de méthode HTTP envoyée par le client
    $http_method = $_SERVER['REQUEST_METHOD'];
    switch ($http_method){
    case "GET" :
        $bearer_token = '';
        $bearer_token = get_bearer_token(); 
        if(is_jwt_valid($bearer_token)) {
            $request_uri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
            $fonction = $request_uri[count($request_uri) - 2];
            $val = end($request_uri);
            $param = array(
                'n' => 'getNDernieresPhrases',
                'vote' => 'getNMeilleuresPhrases'
            );
            
            if (isset($param[$fonction])) {
                $functionName = $param[$fonction];
                if (isset($val)) {
                    $result = $functionName($linkedPDO, $val);
                    if ($result == ERREUR_SERVEUR || $result == ERREUR_DATA || $result == PARAMETRE_INVALIDE) {
                        gestionErreur($result);
                    } else {
                        $message = ($fonction == 'n') ? "!! Les ".$val." dernieres phrases !!" : "!! Les ".$val." meilleures phrases !!";
                        deliver_response(200, $message, $result);
                    }
                }
            } else if (is_numeric($val)) {
                $result = getDataById($linkedPDO, $val);
                if ($result == ERREUR_SERVEUR || $result == ERREUR_DATA || $result == PARAMETRE_INVALIDE) {
                    gestionErreur($result);
                } else {
                    deliver_response(200, "!! Phrase trouvee !!", $result);
                }
            }
        } else {
            deliver_response(500, "Erreur de Token", NULL);
        }
        break;
    case "DELETE":
        $bearer_token = '';
        $bearer_token = get_bearer_token(); 
        if(is_jwt_valid($bearer_token)) {
            $role = role_Token($bearer_token);
            if ($role == 'modo'){
                if (!empty($_GET['id'])) {
                    $result = deleteById($linkedPDO, $_GET['id']);
                    if ($result == -1 || $result == -2 || $result == -3) {
                        gestionErreur($result);
                    } else {
                        deliver_response(200, "Suppression validee", "ID : ".$result." Supprime");
                    }
                } else {
                    gestionErreur(ERREUR_SERVEUR);
                }
            } else {
                deliver_response(400, "Ce role ne permet pas la suppresion", NULL);
            }
        }
        break;
    }
?>