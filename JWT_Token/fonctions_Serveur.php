<?php
    $users = array( 
        array(
            "username" => "Eliott",
            "password" => "1234",
            "rôle" => "modo"
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

    function isValidUser($name, $pwd) {
        global $users;
        foreach ($users as $user) {
            if($user['username'] == $name) {
                if($user['password'] == $pwd) {
                    return $user['rôle'];
                }
                return false;
            }
        }
        return false;
    }

    function role_Token($token){
        $tokenParts = explode('.', $token);
        $payload = base64_decode($tokenParts[1]);
        $role = json_decode($payload)->role;
        return $role;
    }

    function getDataById($linkedPDO, $id){

        if (!is_numeric($id)){
            return PARAMETRE_INVALIDE;
        }
        try {
            $matchingData = $linkedPDO->prepare("SELECT * FROM Chuckn_facts WHERE id = :id");
        } catch (Exception $e) { 
            return ERREUR_SERVEUR;
        }
        if (!$matchingData->bindParam(':id',$id, PDO::PARAM_INT)){
            return PARAMETRE_INVALIDE;
        }
        $matchingData->execute();
        $data = $matchingData->fetchAll(PDO::FETCH_ASSOC);
        if (empty($data)) {
            return ERREUR_DATA;
        }
        return $data;
    }

    function deleteById($linkedPDO, $id) {
        if (!is_numeric($id)){
            return PARAMETRE_INVALIDE;
        }
        $data = getDataById($linkedPDO, $id);
        if ($data == ERREUR_DATA) {
            return $data;
        }
        $linkedPDO->beginTransaction();
        $linkedPDO->exec('SAVEPOINT savepoint');
        try {
            $matchingData = $linkedPDO->prepare("DELETE FROM Chuckn_facts WHERE id = :id");
        } catch (Exception $e) { 
            $linkedPDO->rollback();
            return ERREUR_SERVEUR;
        }
        if (!$matchingData->bindParam(':id',$id, PDO::PARAM_INT)){
            $linkedPDO->rollback();
            return PARAMETRE_INVALIDE;
        }
        try {
            $matchingData->execute();
        } catch (Exception $e) {
            $linkedPDO->rollback('savepoint');
            return ERREUR_SERVEUR;
        }
        return $id;
    }

    function fonctionPOST($linkedPDO, $phrase) {
        if (!is_string($phrase)){
            return PARAMETRE_INVALIDE;
        }
        $linkedPDO->beginTransaction();
        try {
            $matchingData = $linkedPDO->prepare("INSERT INTO Chuckn_facts (phrase, date_ajout) VALUES (:phrase, NOW())");
        } catch (Exception $e) {
            $linkedPDO->rollback();
            return ERREUR_SERVEUR;
        }
        if (!$matchingData->bindParam(':phrase', $phrase, PDO::PARAM_STR)){
            return PARAMETRE_INVALIDE;
        }
    
        try {
            $matchingData->execute();
            $id = $linkedPDO->lastInsertId();
        } catch (Exception $e) {
            $linkedPDO->rollback();
            return ERREUR_SERVEUR;
        }
        $linkedPDO->commit();
        return $id;        
    }

    function fonctionPUT($linkedPDO, $id, $phrase, $vote, $faute, $signalement) {
        if (!isset($phrase) || !isset($vote) || !isset($faute) || !isset($signalement)){
            return ERREUR_DATA_FORBIDDEN;
        }
        if (!is_numeric($id)){
            return PARAMETRE_INVALIDE;
        }
        $data = getDataById($linkedPDO, $id);
        if ($data == ERREUR_DATA) {
            return $data;
        }
        try {
            $matchingData = $linkedPDO->prepare("UPDATE Chuckn_facts SET phrase = :phrase, vote = :vote, date_modif = NOW(), faute = :faute, signalement = :signalement WHERE id = :id");
        } catch (Exception $e) {
            return ERREUR_SERVEUR;
        }

        $matchingData->bindParam(':id',$id, PDO::PARAM_INT);
        $matchingData->bindParam(':phrase',$phrase, PDO::PARAM_STR);
        $matchingData->bindParam(':vote',$vote, PDO::PARAM_INT);
        $matchingData->bindParam(':faute',$faute, PDO::PARAM_BOOL);
        $matchingData->bindParam(':signalement',$signalement, PDO::PARAM_INT);
        try {
            $matchingData->execute();
        } catch (Exception $e) {
            return ERREUR_SERVEUR;
        }
        return $id;
    }

    function getNDernieresPhrases($linkedPDO, $N) {
        if (!is_numeric($N)){
            return PARAMETRE_INVALIDE;
        }
        try {
            $matchingData = $linkedPDO->prepare("SELECT * FROM Chuckn_facts ORDER BY date_ajout DESC LIMIT :n");
        } catch (Exception $e) { 
            return ERREUR_SERVEUR;
        }
        if (!$matchingData->bindParam(':n', $N, PDO::PARAM_INT)){
            return PARAMETRE_INVALIDE;
        }
        try {
            $matchingData->execute();
        } catch (Exception $e) {
            return ERREUR_SERVEUR;
        }
        $data = $matchingData->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }

    function getNMeilleuresPhrases($linkedPDO, $N) {
        if (!is_numeric($N)) {
            return PARAMETRE_INVALIDE;
        }
    
        try {
            $matchingData = $linkedPDO->prepare("SELECT * FROM Chuckn_facts ORDER BY vote DESC LIMIT :N");
        } catch (Exception $e) { 
            return ERREUR_SERVEUR;
        }
        if (!$matchingData->bindParam(':N', $N, PDO::PARAM_INT)){
            return PARAMETRE_INVALIDE;
        }
        try {
            $matchingData->execute();
        } catch (Exception $e) {
            return ERREUR_SERVEUR;
        }
    
        $data = $matchingData->fetchAll(PDO::FETCH_ASSOC);
        return $data;
    }
    
    
    function connectionBD(){
        $server = "localhost";
        $login = "root";
        $mdp = "";
        $db = "bd_rest";

        try {
            $linkedPDO = new PDO("mysql:host=$server;dbname=$db;charset=UTF8",$login,$mdp);
            $linkedPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $linkedPDO;
        } catch (PDOException $e) {
            echo 'Erreur de connexion : ' . $e->getMessage();
            return null;
        }
    }

?>