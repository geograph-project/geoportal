</div>
<footer>
<?
$template_links['about.php'] = 'about';

foreach ($template_links as $link => $html) {
	print "<a href=\"$link\">".he($html)."</a>";
}
?>
	<? if (!empty($CONF['credit'])) { ?>
		<credit>Portal created by <? echo $CONF['credit']; ?></credit>
	<? } ?>
</footer>
<div class="footertext">
	Website based on <a href="https://github.com/barryhunter/geoportal">geoportal</a>, drawing images from <a href="http://<? echo $CONF['geograph_domain']; ?>/"><? echo $CONF['geograph_domain']; ?></a>. All images are creative commons licenced.
</div>
</body>
</html>
