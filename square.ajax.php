<?

include "includes/start.inc.php";

$where = array();

if (!empty($_GET['gridref']) && preg_match('/^(\w{1,2})(\d?)(\d?)(\d{2})?$/',$_GET['gridref'],$m)) {
        $where[] = "grid_reference = ".$db->quote($_GET['gridref']);
}

$data = $db->getRow("SELECT * FROM {$db->table_square} WHERE ".implode(" AND ",$where));
if (!empty($data))
	$data['domain'] = $CONF['geograph_domain'];

if (!empty($data['images'])) {
	$data['rows'] = array();

	$where[] = "active != 'deleted'";

	$where = implode(" AND ",$where);
	$rows = $db->getAll("SELECT image_id,title,user_id,realname,hash FROM {$db->table_image} WHERE $where ORDER BY fetched DESC LIMIT 5");

	foreach ($rows as $row) {
		$row['thumb'] = getGeographUrl($row['image_id'],$row['hash'],'small');
		unset($row['hash']);
		$data['rows'][] = $row;
	}
}

print json_encode($data);
