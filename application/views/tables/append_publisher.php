<?php if (isset($resources) AND $resources->count() != 0) { ?>

<div class="table">

    <?= html::image(array('src'=>'media/img/bg-th-left.gif', 'width'=>'8', 'height'=>'7', 'class'=>'left')) ?>
    <?= html::image(array('src'=>'media/img/bg-th-right.gif', 'width'=>'7', 'height'=>'7', 'class'=>'right')) ?>

    <table class="listing" cellpadding="0" cellspacing="0">
        <tr>
            <th class="first" width="50%">Zdroj</th>
            <th class="last">Kontakt</th>
        </tr>
<?php foreach ($resources as $resource) {
        $resource_url = 'tables/resources/view/'.$resource->id;
        $contact_url = 'tables/contacts/view/'.$resource->contact->id;
?>
        <tr>
            <td><a href="<?= url::site($resource_url) ?>"><?= $resource->title ?></a></td>
            <td><a href="<?= url::site($contact_url) ?>"><?= $resource->contact ?></a></td>
<? } ?>
    </table>
</div>

<? } ?>