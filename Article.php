<?php
	class Article {
		private $id;
		private $likes;
		private $dislike;
		private $nbrLikes;
		private $nbrDislikes;

		public function __construct($id) {
      		$this->id = $id;
      		$this->likes = array(array());
      		$this->nbrLikes = 0;
      		$this->dislike = array(array());
      		$this->nbrDislikes = 0;
   		}

   		//$username --> utilisateur ayant liké l'article
   		//Permet d'ajouter un like à un article.
   		//On associera au like l'utilisateur ayant liké
   		public function incrementerLikes($username) {
   			if($this->likes == 0) { 
   				$this->likes = array(array("username" => $username));
   			}

   			//Si l'utilisateur à liké, supprime son like
   			if($this->aLike($username)) {
   				unset($this->likes[$username]);
   				$this->nbrLikes -= 1;
   			} 
   			//Si l'utilisateur à disliké, supprime son dislike et rajoute un like
   			else if($this->aDislike($username)) {
   				unset($this->dislike[$username]);
   				$this->nbrDislikes -= 1;
   				array_push($this->likes, array("username" => $username));
   				$this->nbrLikes += 1;
   			} 
   			//Ajoute un like
   			else {
   				array_push($this->likes, array("username" => $username));
   				$this->nbrLikes += 1;
   			}
   		}

   		//$username --> utilisateur ayant disliké l'article
   		//Permet d'ajouter un dislike à un article.
   		//On associera au dislike l'utilisateur ayant disliké
   		public function incrementerDisLikes($username) {
   			if($this->nbrDislikes == 0) { 
   				$this->dislike = array(array("username" => $username));
   			}
   			//Si l'utilisateur à disliké, supprime son dislike
   			if($this->aDislike($username)) {
   				unset($this->dislike[$username]);
   				$this->nbrDislikes -= 1;
   			} 
   			//Si l'utilisateur à liké, supprime son like et rajoute un dislike
   			else if($this->aLike($username)) {
   				unset($this->likes[$username]);
   				$this->nbrLikes -= 1;
   				array_push($this->dislike, array("username" => $username));
   				$this->nbrDislikes += 1;
   			} 
   			//Ajoute un dislike
   			else {
   				array_push($this->dislike, array("username" => $username));
   				$this->nbrDislikes += 1;
   			}
   		}

   		//return un integer.
   		//Permet de connaitre le nombre de like d'un article
   		public function getNbrLikes() {
   			return $this->nbrLikes;
   		}

		//return un integer.
   		//Permet de connaitre le nombre de Dislike d'un article
   		public function getNbrDislikes() {
   			return $this->nbrDislikes;
   		}

   		public function aLike($username) {
   			if($this->nbrLikes == 0) {
   				return false;
   			}
   			if (in_array($username, array_column($this->likes, 'username'))) {
		        return true;
		    }
   			return false;
   		}

   		public function aDislike($username) {
   			if($this->nbrDislikes == 0) {
   				return false;
   			}
   			if (in_array($username, array_column($this->dislike, 'username'))) {
		        return true;
		    }
   			return false;
   		}
	}
?>

