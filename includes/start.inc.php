<?

include "config.php";

include "includes/functions.inc.php";
include "includes/database.class.php";
include "includes/token.class.php";

$db = new Database;
$db->connect();

if (!($result = mysql_query("SELECT * FROM {$db->table_configuration}", $db->db))) {
	header("Location:./setup.php");
	exit;
}

while($result && ($row = mysql_fetch_assoc($result))) {
	$CONF[$row['name']] = $row['value'];
}

init_session();

