<?

include "includes/start.inc.php";

include "templates/header.inc.php";

if (!empty($CONF['submission_prompt'])) {
        print "<div class=warning>Below is the Geograph Browser application, preloaded with a query that shows <b>roughly</b> the same images as this portal.</div>";
}

?>
<iframe src="http://<? echo $CONF['geograph_domain']; ?>/browser/#/q=<? echo urlencode($CONF['query']); ?>/" style="border:0;width:100%;height:700px"></iframe>

<?

include "templates/footer.inc.php";

