<h2>Combats</h2>
<?php if($user->isLoggued()){

    // Fleet selection
    if($select_fleet){?>

        <form method="post" id="unit-selection">
            <input type="hidden" name="attacker_id" value="<?php echo $user->id ?>"><?php echo $user->pseudo ?> VS
            <input type="hidden" name="defender_id" value="<?php echo $defender->id ?>"><?php echo $defender->pseudo ?>
            <table class="col-8 center">
                <?php if(count($fleet) > 0) {
                    foreach ($fleet as $unit){
                        if($unit['quantity'] > 0){ ?>
                             <tbody>
                                <tr>
                                    <td class="unit-name"><?php echo $unit['name'] ?></td>
                                    <td>
                                        <label>
                                            <input type="number" class="js-quantity input-number" name="unit_id-<?php echo $unit['id'] ?>" placeholder="0" min="0" max="<?php echo $unit['quantity'] ?>">/ <?php echo $unit['quantity'] ?>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <div class="slider" data-max="<?php echo $unit['quantity'] ?>"></div>
                                    </td>
                                </tr>
                             </tbody>
                <?php } } }?>
            </table>
            <button type="submit" name="start_war">start_war</button>
        </form>

    <?php // Target Choice
    } else { ?>
        <ul class="players-list col-4 center">
            <?php
                if(!empty($targets)){
                    foreach ($targets as $target){
                        if($user->id != $target['id'])
                            echo '<li><a href="'._ROOT_.'war/attack/'. $target['id'] .'">'. $target['pseudo'] .' - '.  $target['score'] .'</a></li>';
                        else $errors[] = 'vous ne pouvez pas attaquer votre empire';
                    }
                } else {
                    $errors[] = 'Aucun utilisateur de votre niveau à portée';
                } ?>
        </ul>

    <?php } ?>

    <script>
        $(function(){
            $('.slider').slider({
                animate:'slow',
                min : 0 ,
                max : $(this).data('max') ,
                step : 1,
                slide: function(evt,target){
                    var qty = Math.floor($(this).data('max') * target.value / 100) ;
                    $(this).closest('tbody').find('.js-quantity').val(qty) ;
                }
            });
            $('.js-quantity').blur(function(e){
                var slider = $(this).closest('tbody').find('.slider')
                var quantity =  $(this).val() / slider.data('max') * 100;
                slider.slider({value:quantity})
            })
        });
    </script>
<?php } else {
    echo '<p>Vous n\'avez rien à faire ici</p>';
}?>