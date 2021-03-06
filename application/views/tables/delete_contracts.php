<div class="top-bar" id="solo">
	<h1>Smazat - <?= $contract ?></h1>
</div>
<?php
if (isset($resources))
{
	echo table::header();
	if ($resources->count() == 1)
	{
		?>
	<tr>
		<th class="first last" width="75%">Zdroj</th>
	</tr>
	<td><?= $resources->current()->title ?></td>
	<?php
	}
	if ($resources->count() > 1)
	{
		echo '<tr><th class="first" width="75%">Zdroj</th><th class="last">Odstranit propojení</th></tr>';
		$delete_icon = icon::img('delete', 'Odstranit propojení vydavatele a zdroje.');
		foreach ($resources as $resource)
		{
			$delete_url = url::site('/tables/contracts/remove_from_resource/'.$resource->id);
			$resource_title = html::anchor(url::site('/tables/resources/view/'.$resource->id), $resource->title);
			echo "<tr>
				<td>{$resource_title}</td>
				<td class='center'><a href='{$delete_url}' class='confirm'>{$delete_icon}</a></td>
			  </tr>";
		}
	}

	echo table::footer();

	$delete_url = url::site('/tables/contracts/erase/'.$contract->id);
	?>
<div class="center">
	<a href="<?= $delete_url ?>" class="delete_contract_conf">
		<button>Smazat smlouvu</button>
	</a>
</div>

<?php } ?>