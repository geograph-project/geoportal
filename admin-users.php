<?

include "includes/start.inc.php";

if (empty($_SESSION['user_id']) || empty($_SESSION['admin']) ) {
	header("Location: login.php");
	exit;
}

include "templates/header.inc.php";

if (!empty($_POST)) {
	if (!empty($_POST['invoke'])) {
		foreach ($_POST['invoke'] as $user_id => $row) {
			if (!is_numeric($user_id)) 
				die("invalid user");
			foreach ($row as $right => $label) {
				if (!ctype_alpha($right)) 
					die("invalid right");
				$sql = "UPDATE {$db->table_user} SET rights = CONCAT(rights,',$right') WHERE user_id = ".intval($user_id);
				$db->query($sql);
			}
		}
	}
	if (!empty($_POST['revoke'])) {
		foreach ($_POST['revoke'] as $user_id => $row) {
			if (!is_numeric($user_id)) 
				die("invalid user");
			foreach ($row as $right => $label) {
				if (!ctype_alpha($right)) 
					die("invalid right");
				$sql = "UPDATE {$db->table_user} SET rights = REPLACE(rights,'$right','') WHERE user_id = ".intval($user_id);
				$db->query($sql);
			}
		}
	}
}
?>
<h3>User Admin</h3>

<form method=post>
<table cellspacing=0 cellpadding=4 border=1>
	<tr>
		<th>user_id</th>
		<th>realname</th>
		<th>nickname</th>
		<th>first joined</th>
		<th>last login</th>
		<th>rights</th>
		<th>actions</th>
	</tr>
<?

$rows = $db->getAll("SELECT * FROM {$db->table_user}");

foreach ($rows as $row) {
	print "<tr>";
	print "<td align=right>".he($row['user_id'])."</td>";
	print "<td>".he($row['realname'])."</td>";
	print "<td>".he($row['nickname'])."</td>";
	print "<td>".he($row['created'])."</td>";
	print "<td>".he($row['loggedin'])."</td>";
	print "<td>".he($row['rights'])."</td>";
	print "<td>";
	if (strpos($row['rights'],'basic') !== FALSE) {
		print "<input type=submit name=\"revoke[{$row['user_id']}][basic]\" value=\"ban user\">";

		if (strpos($row['rights'],'admin') !== FALSE) {
	                print "<input type=submit name=\"revoke[{$row['user_id']}][admin]\" value=\"remove admin\">";
		} else {
	                print "<input type=submit name=\"invoke[{$row['user_id']}][admin]\" value=\"make admin\">";
		}
		if (strpos($row['rights'],'moderator') !== FALSE) {
	                print "<input type=submit name=\"revoke[{$row['user_id']}][moderator]\" value=\"remove moderator\">";
		} else {
	                print "<input type=submit name=\"invoke[{$row['user_id']}][moderator]\" value=\"make moderator\">";
		}
	} else {
		print "<input type=submit name=\"invoke[{$row['user_id']}][basic]\" value=\"add again\">";
	}
	print "</tr>";
}

print "</table></form>";

include "templates/footer.inc.php";

