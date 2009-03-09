<div class="top-bar">
	<h1>Editace zdroje</h1>
</div>
<br />
<div class="select-bar">
	<form action="<?= url::base().url::current() ?>/search/">
		<label> <input type="text" name="search_string" /> </label>
		<label> <input type="submit" name="Submit" value="<?= Kohana::lang('tables.search');?>" /> </label>
	</form>
</div>
<?php

if (isset($resource_id))
{
	$resource = ORM::factory('resource', $resource_id);
	$ratings_array = Kohana::config('wadmin.ratings_result');
	echo '<h2>Zdroj byl uspesne vlozen</h2>';
?>

	<table border="1 ">
		<tr><td>ID zdroje</td><td><?= $resource->id ?></td></tr>
		<tr><td>Nazev</td><td><?= $resource->title ?></td></tr>
		<tr><td>Vydavatel</td><td><a href=""><?= $resource->publisher->name ?></a></td></tr>
		<tr><td>URL</td><td><?= $resource->url ?></td></tr>
		<tr><td>Datum</td><td><?= date('d.m.Y', strtotime($resource->date)) ?></td></tr>
		<tr><td>ISSN</td><td><?= $resource->ISSN ?></td></tr>
		<tr><td>Aleph ID</td><td><?= $resource->aleph_id ?></td></tr>
		<tr><td>Smlouva</td><td><?= $resource->contract->contract_no ?></td></tr>
		<tr><td>Navrhl</td><td><?= $resource->suggested_by->proposer ?></td></tr>
		<tr><td>Status</td><td><?= $resource->resource_status->status ?></td></tr>
		<tr><td>Konspekt</td><td><?= $resource->conspectus->category ?></td></tr>
		<tr><td>Hodnoceni zdroje</td><td><?= $ratings_array[$resource->rating_result] ?></td></tr>
		<tr><td>Kurátor</td><td><?= $resource->curator->lastname ?></td></tr>
		<tr><td>Frekvence sklizeni</td><td><?= $resource->crawl_freq->frequency ?></td></tr>
		<tr><td>Katalogizovan</td><td><?= $resource->catalogued ?></td></tr>
		<tr><td>Metadata</td><td><?= $resource->metadata ?></td></tr>
		<tr><td>Creative Commons</td><td><?= $resource->creative_commons ?></td></tr>
		<tr><td>Technicke problemy</td><td><?= $resource->tech_problems ?></td></tr>
		<tr><td>Komentar</td><td><?= $resource->comments ?></td></tr>
					
		
	</table>

<?php
} else {
echo $content;
?>
<?php
}
?>