<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

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


 include("../../oreon.conf.php");
 require_once ("../../$classdir/Session.class.php");
 require_once ("../../$classdir/Oreon.class.php");
 Session::start();

 if (!isset($_SESSION["oreon"])) {
 	// Quick dirty protection
 	header("Location: ../../index.php");
 	//exit();
 }else {
 	$oreon =& $_SESSION["oreon"];
 }


	if (isset($_GET["host"]))
		$host = $_GET["host"];
	else if (isset($_POST["host"]))
		$host = $_POST["host"];
	else {
		print "Bad Request !";
		exit;
	}


	require ("Net/Ping.php");
	$ping = Net_Ping::factory();

	if(!PEAR::isError($ping))
	{
    	$ping->setArgs(array("count" => 4));
		$response = $ping->ping($host);
		foreach ($response->getRawData() as $key => $data) {
   			$msg .= $data ."<br>";
		}
		print $msg;
	}

?>