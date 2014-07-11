<?

$tables = '';
$limit = 40;
$sort = 'created desc';
$where = array("active != 'deleted'");
$order = "$sort, image_id DESC";

if (!empty($_GET['user_id']) && is_numeric($_GET['user_id'])) {
	$realname = $db->getOne("SELECT realname FROM {$db->table_image} WHERE user_id = {$_GET['user_id']} LIMIT 1");
	print "&middot; Images by <a href=\"http://{$CONF['geograph_domain']}/profile/{$_GET['user_id']}\">".he($realname)."</a><br/>";

	$where[] = "user_id = {$_GET['user_id']}";
}
if (!empty($_GET['gridref']) && preg_match('/^(\w{1,2})(\d?)(\d?)(\d{2})?$/',$_GET['gridref'],$m)) {
	print "&middot; Images in <a href=\"http://{$CONF['geograph_domain']}/gridref/{$_GET['gridref']}\">".he($_GET['gridref'])."</a><br/>";
	
	if (strlen($m[0]) >= 5) {
		$where[] = "grid_reference = ".$db->quote($_GET['gridref']);
	} elseif (!empty($m[2])) {
		$where[] = "grid_reference LIKE ".$db->quote($m[1].$m[2].'_'.$m[3].'_');
	} else {
		$where[] = "grid_reference LIKE ".$db->quote($m[1].'____');
	}
}
if (!empty($_GET['taken']) && preg_match('/^\d{3,4}(-\d{2})*$/',$_GET['taken'])) {
	print "&middot; Images Taken in <a>".he($_GET['taken'])."</a><br/>";

	$where[] = "taken LIKE ".$db->quote($_GET['taken']."%");
}
if (!empty($_GET['label']) && preg_match('/^[\w -]+$/',$_GET['label'])) {
	print "&middot; Images labeled <a href=\"http://{$CONF['geograph_domain']}/of/".urlencode($_GET['label'])."\">".he($_GET['label'])."</a><br/>";
	
	$label_id = $db->getOne("SELECT label_id FROM {$db->table_label} WHERE name = ".$db->quote($_GET['label']));
	if ($label_id) {
		$tables .= " INNER JOIN {$db->table_image_label} USING (image_id)";
		$where[] = "label_id = $label_id";
	}
}
if (!empty($_GET['tag']) && preg_match('/^[\w: -]+$/',$_GET['tag'])) {
	print "&middot; Images Tagged [<a href=\"http://{$CONF['geograph_domain']}/tagged/".urlencode($_GET['tag'])."\">".he($_GET['tag'])."</a>]<br/>";

	$where[] = "tags LIKE ".$db->quote('%_SEP_ '.$_GET['tag']." _SEP_%");
}
if (!empty($_GET['context']) && preg_match('/^[\w:, -]+$/',$_GET['context'])) {
	print "&middot; Geographical Context [<a href=\"http://{$CONF['geograph_domain']}/tagged/top:".urlencode($_GET['context'])."\">".he($_GET['context'])."</a>]<br/>";

	$where[] = "contexts LIKE ".$db->quote('%_SEP_ '.$_GET['context']." _SEP_%");
}

if (!empty($_GET['q']) && preg_match('/^\w+$/',$_GET['q'])) {
	print "&middot; Title containing {<a href=\"http://{$CONF['geograph_domain']}/of/title:".urlencode($_GET['q'])."\">".he($_GET['q'])."</a>}<br/>";
  $where[] = "title LIKE ".$db->quote('%'.$_GET['q'].'%');
}

$where = empty($where)?1:implode(" AND ",$where);
