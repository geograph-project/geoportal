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



print "<p>Latest $limit images...</p>";


$where = empty($where)?1:implode(" AND ",$where);
foreach ($db->getAll("SELECT * FROM {$db->table_image} WHERE $where ORDER BY $order LIMIT $limit") as $row) {
	?>
	<div class="imagerow">
		<div class=thumb>
			<a href="http://<? echo $CONF['geograph_domain']; ?>/photo/<? echo $row['image_id']; ?>" 
				title="<? echo he($row['title']); ?> by <? echo he($row['realname']); ?>">
				<img src="<? echo getGeographUrl($row['image_id'],$row['hash'],'small'); ?>"/>
			</a>
		</div>
		<div class="data">
			<a href="http://<? echo $CONF['geograph_domain']; ?>/photo/<? echo $row['image_id']; ?>"><? echo he($row['title']); ?></a> 
			by <a href="?user_id=<? echo he($row['user_id']); ?>"><? echo he($row['realname']); ?></a>
			for <a href="?gridref=<? echo he($row['grid_reference']); ?>"><? echo he($row['grid_reference']); ?></a>
			taken <a href="?taken=<? echo he($row['taken']); ?>"><? echo he($row['taken']); ?></a>
		</div>
		<br class="clear"/>
	</div>
	<?
}

if (!empty($where)) 
	print "<a href=?>View All Images</a>";


include "templates/footer.inc.php";

