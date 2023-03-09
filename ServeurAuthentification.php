<?php
    include 'fonctions_Serveur.php';
    include 'jwt_utils.php';
    

    /// Paramétrage de l'entête HTTP (pour la réponse au Client)
    header("Content-Type:application/json");

    $http_method = $_SERVER['REQUEST_METHOD'];
    switch ($http_method){
    case "POST" :
        // Récupération des données envoyées par le Client
        $postedData = file_get_contents('php://input');
        $data = json_decode($postedData);
        
        if (!isValidUser($data->username, $data->password)) {
            echo "Erreur d'authentification"; //Erreur 401
        }

        $role = getRole($data->username, $data->password);
        $headers = array('alg'=>'HS256','typ'=>'JWT');
        $payload = array('username'=>$data->username, 'exp'=>(time() + 300), 'role'=>$role);
        $jwt = generate_jwt($headers, $payload);
        echo $jwt;
        break;
    default:
        echo "Erreur 501"; //Erreur 501
        break;
    }
?>