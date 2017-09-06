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
//--------------------------------------------------------------------------------------------------
//define objects
//--------------------------------------------------------------------------------------------------
$dbs=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']);
$tbl_course  = Database::get_main_table(TABLE_MAIN_COURSE);
$tbl_category = Database::get_main_table(TABLE_MAIN_CATEGORY);
//--------------------------------------------------------------------------------------------------
function synclog_add_category($type, $action, $descr)
{global $dbs;
 $sql='INSERT INTO mssql_synclog (`type`, `script`, `action`, `description`, `datetime`) VALUES ("'.strtoupper($type).'", "CATEGORY", "'.strtoupper($action).'" ,"'.htmlentities($descr).'", NOW());';
 $result=mysqli_query($dbs, $sql);
 return $result;
}
//--------------------------------------------------------------------------------------------------
//create category
function category_add()
{global $dbs, $tbl_category;
 $sql='SELECT t1.* FROM mssql_category AS t1 LEFT JOIN '.$tbl_category.' AS t2 ON t1.code=t2.code WHERE t2.code is null;';
 $rcs=mysqli_query($dbs, $sql);
 while($row = mysqli_fetch_array($rcs))
 {
  $cat_code=strtoupper($row['code']);
  $addres=addNode($cat_code, $row['name'], true, $row['parent_code']);
  if(!addres){synclog_add_category('ERROR', 'CATEGORY ADD', 'Category with code "'.$cat_code.'" creation failed');}
  else{synclog_add_category('INFO', 'CATEGORY ADD', 'Category with code "'.$cat_code.'" created');}
 }
}
//--------------------------------------------------------------------------------------------------
//delete category
function category_del()
{global $dbs, $tbl_category;
 $sql='SELECT t2.code FROM mssql_category AS t1 RIGHT JOIN '.$tbl_category.' AS t2 ON t1.code=t2.code WHERE t1.code is null;';
 $rcs=mysqli_query($dbs, $sql);
 while($row = mysqli_fetch_array($rcs))
 {
  $cat_code=strtoupper($row['code']);
  $delres=deleteNode($cat_code);
  if(!$delres){synclog_add_category('ERROR', 'CATEGORY DELETE', 'Category with code "'.$cat_code.'" deletion failed');}
  else{synclog_add_category('INFO', 'CATEGORY DELETE', 'Category with code "'.$cat_code.'" deleted');}
 }
}
//--------------------------------------------------------------------------------------------------
function deleteNode($node)
{global $tbl_category, $tbl_course;
 $result=api_sql_query("SELECT parent_id,tree_pos FROM $tbl_category WHERE code='$node'",__FILE__,__LINE__);
 if($row=mysql_fetch_array($result))
 {if(!empty($row['parent_id']))
  {api_sql_query("UPDATE $tbl_course SET category_code='$row[parent_id]' WHERE category_code='$node'",__FILE__,__LINE__);
   api_sql_query("UPDATE $tbl_category SET parent_id='$row[parent_id]' WHERE parent_id='$node'",__FILE__,__LINE__);
  }
  else
  {api_sql_query("UPDATE $tbl_course SET category_code='' WHERE category_code='$node'",__FILE__,__LINE__);
   api_sql_query("UPDATE $tbl_category SET parent_id=NULL WHERE parent_id='$node'",__FILE__,__LINE__);
  }
  api_sql_query("UPDATE $tbl_category SET tree_pos=tree_pos-1 WHERE tree_pos > '$row[tree_pos]'",__FILE__,__LINE__);
  api_sql_query("DELETE FROM $tbl_category WHERE code='$node'",__FILE__,__LINE__);
  if(!empty($row['parent_id']))
  {updateFils($row['parent_id']);
  }
 }
 return true;
}
//--------------------------------------------------------------------------------------------------
function addNode($code,$name,$canHaveCourses,$parent_id)
{global $tbl_category;
 $canHaveCourses=$canHaveCourses?'TRUE':'FALSE';
 $result=api_sql_query('SELECT 1 FROM '.$tbl_category.' WHERE code="'.$code.'"', __FILE__, __LINE__);
 if(mysql_num_rows($result))
 {return false;
 }
 $result=api_sql_query('SELECT MAX(tree_pos) AS maxTreePos FROM '.$tbl_category, __FILE__, __LINE__);
 $row=mysql_fetch_array($result);
 $tree_pos=$row['maxTreePos']+1;
 	api_sql_query("INSERT INTO $tbl_category(name,code,parent_id,tree_pos,children_count,auth_course_child) VALUES('$name','$code',".(empty($parent_id)?"NULL":"'$parent_id'").",'$tree_pos','0','$canHaveCourses')", __FILE__, __LINE__);
 updateFils($parent_id);
 return true;
}
//--------------------------------------------------------------------------------------------------
function updateFils($category)
{global $tbl_category;
 $result=api_sql_query('SELECT parent_id FROM '.$tbl_category.' WHERE code="'.$category.'"', __FILE__, __LINE__);
 if($row=mysql_fetch_array($result))
 {updateFils($row['parent_id']);
 }
 $children_count=compterFils($category,0)-1;
 api_sql_query('UPDATE '.$tbl_category.' SET children_count='.$children_count.' WHERE code="'.$category.'"', __FILE__, __LINE__);
}
//--------------------------------------------------------------------------------------------------
function compterFils($pere,$cpt)
{global $tbl_category;
 $result=api_sql_query("SELECT code FROM $tbl_category WHERE parent_id='$pere'",__FILE__,__LINE__);
 while($row=mysql_fetch_array($result))
 {$cpt=compterFils($row['code'],$cpt);
 }
 return ($cpt+1);
}
//--------------------------------------------------------------------------------------------------
echo('<br>Category proces terminated');
//--------------------------------------------------------------------------------------------------
?>