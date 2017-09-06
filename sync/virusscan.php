<?
function is_virus($full_path)
{
	// Scan the file
	$retval = false;
	$cmd = 'start /B "virscan_wndhandler" "C:\Program Files\ClamWin\bin\clamscan.exe" "--database=C:\Documents and Settings\All Users\.clamwin\db" "--remove" "--tempdir=C:\WINDOWS\Temp" "'.$full_path.'"';

	
	exec($cmd, $output);
	
	for($i=0; $i<sizeof($output); $i++)
	{
		if (eregi ("FOUND", $output[$i]))
 		{
			$retval = true;
  			/*
			$current = $output[$i];
  			print "\n\n$current\n\n";
  			$parsing_name_part1 = explode("$filename: ",$current);
  			$parsing_name_part2 = explode(" FOUND",$parsing_name_part1[1]);
  			$parsing_name_done = $parsing_name_part2[0];
  			*/
		}
 	}
	
	return $retval;
}
/**
 * If there are any files posted, check them for viruses.
 */
if (count($_FILES) > 0)
{
	foreach ($_FILES as $name => $file)
	{
		if (strlen($file['tmp_name'])>0 && is_virus($file['tmp_name'])) //check for viruses
		{
			$infected_files[] = $file['name'];
			unlink($file['tmp_name']);
		}
	}
	if (count($infected_files) > 0)
	{
		$log_handle = fopen(dirname(__FILE__).'/virusscan.log', "a");
		$log_entry = date("d/m/Y H:i:s").';'.$_user['official_code'].';'.$_user['lastName'].';'.$_user['firstName'].';'.$_course['sysCode'].';['.implode('|', $infected_files).']';
		fwrite($log_handle, $log_entry."\n");
		fclose($log_handle);
		$nameTools = 'Virus detected';
		Display :: display_header($nameTools);
		?>
		<div style="border:4px solid #990000;width:60%;margin:50px;margin-left:auto;margin-right:auto;padding:10px;color:#990000;">
		<p style="font-size:18px;"><img src="<?php echo api_get_path(WEB_PATH); ?>/sync/virus.jpg" align="right"/><b><?php echo $nameTools; ?> !</b></p>
		<p><?php echo '<strong>Operation stopped</strong>'; ?></p>
		<ul>
		<?php
		foreach ($infected_files as $file)
		{
			echo '<li>'.$file.'</li>';
		}
		?>
		</ul>
		<p><?php echo '<strong>Infected file is deleted. Please check your computer for viruses thoroughly !</strong>'; ?></p>
		<p><a href="<?=$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']; ?>">&laquo; <?php echo 'Back'; ?></a>
		</div>
		<?php
		Display :: display_footer();
		exit;
	}
}
?>