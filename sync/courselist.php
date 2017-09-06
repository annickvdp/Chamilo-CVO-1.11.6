<?php
/*
============================================================================== 
	Dokeos - elearning and course management software
	
	Copyright (c) 2004 Dokeos S.A.
	Copyright (c) 2008 Dirk Dewit
	
	For a full list of contributors, see "credits.txt".
	The full license can be read in "license.txt".
	
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.
	
	See the GNU General Public License for more details.
	
	Contact address: Dokeos, 44 rue des palais, B-1030 Brussels, Belgium
	Mail: info@dokeos.com
============================================================================== 
*/
/**
 * ==============================================================================
 * Course activation 
 * 
 * @author Dirk Dewit <dirk.dewit@cvoleuven.be>
 * @package ajax courselist.php
 * ==============================================================================
 */
/*
==============================================================================
		INIT SECTION
==============================================================================
*/
// name of the language file that needs to be included 
//$language_file = array('admin');
include_once('../main/inc/global.inc.php');
include_once('func.course.php');
/*
==============================================================================
		DATABASE OBJECT
==============================================================================
*/
$dbs=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']);

if(!$dbs){exit;}
/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 
$action=$_GET['action'];
$user_id=api_get_user_id();

if($action=='create')
{  $sql='SELECT t1.code, t1.title FROM mssql_course AS t1  
		INNER JOIN mssql_class_tutor as t5 on t1.code = t5.classname
		LEFT JOIN course AS t3 ON t1.code=t3.code
		LEFT JOIN user AS t2 ON t5.username = t2.username  														       	WHERE t2.user_id='.$user_id.'   AND (t3.code is null OR t3.visibility = 4);';
		
		/*SELECT t1.code, t1.title FROM mssql_course AS t1  
		INNER JOIN user AS t2 ON t1.username_professor = t2.username  														       
		LEFT JOIN course AS t3 ON t1.code=t3.code
		WHERE t2.user_id=9518  AND (t3.code is null OR t3.visibility = 4);
		*/
		/*
		 //make sure head teacher is added to class tutor list -- add to sync
		  insert ignore into mssql_class_tutor select t1.username_professor, t1.code as classname 			from mssql_course  as t1 
 

		//select if primary teacher or not
		SELECT t1.code, t1.title FROM mssql_course AS t1  
		INNER JOIN mssql_class_tutor as t5 on t1.code = t5.classname
		LEFT JOIN course AS t3 ON t1.code=t3.code
		LEFT JOIN user AS t2 ON t5.username = t2.username  														       	WHERE t2.user_id=9518  AND (t3.code is null OR t3.visibility = 4)
		*/
}
elseif($action=='delete')
{ /* $sql='SELECT t1.code, t1.title FROM course AS t1 '.
      'INNER JOIN user as t2 ON t1.tutor_name=CONCAT(t2.lastname, \' \', t2.firstname) WHERE t2.user_id='.$user_id.' 
	   AND t1.visibility<>4;';*/
	  /* 
	$sql='SELECT t1.code, t1.title FROM mssql_course AS t1 '.
      'INNER JOIN user AS t2 ON t1.username_professor = t2.username '.
	  'LEFT JOIN course AS t3 ON t1.code=t3.code '.
      'WHERE t2.user_id='.$user_id.' '.
     // 'AND t3.code is null;';
	'AND (t3.code is NOT null AND t3.visibility <> 4);'; 
	*/
	
	$sql='SELECT t1.code, t1.title FROM mssql_course AS t1  
INNER JOIN mssql_class_tutor as t5 on t1.code = t5.classname
		LEFT JOIN course AS t3 ON t1.code=t3.code
		LEFT JOIN user AS t2 ON t5.username = t2.username  														     
WHERE t2.user_id='.$user_id.'  AND (t3.code is NOT null AND t3.visibility <> 4);'; 
	
}

$rcs=mysqli_query($dbs, $sql);

echo('self.elementObj.options.length=0;');
while($row=mysqli_fetch_array($rcs))
{echo('self.elementObj.options[self.elementObj.options.length]=new Option(\''.$row['title'].'\', \''.strtoupper($row['code']).'\');'."\n");
}
?>