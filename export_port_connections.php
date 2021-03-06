<?php
	require_once( "db.inc.php" );
	require_once( "facilities.inc.php" );
	require_once( "connections_spreadsheet.php" );
	require_once( "PHPExcel/PHPExcel/Writer/Excel2007.php" );
	
	if((isset($_REQUEST["deviceid"]) && ($_REQUEST["deviceid"]=="" || $_REQUEST["deviceid"]==null)) || !isset($_REQUEST["deviceid"])){
		// No soup for you.
		header('Location: '.redirect());
		exit;
	}
	
	$devList = array();
	
	if ( $_REQUEST["deviceid"] == "wo" ) {
		// Special case, we are printing all connections for a work order, which has a cookie associated with it
		$woList = json_decode( $_COOKIE["workOrder"] );
		foreach($woList as $woDev){
			$dev=new Device();
			$dev->DeviceID=$woDev;
			if($dev->GetDevice()){
				$devList[]=$dev;
			}
		}
	} else {
		$devList[0] = new Device();
		$devList[0]->DeviceID = $_REQUEST["deviceid"];
		if ( ! $devList[0]->GetDevice() ) {
			// Not a valid device ID
			header('Location: '.redirect());
			exit;
		}
	}
	
	$writer = new PHPExcel_Writer_Excel2007(generate_spreadsheet($devList));

	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	if ( $_REQUEST["deviceid"] == "wo" ) {
		header( sprintf( "Content-Disposition: attachment;filename=\"openDCIM-workorder-%s-connections.xlsx\"", date( "YmdHis" ) ) );	
	} else {
		header( "Content-Disposition: attachment;filename=\"openDCIM-dev" . $dev->DeviceID . "-connections.xlsx\"" );
	}

	$writer->save("php://output");
	
?>
