<?

include "includes/start.inc.php";

include "templates/header.inc.php";

print '<h3><b>Breakdown</b></h3>';

include "includes/filter.inc.php";

if (empty($_GET['by']))
	$_GET['by'] = 'year';

$count = $db->getOne("SELECT COUNT(*) FROM {$db->table_image} $tables WHERE $where");



print "<div class=\"sidebar\" style=\"width:150px\">";

print "<p>Total Images: <b>$count</b>";

print "<hr/>";


$list = array(
	'user_id' => 'Contributor',
	'day' => 'Day Taken',
	'month' => 'Month Taken',
	'year' => 'Year Taken',
	'decade' => 'Decade Taken',
	'category' => 'Category',
	'grid_reference' => 'Grid Square',
	'hectad' => 'Hectad',
	'myriad' => 'Myriad',
	'place' => 'Place',
	'county' => 'County',
	'country' => 'Country',
	'label' => 'Labels',
	'tag' => 'Tags',
	'context' => 'Geographical Context',
	'created' => 'Added to portal',
);
foreach ($list as $key => $value) {
	if ($key == $_GET['by']) {
		print "<b>$value</b><br> ";
	} else {
		print "&middot; <a href=\"?".http_build_query($_GET)."&amp;by=$key\">$value</a><br> ";
	}
}
print "</div>";

print "<div style=\"padding-left:165px\">";
print "<h4>Breakdown by {$list[$_GET['by']]}</h4>";

$tables = '';
$key = $by = $display = '';
switch($_GET['by']) {
	case 'place': 
	case 'county': 
	case 'country': $key = $by = $_GET['by'];
		
	case 'user_id': if (empty($by)) { $key = $by = 'user_id'; $display = 'realname'; }
	case 'created': if (empty($by)) { $key = 'created'; $by = 'substring(created,1,10)'; } 
	case 'day': if (empty($by)) { $key = 'taken'; $by = 'taken'; } 
	case 'month': if (empty($by)) { $key = 'taken'; $by = 'substring(taken,1,7)'; } 
	case 'year': if (empty($by)) { $key = 'taken'; $by = 'substring(taken,1,4)'; } 
	case 'decade': if (empty($by)) { $key = 'taken'; $by = 'substring(taken,1,3)'; $display = "concat(substring(taken,1,3),'0s')"; } 
	case 'category': if (empty($by)) { $key = $by = 'category'; } 
	case 'grid_reference': if (empty($by)) { $key = 'gridref'; $by='grid_reference'; } 
	
	case 'hectad': if (empty($by)) { $key = 'gridref'; $by='CONCAT(SUBSTRING(grid_reference,1,LENGTH(grid_reference)-3),SUBSTRING(grid_reference,LENGTH(grid_reference)-1,1))'; } 
	case 'myriad': if (empty($by)) { $key = 'gridref'; $by='SUBSTRING(grid_reference,1,LENGTH(grid_reference)-4)'; } 
	 
	
	
		if (!empty($display)) {
			$rows = $db->getAll("SELECT $by AS $key,$display,COUNT(*) AS images FROM {$db->table_image} i $tables WHERE $where GROUP BY $by ORDER BY images DESC LIMIT 500");
		} else {
			$display = $key;
			$rows = $db->getAll("SELECT $by AS $key,COUNT(*) AS images FROM {$db->table_image} i $tables WHERE $where GROUP BY $by ORDER BY images DESC LIMIT 500");
		}
		break;
		
	case 'label': $key = $display = 'label'; $by='label_id';
		$rows = $db->getAll("SELECT name AS $key,COUNT(*) AS images FROM {$db->table_label} INNER JOIN {$db->table_image_label} USING (label_id) INNER JOIN {$db->table_image} i USING (image_id) GROUP BY $by ORDER BY images DESC LIMIT 500");
		break;
		
	case 'tag': 
	case 'context': $key = $display = $_GET['by'];
	
		$data = $db->getAll($sql = "SELECT {$key}s,COUNT(*) AS images FROM {$db->table_image} i $tables WHERE $where GROUP BY {$key}s ORDER BY null LIMIT 500");
		$rows = array();
		foreach ($data as $row)
			if (!empty($row[$key.'s']))
				foreach(explode('_SEP_',$row[$key.'s']) as $tag)
					if ($tag = trim($tag))
						if (isset($rows[$tag])) 
							$rows[$tag]['images']+=$row['images'];
						else
							$rows[$tag] = array($key=>$tag, 'images'=>$row['images']);

		ksort($rows); //todo, should sort by number, not name, to match others
		break;		
	
}

if ($_GET['by'] == 'tag') {
	print "<div class=tags>";
        foreach ($rows as $row) {
		$size = (log($row['images'])/2)+0.7;
                ?>
			<a href="images.php?<? echo http_build_query($_GET)."&amp;$key=".urlencode($row[$key]); ?>" style="font-size:<? echo $size; ?>em" title="<? echo $row['images']; ?> images"><? echo he($row[$display]); ?></a>
                <?
        }
        print "</div>";
} else {
	print "<ol class=stats>";
	foreach ($rows as $row) {
		?>
		<li value="<? echo $row['images']; ?>">
			<a href="images.php?<? echo http_build_query($_GET)."&amp;$key=".urlencode($row[$key]); ?>"><? echo he($row[$display]); ?></a>
		</li>
		<?
	}
	print "</ol>";
}

print "</div>";
print "<br style=\"clear:both\">";

include "templates/footer.inc.php";

