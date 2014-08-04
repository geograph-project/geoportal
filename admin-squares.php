<?

include "includes/start.inc.php";

if (empty($_SESSION['user_id'])) {
	$_SESSION['continue'] = "admin-squares.php";
        header("Location: login.php");
        exit;
}
if (empty($_SESSION['admin']))
	die('You need to be an admin to access this facility');

include "templates/header.inc.php";

if (!empty($_POST['content'])) {
        include "includes/conversionslatlong.class.php";
        $conv = new ConversionsLatLong();

	$lines = explode("\n",str_replace("\r",'',$_POST['content']));

	foreach ($lines as $idx => $line) {
		if (preg_match('/^(\s*)(.+?)( *\[\d+\])?\s*$/',$line,$m)) {
			$gridref = strtoupper($m[2]);
			$id = trim($m[3],'[] ');

			$updates = array();
			$updates['grid_reference'] = $gridref;
			if ($id) {
				$updates['square_id'] = $id;
			} elseif (strlen($updates['grid_reference']) == 6) {
				list($e,$n) = $conv->gridref_to_osgb36($updates['grid_reference']);
				if ($e && $n) {
					$updates['e'] = $e; 
					$updates['n'] = $n;
				}
			} elseif (strlen($updates['grid_reference']) == 5) {
				//todo! need a gridref_to_irish() function!
			}
			if (!empty($updates['e'])) {
				$func = (strlen($updates['grid_reference'])==5)?'irish_to_wgs84':'osgb36_to_wgs84';
	        	        list($updates['wgs84_lat'],$updates['wgs84_long']) = $conv->{$func}($updates['e'],$updates['n']);
			}
			$sql = $db->updates_to_insertupdate($db->table_square,$updates);
			$db->query($sql);
			//if (!$id)
			//	$id = $db->insert_id();
		}
	}
}

?>
<form method=post>

Use this page to setup a list of squares that this portal covers. Should be ONE gridsquare per line, in 4fig format, eg SH5045, J4542 or TQ1234 - no spaces. 

<ul>
	<li>Add new Square as needed (omit the id at the end)</li>
	<li>deleting is not yet supported</li>
</ul>
<textarea name="content" rows="50" cols="80"><?

	$rows = $db->getAll("SELECT * FROM {$db->table_square}");
	if (!empty($rows))
		foreach ($rows as $row) {
			print he($row['grid_reference']).' ['.$row['square_id'].']'."\n";
		}

?></textarea>
<input type=submit value="Save Changes">
</form>
<?
include "templates/footer.inc.php";

