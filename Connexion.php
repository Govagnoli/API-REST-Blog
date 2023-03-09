<?php
	class Connexion {
		private static $connexion = null;

		private function __construct() {
			$server = 'localhost';
	        $login = 'root';
	        $mdp = '';
	        $db = 'blog';

	        try {
	            self::$connexion = new PDO("mysql:host=$server;dbname=$db;charset=UTF8", $login, $mdp);
	        } catch (Exception $e) {
	            die('Erreur : ' . $e->getMessage());
	        }
		}

		//Implémentation du Singleton. Si la connexion est null alors une connexion est créée. Sinon ça renvoie la connexion courante.
		public static function getConnexion() {
		    if (is_null(self::$connexion)) {
		    	new Connexion();
		    }
		    return self::$connexion;
		}

		public function closeConnexion() {
			if(!is_null(self::$connexion)) {
				self::$connexion = null;
			}
		}
	}
?>