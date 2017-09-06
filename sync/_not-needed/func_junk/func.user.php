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
require_once($incpath.'lib/usermanager.lib.php');
//--------------------------------------------------------------------------------------------------
//define objects
//--------------------------------------------------------------------------------------------------
$user=new UserManager();
$dbs=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']);
//--------------------------------------------------------------------------------------------------
function synclog_add_user($type, $action, $descr)
{global $dbs;
 $sql='INSERT INTO mssql_synclog (`type`, `script`, `action`, `description`, `datetime`) VALUES ("'.strtoupper($type).'", "USER", "'.strtoupper($action).'" ,"'.htmlentities($descr).'", NOW());';
 $result=mysqli_query($dbs, $sql);
 return $result;
}
//--------------------------------------------------------------------------------------------------
//delete existing users that not exist in the mssql database
function user_del()
{global $user, $dbs;
 $sql='SELECT t2.user_id, t2.username FROM mssql_user AS t1 RIGHT JOIN user AS t2 ON t1.username=t2.username WHERE t1.username is null AND t2.user_id NOT IN (SELECT user_id FROM admin);'; //if platform admin,  no deletion
 $rcs=mysqli_query($dbs, $sql);
 $counter=0;
 while($row = mysqli_fetch_array($rcs))
 {
  $user_id=(int)$row['user_id'];
  $user_name=strtolower($row['username']);
  $delres=$user->delete_user($user_id);
  if(!delres){synclog_add_user('ERROR', 'USER DELETE', 'User with username "'.$user_name.'" failed to delete');}
  else{$counter++;}
 }
 if($counter>0){synclog_add_user('INFO', 'USER DELETE', $counter.' users deleted');}
}
//--------------------------------------------------------------------------------------------------
//create non-existing users that exist in the mssql database
function user_add()
{global $user, $dbs;
 $sql='SELECT t1.* FROM mssql_user AS t1 LEFT JOIN user AS t2 ON t1.username=t2.username WHERE t2.username is null;';
 $rcs=mysqli_query($dbs, $sql);
 $counter=0;
 while($row = mysqli_fetch_array($rcs))
 {
  if(strtolower($row['username'])!='hubert.bergs'){ // @todo  - - delete this if statement
	  $user_name=strtolower($row['username']);
	  $addres=$user->create_user($row['firstname'], $row['lastname'], $row['status'], $row['email'], $user_name, $row['password'], '', $row['language']);
	  if(!addres){synclog_add_user('ERROR', 'USER ADD', 'User with username "'.$user_name.'" failed to create');}
	  else
	  {mysqli_query($dbs, 'UPDATE user SET code="'.$row['code'].'" WHERE username="'.$user_name.'";');
	   $counter++;
	  }
	}
 }
 if($counter>0){synclog_add_user('INFO', 'USER ADD', $counter.' users created');}
}
//--------------------------------------------------------------------------------------------------
function user_sync()
{global $dbs;
 $sql='SELECT t1.lastname, t1.firstname, t1.username, t1.code AS code1, t2.code AS code2 '.
      'FROM mssql_user AS t1 INNER JOIN user AS t2 ON t1.code=t2.code '.
	  'WHERE t1.lastname<>t2.lastname OR t1.firstname<>t2.firstname OR t1.username<>t2.username AND t2.code IS NOT NULL '.
	  'AND t2.code<>"";';
 $res=mysqli_query($dbs, $sql);
 $fields    =mysqli_fetch_fields($res);
 $fieldnames=array(); 
 foreach ($fields as $val)
 {$fieldnames[]=$val->name;
 }
 if(in_array('code1', $fieldnames) && in_array('code2', $fieldnames))
 {
  while($row=mysqli_fetch_array($res))
  {$updsql='UPDATE user SET lastname="'.$row['lastname'].'", firstname="'.$row['firstname'].'", username="'.$row['username'].'" '.
           'WHERE code="'.$row['code2'].'";';
   $updres=mysqli_query($dbs, $updsql);
   if($updres){synclog_add_user('INFO', 'USER SYNC', 'User with code "'.$row['code2'].'" updated');}
   else{synclog_add_user('ERROR', 'USER SYNC', 'User width code "'.$row['code2'].'" failed to update');}
  }
 }
 else{synclog_add_user('ERROR', 'USER SYNC', 'Code fields are not configured in the DB-tables');}
}
//--------------------------------------------------------------------------------------------------
?>