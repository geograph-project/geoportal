<?

include "includes/start.inc.php";

if (empty($_SESSION['user_id']) || (empty($_SESSION['admin']) && empty($_SESSION['moderator'])) ) {
	header("Location: login.php");
	exit;	
}

include "templates/header.inc.php";


if (!empty($_SESSION['admin'])) {
?>
<h4>Admin Tools</h4>
<ul>
	<li><a href="admin-setup.php">Portal Configuration</a></li>
	<li><a href="admin-users.php">Setup Users</a></li>
	<li><a href="admin-squares.php">Setup Squares for this Portal</a></li>
	<li><a href="ping.php">Fetch Images Now</a></li>
</ul>
<?
}

if (!empty($_SESSION['moderator'])) {
?>
<h4>Moderator Tools</h4>
<ul>
	<li><a href="admin-moderate.php">Moderate New Images</a></li>
	<li><a href="admin-label-image.php">Label new Images</a></li>
	<li><a href="admin-labels.php">Configure Labels</a></li>
</ul>

<?
}

include "templates/footer.inc.php";

