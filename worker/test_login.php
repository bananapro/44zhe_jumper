<?PHP

require './common.php';

if(!@$_GET)die('enter: type & uid');
$curl->cookie_path = COOKIE . $_GET['type'] .'/' . $_GET['uid'] . '.cookie';

if(!is_file($curl->cookie_path))die($curl->cookie_path.' do not exist!');
echo $curl->get('http://i.mizhe.com');
?>