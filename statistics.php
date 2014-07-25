<?

include "includes/start.inc.php";

include "templates/header.inc.php";

print '<h3><b>Statistics</b></h3></h3>';

include "includes/filter.inc.php";


$row = $db->getRow("SELECT COUNT(*) as images, COUNT(DISTINCT user_id) AS users, COUNT(DISTINCT grid_reference) AS squares 
	FROM {$db->table_image} $tables WHERE $where");

print "<p align=center>Total Images: ".dis($row['images']).", by ".dis($row['users'])." users, in ".dis($row['squares'])." different squares</p>";



$row = $db->getRow("SELECT COUNT(DISTINCT s.grid_reference) AS squares, COUNT(DISTINCT i.grid_reference) AS photographed
	 FROM {$db->table_square} s LEFT JOIN {$db->table_image} i ON (i.grid_reference = s.grid_reference AND $where)");

if (!empty($row['squares'])) {
	print "<div class=statbar>";


	$row['percentage'] = sprintf('%.1f',$row['photographed']/$row['squares']*100);
	print "<p>Total Squares: ".dis($row['squares']).", of which ".dis($row['photographed'])." have photographs; which is a coverage of <b>{$row['percentage']}%</b></p>";
	$total = $row['squares'];


	$row = $db->getRow("SELECT COUNT(*) as images, COUNT(DISTINCT user_id) AS users, COUNT(DISTINCT grid_reference) AS squares 
		FROM {$db->table_image} $tables LEFT JOIN {$db->table_square} s USING (grid_reference) WHERE s.square_id is NULL AND $where");

	if (!empty($row['squares'])) {
		print "<p>In addition, we also have ".dis($row['images'])." images, in ".dis($row['squares'])." different squares, that are NOT listed known squares.</p>";
	}

	if (!empty($CONF['square_source'])) {
		print "<div class=source>".nl2br(he($CONF['square_source']))."</div>";
	}

	print "</div>";
}


print "<div class=sidebar><p>Images by Year<br>(submitted to Geograph)</p>";
print "<ol class=stats>";
foreach ($db->getAll("SELECT substring(submitted,1,4) as year,COUNT(*) images FROM {$db->table_image} WHERE active != 'deleted' GROUP BY substring(submitted,1,4) DESC LIMIT 50") as $row) {
        print "<li value=\"{$row['images']}\"><a href=\"images.php?submitted=".urlencode($row['year'])."\">".he($row['year'])."</a></li>";
}
print "</ol>";
print "</div>";

print "<div class=sidebar><p>Coverage Buildup<br>(Squares photographed)</p>";
print "<ol class=stats>";
$squares = 0;
foreach ($db->getAll("SELECT substring(submitted,1,4) as year,COUNT(*) squares
	FROM {$db->table_image} INNER JOIN {$db->table_square} s USING (grid_reference) WHERE active != 'deleted' and point=1 
	GROUP BY substring(submitted,1,4) ASC LIMIT 50") as $row) {
	$squares += $row['squares']; //cumulative total
	$percentage = sprintf('%.1f',$squares/$total*100);
        print "<li value=\"{$squares}\"><a href=\"images.php?submitted=".urlencode($row['year'])."\">".he($row['year'])."</a> ($percentage%)</li>";
}
print "</ol>";
print "</div>";

if ($squares) { //set by header.php!

print "<div class=sidebar><p>Squares by number of images</p>";
print "<ol class=stats>";
foreach ($db->getAll("SELECT images,COUNT(*) squares FROM {$db->table_square} GROUP BY images LIMIT 50") as $row) {
        print "<li value=\"{$row['squares']}\">with ".he($row['images'])." image".($row['images']==1?'':'s')."</li>";
}
print "</ol>";
print "</div>";

}

print "<br style=clear:both>";


include "templates/footer.inc.php";

