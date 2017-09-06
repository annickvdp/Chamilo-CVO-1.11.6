<?
//--------------------------------------------------------------------------------------------------
//define include path (must be absolute for scheduled tasks)
//--------------------------------------------------------------------------------------------------
//$incpath=dirname(realpath('.')).'\\main\\inc\\';
$incpath='/home/cvo/var/public_html/chamilo2015/main/inc/';
//--------------------------------------------------------------------------------------------------
//include files
//--------------------------------------------------------------------------------------------------
require_once($incpath.'global.inc.php');
//--------------------------------------------------------------------------------------------------
//connect to the MSSQL database
//$mysql=mysqli_connect('127.0.0.1', 'cvoleuven', 'cvoleuven', 'dokeos_main') or die;
$mysql=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']) or die('mysql connect failed<br/>');
//$mssql=mssql_connect('nt6server2', 'dokeos', 'netneduts!mroftalp') or die; 
//$mssql=mssql_connect('192.168.0.2', 'dokeos', 'netneduts!mroftalp') or die; 

//last working connection to old mssql database
//$mssql=mssql_connect('databank.cvoleuven.be:1433', 'dokeos', 'netneduts!mroftalp') or die('mssql_connect failed<br/>');
//mssql_select_db('test3', $mssql);


//mssql_select_db('test3tijdelijk2', $mssql);
//--------------------------------------------------------------------------------------------------
function synclog_add($type, $action, $descr,$script)
{global $mysql;
 $sql='INSERT INTO mssql_synclog (`type`, `script`, `action`, `description`, `datetime`) VALUES ("'.strtoupper($type).'", "'.strtoupper($script).'","'.strtoupper($action).'" ,"'.htmlentities($descr).'", NOW());';
 $result=mysqli_query($mysql, $sql);
 return $result;
}
//--------------------------------------------------------------------------------------------------


if(!function_exists('clean')){
	function clean($str, $encode_ent = false) {
		$str  = @trim($str);
		if($encode_ent) {
			$str = htmlentities($str);
		}
		if(version_compare(phpversion(),'4.3.0') >= 0) {
			if(get_magic_quotes_gpc()) {
				$str = stripslashes($str);
			}
			if(@mysql_ping()) {
				$str = mysql_real_escape_string($str);
			}
			else {
				$str = addslashes($str);
			}
		}
		else {
			if(!get_magic_quotes_gpc()) {
				$str = addslashes($str);
			}
		}
		return $str;
	}
}


?>
