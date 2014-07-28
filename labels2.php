<?

include "includes/start.inc.php";

include "templates/header.inc.php";


foreach ($db->getAll("SELECT * FROM {$db->table_label}") as $label) {

	$q = $db->quote("%{$label['name']}%");
	$fields = array('title','tags','category');
	$where = "`".implode("` LIKE $q OR `",$fields)."` LIKE $q";

	$images = $db->getAll("SELECT * 
	FROM {$db->table_image} i
	WHERE active != 'deleted' AND ($where)
	ORDER BY RAND() LIMIT 6");

	if (empty($images))
		continue;


	print "<h4 class=\"group\">".he($label['name'])."</h4>";

	foreach ($images as $row) {

		?>
		<div class=thumb>
			<a href="http://<? echo $CONF['geograph_domain']; ?>/photo/<? echo $row['image_id']; ?>" 
				title="<? echo he($row['title']); ?> by <? echo he($row['realname']); ?>">
				<img src="<? echo getGeographUrl($row['image_id'],$row['hash'],'small'); ?>"/>
			</a>
		</div>
		<?
	}
}

?>
<br class="clear"/>
<?


include "templates/footer.inc.php";

