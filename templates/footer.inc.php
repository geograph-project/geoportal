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
</body>
</html>
