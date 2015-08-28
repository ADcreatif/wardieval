<div class="col-8 sm-full">
<h2>Accueil</h2>

    <p>Bienvenue</p>

    <ul>
        <li class="title">Utilisateurs</li>
        <li>Création compte</li>
        <li>Login</li>
        <li class="title">Empire</li>
        <li>Création unités</li>
        <li>gestion des files d'attente unités</li>
        <li>gestion des attaques en cours</li>
        <li>affichage des flottes en approche</li>
        <li>gestion des messages</li>
        <li class="title">Combats</li>
        <li>choix des cibles à portées</li>
        <li>choix de la flotte à envoyer</li>
        <li>lancement de l'attaque</li>
        <li>rapports de combats</li>
        <li>pillages de ressources si victoire</li>
        <li class="title">Stats</li>
        <li>Affichage du top 5</li>
    </ul>
</div>
<div class="col-4 sm-full">
<h3>Top 10 joueurs</h3>
    <ol>
        <?php
        $users = User::get_users_list(10, 'score', 'DESC');
        foreach ($users as $user)
            echo '<li>' . ucfirst(htmlentities($user['pseudo'])) . ' - ' . $user['score'] . '</li>';
        ?>

    </ol>
</div>