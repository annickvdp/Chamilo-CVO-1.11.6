<?
// simple connection - with website username
 $mssql=mssql_connect('databank.cvoleuven.be,1433', 'website2', 'ngnageot!etisbew') or die('Database connection failed');
 mssql_select_db('test3', $mssql)or die('Database selection failed');

print ('I think we are good');

?>