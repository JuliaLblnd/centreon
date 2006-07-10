<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	
	if (!isset($oreon))
		exit();
	
	$handle = create_file($nagiosCFGPath."resource.cfg", $oreon->user->get_name());
	$res =& $pearDB->query("SELECT * FROM `cfg_resource` WHERE `resource_activate` = '1'");
	$str = NULL;
	while ($res->fetchInto($resource))	{
		$ret["comment"]["comment"] ? ($str .= "# '".$resource["resource_name"]."'\n") : NULL;
		if ($ret["comment"]["comment"] && $resource["resource_comment"])	{
			$comment = array();
			$comment = explode("\n", $resource["resource_comment"]);
			foreach ($comment as $cmt)
				$str .= "# ".$cmt."\n";
		}
		$str .= $resource["resource_line"]."\n";
	}
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES), $nagiosCFGPath."resource.cfg");
	fclose($handle);
	$res->free();
	unset($str);
?>