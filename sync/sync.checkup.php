<?


 exit;
//--------------------------------------------------------------------------------------------------
//define include path (must be absolute for scheduled tasks)
//--------------------------------------------------------------------------------------------------
//$incpath=dirname(realpath('.')).'\\main\\inc\\';
//$incpath='C:/Inetpub/wwwroot/Dokeos/main/inc/';
//--------------------------------------------------------------------------------------------------
//include files
//--------------------------------------------------------------------------------------------------
require_once('inc.sync.php');
//require_once($incpath.'global.inc.php');
require_once($incpath.'lib/main_api.lib.php');
require_once($incpath.'lib/course.lib.php');
require_once($incpath.'lib/classmanager.lib.php');
 
require_once('func.course.php');

//--------------------------------------------------------------------------------------------------
//define objects
//--------------------------------------------------------------------------------------------------
$course=new CourseManager();
$class=new ClassManager();
$dbs=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']);
//--------------------------------------------------------------------------------------------------
/*if ( !function_exists('synclog_add')) {
	function synclog_add($type, $action, $descr)
	{global $dbs;
	 $sql='INSERT INTO mssql_synclog (`type`, `script`, `action`, `description`, `datetime`) VALUES ("'.strtoupper($type).'", "CHECKUP", "'.strtoupper($action).'" ,"'.htmlentities($descr).'", NOW());';
	 $result=mysqli_query($dbs, $sql);
	 return $result;
	}
}*/

$script='CHECKUP'; 


//-------------------------------------------------------------------------------------------------------
//------ THIS WAS USED FOR THE MIGRATION ------- BUT NOW IT'S NOT NECESSARY -- DOKEOS LIKES TO MAKE DBs in uppercate and it's ok as long as it's properly registered.
// make sure all course,class ids, database references are lowercase - this is needed for linux since DBs are case sensitive
/*
//$sql = "UPDATE dokeos_main.course SET db_name = LOWER(db_name),code=LOWER(code),directory=LOWER(directory)";
  
$sql = "UPDATE dokeos_main.course SET db_name = LOWER(db_name),code=LOWER(code),directory=upper(directory)";
$res=mysqli_query($dbs, $sql);
				
	 
$sql = "UPDATE dokeos_main.class SET name = LOWER(name)";
$res=mysqli_query($dbs, $sql);
				
	 
$sql = "UPDATE dokeos_main.course_rel_class SET course_code = LOWER(course_code)";
$res=mysqli_query($dbs, $sql);
				
	 
$sql = "UPDATE dokeos_main.course_rel_user SET course_code = LOWER(course_code)";
$res=mysqli_query($dbs, $sql);

*/
//-------------------------------------------------------------------------------------------------------
//delete expired courses (two weeks after expiration)
/* 
$sql='SELECT code FROM course where expiration_date <  (CURDATE() -INTERVAL 2 week) ;';
$res=mysqli_query($dbs, $sql);
while($row = mysqli_fetch_array($res))
{$check = course_del($row['code']);
  if($check){
	  	synclog_add('CHECK', 'COURSE DELETE', 'Expired course with course_code "'.$row['code'].'" deleted', $script);
  }
 
}
*/ 

//--------------------------------------------------------------------------------------------------
// delete logs - all logs older than 12 months are deleted
$sql='DELETE FROM dokeos_main.`mssql_synclog` WHERE `datetime` < (CURDATE() - INTERVAL 12 MONTH)  ;';
$res=mysqli_query($dbs, $sql);
$num=mysqli_affected_rows($dbs);
if($num>0){synclog_add('CHECK', 'MSSQL_SYNCLOG DELETE', $num.' record(s) older than 12 months deleted',$script);}


//--------------------------------------------------------------------------------------------------

//--------------------------------------------------------------------------------------------------
//delete classes without a course
$sql='SELECT t1.id FROM class AS t1 LEFT JOIN course_rel_class AS t2 ON t1.id = t2.class_id WHERE t2.class_id IS NULL OR t1.name NOT IN (SELECT code FROM course);';
$res=mysqli_query($dbs, $sql);
while($row = mysqli_fetch_array($res))
{$class->delete_class($row['id']);
 synclog_add('CHECK', 'CLASS DELETE', 'Orphaned class with class_id "'.$row['id'].'" deleted', $script);
 //delete classes from the reservation table
 $reservationsql='DELETE FROM dokeos_reservation.item_rights WHERE class_id='.$row['id'].';';
 api_sql_query($reservationsql,__FILE__,__LINE__);
}
//--------------------------------------------------------------------------------------------------
//delete class_user records with a non-existant class_id
$sql='DELETE FROM class_user WHERE class_id NOT IN (SELECT id FROM class);';
$res=mysqli_query($dbs, $sql);
$num=mysqli_affected_rows($dbs);
if($num>0){synclog_add('CHECK', 'CLASS_USER DELETE', $num.' record(s) with no related class deleted', $script);}
//--------------------------------------------------------------------------------------------------
//delete class_user records with a non-existant user_id
$sql='DELETE FROM class_user WHERE user_id NOT IN (SELECT user_id FROM user);';
$res=mysqli_query($dbs, $sql);
$num=mysqli_affected_rows($dbs);
if($num>0){synclog_add('CHECK', 'CLASS_USER DELETE', $num.' record(s) with no related user deleted',$script);}
//--------------------------------------------------------------------------------------------------
//delete course_rel_class records with a non-existant course_code
$sql='DELETE FROM course_rel_class WHERE course_code NOT IN (SELECT code FROM course);';
$res=mysqli_query($dbs, $sql);
$num=mysqli_affected_rows($dbs);
if($num>0){synclog_add('CHECK', 'COURSE_REL_CLASS DELETE', $num.' record(s) with no related course deleted',$script);}
//--------------------------------------------------------------------------------------------------
//delete course_rel_class records with a non-existant class_id
$sql='DELETE FROM course_rel_class WHERE class_id NOT IN (SELECT id FROM class);';
$res=mysqli_query($dbs, $sql);
$num=mysqli_affected_rows($dbs);
if($num>0){synclog_add('CHECK', 'COURSE_REL_CLASS DELETE', $num.' record(s) with no related class deleted',$script);}
//--------------------------------------------------------------------------------------------------
//delete course_rel_user records with a non-existant course_code
$sql='DELETE FROM course_rel_user WHERE course_code NOT IN (SELECT code FROM course);';
$res=mysqli_query($dbs, $sql);
$num=mysqli_affected_rows($dbs);
if($num>0){synclog_add('CHECK', 'COURSE_REL_USER DELETE', $num.' record(s) with no related course deleted',$script);}
//--------------------------------------------------------------------------------------------------
//delete course_rel_user records with a non-existant user_id
$sql='DELETE FROM course_rel_user WHERE user_id NOT IN (SELECT user_id FROM user);';
$res=mysqli_query($dbs, $sql);
$num=mysqli_affected_rows($dbs);
if($num>0){synclog_add('CHECK', 'COURSE_REL_USER DELETE', $num.' record(s) with no related user deleted',$script);}
//--------------------------------------------------------------------------------------------------
//delete reservation and subscription records in the past
$reservationsql ='DELETE FROM dokeos_reservation.reservation WHERE end_at < NOW();';
api_sql_query($reservationsql,__FILE__,__LINE__);
$subscriptionsql='DELETE FROM dokeos_reservation.subscription WHERE end_at < NOW();';
api_sql_query($subscriptionsql,__FILE__,__LINE__);
//--------------------------------------------------------------------------------------------------
//delete item_rights with a non-existant class_id 
$reservationsql ='DELETE FROM dokeos_reservation.item_rights WHERE class_id NOT IN (SELECT id FROM class);';
api_sql_query($reservationsql,__FILE__,__LINE__);
//--------------------------------------------------------------------------------------------------


//delete directories without a course record - - must re write for linux
/*
$path=$_configuration['root_sys'].$_configuration['course_folder'];
$list_dirs=array();
if ($dir=@opendir($path))
{while(($subdir=readdir($dir))!==false)
 {if($subdir!='.' && $subdir!='..' && is_dir($path.$subdir)){array_push($list_dirs, strtoupper($subdir));}
 }  
 closedir($dir);
}

$list_course=array();
$sql='SELECT directory FROM course;';
$res =mysqli_query($dbs, $sql);
while($row = mysqli_fetch_array($res))
{array_push($list_course, strtoupper($row['directory']));
}

$result = array_diff($list_dirs, $list_course);  //get elements in list_dirs that not exist in list_course
foreach($result as $dir)
{$dirres=deltree($path.$dir);
 if($dirres){synclog_add('CHECK', 'DIRECTORY DELETE', 'Orphaned directory with name "'.$dir.'" deleted',$script);}
 else{synclog_add('ERROR', 'DIRECTORY DELETE', 'Orphaned directory with name "'.$dir.'" failed to delete',$script);}
}
//--------------------------------------------------------------------------------------------------
//recursive directory delete
function deltree($path)
{if(is_dir($path))
 {if($handle=opendir($path))
  {while(false!==($file=readdir($handle)))
   {if($file!='.' && $file!='..')
    {if(is_dir($path.'/'.$file)){deltree($path.'/'.$file);}
	 if(is_file($path.'/'.$file)){unlink($path.'/'.$file);}
	}
   }
   closedir($handle);
   return rmdir($path);
  }
 }
 return false;
}
*/

//--------------------------------------------------------------------------------------------------
//delete the garbage directory
//MUST REWRITE FOR LINIX

/*

$garbagepath=api_get_path(GARBAGE_PATH);
$garbagecount=0;
if($dir=@opendir($garbagepath))
{while(($subdir=readdir($dir))!==false)
 {if($subdir!='.' && $subdir!='..' && is_dir($garbagepath.$subdir))
  {$garbageres=deltree($garbagepath.$subdir);
   if($garbageres==true){$garbagecount++;}
  }
 } 
 closedir($dir);
}
if($garbagecount>0)
{if($garbageres){synclog_add('CHECK', 'GARBAGE DELETE', $garbagecount.' garbage directories cleared',$script);}
 else{synclog_add('ERROR', 'GARBAGE DELETE', 'Failed to clear garbage directory',$script);}
}
//--------------------------------------------------------------------------------------------------
*/

//delete databases without a course record

// TEMPORARILY DISABLED
 /*
$list_dbs=array();
$sql ='SHOW DATABASES LIKE "dokeos_course_%";';
$res =mysqli_query($dbs, $sql);
while($row = mysqli_fetch_array($res))
{array_push($list_dbs, strtoupper($row[0]));
}

$list_course=array();
$sql='SELECT db_name FROM course;';
$res =mysqli_query($dbs, $sql);
while($row = mysqli_fetch_array($res))
{array_push($list_course, strtoupper($row['db_name']));
}

$result=array_diff($list_dbs, $list_course);  //get elements in list_dbs that not exist in list_course
foreach($result as $dbase)
{$dbres=mysqli_query($dbs, 'DROP DATABASE '.$dbase.';');
 if($dbres){synclog_add('CHECK', 'DATABASE DELETE', 'Orphaned database with name "'.$dbase.'" deleted',$script);}
 else{synclog_add('ERROR', 'DATABASE DELETE', 'Orphaned database with name "'.$dbase.'" failed to delete',$script);}
}
 */
//--------------------------------------------------------------------------------------------------
//optimize tables with overhead (only in the main database)
$result=mysqli_query($dbs, 'SHOW TABLE STATUS');
while($row=mysqli_fetch_array($result))
{$overhead=($row['Data_length']>0) ? $row['Data_free']/$row['Data_length']*100 : 0;
 if($overhead > 10)
 {$dboptres=mysqli_query($dbs, 'OPTIMIZE TABLE ' . $row['Name']);
  synclog_add('CHECK', 'TABLE OPTIMIZE', 'Table "'.$row['Name'].'" in database "'.$_configuration['main_database'].'" optimized',$script);
 }
}
//--------------------------------------------------------------------------------------------------
echo('<br>Checkup proces terminated');
//--------------------------------------------------------------------------------------------------
?>
