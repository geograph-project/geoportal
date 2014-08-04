<?

include "includes/start.inc.php";

if (empty($_SESSION['user_id'])) {
	$_SESSION['continue'] = "admin-moderate.php";
        header("Location: login.php");
        exit;
}
if (empty($_SESSION['moderator']) && empty($_SESSION['admin']))
	die('You need to be an admin or moderator to access this facility');

include "templates/header.inc.php";

//sets up $where etc... 
include "includes/filter.inc.php";

if (!empty($_GET['remove'])) {
	$updates = array();
	$updates['active'] = 'deleted';

	$sql = $db->updates_to_update($db->table_image,$updates,'image_id',intval($_GET['remove']));
	$db->query($sql);
}




print "<p>Latest $limit images...</p>";

foreach ($db->getAll("SELECT i.* FROM {$db->table_image} i $tables WHERE $where ORDER BY $order LIMIT $limit") as $row) {
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

			<br/><br/>
			<input placeholder="enter labels here">  <input type=button value="remove from portal" onclick="location.href='?remove=<? echo $row['image_id']; ?>';">
			
		</div>
		<br class="clear"/>
	</div>
	<?
}

include "templates/footer.inc.php";

