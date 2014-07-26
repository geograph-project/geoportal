<?

$squares = $db->getOne("SELECT COUNT(*) FROM {$db->table_square}");

//todo, this rather basic, actully we want to trip it down to jsut filters!
$extra = '';
if (!empty($_GET))
	$extra = '?'.http_build_query($_GET);


$template_links = array();
if (basename($_SERVER['PHP_SELF']) != 'index.php')
	$template_links['./'] = 'back to homepage';

if (empty($_GET) && !empty($CONF['query']))
	$template_links['browser.php'] = 'browser';
else
	$template_links['images.php'.$extra] = 'images';
$template_links['breakdown.php'.$extra] = 'breakdown';
$template_links['heatmap.php'.$extra] = 'heat map';

if ($squares)
	$template_links['statistics.php'] = 'statistics';
$template_links['tags.php'] = 'tags';
$template_links['leaderboard.php'] = 'leaderboard';

//$template_links['labelled.php'] = 'labelled';

if ($squares)
	$template_links['coveragemap.php'] = 'coverage map';

if (!empty($CONF['submission_prompt']))
	$template_links['submit.php'] = 'submit';

?>
<html>
<head>
	<title><? echo $CONF['title']; ?></title>
	<link rel="stylesheet" type="text/css" href="templates/style.css" />
	<? if (!empty($_GET)) {
				print '<link rel="canonical" href="'.$CONF['url'].basename($_SERVER['PHP_SELF']).'?'.http_build_query($_GET).'"/>';
	} ?>
</head>
<body>
<header>
	<div class="login">
	<? if (!empty($_SESSION['user_id'])) { ?>
		Welcome <? echo he($_SESSION['realname']);
		if (!empty($_SESSION['admin'])) {
			print ", <a href=admin.php>Admin</a>";
		}
		?>, <a href="login.php?logout=true">Logout</a>
	<? } else { ?>
		<a href="login.php">Login</a>
	<? } ?>
	</div>
	<h2><a href="./"><? echo he($CONF['title']); ?></a></h2>
	<? if (!empty($CONF['subject']) && $CONF['subject'] != $CONF['title']) {
		print "<h4>A portal about ".he($CONF['subject'])."</h4>";
	} ?>
	<div class="tabs">
		<? foreach ($template_links as $link => $html) {
			if (basename($_SERVER['PHP_SELF']) == $link) {
			        print "<a href=\"$link\" class=\"selected\">".he($html)."</a>";
			} else
			        print "<a href=\"$link\">".he($html)."</a>";
		} ?>
	</div>
</header>
<div class="content">
