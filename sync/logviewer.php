<?

//--------------------------------------------------------------------------------------------------
//define include path (must be absolute for scheduled tasks)
//--------------------------------------------------------------------------------------------------
$incpath=dirname(realpath('.')).'/main/inc/';
//--------------------------------------------------------------------------------------------------
//include files
//--------------------------------------------------------------------------------------------------
require_once($incpath.'conf/configuration.php');
//include files for restriction access
require($incpath.'global.inc.php');
include_once($incpath.'installedVersion.inc.php');
require_once(api_get_path(LIBRARY_PATH).'security.lib.php');
//--------------------------------------------------------------------------------------------------
// Access restrictions
//--------------------------------------------------------------------------------------------------
api_protect_admin_script();
//--------------------------------------------------------------------------------------------------
//define objects
//--------------------------------------------------------------------------------------------------
$dbs=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']);
//--------------------------------------------------------------------------------------------------
//get all the different types form the log table
$types=array();
$sql='SELECT DISTINCT(`type`) FROM mssql_synclog';
$rcs=mysqli_query($dbs, $sql);
while($row = mysqli_fetch_array($rcs))
{array_push($types, $row['type']);
}
//get all the different scripts form the log table
$scripts=array();
$sql='SELECT DISTINCT(`script`) FROM mssql_synclog';
$rcs=mysqli_query($dbs, $sql);
while($row = mysqli_fetch_array($rcs))
{array_push($scripts, $row['script']);
}
//get all the different actions form the log table
$actions=array();
$sql='SELECT DISTINCT(`action`) FROM mssql_synclog';
$rcs=mysqli_query($dbs, $sql);
while($row = mysqli_fetch_array($rcs))
{array_push($actions, $row['action']);
}
//get all the different dates form the log table
$dates=array();
$sql='SELECT DISTINCT(DATE(`datetime`)) AS date FROM mssql_synclog';
$rcs=mysqli_query($dbs, $sql);
while($row = mysqli_fetch_array($rcs))
{array_push($dates, $row['date']);
}
//--------------------------------------------------------------------------------------------------
if($_GET['dellog'])
{mysqli_query($dbs, 'TRUNCATE mssql_synclog;');
 header('location: '.$_SERVER['PHP_SELF']);
}
$sqlwhere='true';
if($_POST)
{$seltype=$_POST['seltype'];
 $sqlwhere=($seltype!='')?'`type`="'.$seltype.'"':'true';
 $selscript=$_POST['selscript'];
 $sqlwhere.=($selscript!='')?' AND `script`="'.$selscript.'"':'';
 $selaction=$_POST['selaction'];
 $sqlwhere.=($selaction!='')?' AND `action`="'.$selaction.'"':'';
 $seldate=$_POST['seldate'];
 $sqlwhere.=($seldate!='')?' AND DATE(`datetime`)="'.$seldate.'"':'';
}
//--------------------------------------------------------------------------------------------------
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
//--------------------------------------------------------------------------------------------------
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Dokeos MSSQL Sync logging</title>
<style>
html, body{
font-family:Arial, Helvetica, sans-serif;
font-size:11px;
}
tr.info{
color: blue;
}
tr.error{
color:red;
}
tr.check{
color:green;
}
</style>
<script>
function dellog()
{var answer = confirm("Alle loggegevens verwijderen ?")
 if(answer){window.location="<?=$_SERVER['PHP_SELF'].'?dellog=1;'; ?>";}
 else{window.location="<?=$_SERVER['PHP_SELF']; ?>";}
}
</script>
</head>

<body>
<h1>Dokeos MSSQL Sync logging</h1>
<form id="frmlog" name="frmlog" action="<?=$_SERVER['PHP_SELF']; ?>" method="post">
<table width="100%" border="1" cellspacing="1" cellpadding="2">
<tr>
<td width="70">
<select id="seltype" name="seltype" onchange="document.frmlog.submit();" style="width:100%">
<option value="" <?=($seltype=='')?'selected':''; ?>>ALL</option>
<?
foreach($types as $type)
{$selected=($seltype==$type)?'selected':'';
 echo('<option value="'.$type.'" '.$selected.'>'.$type.'</option>');
}
?>
</select>
</td>
<td width="120">
<select id="selscript" name="selscript" onchange="document.frmlog.submit();" style="width:100%">
<option value="" <?=($selscript=='')?'selected':''; ?>>ALL</option>
<?
foreach($scripts as $script)
{$selected=($selscript==$script)?'selected':'';
 echo('<option value="'.$script.'" '.$selected.'>'.$script.'</option>');
}
?>
</select>
</td>
<td width="160">
<select id="selaction" name="selaction" onchange="document.frmlog.submit();" style="width:100%">
<option value="" <?=($selaction=='')?'selected':''; ?>>ALL</option>
<?
foreach($actions as $action)
{$selected=($selaction==$action)?'selected':'';
 echo('<option value="'.$action.'" '.$selected.'>'.$action.'</option>');
}
?>
</select>
</td>
<td width="140">
<select id="seldate" name="seldate" onchange="document.frmlog.submit();" style="width:100%">
<option value="" <?=($seldate=='')?'selected':''; ?>>ALL</option>
<?
foreach($dates as $date)
{$selected=($seldate==$date)?'selected':'';
 echo('<option value="'.$date.'" '.$selected.'>'.$date.'</option>');
}
?>
</select>
</td>
<td>DESCRIPTION</td>
</tr>
<?
$sql='SELECT * FROM mssql_synclog WHERE '.$sqlwhere.' ORDER BY `datetime` DESC';
$rcs=mysqli_query($dbs, $sql);
while($row = mysqli_fetch_array($rcs))
{echo('<tr class="'.strtolower($row['type']).'">'."\n");
 echo('<td>'.$row['type'].'</td>'."\n");
 echo('<td>'.$row['script'].'</td>'."\n");
 echo('<td>'.$row['action'].'</td>'."\n");
 echo('<td>'.$row['datetime'].'</td>'."\n");
 echo('<td>'.$row['description'].'</td>'."\n");
 echo('</tr>'."\n");
}
?>
</table>
<input type="button" value="Log verwijderen" style="margin-top:20px;" onclick="dellog();" />&nbsp;&nbsp;<input type="button" value="Vernieuwen" style="margin-top:20px" onclick="document.location.reload(true);" />
</form>
</body>
</html>
