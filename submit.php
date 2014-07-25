<?

include "includes/start.inc.php";

include "templates/header.inc.php";

if (!empty($CONF['submission_prompt'])) {
        print "<div class=warning>".nl2br(he($CONF['submission_prompt']))." <a href=\"http://{$CONF['geograph_domain']}/submit.php\">Open Geograph Submission Process</a> or we've included it below in a frame for convenience.</div>";
}

?>
<iframe src="http://<? echo $CONF['geograph_domain']; ?>/submit.php" style="border:0;width:100%;height:700px"></iframe>

<?

include "templates/footer.inc.php";

