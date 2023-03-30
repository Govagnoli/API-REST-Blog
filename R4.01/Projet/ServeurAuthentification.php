<?php
    include 'fonctions_Serveur.php';
    include 'jwt_utils.php';
    // Paramétrage de l'entête HTTP (pour la réponse au Client)
    header("Content-Type:application/json");
    $http_method = $_SERVER['REQUEST_METHOD'];
    switch ($http_method){
    case "POST" :
        // Récupération des données envoyées par le Client
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData);
        if (!isValidUser($data->username, $data->password)) {
            deliver_response(401, "Mot de passe ou identifiant incorrect.", NULL);
            break;
        }
        $role = getRole($data->username, $data->password);
        $headers = array('alg'=>'HS256','typ'=>'JWT');
        $payload = array('username'=>$data->username, 'exp'=>(time() + 10800), 'role'=>$role);
        $jwt = generate_jwt($headers, $payload);
        deliver_response(200, "Vous êtes bien authentifié en tant que ".$role.". Voici votre token :", $jwt);
        break;
    default:
        deliver_response(501, "Erreur d'authentification. Pour s'authentifier veuillez faire une méthode post. Vous pouvez lire la documention pour plus d'informations", NULL);
        break;
    }
?>