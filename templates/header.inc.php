<html>
<head>
	<title>Portal</title>
	<link rel="stylesheet" type="text/css" href="templates/style.css" />
</head>
<body>
<header>
	<h2><a href="./"><? echo he($CONF['title']); ?></a></h2>
	<? if (!empty($CONF['subject']) && $CONF['subject'] != $CONF['title']) {
		print "<h4>A portal about ".he($CONF['subject'])."</h4>";
	} ?>
</header>
<div class="content">
