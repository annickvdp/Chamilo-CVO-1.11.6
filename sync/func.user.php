<?
//--------------------------------------------------------------------------------------------------
//define include path (must be absolute for scheduled tasks)
//--------------------------------------------------------------------------------------------------
//$incpath=dirname(realpath('.')).'/main/inc/';
//$incpath='C:/Inetpub/wwwroot/Dokeos/main/inc/';
//--------------------------------------------------------------------------------------------------
//include files
//--------------------------------------------------------------------------------------------------
//require_once($incpath.'global.inc.php');
require_once('inc.sync.php');
require_once($incpath.'lib/main_api.lib.php');
require_once($incpath.'lib/usermanager.lib.php');
//--------------------------------------------------------------------------------------------------
//define objects
//--------------------------------------------------------------------------------------------------
$user=new UserManager();
//$mysql=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']);
//--------------------------------------------------------------------------------------------------
/*function synclog_add_user($type, $action, $descr)
{global $mysql;
 $sql='INSERT INTO mssql_synclog (`type`, `script`, `action`, `description`, `datetime`) VALUES ("'.strtoupper($type).'", "USER", "'.strtoupper($action).'" ,"'.htmlentities($descr).'", NOW());';
 $result=mysqli_query($mysql, $sql);
 return $result;
}
*/
$script = 'USER';
//--------------------------------------------------------------------------------------------------
//delete existing users that not exist in the mssql database
function user_del()
{global $user, $mysql;
 $sql='SELECT t2.user_id, t2.username FROM mssql_user AS t1 RIGHT JOIN user AS t2 ON t1.username=t2.username WHERE t1.username is null AND t2.user_id NOT IN (SELECT user_id FROM admin);'; //if platform admin,  no deletion
 $rcs=mysqli_query($mysql, $sql);
 $counter=0;
 while($row = mysqli_fetch_array($rcs))
 {
  $user_id=(int)$row['user_id'];
  $user_name=strtolower($row['username']);
  $delres=$user->delete_user($user_id);
  if(!delres){synclog_add('ERROR', 'USER DELETE', 'User with username "'.$user_name.'" failed to delete',$script);}
  else{$counter++;}
 }
 if($counter>0){synclog_add('INFO', 'USER DELETE', $counter.' users deleted',$script);}
}
//-------------------------------------------------------------------------------------------------
function user_deactivate()
{global $user, $mysql;
 //deactivate existing users that not exist in the mssql database //excluding teacher dummy cursist accounts
 $sql='SELECT t2.user_id, t2.username FROM mssql_user AS t1 RIGHT JOIN user AS t2 ON t1.code=t2.code WHERE t1.code is null AND t2.user_id NOT IN (SELECT user_id FROM admin) and t2.active<>0 AND t2.firstname <> "cursist" AND t2.firstname <> "leerkracht";'; //if platform admin, or test cursist account - no deactivation // 0.3046 sec 
 
 $res=mysqli_query($mysql, $sql);
 $cnt=0;
 while($row = mysqli_fetch_array($res))
 {
  $sql='UPDATE user SET active=0 WHERE user_id='.$row['user_id'].';';
  $val=api_sql_query($sql,__FILE__,__LINE__);
  if($val){$cnt++;}
 }
 if($cnt>0){synclog_add('INFO', 'USER DEACTIVATE', $cnt.' user(s) without a course subscription deactivated',$script);}
}
//--------------------------------------------------------------------------------------------------

//-------------------------------------------------------------------------------------------------
function user_activate()
{global $user, $mysql;
 // 
 $sql='SELECT t2.user_id, t2.username FROM mssql_user AS t1 INNER JOIN user AS t2 ON t1.code=t2.code    WHERE t2.active = 0   ';  
 
 $res=mysqli_query($mysql, $sql);
 $cnt=0;
 while($row = mysqli_fetch_array($res))
 {
  $sql='UPDATE user SET active=1 WHERE user_id='.$row['user_id'].';';
  $val=api_sql_query($sql,__FILE__,__LINE__);
  if($val){$cnt++;}
 }
 if($cnt>0){synclog_add('INFO', 'USER ACTIVATE', $cnt.' user(s) activated',$script);}
}
//--------------------------------------------------------------------------------------------------

//create non-existing users that exist in the mssql database
function user_add() // this is very slow -- move to a daily sync???
{global $user, $mysql;



 print '------ START ADD USERS --------';
 //$sql='SELECT t1.* FROM mssql_user AS t1 LEFT JOIN user AS t2 ON (t1.code=t2.code) WHERE t2.code IS NULL;' ; //218.7634 sec =<
 $sql='SELECT * FROM mssql_user WHERE  code NOT IN (select code from user );' ; // 185.0135 sec =/ -- 30 sec better //after optimizing -- 162.0467 sec =|
 
 $rcs=mysqli_query($mysql, $sql)or die( 'user add select failed'.mysqli_error($mysql));
 $counter=0;


//testing testing ------------------------------------------------------------------ /
 /*
$active = 1;
$send_mail= 1;
//$addres=$user->create_user('sha', 'mac', 5, 'shaun.maclennan@cvovolt.be', 'cursist.shaun5', 1234, null, 'dutch', '', null, null, '' , $active, '' , null, null, $send_mail);
$addres=$user->create_user('sha',  'mac', 5, 'shaun.maclennan@cvovolt.be', 'cursist.shaun9', '1234', '', null);
 //example from chamilo - $user_id = UserManager::create_user($firstname, $lastname, $status, $email, $username, $password, $official_code, $language, $phone, null, $auth_source, $expiration_date, $active, $hr_dept_id, null, null, $send_mail);
exit;	
 */
//------------------------------------------------------------------


 while($row = mysqli_fetch_array($rcs))
 { 
  //print_r($row);print('<br/>');
  
	  $user_name=strtolower($row['username']);
	 //previous active version $addres=$user->create_user($row['firstname'], $row['lastname'], $row['status'], $row['email'], $user_name, $row['password'], '', $row['language']);
	  //example from chamilo - $user_id = UserManager::create_user($firstname, $lastname, $status, $email, $username, $password, $official_code, $language, $phone, null, $auth_source, $expiration_date, $active, $hr_dept_id, null, null, $send_mail);
	 // $active = 1;
	 // $send_mail= 1;
       $addres=$user->create_user($row['firstname'], $row['lastname'], $row['status'], $row['email'], $user_name, $row['password'], '', null);
	 
	  if(!addres){synclog_add('ERROR', 'USER ADD', 'User with username "'.$user_name.'" failed to create',$script);}
	  else
	  {mysqli_query($mysql, 'UPDATE user SET code="'.$row['code'].'" WHERE username="'.$user_name.'";');
	   $counter++;
	  }
	 
 }
 if($counter>0){synclog_add('INFO', 'USER ADD', $counter.' users created',$script);}
}
//--------------------------------------------------------------------------------------------------
function user_sync()
{global $mysql;
 
/* $sql='SELECT t1.lastname, t1.firstname, t1.username, t1.email, t1.code AS code1, t2.code AS code2 
 	   FROM mssql_user AS t1 INNER JOIN user AS t2 ON t1.code=t2.code 
	   WHERE t1.lastname<>t2.lastname OR t1.firstname<>t2.firstname OR t1.username<>t2.username '
	   //.'OR (t1.email<>t2.email AND t1.email IS NOT NULL AND t1.email<>"")' // need to make a decision about this still //teachers maybe don't want thier dokeos email overwritten - somtimes wisa gives a personal email and somtimes work email at the moment
	   
	   .'AND t2.code IS NOT NULL 
	   AND t2.code<>"";';
	   
	   */
	  
	   $sql='SELECT t1.lastname,t1.lastname as lasatname2, t1.firstname,t1.firstname as firstname2, t1.username,t1.username as username2, t1.email,t1.email as email2, t1.code AS code1, t2.code AS code2 
 	   FROM mssql_user AS t1 inner join user AS t2 ON t1.code=t2.code 
	   WHERE (t2.username is NULL or t1.lastname<>t2.lastname OR t1.firstname<>t2.firstname OR t1.username<>t2.username  )
           
             OR (t1.email<>t2.email AND t1.email IS NOT NULL AND t1.email<>"") 
			   AND t2.code IS NOT NULL 
	   AND t2.code<>""; '; //0.1998 sec
	   
	   
	   
 $res=mysqli_query($mysql, $sql);
 $fields    =mysqli_fetch_fields($res);
 $fieldnames=array(); 
 foreach ($fields as $val)
 {$fieldnames[]=$val->name;
 }
 if(in_array('code1', $fieldnames) && in_array('code2', $fieldnames))
 {
  while($row=mysqli_fetch_array($res))
  {print '<hr>'; 
   print $updsql='UPDATE user SET lastname="'.$row['lastname'].'", firstname="'.$row['firstname'].'", username="'.$row['username'].'" '
          .', email="'.$row['email'].'" '
		   .' WHERE code="'.$row['code1'].'";';
		 
   $updres=mysqli_query($mysql, $updsql);
   if($updres){synclog_add('INFO', 'USER SYNC', 'User with code "'.$row['code1'].'" updated',$script);}
   else{synclog_add('ERROR', 'USER SYNC', 'User width code "'.$row['code1'].'" failed to update',$script);}
  }
 }
 else{synclog_add('ERROR', 'USER SYNC', 'Code fields are not configured in the DB-tables',$script);}
}
//--------------------------------------------------------------------------------------------------
?>
