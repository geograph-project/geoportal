<?

include "includes/start.inc.php";

include "templates/header.inc.php";

$images = $db->getOne("SELECT COUNT(*) FROM {$db->table_image} WHERE active != 'deleted'");

?>

<h3>About this website</h3>

<?

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

?>

<p>This website is a mini-website created as a way to view a subset of images from <a href="http://<? echo $CONF['geograph_domain']; ?>/"><? echo $CONF['geograph_domain']; ?></a>.</p>

<? if (!empty($CONF['subject']) && $CONF['subject'] != $CONF['title']) { ?>
	<p>This particular website is about <? echo he($CONF['subject']); ?>, and contains <? echo dis($images); ?> images in total. Use the links above to explore the images in more details. </p>
<? }

if (!empty($CONF['query'])) { ?>
	<p>Images are loaded by performing a keyword search for <a href="http://<? echo $CONF['geograph_domain']; ?>/of/<? echo urlencode($CONF['query']); ?>"><? echo he($CONF['query']); ?></a>,
	 and last time we checked found <? echo dis($db->getOne("SELECT MAX(total) FROM {$db->table_fetcher}")); ?> images. However not all may be shown here, 
	 as we attempt top remove false positives as we find them.</p>
<? }

if (!empty($CONF['submission_prompt'])) {
        print "<p>".nl2br(he($CONF['submission_prompt']))." <a href=\"http://{$CONF['geograph_domain']}/submit.php\">Open Geograph Submission Process</a><p>";
}


if (!empty($CONF['credit'])) { ?>
        <p><b>Portal created by <? echo $CONF['credit']; ?></b></p>
<? }

?>
<hr/>
<?

if (!empty($row['squares'])) {
        print "<div class=statbar>";

        if (!empty($CONF['square_source'])) {
                print "<div class=source>".nl2br(he($CONF['square_source']))."</div>";
        }

        print "</div>";
}

?>

<br style=clear:both>

<?

include "templates/footer.inc.php";

