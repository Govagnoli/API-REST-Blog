<?php
    $users = array( 
        array(
            "username" => "Eliott",
            "password" => "1234",
            "rôle" => "moderator"
        ),
        array(
            "username" => "Anass",
            "password" => "azerty",
            "rôle" => "publisher"
        )
    );


    /// Envoi de la réponse au Client
    function deliver_response($status, $status_message, $data){
        /// Paramétrage de l'entête HTTP, suite
        header("HTTP/1.1 $status $status_message");

        /// Paramétrage de la réponse retournée
        $response['status'] = $status;
        $response['status_message'] = $status_message;
        $response['data'] = $data;
        
        /// Mapping de la réponse au format JSON
        $json_response = json_encode($response);
        echo $json_response;
    }

    //Return true si l'utilisateur est valide sinon false
    //Permet de savoir si un utilisateur est valide. C'est à dire, lors de sa connexion son nom et son mot de passe correspond à un compte existant.
    function isValidUser($username, $password) {
        global $users;
        foreach ($users as $user) {
            if($user['username'] == $username) {
                if($user['password'] == $password) {
                    return true;
                }
                return false;
            }
        }
        return false;
    }

    //à partir du token il renvoie le rôle de l'utilisateur authentifié.
    //$token est le JWT récupéré lors de l'envoie d'une requête au serveur.
    //Return le rôle de l'utilisateur
    function role_Token($token){
        $tokenParts = explode('.', $token);
        $payload = base64_decode($tokenParts[1]);
        $role = json_decode($payload)->role;
        return $role;
    }

    //Permet de récupérer le rôle d'un utilisateur
    //Lève une exception si le rôle n'est pas valide
    //retourne le rôle un utilisateur
    function getRole($username, $password) {
        if(!isValidUser($username, $password)) {
            return null;
        }
        global $users;
        foreach ($users as $user) {
            if($user['username'] == $username) {
                if($user['password'] == $password) {
                    return $user['rôle'];
                }
            }
        }
        return null;
    }

    //Permet de récupérer l'auteur la date de publication et le contenu d'un article définit par son identifiant
    //$linkpdo --> connexion avec la BD
    //$id --> identifiant de l'article
    function getArticle($linkpdo, $id) {
        if(is_null($id) || !is_numeric($id) ) {
            return SYNTAXE;
        }
        try{
            $req = $linkpdo->prepare('
                SELECT *
                FROM article
                where id = :id
            ');
        } catch(PDOException $e) {
            return ERREUR_SQL;
        }

        if($req->bindParam(':id', $id, PDO::PARAM_INT)) {
            if($req->execute()) {
                return $req->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        return ERREUR_SQL;
    }

    function isID($linkpdo, $id) {
        if(is_null($id) || !is_numeric($id)) {
            return false;
        }
        $data = getArticle($linkpdo, $id);
        if(empty($data)) {
            return false;
        }
        return true;
    }
?>