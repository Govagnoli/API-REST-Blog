# API-REST-Blog

Création d'une API pour la gestion d'un blog. Notre API prend en compte les fonctionnalités suivantes :
  ● La publication, la consultation, la modification et la suppression des articles de blogs. Un article est caractérisé, a minima, par sa date de publication, son auteur et son contenu.
  ● L’authentification des utilisateurs souhaitant interagir avec les articles. Cette fonctionnalité devra s’appuyer sur les JSON Web Token (JWT). Un utilisateur est caractérisé, a minima, par un nom d’utilisateur, un mot       de passe et un rôle (moderator ou publisher).
  ● La possibilité de liker/disliker un article. La solution doit permettre de retrouver quel(s) utilisateur(s) a liké/disliké un article.

La gestion des restrictions d'accès :

        - Un utilisateur authentifié avec le rôle moderator peut :
                - Consulter n’importe quel article. Un utilisateur moderator doit accéder à l’ensemble des informations décrivant un article : auteur, date de publication, contenu, liste des utilisateurs ayant liké l’article, nombre total de like, liste des utilisateurs ayant disliké l’article, nombre total de dislike.
                - Supprimer n’importe quel article. 
        - Un utilisateur authentifié avec le rôle publisher peut :
                - Poster un nouvel article.
                - Consulter ses propres articles.
                - Consulter les articles publiés par les autres utilisateurs. Un utilisateur publisher doit accéder aux informations suivantes relatives à un article : auteur, date de publication, contenu, nombre total de like, nombre total de dislike.
                - Modifier les articles dont il est l’auteur.
                - Supprimer les articles dont il est l’auteur.
                - Liker/disliker les articles publiés par les autres utilisateurs.IUT INFORMATIQUE R4.01
        - Un utilisateur non authentifié peut :
                - Consulter les articles existants. Seules les informations suivantes doivent être disponibles : auteur, date de publication, contenu.
               
La gestion des Erreurs fut un point important lors du développement. Notre API est entièrement couverte en message d'erreur avec des réponses pertinentes en fonction des problèmes reçu.
