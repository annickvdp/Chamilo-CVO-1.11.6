


			

<?php
// simple connection - just as in syncronization scripts
//$mssql=mssql_connect('databank.cvoleuven.be,1433', 'dokeos', 'netneduts!mroftalp') or die;
//mssql_select_db('test3', $mssql);

$mssql=mssql_connect('databank.cvoleuven.be,1433', 'dokeos', 'netneduts!mroftalp') or die('Database connection failed');
mssql_select_db('test3', $mssql)or die('Database Selection failed');

if(!$mssql){
 echo('there really is no connection');}
else{echo('everything is cool');}


// simple connection - with website username
//$mssql=mssql_connect('databank.cvoleuven.be,1433', 'website', 'ngnageot!etisbew') or die;
//mssql_select_db('test3tijdelijk2', $mssql);


// Server in the this format: <computer>\<instance name> or 
// <server>,<port> when using a non default port number
//$server = 'database.cvoleuven.be,1433';

//$link = mssql_connect($server, 'website', 'gnageot!etisbew');

//if(!$link)
//{
 //   die('Something went wrong while connecting to MSSQL');
//}



// connect to MSSQL SERVER
//$mssql=mssql_connect('database.cvoleuven.be', 'website', 'gnageot!etisbew') or die;
//mssql_select_db('test3tijdelijk2', $mssql);

// connect to MySQL SERVER
//$mysql_connection=mysql_connect('localhost', 'root', 'greed') or die('Database connection failed');


/* prepare the statement resource */
//$stmt=mssql_init("WebMeldingToevoegen", $mssql);
	// close connection
	//mysql_close($mysql_connection);
?>


