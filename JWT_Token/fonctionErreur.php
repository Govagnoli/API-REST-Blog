<?php
	define("PARAMETRE_INVALIDE", -1);
	define("ERREUR_SERVEUR", -2);
	define("ERREUR_DATA", -3);
	define("ERREUR_DATA_FORBIDDEN", -4);

	function gestionErreur($codeErreur) {
		switch($codeErreur) {
			case PARAMETRE_INVALIDE:
				deliver_response(400, "Erreur côté client", NULL);
				break;
			case ERREUR_SERVEUR:
				deliver_response(500, "Erreur côté serveur", NULL);
				break;
			case ERREUR_DATA:
				deliver_response(404, "Veuillez renseigner un ID existant", "ID : ".$_GET['id']." introuvable");
				break;
			case ERREUR_DATA_FORBIDDEN:
				deliver_response(403, "Champs manquant", NULL);
				break;     
		}
	}
?>