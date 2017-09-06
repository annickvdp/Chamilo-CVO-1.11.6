<?
$time='36 months';
print $sql='SELECT * FROM mssql_course AS t1 RIGHT JOIN course AS t2 ON t1.code=t2.code WHERE t1.code is null AND t2.expiration_date < "'.date("Y-m-d H:m:s", time()).'"  and ( concat("",t2.code * 1) = t2.code);';

?>
