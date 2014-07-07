<?

include "includes/start.inc.php";

include "templates/header.inc.php";

$limit = 40;
$sort = 'created desc';
$where = array();
$order = "$sort, image_id DESC";

if (!empty($_GET['user_id']) && is_numeric($_GET['user_id'])) {
	$where[] = "user_id = {$_GET['user_id']}";
}
if (!empty($_GET['gridref']) && preg_match('/^\w{1,2}\d{4}$/',$_GET['gridref'])) {
	$where[] = "grid_reference = ".$db->quote($_GET['gridref']);
}
if (!empty($_GET['taken']) && preg_match('/^\d{4}(-\d{2})*$/',$_GET['taken'])) {
	$where[] = "taken LIKE ".$db->quote($_GET['taken']."%");
}


if (empty($_GET['by']))
	$_GET['by'] = 'year';


$count = $db->getOne("SELECT COUNT(*) FROM {$db->table_image}");

print "<p>Total Images: <b>$count</b>";

print "<h3>Breakdown</h3>";

switch($_GET['by']) {
	case 'user_id': if (empty($by)) { $key = 'user_id'; $by='user_id'; $display = 'realname'; }
	case 'year': if (empty($by)) { $key = 'taken'; $by='substring(taken,1,4)'; } 
	case 'category': if (empty($by)) { $key = 'category'; $by='category'; } 
	case 'grid_reference': if (empty($by)) { $key = 'gridref'; $by='grid_reference'; } 
	
		if (!empty($display)) {
			$rows = $db->getAll("SELECT $by as $key,$display,COUNT(*) AS images FROM {$db->table_image} GROUP BY $by ORDER BY images DESC LIMIT 500");
		} else {
			$rows = $db->getAll("SELECT $by as $key,COUNT(*) AS images FROM {$db->table_image} GROUP BY $by ORDER BY images DESC LIMIT 500");
		}
		break;
}
$where = empty($where)?1:implode(" AND ",$where);

print "<ol>";
foreach ($rows as $row) {
	?>
	<li value="<? echo $row['images']; ?>">
		<a href="images.php?<? echo "$key=".urlencode($row[$key]); ?>">
			<? echo he($row[isset($display)?$display:$key]); ?>
		</a>
	</li>
	<?
}
print "</ol>";

if (!empty($where)) 
	print "<a href=?>View All Images</a>";


include "templates/footer.inc.php";

