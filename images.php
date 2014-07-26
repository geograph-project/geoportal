<?

include "includes/start.inc.php";

include "templates/header.inc.php";

print '<h3><b>View Images</b></h3>';

include "includes/filter.inc.php";

$rows = $db->getAll("SELECT i.* FROM {$db->table_image} i $tables WHERE $where ORDER BY $order LIMIT $limit");

if (empty($rows)) {
	print "<p>No Matching Images found, maybe no suitable images have been submitted to Geograph. Or maybe simply the filtered used to build this portal simply dont cover them.</p>";

	print "<p>If you have any matching images, be sure to <a href=submit.php>submit them</a>! Thank you.</p>";

} else if (count($rows) < $limit) {
	$limit = count($rows);
	print "<p>Matching $limit image(s)...</p>";	
} else if (!empty($where)) {
	print "<p>Latest matching $limit images...</p>";
} else {
	print "<p>Latest $limit images...</p>";
}

foreach ($rows as $row) {
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
	print "<hr/><a href=?>View All Images</a>";


include "templates/footer.inc.php";

