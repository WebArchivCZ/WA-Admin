<?= form::open(url::base(FALSE).url::current().'/save/'.$status, array('id'=> $form_name, 'name'=> $form_name)); ?>

<h2><?= $title ?></h2>

<div class="table">
    <?=html::image(array('src' => 'media/img/bg-th-left.gif' , 'width' => '8' , 'height' => '7' , 'class' => 'left'))?>
    <?=html::image(array('src' => 'media/img/bg-th-right.gif' , 'width' => '7' , 'height' => '7' , 'class' => 'right'))?>
        <table class="listing" cellpadding="0" cellspacing="0">
            <tr>
                <th class="first">Název</th>
                <th>URL</th>
                <th>Kurátor</th>
                <th>Konspekt</th>
                <th>Hodnocení</th>
                <th>Podobné</th>
                <th class="last">Hod. ostatních</th>
            </tr>
                <?php foreach ($resources as $resource)
                { ?>
            <tr>
                <td class="first"><?=$resource->title ?></td>
                <td><a href="<?=$resource->url ?>" target="_blank"><?=$resource->url ?></a></td>
                <td><?=$resource->curator->username ?></td>
                <td><?=$resource->conspectus->category ?></td>
                <td>
                            <?= form::dropdown("rating[$resource->id]", $rating_values_array, $ratings[$resource->id]); ?>
                </td>
                <td class="center">
                            <?=html::image(array('src' => 'media/img/icons/find.png' , 'width' => '16' , 'height' => '16'))?>
                </td>
                <td class="center">
                            <?=html::image(array('src' => 'media/img/icons/find.png' , 'width' => '16' , 'height' => '16'))?>
                </td>
            </tr>
                <?php } ?>
        </table>
</div>
    <p class="center">
            <?=form::submit('submit', 'Uložit hodnocení') ?>
    </p>
    
<?=form::close() ?>