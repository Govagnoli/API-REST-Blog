<?php
	//$username --> utilisateur ayant liké l'article
	//Permet d'ajouter un like à un article.
	//On associera au like l'utilisateur ayant liké
	function incrementerLikes($linkpdo, $id, $nom) {
		$nbrLikes = NbrLikes($linkpdo, $id, true);
		$nbrDislikes = NbrLikes($linkpdo, $id, false);
		//Si l'utilisateur n'a pas voté l'article alors on ajoute un like
		if($nbrLikes == 0 && $nbrDislikes == 0) {
			$code = ajouterVote($linkpdo, $id, $nom, true);
			return $code;
		} 
		//Si l'utilisateur avait déjà liké, alors on retire son like.
		else if(aLike($linkpdo, $id, $nom)) {
			$code = suprimerVote($linkpdo, $id, $nom);
			return $code;
		}
		//si l'utilisateur avait disliké, on supprime son dislike et on like l'article
		else if(!aLike($linkpdo, $id, $nom)) {
			$code = suprimerVote($linkpdo, $id, $nom);
			if($code == ERREUR_SQL) { return $code; }
			$code = ajouterVote($linkpdo, $id, $nom, true);
			return $code;
		} else {
			$code = ajouterVote($linkpdo, $id, $nom, true);
			return $code;
		}
	}

	//Permet de liker ou disliker un article en fonction d'un utilisateur
	//Renvoie ERREUR_SQL s'il y a une erreur lors de l'exécution du code SQL
	//Renvoie CORRECT si l'ajout c'est passé correctement
	//$id --> int: identifiant de l'aticle
	//$nom --> string: nom de la personne ayant voté
	//$aimer --> boolean: true s'il à liké sinon false
	function ajouterVote($linkpdo, $id, $nom, $aimer) {
		try{
	        $req = $linkpdo->prepare('
	            INSERT INTO vote(id, nom, aimer) VALUES (:id, :nom, :aimer)
	        ');
	    } catch(PDOException $e) {
	        return ERREUR_SQL;
	    }

	    if(!$req->bindParam(':id', $id, PDO::PARAM_INT)) { return ERREUR_SQL; }
	    if(!$req->bindParam(':nom', $nom, PDO::PARAM_STR)) { return ERREUR_SQL; }
	    if(!$req->bindParam(':aimer', $aimer, PDO::PARAM_BOOL)) { return ERREUR_SQL; }
	    if(!$req->execute()) {
	        return ERREUR_SQL;
	    }
    	return CORRECT;
	}


	//Permet supprimer un vote.
	//Renvoie ERREUR_SQL s'il y a une erreur lors de l'exécution du code SQL
	//Renvoie CORRECT si l'ajout c'est passé correctement
	//$id --> int: identifiant de l'aticle
	//$nom --> string: nom de la personne ayant voté
	function suprimerVote($linkpdo, $id, $nom) {
		try{
	        $req = $linkpdo->prepare('
	            DELETE FROM vote WHERE id = :id and nom = :nom
	        ');
	    } catch(PDOException $e) {
	        return ERREUR_SQL;
	    }

	    if(!$req->bindParam(':id', $id, PDO::PARAM_INT)) { return ERREUR_SQL; }
	    if(!$req->bindParam(':nom', $nom, PDO::PARAM_STR)) { return ERREUR_SQL; }
	    if(!$req->execute()) {
	        return ERREUR_SQL;
	    }
    	return CORRECT;
	}

	//$username --> utilisateur ayant disliké l'article
	//Permet d'ajouter un dislike à un article.
	//On associera au dislike l'utilisateur ayant disliké
	function incrementerDisLikes($linkpdo, $id, $nom) {
		$nbrLikes = NbrLikes($id, true);
		$nbrDislikes = NbrLikes($id, false);
		//Si l'utilisateur n'a pas voté l'article, alors on ajoute un dislike
		if($nbrLikes == 0 && $nbrDislikes == 0) {
			$code = ajouterVote($id, $nom, false);
			return $code;
		} 
		//Si l'utilisateur avait déjà disliké, alors on retire son dislike.
		else if(!aLike($id, $nom)) {
			$code = suprimerVote($id, $nom);
			return $code;
		}
		//si l'utilisateur avait liké, on supprime son like et on dislike l'article
		else if(aLike($id, $nom)) {
			$code = suprimerVote($id, $nom);
			if($code == ERREUR_SQL) { return $code; }
			$code = ajouterVote($id, $nom, false);
			return $code;
		} else {
			$code = ajouterVote($id, $nom, false);
			return $code;
		}
	}

	//return un integer.
	//Permet de connaitre le nombre de like d'un article
	//$id --> identifiant de l'article
	//$aimer --> boolean true = à liké; false = à disliké
	function NbrLikes($linkpdo, $id, $aimer) {
		if(!isVote($linkpdo, $id) || is_null($aimer)) {
			return SYNTAXE;
		}
	    try{
	        $req = $linkpdo->prepare('
	            SELECT count(*)
	            FROM vote
	            where id = :id
	            and aimer = :aimer 
	        ');
	    } catch(PDOException $e) {
	        return ERREUR_SQL;
	    }

	    if(!$req->bindParam(':id', $id, PDO::PARAM_INT)) { return ERREUR_SQL; }
	    if(!$req->bindParam(':aimer', $aimer, PDO::PARAM_BOOL)) { return ERREUR_SQL; }
	    if($req->execute()) {
	        return $req->fetchColumn();;
	    }
	}

	//Permet de savoir si un utilisateur à liké ou non un article
	//Renvoie true s'il à liké renvoie false sinon
	function aLike($linkpdo, $id, $user) {
		if(!isVoteUser($id, $user)) {
			return false;
		}
		try{
	        $req = $linkpdo->prepare('
	            SELECT aimer
	            FROM vote
	            where id = :id
	            and nom = :user 
	        ');
	    } catch(PDOException $e) {
	        return ERREUR_SQL;
	    }
	    if(!$req->bindParam(':id', $id, PDO::PARAM_INT)) { return ERREUR_SQL; }
	    if(!$req->bindParam(':user', $user, PDO::PARAM_STR)) { return ERREUR_SQL; }
	    if($req->execute()) {
	        if(!empty($req->fetchColumn())) {
	        	return true;
	        }
	        return false;
		}
	}

	//Permet de savoir si un Article à des votes associés
	function isVote($linkpdo, $id) {
		try{
        	$req = $linkpdo->prepare('
	            SELECT *
	            FROM vote
	            where id = :id 
	        ');
	    } catch(PDOException $e) {
	        return ERREUR_SQL;
	    }
	    if(!$req->bindParam(':id', $id, PDO::PARAM_INT)) { return ERREUR_SQL; }
	    if($req->execute()) {
        	$resultat = $req->fetchAll(PDO::FETCH_ASSOC);
    	}
	    if(!empty($resultat)) {
	    	return true;
	    }
	    return false;
	}

	//Permet de savoir si un utilisateur à voté sur un Article.
	function isVoteUser($linkpdo, $id, $user) {
		try{
	        $req = $linkpdo->prepare('
	            SELECT *
	            FROM vote
	            where id = :id
	            and nom = :user 
	        ');
	    } catch(PDOException $e) {
	        return ERREUR_SQL;
	    }
	    if(!$req->bindParam(':id', $id, PDO::PARAM_INT)) { return ERREUR_SQL; }
	    if(!$req->bindParam(':user', $user, PDO::PARAM_STR)) { return ERREUR_SQL; }
	    if($req->execute()) {
        	$resultat = $req->fetchAll(PDO::FETCH_ASSOC);
	    }
	    if(!empty($resultat)) {
	    	return true;
	    }
	    return false;
	}


	//Permet de renvoyer tous les votes (likes et dislike) (id, nom et vote),
	//$aimé : bool: permet de spécifier si on veut les likes ou les dislike.
	//Renvoie un tableau des votes avec un identifiant, un like et un dislike
	function allVotes($linkpdo, $id, $aimer=null) {
		if(is_null($id) || !is_numeric($id)) {
            return SYNTAXE;
        }
        if(is_null($aimer)) {
        	try{
		        $req = $linkpdo->prepare('
		            SELECT *
		            FROM vote
		            where id = :id
		        ');
		    } catch(PDOException $e) {
		        return ERREUR_SQL;
		    }
		    if(!$req->bindParam(':id', $id, PDO::PARAM_INT)) { return ERREUR_SQL; }
		    if($req->execute()) {
	        	return $req->fetchAll(PDO::FETCH_ASSOC);
		    }
		    return ERREUR_SQL;
        }
        //le cas ou $aimer n'est pas null
        try{
	        $req = $linkpdo->prepare('
	            SELECT *
	            FROM vote
	            where id = :id
	            and aimer = :aimer
	        ');
	    } catch(PDOException $e) {
	        return ERREUR_SQL;
	    }
	    if(!$req->bindParam(':id', $id, PDO::PARAM_INT)) { return ERREUR_SQL; }
	    if(!$req->bindParam(':aimer', $aimer, PDO::PARAM_BOOL)) { return ERREUR_SQL; }
	    if($req->execute()) {
	    	return $req->fetchAll(PDO::FETCH_ASSOC);
	    }
	    return ERREUR_SQL;
	}
?>

