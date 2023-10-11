# TP_Symfony

##Enoncé  
Vous devez créer un moteur de blog où l'on peut créer des articles qui sont rangés par catégorie.
Les utilisateurs peuvent laisser un commentaire sur un article. Pour être publié, le commentaire doit être approuvé par un administrateur.  
##Les articles contiennent:
- un titre
- un texte
- une image d'accroche  
##Les commentaires:
- un pseudo
- un texte 
- une note sur 5  
##Les catégories:
- un titre  
 
Vous devez aussi construire l'interface d'administration et le front.

Consignes 2
- Choisir un thème
- Faire un style simple
- Pouvoir créer un compte utilisateur => pouvoir s'inscrire
- Ajouter des rôles
  - Admin: peut accéder à l'espace admin
  - Modérateur: Peut valider ou supprimer des commentaires
  - Rédacteur: Peut écrire des articles et les soumettre à validation par les Modérateur ou Admin
  - Utilisateur premium: Peut voir des articles réservés aux abonnés
  - Utilisateur: peut voir les articles et laisser des commentaires
 
- Sécurité: 
  - Bloquer l'accès des routes aux utilisateurs non autorisés
  - Bloquer l'accès aux fonctionnalités réservées à certains rôles
- Vous pouvez ajouter les fonctionnalité de votre choix!