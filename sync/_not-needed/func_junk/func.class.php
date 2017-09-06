<?
//--------------------------------------------------------------------------------------------------
//define include path (must be absolute for scheduled tasks)
//--------------------------------------------------------------------------------------------------
//$incpath=dirname(realpath('.')).'/main/inc/';
$incpath='C:/Inetpub/wwwroot/Dokeos/main/inc/';
//--------------------------------------------------------------------------------------------------
//include files
//--------------------------------------------------------------------------------------------------
require_once($incpath.'global.inc.php');
require_once($incpath.'lib/main_api.lib.php');
require_once($incpath.'lib/classmanager.lib.php');
//--------------------------------------------------------------------------------------------------
//define objects
//--------------------------------------------------------------------------------------------------
$class=new ClassManager();
$dbs=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']);
//--------------------------------------------------------------------------------------------------
function synclog_add_class($type, $action, $descr)
{global $dbs;
 $sql='INSERT INTO mssql_synclog (`type`, `script`, `action`, `description`, `datetime`) VALUES ("'.strtoupper($type).'", "CLASS","'.strtoupper($action).'" ,"'.htmlentities($descr).'", NOW());';
 $result=mysqli_query($dbs, $sql);
 return $result;
}
//--------------------------------------------------------------------------------------------------
//add user to class
function user2class_add($course=NULL)
{global $class, $dbs;
 $msg_arr=array();
 $sql='SELECT t1.* , t2.user_id, t3.id AS class_id FROM mssql_class AS t1 INNER JOIN user AS t2 ON t1.username = t2.username INNER JOIN class AS t3 ON t1.classname = t3.name where ';
  if ($course!=NULL){$sql.=' t3.id = '.$course.' AND ';} //this is if course code is specified - used for activation script
  $sql.=' t2.user_id NOT IN (SELECT user_id FROM class_user WHERE class_id=t3.id) ORDER BY t3.id;';
  
 print '<Br/><br/>';
 $rcs=mysqli_query($dbs, $sql) or print ('user2class_add query: '.$sql);
 $counter=0;
 while($row = mysqli_fetch_array($rcs))
 {
  $user_id=(int)$row['user_id'];
  $class_id=(int)($row['class_id']);
  $addres=$class->add_user($user_id, $class_id);
  if(!addres)
  {synclog_add_class('ERROR', 'USER SUBSCRIBE', 'User with user_id "'.$user_id.'" failed to subscribe in class "'.$class_id.'"');
  }
  else
  {$msg_arr[$class_id]=$msg_arr[$class_id]+1;
  }
 }
 foreach($msg_arr as $class=>$counter)
 {synclog_add_class('INFO', 'USER SUBSCRIBE', $counter.' users subscribed in class "'.$class.'"');
 }
}
//--------------------------------------------------------------------------------------------------
//delete user to class
function user2class_del()
{global $class, $dbs;
 $msg_arr=array();
 $sql='SELECT t2.user_id, t3.id AS class_id FROM class_user AS t1 INNER JOIN user AS t2 ON t1.user_id = t2.user_id INNER JOIN class AS t3 ON t1.class_id = t3.id WHERE t2.username NOT IN (SELECT username FROM mssql_class WHERE classname=t3.name) ORDER BY t3.id;';
 $rcs=mysqli_query($dbs, $sql);
 $counter=0;
 while($row = mysqli_fetch_array($rcs))
 {
  $user_id=(int)$row['user_id'];
  $class_id=(int)($row['class_id']);
  $delres=$class->unsubscribe_user($user_id, $class_id);
  if(!addres)
  {synclog_add_class('ERROR', 'USER UNSUBSCRIBE', 'User with user_id "'.$user_id.'" failed to unsubscribe in class "'.$class_id.'"');
  }
  else
  {$msg_arr[$class_id]=$msg_arr[$class_id]+1;
  }
 }
 foreach($msg_arr as $class=>$counter)
 {synclog_add_class('INFO', 'USER UNSUBSCRIBE', $counter.' users unsubscribed in class "'.$class.'"');
 }
}
//--------------------------------------------------------------------------------------------------
function user_deactivate()
{global $dbs;
 //deactivate active users without any subscription in a course and that are not teachers
 $sql='SELECT t1.user_id FROM user AS t1 LEFT JOIN course_rel_user AS t2 ON t1.user_id = t2.user_id '.
      'WHERE t1.status=5 AND t1.active=1 AND t2.user_id IS NULL;';
 $res=mysqli_query($dbs, $sql);
 $cnt=0;
 while($row = mysqli_fetch_array($res))
 {
  $sql='UPDATE user SET active=0 WHERE user_id='.$row['user_id'].';';
  $val=api_sql_query($sql,__FILE__,__LINE__);
  if($val){$cnt++;}
 }
 if($cnt>0){synclog_add_class('INFO', 'USER DEACTIVATE', $cnt.' user(s) without a course subscription deactivated');}
}
//--------------------------------------------------------------------------------------------------
function user_activate()
{global $dbs;
 //activate deactive users with any subscription in a course and that are not teachers
 $sql='SELECT t1.user_id FROM user AS t1 LEFT JOIN course_rel_user AS t2 ON t1.user_id = t2.user_id '.
      'WHERE t1.status=5 AND t1.active=0 AND t2.user_id IS NOT NULL;';
 $res=mysqli_query($dbs, $sql);
 $cnt=0;
 while($row = mysqli_fetch_array($res))
 {
  $sql='UPDATE user SET active=1 WHERE user_id='.$row['user_id'].';';
  $val=api_sql_query($sql,__FILE__,__LINE__);
  if($val){$cnt++;}
 }
 if($cnt>0){synclog_add_class('INFO', 'USER ACTIVATE', $cnt.' user(s) with a course subscription activated');}
}
//--------------------------------------------------------------------------------------------------
//echo('<br>User2class proces terminated');
//--------------------------------------------------------------------------------------------------
?>

