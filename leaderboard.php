<?

include "includes/start.inc.php";

include "templates/header.inc.php";

print '<h3>Leaderboard</h3>';

        $row = $db->getRow("SELECT COUNT(*) AS squares, SUM(images>0) AS photographed FROM {$db->table_square}");

        if (!empty($row['squares'])) {
                print "<div class=statbar>";
                $row['percentage'] = sprintf('%.1f',$row['photographed']/$row['squares']*100);
                print "Total Squares: ".dis($row['squares']).", of which ".dis($row['photographed'])." have photographs; which is a coverage of <b>{$row['percentage']}%</b>";
                print " <a href=statistics.php>MORE...</a>";
                if (!empty($CONF['square_source'])) {
                        print "<div class=source>".nl2br(he($CONF['square_source']))."</div>";
                }
                print "</div>";
        }
	
?>
<p>Points are awarded for the first images submitted to one of the squares being tracked. Can also view a images leaderboard <a href="breakdown.php?by=user_id">via the breakdown page</a></p>
<table>
<tr>
	<th>Rank</th>
	<th>Points</th>
	<th>Contributor</th>
</tr>
<?

$rows = $db->getAll("SELECT user_id,realname,COUNT(*) AS images 
		FROM {$db->table_image} i INNER JOIN {$db->table_square} s USING (grid_reference) WHERE point = 1 AND active != 'deleted'
		GROUP BY user_id ORDER BY images DESC LIMIT 500");

	$rank = 0;
	$last = 99999999999999999;
	foreach ($rows as $row) {
		if ($last > $row['images'])
			$rank++;
		?>
		<tr>
			<td align=right><? echo ($last > $row['images'])?"$rank.":"&quot;"; ?></td>
			<td align=right><? echo $row['images']; ?></td>
			<td><a href="images.php?<? echo http_build_query($_GET)."&amp;$key=".urlencode($row['user_id']); ?>"><? echo he($row['realname']); ?></a></td>
		</tr>
		<?
		$last = $row['images'];
	}
	print "</table>";

include "templates/footer.inc.php";

