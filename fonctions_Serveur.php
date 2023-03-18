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

    //à partir du token cette fonction renvoie l'un  de l'utilisateur authentifié.
    //$token est le JWT récupéré lors de l'envoie d'une requête au serveur.
    //$property est l'information voulue présent dans le champ payload du token (username, rôle, expiration)
    //Return le rôle/username/expiration de l'utilisateur
    function getPropertyFromToken($token, $property){
        $tokenParts = explode('.', $token);
        $payload = base64_decode($tokenParts[1]);
        $value = json_decode($payload)->{$property};
        return $value;
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

    //Permet d'ajouter un article dans la bd grâce au contenu, auteur, et la date de publication
    //$linkpdo --> connexion avec la BD
    //$contenu --> correspond au contenu de l'article présent dans le body de la requête
    //$auteur --> correspond à l'username de l'utilsateur qui publis l'article
    //le paramètre date_publication est définie par la fonction PHP NOW()
    function ajoutArticle($linkpdo, $contenu, $auteur){
        if (!is_string($contenu)){
            return ERREUR_PARAM;
        }

        if (strlen($contenu) > 140) {//Vérification de la contrainte de 140 caractère max dans la bd
            return ERREUR_PARAM;
        }

        $linkpdo->beginTransaction();

        try {
            $req = $linkpdo->prepare('INSERT INTO article (contenu, auteur, date_publication) VALUES (:contenu, :auteur, NOW())');
        } catch (Exception $e) {
            $linkpdo->rollback();
            return ERREUR_SQL;
        }

        $req->bindParam(':contenu', $contenu, PDO::PARAM_STR);
        $req->bindParam(':auteur', $auteur, PDO::PARAM_STR);
        try {
            $req->execute();
            $id = $linkpdo->lastInsertId();//permet de récupèrer l'id de la dernière données ajouter à la bd
        } catch (Exception $e) {
            $linkpdo->rollback();
            return ERREUR_SQL;
        }

        $linkpdo->commit();
        return $id;//retourne l'ID pour la personnalisation du message de retour
    }

    //Permet de supprimer un article grâce à son ID
    //$linkpdo --> connexion avec la BD
    //$id --> id de l'article qui doit être supprimée
    function deleteArticle($linkpdo, $id){
        if (!is_numeric($id)){
            return SYNTAXE;
        }
        if (!isID($linkpdo, $id)){//vérification de la présence de l'ID dans la BD
            return ID_INCONNU;//retourne une erreur ID si non présente dans la BD
        }
        $linkpdo->beginTransaction();
        try {
            $matchingData = $linkpdo->prepare("DELETE FROM article WHERE id = :id");
        } catch (Exception $e) { 
            $linkpdo->rollback();
            return ERREUR_SQL;
        }
        if (!$matchingData->bindParam(':id',$id, PDO::PARAM_INT)){
            $linkpdo->rollback();
            return ERREUR_SQL;
        }
        try {
            $matchingData->execute();
        } catch (Exception $e) {
            $linkpdo->rollback();
            return ERREUR_SQL;
        }
        return $id;//retourne l'ID pour la personnalisation du message de retour
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

    function verifRole($bearer_token) {
        if(!empty($bearer_token) && !is_jwt_valid($bearer_token)) {
            return null;
        } elseif(!empty($bearer_token)) {
            return getPropertyFromToken($bearer_token, 'role');
        }
        return 'anonyme';
    }

    // Fonction permettant de récuperer l'auteur de l'article dont l'identifiant est passé en paramétre  de l'url/requête
    function getAuteurArticle($linkpdo, $id){ 
        if(empty($id) || !is_numeric($id)) {
            return SYNTAXE;
        }
        $data  = getArticle($linkpdo, $_GET['id']);
        if (empty($data)){
            return SYNTAXE;
        }
        $data = $data[0];
        $auteur = $data['auteur'];
        return $auteur;
    }

    //Permet pour un publisher de récupérer tous ses articles
    //Retourne une liste d'article
    function sesArticles($linkpdo, $username) {
        if(is_null($username) || !is_string($username)) {
            return SYNTAXE;
        }        
        try{
	        $req = $linkpdo->prepare('
	            SELECT *
	            FROM article
	            where auteur = :auteur
	        ');
	    } catch(PDOException $e) {
	        return ERREUR_SQL;
	    }
        if(!$req->bindParam(':auteur', $username, PDO::PARAM_STR)) { return ERREUR_SQL; }
	    if($req->execute()) {
        	return $req->fetchAll(PDO::FETCH_ASSOC);
	    }
        return ERREUR_SQL;
    }
?>