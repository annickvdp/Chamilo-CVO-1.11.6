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
 * @package activate.php
 * ==============================================================================
 */
/*
==============================================================================
		INIT SECTION
==============================================================================
*/
// name of the language file that needs to be included 
//$language_file = array('admin');
require_once('inc.sync.php');
//require_once('../main/inc/global.inc.php');
require_once('func.course.php');
require_once('func.class.php');
require_once('func.tutor.php');
//include_once('func.class.php');
/*
==============================================================================
		DATABASE OBJECT
==============================================================================
*/
//$dbs=mysqli_connect($_configuration['db_host'], $_configuration['db_user'], $_configuration['db_password'], $_configuration['main_database']);

if(!$mysql){exit;}

if(!function_exists('course_add')){print 'course_add function not found =(';exit;}
//if(function_exists('course_add')){print 'course_add function exists =)<br/>';}

$user_info = api_get_user_info(api_get_user_id());
$user_status = $user_info['status'];

if ($user_status!=1)
{
	api_not_allowed(true);
}

//remove memory and time limits as much as possible as this might be a long process...
if(function_exists('ini_set'))
{
	ini_set('memory_limit','256M');
	ini_set('max_execution_time',1800);
}

$nameTools = get_lang('Cursus activeren / deactiveren');

Display::display_header($nameTools);
?>
<script src="ajax.js" type="text/javascript"></script>

<script type="text/javascript">
var ajax = new sack();

function listLoading()
{document.getElementById('exec').disabled=true;
 showDiv('overlay_div', 'overlay_iframe');
}

function listCompleted()
{if(document.getElementById('course_code[]').options.length>0)
 {document.getElementById('exec').disabled=true;
 }
 hideDiv('overlay_div', 'overlay_iframe');
}

function getCourseList(type)
{ajax.requestFile='courselist.php?action='+type;
 ajax.element='course_code[]';
 ajax.method='POST';
 ajax.onLoading=listLoading;
 ajax.onCompletion=listCompleted;
 ajax.runAJAX();
}

function courseSelect(state)
{document.getElementById('exec').disabled=false;
}

function positionIFrame(divid, frmid)
{var div = document.getElementById(divid);
 var frm = document.getElementById(frmid);
 frm.style.left = div.style.left;
 frm.style.top = div.style.top;
 frm.style.height = div.offsetHeight+'px';
 frm.style.width = div.offsetWidth+'px';
 frm.style.display = 'block';
}

function findPos(obj)
{var curleft = curtop = 0;
 if(obj.offsetParent)
 {curleft = obj.offsetLeft
  curtop = obj.offsetTop
  while(obj = obj.offsetParent)
  {curleft += obj.offsetLeft
   curtop += obj.offsetTop
  }
 }
 return [curleft,curtop];
}

function showDiv(divid, frmid)
{var div = document.getElementById(divid);
 var obj = document.getElementById('course_code[]');
 var pos=findPos(obj);
 div.style.left=pos[0]+'px';
 div.style.top=pos[1]+'px';
 div.style.height=obj.offsetHeight+'px';
 positionIFrame(divid, frmid);
 div.style.display='block';
}

function hideDiv(divid, frmid)
{var div = document.getElementById(divid);
 var frm = document.getElementById(frmid);
 div.style.display='none';
 frm.style.display='none';
}
</script>
<?
/*
==============================================================================
		MAIN CODE
==============================================================================
*/ 
if($_POST)
{$action = addslashes(trim($_POST['course_action']));
 $message = array();
 if($action == 'create')
 {foreach($_POST['course_code'] as $course)
  {$course = addslashes(trim($course));
  
   $value = course_add($course);
   
   if($value){$message[] = 'Cursus met cursuscode '.$course.' succesvol aangemaakt<br />';
   //user2class_add($course); //this makes the script run slower but it adds all students immediatly
    
  
   }
   else{$message[] = '<font style="color:red">Er is een fout opgetreden bij het aanmaken van cursus met cursuscode '.$course.'. Contacteer de platformbeheerder<br /></font>';}
   }
   
   tutor2course_add();
  // user2class_add(); //this makes the script run slower but it adds all students immediatly
  user2course_add();
  
  }
  
  /*
  elseif($action == 'delete')
  {foreach($_POST['course_code'] as $course)
   {$course = addslashes(trim($course));
    $value = course_del($course);
    if($value){$message[] = 'Cursus met cursuscode '.$course.' succesvol verwijderd<br />';}
    else{$message[] = '<font style="color:red">Er is een fout opgetreden bij het verwijderen van cursus met cursuscode '.$course.'. Contacteer de platformbeheerder<br /></font>';}
	
   }
   
  }*/
   elseif($action == 'delete')
  {foreach($_POST['course_code'] as $course)
   {$course = addslashes(trim($course));
    $value = course_deactivate($course);
    if($value){$message[] = 'Cursus met cursuscode '.$course.' succesvol gedeactiveerd<br />';}
    else{$message[] = '<font style="color:red">Er is een fout opgetreden bij het deactiveren van cursus met cursuscode '.$course.'. Contacteer de platformbeheerder<br /></font>';}
	
   }
   
  }
  
?>
<table width="70%" border="0" cellspacing="10" cellpadding="0">
<tr>
<td>
<strong>Resultaat van de cursusactivatie/-deactivatie:<br /><br /></strong>
<ul>
<? foreach($message as $msg){echo('<li>'.$msg.'</li>');} ?>
</ul>
<a href="../user_portal.php">Klik hier</a> om terug te keren naar de startpagina.
</td>
</tr>
</table>
<?
}
else
{
?>
<style type="text/css">
	#activate td{vertical-align:top;padding:5px;}
	

</style>
<form id="activate" name="activate" method="post" action="<?=$_SERVER['PHP_SELF']; ?>">
<table width="70%" border="0" cellspacing="10" cellpadding="0">
<tr>
<td colspan="2">Op deze activatiepagina kun je zelf je cursussen activeren. Een lijst van curriculumcursussen waaraan je naam gekoppeld is wordt aangeboden. Maak je keuze uit de lijst door de betreffende cursus(sen) te selecteren.</td>
</tr>
<tr style="padding-top:10px;">
<td align="right" valign='top'><input id="course_action" name="course_action" type="radio" value="create" onclick="getCourseList(this.value);" style="padding:0px; margin:0px;" /></td>
<td><strong>Activeer cursussen: </strong>De geselecteerde cursussen worden meteen aangemaakt in Dokeos. <br/>
  <span style="background:yellow;">Cursisten worden binnen 1 uur bijgevoed, en meestal gebeurt dit direct.</span></td>
</tr>
<tr style="padding-top:5px;">
<td align="right"><input id="course_action" name="course_action" type="radio" value="delete" onclick="getCourseList(this.value);" style="padding:0px; margin:0px;" /></td>
<td><strong>Deactiveer cursussen:</strong> De geselecteerde cursussen worden gedeactiveerd, <br/>daarna kan je ze opnieuw activeren zonder verlies (binnen 14 maanden).</span></td>
</tr>
<tr style="padding-top:15px;">
<td width="70" valign="top"><strong>Cursussen:</strong></td>
<td>
<select id="course_code[]" name="course_code[]" size="7" multiple="multiple" style="width:500px" onchange="courseSelect(this.selectedIndex);">
<?php
while($row=mysqli_fetch_array($rcs_create))
{
?>
<option value="<?=strtoupper($row['code']); ?>"><?=$row['title']; ?></option>
<?php
}
?>
</select>
</td>
</tr>
<tr style="padding-top:15px;">
<td>&nbsp;</td>
<td><input id="exec" name="exec" type="submit" value="&nbsp;Uitvoeren&nbsp;" disabled="disabled" /></td>
</tr>
</table>
</form>
<?php
}
/*
==============================================================================
		FOOTER 
==============================================================================
*/ 
Display::display_footer();
?>
<div id="overlay_div" style="width:500px;height:100px;position:absolute;display:none;z-index:1000;text-align:center;background-image:url(../main/img/spacer.gif)"><img src="loader.gif" border="0" width="50" height="50" alt="" style="margin-top:27px;" /></div>
<iframe id="overlay_iframe" frameborder="0" src="about:blank" style="width:500px;position:absolute;display:none;z-index:999;"></iframe>