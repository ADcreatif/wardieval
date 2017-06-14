##Wardieval

_Développé dans le cadre de la formation Drupal_

**Améliorations à faire**
- dévoiler plus d'unités en fonction que le score augmente
- A la fin d'un combat, ou de son annulation, les unitées sont directement remise en stock (il faut leur ajouter un temps de retour).
- améliorer le calcul distance/temps qui sépare le trajet entre deux joueurs (actuellement 1pt de score = 1 seconde
- augmentation du score des combatants si destruction d'unités (ou pas ?)
- la méthode get_fleets devrait utiliser le modèle Fleet plutôt qu'aller les chercher en sql

**Maj 11/05/17**
- Suppression de ObjectModel
- Combats
    - Calcul des combats
    - Utilisation d'un template pour les rapports de combat
    
**Maj 01/04/17**
- Amélioration des mails
    - création d'une page dédié
    - autocompletion
    - favoris
    - répondre
    - envoi de mail
- Création de la page de profil du joueur
- Amélioration du calcul de la file de construction
- Design : ajout du html sémantique + flex

**Maj 11/06/16**
- Gestionaire d'erreurs
- Amélioration du design

**Maj 10/06/16**
- Infos bulles entre les pages
- Mail à l'inscription
- Refonte complète du moteur de combat
- Refonte complète des transactions ajax
- Equilibrage des scores
- Responsive