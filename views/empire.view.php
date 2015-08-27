<div class="row">
    <?php if ($user->isLoggued()) { ?>
        <script>
            var unit_infos = <?php echo json_encode($empire->get_unit_list()); ?>;
            var modifiers = <?php echo json_encode($empire->modifiers); ?>;
        </script>
        <script src="<?php echo _ROOT_JS_ ?>empire.js"></script>
        <h2 class="col-12">Mon empire</h2>
        <div class="col-6 sm-full">
            <fieldset class="row">
                <legend class="btn-wood-small">Construction d'unités</legend>

                <?php
                foreach ($empire->get_units_owned() as $unit) {
                    $quantity = isset($unit['quantity']) ? $unit['quantity'] : 0;
                    echo '
                    <figure class="unit col-12" id="unit_' . $unit['id'] . '">
                        <div class="col-3"><img src="' . _ROOT_IMG_ . 'units/' . $unit['image_name'] . '"></div>
                        <figcaption class="col-9">
                            <h4>' . $unit['name'] . ' <small class="alert alert-info">possédé :<span class="js-quantity">' . $quantity . '</span></small></h4>
                            <p>' . $unit['description'] . '</p>
                            <ul>
                                <li><i class="icon dollar"></i>' . $unit['price'] . '</li>
                                <li><i class="icon clock"></i>' . $unit['building_time'] . '</li>
                                <li><i class="icon damages"></i>' . $unit['damage'] . '</li>
                                <li><i class="icon life"></i>' . $unit['life'] . '</li>
                            </ul>
                            <form method="post" class="unit-factory">
                                <label>
                                    <input type="number" data-unit-id="' . $unit['id'] . '" name="quantity" placeholder="0" maxlenght="4">
                                </label>
                                <button type="submit" name="submit" value="envoyer">Construire</button>
                                <input type="hidden" name="unit_id" value="' . $unit['id'] . '">
                                <div class="js-span-info alert alert-info hide"></div>
                            </form>
                        </figcaption>
                    </figure>
                    ';
                } ?>
            </fieldset>
        </div>
        <div class="col-6 sm-full">
            <fieldset>
                <legend class="btn-wood-small">Messages <a href=""><i class="js-new-mail icon new-mail"></i></a>
                </legend>
                <table id="js-messages">
                    <?php
                    $messages = $empire->get_mails();
                    if (! empty($messages)) {
                        echo '<tr><th>de</th><th>sujet</th><th>reçu le </th><th></th></tr>';
                        foreach ($messages as $id => $message) {
                            $unread = $message['unread'] ? 'unread' : '';
                            echo '
                                <tr class="topic ' . $unread . '" data-mail-id="' . $message['id'] . '">
                                    <td>' . $message['author'] . '</td>
                                    <td>' . $message['topic'] . '</td>
                                    <td>' . $message['send_date'] . '</td>
                                    <td><a class="alert alert-error" href="#" data-mail-id="' . $message['id'] . '">X</a></td>
                                </tr>
                                <tr class="hidden message"><td colspan="4">' . $message['message'] . '</td></tr>
                            ';
                        }
                    } else {
                        echo '<li class="alert alert-info">Aucun Message reçu</li>';
                    } ?>
                </table>
            </fieldset>
            <fieldset>
                <legend class="btn-wood-small">File d'attente</legend>
                <ul id="js-queue">
                    <?php
                    $queue = $empire->get_queue();
                    if (! empty($queue)) {
                        foreach ($queue as $item) {
                            echo '
                                <li>
                                    ' . $item['name'] . ' - ' . $item['quantity'] . ' (<span data-countdown="' . $item['finished_at'] . '"></span>)
                                    <a class="alert alert-error" href="#" data-queue-id="' . $item['id'] . '">X</a>
                                </li>
                            ';
                        }
                    } else {
                        echo '<li class="alert alert-info">Aucune unités en file d\'attente, il serait temps de construire !</li>';
                    } ?>
                </ul>
            </fieldset>
            <fieldset>
                <legend class="btn-wood-small">Attaques en cours</legend>
                <ul id="js-fleet">
                    <?php
                    $fleets = $empire->get_fleets();
                    if (! empty($fleets)) {
                        foreach ($fleets as $fleet) {
                            echo '
                            <li>
                                En route vers <b>' . $fleet['target'] . '</b>, arrivée prévue dans <span data-countdown="' . $fleet['arrival_time'] . '"></span>
                                <a class="alert alert-error" href="#" data-fleet-id="' . $fleet['id'] . '">X</a>
                            </li>
                            ';
                        }
                    } else {
                        echo '<li class="alert alert-info">Aucune attaque en cours ! Rendez-vous à la page <a href="' . _ROOT_ . 'war">guerre</a></li>';
                    } ?>
                </ul>
            </fieldset>
            <fieldset>
                <legend class="btn-wood-small">Flotte en approche</legend>
                <ul id="js-fleet">
                    <?php
                    $fleets_incoming = $empire->get_incoming_fleets();
                    if (! empty($fleets_incoming)) {
                        foreach ($fleets_incoming as $fleet) {
                            echo '<li>Attaque de <b>' . htmlentities($fleet['pseudo']) . '</b>, arrivée prévue dans <span data-countdown="' . $fleet['arrival_time'] . '"></span></li>';
                        }
                    } else {
                        echo '<li class="alert alert-info">Nos guetteurs ne signalent rien à l\'horizon</li>';
                    } ?>
                </ul>
            </fieldset>
        </div>
    <?php
    } else {
        echo '<p>Vous n\'avez rien à faire ici</p>';
    }?>
</div>