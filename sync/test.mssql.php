<?

$mssql=mssql_connect('nt6server2', 'dokeos', 'netneduts!mroftalp') or die; 
//$mssql=mssql_connect('databank.cvoleuven.be', 'dokeos', 'netneduts!mroftalp') or die; 
mssql_select_db('test3', $mssql);

$sql='SELECT * FROM dokeos_course';
$res=mssql_query($sql, $mssql);
while($row=mssql_fetch_array($res))
{echo($row['code'].'<br>'."\n");
 echo($row['title'].'<br>'."\n");
 echo($row['category_code'].'<br>'."\n");
 echo($row['username_professor'].'<br>'."\n");
 echo('<hr>');
}
?>