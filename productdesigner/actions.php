<?php
require_once("productdesigner.php");

global $user;

if(isset($_GET['action']) && !empty($_GET['action'])) {
    $action = $_GET['action'];
	
    switch($action) {
        case 'filterDesigns' : $tid = $_GET['tid']; filterDesigns($tid); break;
		case 'filterProducts' : $tid = $_GET['tid']; filterProducts($tid); break;
		case 'updateProductSizes': $nid= $_GET['nid']; updateProductSizes($nid); break;
		case 'getColorOID': $color = $_GET['color']; getColor_OID($color); break;
		case 'getDesignOrderID': getDesignOrderID(); break; 
        // ...etc...
    }
}

if(isset($_POST['action']) && !empty($_POST['action'])) {
    $action = $_POST['action'];
	
    switch($action) {
		case 'storeDesignOrder': 
			$design = $_POST['design']; $mode = $_POST['mode']; $designid = $_POST['designid']; $price = $_POST['price'];
			$preview = $_POST['preview'];
			storeDesignOrder($design, $preview, $mode, $designid, $price); break; 
        // ...etc...
    }
}

function filterDesigns($tid){
	
	if($tid=="All"){
		createDesignSelection("all", array());
	}
	else{
		$results = getDesignsFromCategory($tid);
		createDesignSelection("filter", $results);
	}
	
}

function filterProducts($tid){
	if($tid=="All"){
		createProductSelection("all", array());
	}
	else{
		$results = getProductsFromCategory($tid);
		createProductSelection("filter", $results);
	}
}

function updateSessionValues($nid){
	//if(isset($_SESSION['nid']))
		$_SESSION['nid'] = $nid;
}

function updateProductSizes($nid){
	$sizes = getProductSizes($nid);
					
		foreach($sizes as $size){
			if($size['oid'] == $defaultSz)
				echo "<option value='".$size['oid']."' selected>".$size['name']."</option>";
			else
				echo "<option value='".$size['oid']."'>".$size['name']."</option>";
		}
}

function getColor_OID($color){
	$oid = getColorOID("#".$color);
	echo $oid;
}

function storeDesignOrder($design, $preview, $mode, $designid, $price){
	if($mode == 'new')
		$designid = createDesignOrder($design, $preview, $price);
	else if($mode == 'update')
  		updateDesignOrder($design, $preview, $price, $designid);
	
	echo $designid;
  	
}

function getDesignOrderID(){
	if(isset($_SESSION["DESIGNRECORD"]))
		echo $_SESSION["DESIGNRECORD"];
	else 
		echo -3;
}
