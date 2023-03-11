<?php 

    //cf https://fr.wikipedia.org/wiki/Liste_des_codes_HTTP pour connaitre les bon code d'erreurs.

    //debugBugParam


    #Code Erreur
    define("SYNTAXE", -1); //La syntaxe de la requête est erronée : Erreur 400
    define("ERREUR_SQL", -2); //Peut-être vue comme une erreur système : Erreur 500
    

    
    define("BODY_INCOMPLET", -4);
    define("ID_INCONNU", -5); //Si l'id est correct, mais pas présent dans la BD
    define("ERREUR_PARAM", -6);

    //Prend en paramètre un code, une phrase à écrire en cas de 'Réussite' du passage des erreurs et une variable à retourner au client en cas de 'Réussite' du passage des erreurs.
    //Si le code correspond à un message d'erreur connu, alors un message d'erreur sera retourné
    function testsErreurs($code, $phraseReussite, $varARetourner=null, $codeHTTP=200) {
        if($code == BODY_INCOMPLET) {
            deliver_response(400, "Il manque des données dans le body. Veuillez préciser.", null);
        } elseif($code == SYNTAXE) {
            deliver_response(400, "La syntaxe de la requête est erronée", null);
        } elseif($code == ERREUR_SQL) {
            deliver_response(500, "Une erreur est survenue pendant l'execution de la requête.", null);
        } elseif(empty($code)) {
            deliver_response(204, "Requête traitée avec succès mais pas d’information à renvoyer.", null);
        } elseif($code == ERREUR_PARAM){
            deliver_response(400, "Contenu de l'article ne respectant pas les règles (plus de 140 caractères ou syntaxe invalide).", null);
        } elseif($code == ID_INCONNU) {
            deliver_response(404, "Veuillez renseigner un ID existant", "ID : ".$_GET['id']." introuvable");
        } else {
            deliver_response($codeHTTP, $phraseReussite, $varARetourner);
        }
    }
?>