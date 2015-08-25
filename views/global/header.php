<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Wardieval - a perfect O-Game Style</title>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
    <link href="<?php echo _ROOT_CSS_ ?>style.css" rel="stylesheet">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <script src="<?php echo _ROOT_JS_ ?>jquery.countdown.min.js"></script>

    <?php if(User::isLoggued()){ ?>
        <script>
            var ressources = <?php echo $user->ressources ?>;
        </script>
        <script src="<?php echo _ROOT_JS_ ?>global.js"></script>
    <?php } ?>
</head>

<body>
    <header class="container">
        <div class="row">
            <div class="col-3">Logo</div>
            <nav class="col-7">
                <ul>
                    <li><a href="<?php echo _ROOT_ ?>home">accueil</a></li>
                    <?php if(User::isLoggued()){ ?>
                        <li><a href="<?php echo _ROOT_ ?>empire">mon empire</a></li>
                        <li><a href="<?php echo _ROOT_ ?>war">guerre</a></li>
                    <?php } else { ?>
                        <li><a href="<?php echo _ROOT_ ?>register">inscription</a></li>
                    <?php } ?>
                </ul>
            </nav>
            <div class="col-2 user-infos">
                <?php if(User::isLoggued()){ ?>
                    <ul>
                        <li>Score : <?php echo $user->score ?></li>
                        <li>ressources : <span id="js-ressources"><?php echo $user->ressources ?></span></li>
                        <li><?php echo $user->pseudo ?> <a href="<?php echo _ROOT_ ?>home/logout">déconnexion</a></li>
                    </ul>
                <?php } else { ?>
                    <form id="login" action="<?php echo _ROOT_ ?>home/login" method="post">
                        <input type="text" name="pseudo" placeholder="pseudo" >
                        <input type="password" name="pass" placeholder="mot de passe" >
                        <input type="submit" name="submit" value="login">
                    </form>
                <?php } ?>
            </div>
        </div>
    </header>
    <?php
    if (!empty($errors)){
        echo '<section class="container"><ul class="alert alert-error col-12">';
        foreach ($errors as $error)
            echo '<li>'.$error.'</li>';
        echo '</ul></section>';
    }
    ?>

