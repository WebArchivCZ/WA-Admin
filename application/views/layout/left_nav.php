<?php
    $base_url = url::base().'tables/';
    $pages = array('resources'=>'Zdroje',
                   'publishers' =>  'Vydavatelé',
                   'contacts' => 'Kontakty',
                   'correspondence' => 'Oslovení',
                   'contracts' => 'Smlouvy',
                   'ratings' => 'Hodnocení',
                   'seeds' => 'Semínka');
?>

<h3>Prohlížet</h3>

<ul class="nav">

    <?php
        foreach ($pages as $link => $page) {
            $class = '';
            if (isset($this->table) AND $this->table == $link) {
                $class = " class='active'";
            }
            // TODO pokud je to posledni link v tabulce, pak pridat do class i 'last'
            echo "<li{$class}><a href='{$base_url}{$link}'>{$page}</a></li>";
        }
    ?>
</ul>
<h3>Veřejné</h3>
<a href="http://www.webarchiv.cz" class="link" target="_blank">webarchiv.cz</a>
<a href="http://www.webarchiv.cz/textpattern" class="link" target="_blank">webarchiv.cz - edit</a>
<a href="http://wayback.webarchiv.cz" class="link" target="_blank">wayback</a>
<h3>Interní</h3>
<a href="http://trac.webarchiv.cz" class="link" target="_blank">trac</a>
<a href="https://intranet.webarchiv.cz/blog" class="link" target="_blank">blog</a>
<a href="https://intranet.webarchiv.cz/wiki" class="link" target="_blank">wiki</a>