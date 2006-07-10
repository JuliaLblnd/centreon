<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

This unit, called � Oreon Meta Service � is developped by Merethis company for Lafarge Group, 
under the direction of Jean Baptiste Sarrodie <jean-baptiste@sarrodie.org>

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

	$str = NULL;
	$i = 1;
	$handle = create_file($nagiosCFGPath."meta_dependencies.cfg", $oreon->user->get_name());

	$rq = "SELECT * FROM dependency dep WHERE (SELECT DISTINCT COUNT(*) FROM dependency_metaserviceParent_relation dmspr WHERE dmspr.dependency_dep_id = dep.dep_id) > 0 AND (SELECT DISTINCT COUNT(*) FROM dependency_metaserviceChild_relation dmscr WHERE dmscr.dependency_dep_id = dep.dep_id) > 0";
	$res =& $pearDB->query($rq);
	$dependency = array();
	$i = 1;
	$str = NULL;
	while($res->fetchInto($dependency))	{
		$BP = false;
		$res2 =& $pearDB->query("SELECT meta_service_meta_id FROM dependency_metaserviceParent_relation WHERE dependency_dep_id = '".$dependency["dep_id"]."'");
		$metaPar = NULL;
		while ($res2->fetchInto($metaPar))	{
			$BP = false;
			if ($ret["level"]["level"] == 1)
				array_key_exists($metaPar["meta_service_meta_id"], $gbArr[7]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 2)
				array_key_exists($metaPar["meta_service_meta_id"], $gbArr[7]) ? $BP = true : NULL;
			else if ($ret["level"]["level"] == 3)
				$BP = true;
			if ($BP)	{
				$res3 =& $pearDB->query("SELECT meta_service_meta_id FROM dependency_metaserviceChild_relation WHERE dependency_dep_id = '".$dependency["dep_id"]."'");
				$metaCh = NULL;
				while ($res3->fetchInto($metaCh))	{					
					$BP = false;
					if ($ret["level"]["level"] == 1)
						array_key_exists($metaCh["meta_service_meta_id"], $gbArr[7]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 2)
						array_key_exists($metaCh["meta_service_meta_id"], $gbArr[7]) ? $BP = true : NULL;
					else if ($ret["level"]["level"] == 3)
						$BP = true;
					if ($BP)	{
						$ret["comment"]["comment"] ? ($str .= "# '".$dependency["dep_name"]."' host dependency definition ".$i."\n") : NULL;
						if ($ret["comment"]["comment"] && $dependency["dep_comment"])	{
							$comment = array();
							$comment = explode("\n", $dependency["dep_comment"]);
							foreach ($comment as $cmt)
								$str .= "# ".$cmt."\n";
						}
						$str .= "define servicedependency{\n";
						$str .= print_line("dependent_host_name", "Meta_Module");
						$str .= print_line("dependent_service_description", "meta_".$metaCh["meta_service_meta_id"]);
						$str .= print_line("host_name", "Meta_Module");
						$str .= print_line("service_description", "meta_".$metaPar["meta_service_meta_id"]);
						if ($oreon->user->get_version() == 2)
							if (isset($dependency["inherits_parent"]["inherits_parent"]) && $dependency["inherits_parent"]["inherits_parent"] != NULL) $str .= print_line("inherits_parent", $dependency["inherits_parent"]["inherits_parent"]);
						if (isset($dependency["execution_failure_criteria"]) && $dependency["execution_failure_criteria"] != NULL) $str .= print_line("execution_failure_criteria", $dependency["execution_failure_criteria"]);
						if (isset($dependency["notification_failure_criteria"]) && $dependency["notification_failure_criteria"] != NULL) $str .= print_line("notification_failure_criteria", $dependency["notification_failure_criteria"]);
						$str .= "}\n\n";
						$i++;
					}
				}
				$res3->free();
			}
		}
	}
	unset($dependency);
	$res->free();
	write_in_file($handle, $str, $nagiosCFGPath."meta_dependencies.cfg");
	fclose($handle);
	unset($str);
?>