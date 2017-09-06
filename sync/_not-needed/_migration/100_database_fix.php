<?





$mysql_conn = mysql_connect("localhost", "root", "7cVoAdmin7");
mysql_select_db("dokeos_main",$mysql_conn) or die("no connection with mysql<br/>");

$query = "UPDATE dokeos_main.course SET db_name = LOWER(db_name),code=LOWER(code),directory=upper(directory)";
$result=mysql_query($query, $mysql_conn) ;
				
	 
$query = "UPDATE dokeos_main.class SET name = LOWER(name)";
$result=mysql_query($query, $mysql_conn) ;
				
	 
$query = "UPDATE dokeos_main.course_rel_class SET course_code = LOWER(course_code)";
$result=mysql_query($query, $mysql_conn) ;
				
	 
$query = "UPDATE dokeos_main.course_rel_user SET course_code = LOWER(course_code)";
$result=mysql_query($query, $mysql_conn) ;
				
	 
?> 























