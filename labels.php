<?

include "includes/start.inc.php";

include "templates/header.inc.php";

$last = -1;
foreach ($db->getAll("SELECT * 
FROM {$db->table_image} i
INNER JOIN {$db->table_image_label} USING (image_id)
INNER JOIN {$db->table_label} USING (label_id)
ORDER BY name,RAND() LIMIT 40") as $row) {

	if ($last != $row['label_id']) {
		print "<h4 class=\"group\">".he($row['name'])."</h4>";
		$last = $row['label_id'];
	}

	?>
	<div class=thumb>
		<a href="http://<? echo $CONF['geograph_domain']; ?>/photo/<? echo $row['image_id']; ?>" 
			title="<? echo he($row['title']); ?> by <? echo he($row['realname']); ?>">
			<img src="<? echo getGeographUrl($row['image_id'],$row['hash'],'small'); ?>"/>
		</a>
	</div>
	<?
}

?>
<br class="clear"/>
<?


include "templates/footer.inc.php";

