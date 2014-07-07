</div>
<footer>
	<a href="./">back to homepage</a>
	<a href="images.php">all images</a>
	<a href="breakdown.php">statistics</a>
	<a href="labelled.php">labelled</a>
	<? if (!empty($CONF['credit'])) { ?>
		<i>Portal created by <? echo $CONF['credit']; ?></i>
	<? } ?>
</footer>
</body>
</html>