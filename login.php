<?

require_once('includes/start.inc.php');

require_once('includes/token.class.php');

if (empty($CONF['geograph_magic'])) {
	die("Magic not setup. Unable to continue. Need to enter your Geograph API details on the admin setup page");
}

init_session();

if (!empty($_GET['logout'])) {
	$_SESSION = array();
	 header("Location: ./");
	exit;
}

$token=new Token;
$token->magic = $CONF['geograph_magic'];

if (isset($_GET['t']) && $token->parse($_GET['t']) && $token->hasValue('k') && $token->getValue('k') == $CONF['geograph_apikey']) {
    if ($token->hasValue('user_id') && $token->getValue('user_id') != '' ) {
        #if you get back a user_id you can be certain that they logged in on that account
        
        $updates= array();

        $_SESSION['user_id'] = $updates['user_id'] = $token->getValue('user_id');
        $_SESSION['realname'] = $updates['realname'] = $realname=$token->getValue('realname');
        $updates['nickname'] = $nickname=$token->getValue('nickname');
        
        $updates['loggedin'] = "NOW()";

        $sql = $db->updates_to_insertupdate($db->table_user,$updates);
	$db->query($sql);

	$rights = $db->getOne("SELECT rights FROM {$db->table_user} WHERE user_id = ".$_SESSION['user_id']);

	$_SESSION['basic'] = (strpos($rights,'basic') !== FALSE);

	if (!$_SESSION['basic'])
		die("unable to continue at this time");

	$_SESSION['admin'] = (strpos($rights,'admin') !== FALSE);
	$_SESSION['moderator'] = (strpos($rights,'moderator') !== FALSE);
	
	if (!empty($_SESSION['continue'])) {
		header("Location: {$_SESSION['continue']}");
	} else {
		header("Location: ./");
	}
        
    } else {
        die("login failed");
    }

} else {

	$login_url = 'http://'.$CONF['geograph_domain'].'/auth.php?a='.$CONF['geograph_accesskey'];

	$token->setValue("action", 'authenticate');
	$token->setValue("callback", $CONF['url']."login.php"); //full-path to callback.php on your server
	$login_url .= '&t='.$token->getToken();

	header ("Location: $login_url");
	print "<a href=\"".he($login_url)."\">Login via Geograph</a>";
}


