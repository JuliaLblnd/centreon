<?
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
	if (!isset ($oreon))
		exit ();

	isset($_GET["service_id"]) ? $cG = $_GET["service_id"] : $cG = NULL;
	isset($_POST["service_id"]) ? $cP = $_POST["service_id"] : $cP = NULL;
	$cG ? $service_id = $cG : $service_id = $cP;

	isset($_GET["service_description"]) ? $cG = $_GET["service_description"] : $cG = NULL;
	isset($_POST["service_description"]) ? $cP = $_POST["service_description"] : $cP = NULL;
	$cG ? $service_description = $cG : $service_description = $cP;

	isset($_GET["host_name"]) ? $cG = $_GET["host_name"] : $cG = NULL;
	isset($_POST["host_name"]) ? $cP = $_POST["host_name"] : $cP = NULL;
	$cG ? $host_name = $cG : $host_name = $cP;

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	#Path to the configuration dir
	$path = "./include/views/graphs/hostGraphs/";

	#PHP functions
	require_once "./include/common/common-Func.php";

	#
	## Database retrieve information for differents elements list we need on the page
	#

	$res =& $pearDB->getAssoc("SELECT DISTINCT h.host_name, h.host_name 
									  	FROM host h, host_service_relation hs, service s, command c, hostgroup_relation hgr, extended_service_information e
										WHERE  (
														hs.host_host_id = h.host_id
														OR (
															hgr.host_host_id = h.host_id
															AND hs.hostgroup_hg_id = hgr.hostgroup_hg_id
															)
													)
												AND s.service_id = hs.service_service_id
												AND e.service_service_id = hs.service_service_id
												AND c.command_id = s.command_command_id
												AND c.command_name LIKE 'check_graph%'");


	$debug = 0;
	$attrsTextI		= array("size"=>"3");
	$attrsText 		= array("size"=>"30");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");


	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "./");

	#
	## Form begin
	#

	$form = new HTML_QuickForm('Form', 'get', "?p=".$p);
	$form->addElement('header', 'title', $lang["giv_sr_infos"]);
	#
	## Indicator basic information
	#

	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	$page =& $form->addElement('hidden', 'p');
	$page->setValue($p);
	$minF =& $form->addElement('hidden', 'min');
	$minF->setValue($min);

	$sel =& $form->addElement('select', 'host_name', "Host", $res);

	# "3600"=>$lang["giv_sr_p1h"],

	$periods = array(
				"10800"=>$lang["giv_sr_p3h"],
				"21600"=>$lang["giv_sr_p6h"],
				"43200"=>$lang["giv_sr_p12h"],
				"86400"=>$lang["giv_sr_p24h"],
				"172800"=>$lang["giv_sr_p2d"],
				"302400"=>$lang["giv_sr_p4d"],
				"604800"=>$lang["giv_sr_p7d"],
				"1209600"=>$lang["giv_sr_p14d"],
				"2419200"=>$lang["giv_sr_p28d"]
	);
	$sel =& $form->addElement('select', 'period', $lang["giv_sr_period"], $periods);
	$form->setDefaults(array('period' =>'10800'));

	$form->addElement('text', 'start', $lang['giv_gt_start']);
	$form->addElement('button', "startD", $lang['modify'], array("onclick"=>"displayDatePicker('start')"));
	$form->addElement('text', 'end', $lang['giv_gt_end']);
	$form->addElement('button', "endD", $lang['modify'], array("onclick"=>"displayDatePicker('end')"));


	$nbGraph[] = &HTML_QuickForm::createElement('radio', 'nbGraph', null, 1, '1');
	$nbGraph[] = &HTML_QuickForm::createElement('radio', 'nbGraph', null, 2, '2');
	$form->addGroup($nbGraph, 'nbGraph', $lang['giv_hg_nbGraph'], '&nbsp;&nbsp;&nbsp;&nbsp;');
	$form->setDefaults(array('nbGraph' => '2'));

	$subC =& $form->addElement('submit', 'submitC', $lang["giv_sr_button"]);
	$res =& $form->addElement('reset', 'reset', $lang["reset"]);
  	$res =& $form->addElement('button', 'advanced', $lang["advanced"], array("onclick"=>"DivStatus( 'div', '1' )"));

	if (((isset($_GET["submitC"]) && $_GET["submitC"]) || $min == 1))
		if ($form->validate())	{
		$ret = $form->getsubmitValues();

		$res =& $pearDB->query("SELECT DISTINCT h.host_id, hs.service_service_id,e.graph_id 
									  	FROM host h, host_service_relation hs, service s, command c, hostgroup_relation hgr, extended_service_information e
										WHERE h.host_name = '".$_GET["host_name"]."'
												AND (
														hs.host_host_id = h.host_id
														OR (
															hgr.host_host_id = h.host_id
															AND hs.hostgroup_hg_id = hgr.hostgroup_hg_id
															)
													)
												AND s.service_id = hs.service_service_id
												AND e.service_service_id = hs.service_service_id
												AND c.command_id = s.command_command_id
												AND c.command_name LIKE 'check_graph%'");
		$i=0;
		while($rrd[$i]=$res->fetchRow()){
			if (!file_exists($oreon->optGen["oreon_rrdbase_path"].$rrd[$i]["host_id"]."_".$rrd[$i]["service_service_id"].".rrd"))
				print ("rrd file not found");
		$i++;
		}


		//$_GET["database"] = array("0" => getHostID($_GET["host_name"]) . "_" . getServiceID($_GET["host_name"], $_GET["service_description"]) . ".rrd", "1" => $_GET["host_name"]);

			# Init variable in the page
			$tpl->assign("period", $periods[$ret["period"]]);


			# Init

			if (isset($_GET["start"]) && $_GET["start"]){
				preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $_GET["start"], $matches);
				$start = mktime("0", "0", "0", $matches[1], $matches[2], $matches[3], 1) ;
			}
			if (isset($_GET["end"]) && $_GET["end"]){
				preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $_GET["end"], $matches);
				$end = mktime("23", "59", "59", $matches[1], $matches[2], $matches[3], 1)  + 10;
			}

			if (!isset($start))
				$start = time() - ($ret["period"] + 120);
			if (!isset($end))
				$end = time() + 120;


			$tpl->assign("nbGraph", $_GET["nbGraph"]["nbGraph"]);
			$tpl->assign("end", $end);
			$tpl->assign("start", $start);
			if (isset($_GET["grapht_graph_id"]))
				$tpl->assign('graph_graph_id', $_GET["grapht_graph_id"]);
    }

	#Apply a template definition
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);
	
	if (isset($rrd))
		$tpl->assign("rrd", $rrd);
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('lang', $lang);
	$tpl->assign('session_id', session_id());
	$tpl->display("hostGraphs.ihtml");
?>
