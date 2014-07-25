<?

include "includes/start.inc.php";

include "templates/header.inc.php";

if (!empty($CONF['intro'])) {
	print "<div class=intro>".nl2br(he($CONF['intro']))."</div>";
}
if (!empty($CONF['submission_prompt'])) {
	print "<div class=submission_prompt>".nl2br(he($CONF['submission_prompt']))." <a href=\"http://{$CONF['geograph_domain']}/submit.php\">Open Geograph Submission Process</a></div>";
}

        if (!empty($CONF['query'])) { ?>
		View <? echo $db->getOne("SELECT MAX(total) FROM {$db->table_fetcher}"); ?> Images on Geograph: 
		<a href="http://<? echo $CONF['geograph_domain']; ?>/of/<? echo urlencode($CONF['query']); ?>">Search</a>
		<a href="http://<? echo $CONF['geograph_domain']; ?>/browser/#/q=<? echo urlencode($CONF['query']); ?>/">Browser</a>
		<a href="http://<? echo $CONF['geograph_domain']; ?>/browser/#/q=<? echo urlencode($CONF['query']); ?>/display=map_dots/pagesize=1000">Map</a>
	<? } ?>
        <? if (!empty($CONF['tag'])) { ?>
                <a href="http://<? echo $CONF['geograph_domain']; ?>/stuff/tagmap.php?tag=<? echo he($CONF['tag']); ?>">Tag Map</a>
        <? }

if ($squares) { //set by header.php!
	$row = $db->getRow("SELECT COUNT(*) AS squares, SUM(images>0) AS photographed FROM {$db->table_square}");

	if (!empty($row['squares'])) {
		print "<div class=statbar title=\"".he($CONF['square_source'])."\">";

		$row['percentage'] = sprintf('%.1f',$row['photographed']/$row['squares']*100);
		print "Total Squares: ".dis($row['squares']).", of which ".dis($row['photographed'])." have photographs; which is a coverage of <b>{$row['percentage']}%</b>";
		print " <a href=statistics.php>MORE...</a>";
		print "</div>";
	}

	echo "<a href=\"coveragemap.php\"><img src=\"coveragemap.png.php\" width=300 align=right></a>";
}




if (false) {
print "<div class=sidebar><p>Labels... (<a href=\"labelled.php\">more</a>)</p>";
print "<ol>";
foreach ($db->getAll("SELECT name,COUNT(*) images FROM {$db->table_label} INNER JOIN {$db->table_image_label} USING (label_id) GROUP BY label_id ORDER BY orderby LIMIT 50") as $row) {
	print "<li value=\"{$row['images']}\"><a href=\"images.php?label=".urlencode($row['name'])."\">".he($row['name'])."</a></li>";
}
print "</ol>";
print "</div>";
}

print "<div class=sidebar><p>Countries...</p>";
print "<ol class=stats>";
foreach ($db->getAll("SELECT country,COUNT(*) images FROM {$db->table_image} GROUP BY country ORDER BY images LIMIT 50") as $row) {
	print "<li value=\"{$row['images']}\"><a href=\"images.php?country=".urlencode($row['country'])."\">".he($row['country'])."</a></li>";
}
print "</ol>";
print "</div>";


print "<p>A sampling of images... (<a href=\"images.php\">more</a>)</p>";

foreach ($db->getAll("SELECT * FROM {$db->table_image} WHERE active != 'deleted' ORDER BY sequence LIMIT 16") as $row) {
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

print "<div><p>Decades ... ";
foreach ($db->getAll("SELECT substring(taken,1,3) as decade,COUNT(*) images FROM {$db->table_image} GROUP BY substring(taken,1,3) LIMIT 50") as $row) {
	print "<a href=\"images.php?taken=".urlencode($row['decade'])."\">".he($row['decade'])."0s</a>({$row['images']}) &middot; ";
}
print "</ol>";
print "</div>";


include "templates/footer.inc.php";

