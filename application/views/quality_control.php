<div class="top-bar">
	<h1>Hodnocení zdrojů</h1>
</div>
<br />
<div class="select-bar">
	<form action="<?= url::base().url::current() ?>/search/">
		<label> <input type="text" name="search_string" /> </label>
		<label> <input type="submit" name="Submit" value="<?= Kohana::lang('tables.search');?>" /> </label>
	</form>
</div>
<div class="table">
<?=html::image(array('src' => 'media/img/bg-th-left.gif' , 'width' => '8' , 'height' => '7' , 'class' => 'left'))?>
<?=html::image(array('src' => 'media/img/bg-th-right.gif' , 'width' => '7' , 'height' => '7' , 'class' => 'right'))?>

<table class="listing" cellpadding="0" cellspacing="0">
	<tr>
		<th class="first">Název</th>
		<th>URL</th>
		<th>V pořádku</th>
		<th>Akceptovatelné</th>
		<th class="last">Nevyhovující</th>
	</tr>
	<tr>
		<td class="first">Ikaros</td>
		<td><a href="http://www.ikaros.cz">http://www.ikaros.cz</a></td>
		<td><a href="">V pořádku</a></td>
		<td><a href="">Akceptovatelné</a></td>
		<td><a href="">Nevyhovující</a></td>
	</tr>
	<tr>
		<td class="first">Národní knihovna ČR</td>
		<td><a href="http://www.ikaros.cz">http://www.nkp.cz</a></td>
		<td><a href="">V pořádku</a></td>
		<td><a href="">Akceptovatelné</a></td>
		<td><a href="">Nevyhovující</a></td>
	</tr>
	<tr>
		<td class="first">Otevřete.cz</td>
		<td><a href="http://www.ikaros.cz">http://www.otevrete.cz</a></td>
		<td><a href="">V pořádku</a></td>
		<td><a href="">Akceptovatelné</a></td>
		<td><a href="">Nevyhovující</a></td>
	</tr>
	<tr>
		<td class="first">eCesty.cz</td>
		<td><a href="http://www.ikaros.cz">http://www.ecesty.cz</a></td>
		<td><a href="">V pořádku</a></td>
		<td><a href="">Akceptovatelné</a></td>
		<td><a href="">Nevyhovující</a></td>
	</tr>
</table>
<p class="center">
<button>Zobrazit vše</button>
</p>
</div>