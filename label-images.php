<?

include "includes/start.inc.php";

include "templates/header.inc.php";

if (!empty($_POST['image_id'])) {
	if (!empty($_POST['name'])) {
		$updates = array();
		$updates['name'] = $_POST['name'];
		$sql = $db->updates_to_dupinsert($db->table_label,$updates);
		$db->query($sql);
		$label_ids = array($db->insert_id());
	} else {
		$label_ids = $_POST['label_id'];
	}
	
	if (empty($label_ids)) {
		print "Please select a label!";
	} else {
		foreach ($label_ids as $label_id) {
			$updates = array();
			$updates['label_id'] = intval($label_id);
			$updates['image_id'] = intval($_POST['image_id']);
			$sql = $db->updates_to_insert($db->table_image_label,$updates);
			$db->query($sql);
		}
	}
}


$limit = 1;
$sort = 'i.created desc';
$where = array();
$order = "$sort, image_id DESC";

$where[] = "label_id IS NULL";


$labels = $db->getAll("SELECT * FROM {$db->table_label} ORDER BY name");

$where = empty($where)?1:implode(" AND ",$where);
foreach ($db->getAll("SELECT * FROM {$db->table_image} i LEFT JOIN {$db->table_image_label} l USING (image_id) WHERE $where ORDER BY $order LIMIT $limit") as $row) {
	?>
	<div class="imagerow">
		<div class=thumbmed>
			<a href="http://<? echo $CONF['geograph_domain']; ?>/photo/<? echo $row['image_id']; ?>" 
				title="<? echo he($row['title']); ?> by <? echo he($row['realname']); ?>">
				<img src="<? echo getGeographUrl($row['image_id'],$row['hash'],'med'); ?>"/>
			</a>
		</div>
		<div class="data">
			<a href="http://<? echo $CONF['geograph_domain']; ?>/photo/<? echo $row['image_id']; ?>"><? echo he($row['title']); ?></a> 
			by <a href="?user_id=<? echo he($row['user_id']); ?>"><? echo he($row['realname']); ?></a>
			for <a href="?gridref=<? echo he($row['grid_reference']); ?>"><? echo he($row['grid_reference']); ?></a>
		</div>
		<br class="clear"/>
	</div>
	
	<form method=post>
		<input type="hidden" name="image_id" value="<? echo $row['image_id']; ?>">
		
		<input type="text" name="name" placeholder="{enter new label here}" maxlength="64" size="40"> <input type=submit value="add label"><br/>
		or 
		<select name="label_id[]" size="<? echo min(30,count($labels)); ?>" multiple>
		<? foreach ($labels as $row) {
			printf('<option value="%s">%s</option>',$row['label_id'],he($row['name']));
		}?>
		</select>
				
		
	
	
	</form>
	
	
	
	<?
}

include "templates/footer.inc.php";

