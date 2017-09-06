<?php


 exit;
//connection for cvo dokeos site
$host='localhost'; $user='root'; $password='cvo382';

//connect
$mysql_connection=mysql_connect($host, $user, $password) or die('Database Server Connection Failed_');

if($mysql_connection){print('connected to host<br><br>');};


mysql_select_db("dokeos_main", $mysql_connection) or die('db selection failed_');

if(mysql_select_db){print('connected to database<br>');};

mysql_query("UPDATE dokeos_main.course SET visibility = '1' 
			WHERE visibility = '0'");

mysql_close($con);

?> 