<?

include "includes/start.inc.php";

include "templates/header.inc.php";

if (!empty($CONF['intro'])) {
	print "<div class=intro>".he($CONF['intro'])."</div>";
}

print "<hr/>";

print "<p>Latest 10 images...</p>";

foreach ($db->getAll("SELECT * FROM {$db->table_image} ORDER BY created DESC, RAND() LIMIT 10") as $row) {
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

