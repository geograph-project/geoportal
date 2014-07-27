<?

include "includes/start.inc.php";

include "templates/header.inc.php";

?>

<h3>Unphotographed Squares</h3>

<div class="sidebar" style="width:200px">

<ol class=stats><?
foreach ($db->getAll("SELECT SUBSTRING(grid_reference,1,LENGTH(grid_reference)-4) as myriad,COUNT(*) squares 
		FROM {$db->table_square} WHERE images = 0 GROUP BY myriad ORDER BY LENGTH(grid_reference) DESC,grid_reference DESC") as $row) {
	if ($_GET['gridref'] == $row['myriad']) {
		print "<li value=\"{$row['squares']}\"><b>".he($row['myriad'])."</b></li>";
	} else {
        	print "<li value=\"{$row['squares']}\"><a href=\"?gridref=".urlencode($row['myriad'])."\">".he($row['myriad'])."</a></li>";
	}
}
?></ol>
</div>
<div style="margin-left:220px">
<h4>150 Random squares</h4>

<ul>
<?
	$where = array();

        $where[] = "images = 0";

if (!empty($_GET['gridref']) && preg_match('/^(\w{1,2})(\d?)(\d?)(\d{2})?$/',$_GET['gridref'],$m)) {
	print "&middot; Squares in <a href=\"http://{$CONF['geograph_domain']}/gridref/{$_GET['gridref']}\">".he($_GET['gridref'])."</a><br/>";
	
	if (strlen($m[0]) >= 5) {
		$where[] = "grid_reference = ".$db->quote($_GET['gridref']);
	} elseif (!empty($m[2])) {
		$where[] = "grid_reference LIKE ".$db->quote($m[1].$m[2].'_'.$m[3].'_');
	} else {
		$where[] = "grid_reference LIKE ".$db->quote($m[1].'____');
	}
}

        $where = implode(" AND ",$where);

foreach ($db->getAll("SELECT * FROM {$db->table_square} WHERE $where ORDER BY RAND() LIMIT 150") as $row) {
	print "<li><b>{$row['grid_reference']}</b> ";
	if (!empty($row['link'])) {
		print "<a href=\"{$row['link']}\">";
	}
	if (!empty($row['name'])) {
		print he($row['name']);
	}
	print "</a></li>";
}
?>
</ul>
</div>
<br style=clear:both>
<?

include "templates/footer.inc.php";

