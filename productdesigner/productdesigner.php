<?php
// define static var

global $base_url;
$drupal_path = $_SERVER['DOCUMENT_ROOT'] . "/designyourown2/";
$base_url = "http://localhost/designyourown2";
$current_path = getcwd();
chdir($drupal_path);
require_once ('./includes/bootstrap.inc');
require_once ('./includes/database.inc');

 drupal_bootstrap(DRUPAL_BOOTSTRAP_SESSION);
 drupal_bootstrap(DRUPAL_BOOTSTRAP_DATABASE);
 chdir($current_path);

 
//load user
//global $user;

define("PRODUCT_FOLDER", "PRODUCTS/");
define("DESIGN_FOLDER", "DESIGNS/");
define("DESIGNORDER_FOLDER", $_SERVER['DOCUMENT_ROOT'] . "/designyourown2/productdesigner/DESIGNORDERS/");

/*DEFINE CONSTANTS BASED ON HOW DRUPAL ATTRIBUTES AND VOCABULARY ARE SETUP*/
define("SIZES_AID", 3);
define("COLORS_AID", 1);
define("DESIGNORDER_AID", 4);
define("DESIGNCATEGORY_VID", 5);
define("PRODUCTCATALOG_VID", 1);


function getProducts(){
$result = db_query("SELECT node.nid AS nid, node.title AS node_title, dc.field_designcolors_value AS field_designcolors,
node.type AS node_type, node.vid AS node_vid, xo.field_xoffset_value AS field_xoffset, yo.field_yoffset_value AS field_yoffset,
uc_products.sell_price AS sell_price, uc_products.model AS model, pa.field_printareas_value AS field_printareas
FROM node node
LEFT JOIN (select * from content_field_designcolors group by nid) dc ON node.vid = dc.vid
LEFT JOIN content_field_xoffset xo ON node.vid = xo.vid
LEFT JOIN content_field_yoffset yo ON node.vid = yo.vid
LEFT JOIN content_field_printareas pa ON node.vid = pa.vid
LEFT JOIN uc_products uc_products ON node.vid = uc_products.vid WHERE node.type in ('product','customizable_product')");


return $result;
	
}

function getDesigns(){

$result = db_query("SELECT node.nid AS nid, node.vid AS vid, node.title as title, node_data_field_customizable.field_customizable_value AS customizable,
uc.sell_price AS price, node_data_field_customizable.field_designfilename_value AS designfilename
FROM node node  LEFT JOIN content_type_design node_data_field_customizable ON node.vid = node_data_field_customizable.vid
LEFT JOIN uc_products uc on node.vid=uc.vid
WHERE node.type in ('design')");

return $result;		
}

function getDesignColors($nid, $vid){
$result = db_query("SELECT field_designcolors_value as color FROM content_field_designcolors where nid=%d and vid=%d and field_designcolors_value!='null'", $nid, $vid);

return $result;	
}

function getProductColors($nodeid){
//attributeid for colors = 1

$result = db_query("select name, color_value from uc_product_options a left join uc_attribute_options b on a.oid=b.oid 
left outer join colorvalues c on name=color_name where a.nid=%d and b.aid=%d", $nodeid, COLORS_AID);

return $result;
}

function getProductColorEquiv($nodeid, $color_oid){
	$result = db_query("select name, color_value from uc_product_options a left join uc_attribute_options b on a.oid=b.oid 
left outer join colorvalues c on name=color_name where a.nid=%d and b.aid=%d and b.oid=%d", $nodeid, COLORS_AID, $color_oid);

	$color ='';
	foreach($result as $r){
		$color = $r['color_value'];	
	}
	
	return $color;
}

function getProductSizes($nodeid){
//attributeid for sizes = 3
$result = db_query("select b.oid, b.name from uc_product_options a left join uc_attribute_options b on a.oid=b.oid
where a.nid=%d and b.aid=%d", $nodeid, SIZES_AID);

return $result;
}


function getDesignCategories(){
//Design Category is vid=5
$result = db_query("select tid, name from term_data where vid=%d", DESIGNCATEGORY_VID);

return $result;
}

function getProductCategories(){
//Catalog is vid=1
$result = db_query("SELECT t.tid, t.name FROM term_data t left join term_hierarchy h on t.tid=h.tid where vid=%d and parent=0", PRODUCTCATALOG_VID);

$strOptions='';
foreach ($result as $parent){
	$strOptions.="<option value='".$parent['tid']."'>".$parent['name']."</option>";
	$result2 = db_query("SELECT t.tid, t.name FROM term_data t left join term_hierarchy h on t.tid=h.tid where vid=%d and parent=%d", $parent['tid'], PRODUCTCATALOG_VID);
	foreach($result2 as $child){
		$strOptions.="<option value='".$child['tid']."'>  &nbsp;&nbsp;&nbsp;&nbsp;-".$child['name']."</option>";
	}
}

return $strOptions;
}

function getDesignsFromCategory($tid){
$result = db_query("SELECT n.nid AS nid, n.vid AS vid, n.title as title, nc.field_customizable_value AS customizable,
uc.sell_price AS price, nc.field_designfilename_value AS designfilename FROM node n left join term_node tn on n.nid=tn.nid
left join term_data t on tn.tid=t.tid LEFT JOIN content_type_design nc ON n.vid = nc.vid
LEFT JOIN uc_products uc on uc.vid=n.vid where tn.tid=%d", $tid);

return $result;
}

function getProductsFromCategory($tid){
$result = db_query("SELECT t.tid, t.name, n.nid AS nid, n.title AS node_title, dc.field_designcolors_value AS field_designcolors,
n.type AS node_type, n.vid AS node_vid, xo.field_xoffset_value AS field_xoffset, yo.field_yoffset_value AS field_yoffset,
ucp.sell_price AS sell_price, ucp.model AS model, pa.field_printareas_value AS field_printareas
FROM node n left join term_node tn on n.nid=tn.nid
LEFT JOIN term_data t on tn.tid=t.tid LEFT JOIN (select * from content_field_designcolors group by nid) dc ON n.vid = dc.vid
LEFT JOIN content_field_xoffset xo ON n.vid = xo.vid
LEFT JOIN content_field_yoffset yo ON n.vid = yo.vid
LEFT JOIN content_field_printareas pa ON n.vid = pa.vid
LEFT JOIN uc_products ucp ON n.vid = ucp.vid WHERE n.type in ('product', 'customizable_product') and t.tid=%d", $tid);

return $result;
}
function getNodeType($nid){
	$type = db_query("select type from node where nid=%d", $nid);
	
	foreach($type as $type){
		return $type['type'];
	}
}

function getAndCreateSingleDesign($nid){
$designs = db_query("SELECT node.nid AS nid, node.vid AS vid, node.title as title, node_data_field_customizable.field_customizable_value AS customizable,
uc.sell_price AS price, node_data_field_customizable.field_designfilename_value AS designfilename
FROM node node  LEFT JOIN content_type_design node_data_field_customizable ON node.vid = node_data_field_customizable.vid
LEFT JOIN uc_products uc on node.vid=uc.vid
WHERE node.type in ('design') and node.nid=%d", $nid);

	foreach($designs as $design){
		createSingleDesign($design);
	}
}


function createSingleDesign($design){
	$designpath = DESIGN_FOLDER;
	$design_xoffset = 225;
	$design_yoffset = 200;
	$design_scale = 0.25;
		
	$strdesigncolors='';
			
	//if design is customizable, get the design colors
	if($design['customizable']==1){
		$designcolors = getDesignColors($design['nid'], $design['vid']);
		foreach($designcolors as $color)
			$strdesigncolors.=$color['color'].',';
		
		$strdesigncolors = rtrim($strdesigncolors, ',');
		
		echo '<img src="'.$designpath.$design['designfilename'].'" title="'.$design['title'].'" data-parameters=\'{"x": '.$design_xoffset.', "y": '.$design_yoffset.', "colors": "'.$strdesigncolors.'", "designColors": "'.$strdesigncolors.'", "scale": '.$design_scale.', "removable": true, "draggable": true, "rotatable": true, "resizable": true, "price": '.$design['price'].', "boundingBox": "bounding",  "zChangeable": true}\' />';
	}
	else
		echo '<img src="'.$designpath.$design['designfilename'].'" title="'.$design['title'].'" data-parameters=\'{"x": '.$design_xoffset.', "y": '.$design_yoffset.', "scale": '.$design_scale.', "removable": true, "draggable": true, "rotatable": true, "resizable": true, "price": '.$design['price'].', "boundingBox": "bounding",  "zChangeable": true}\' />';
}

function createDesignSelection($filter, $designs){
		
		if($filter=='all')
			$designs = getDesigns();
		
		foreach($designs as $design){
			createSingleDesign($design);
		}
}

function getAndCreateSingleProduct($nid, $defaultColor){
	$products = db_query("SELECT node.nid AS nid, node.title AS node_title, dc.field_designcolors_value AS field_designcolors,
	node.type AS node_type, node.vid AS node_vid, xo.field_xoffset_value AS field_xoffset, yo.field_yoffset_value AS field_yoffset,
	uc_products.sell_price AS sell_price, uc_products.model AS model, pa.field_printareas_value AS field_printareas FROM node node
	LEFT JOIN (select * from content_field_designcolors group by nid) dc ON node.vid = dc.vid
	LEFT JOIN content_field_xoffset xo ON node.vid = xo.vid
	LEFT JOIN content_field_yoffset yo ON node.vid = yo.vid
	LEFT JOIN content_field_printareas pa ON node.vid = pa.vid
	LEFT JOIN uc_products uc_products ON node.vid = uc_products.vid WHERE node.type in ('product', 'customizable_product') and node.nid=%d", $nid);	
	
	if(!empty($products)){
		foreach($products as $product)
			createSingleProduct($product, $defaultColor);
	}
}

function getColorOID($colorvalue){
	$colorid = db_query("select oid from uc_attribute_options uc left outer join colorvalues cv on uc.name=cv.color_name where cv.color_value='$colorvalue'");
	
	foreach($colorid as $oid){
		return $oid['oid'];
	}
}

function createSingleProduct($product, $defaultColor){
		$nid= $product['nid'];
		$model = $product['model'];
		$path = PRODUCT_FOLDER.$model.'/';
		$x = $product['field_xoffset'];
		$y = $product['field_yoffset'];
		$canvasColor = $product['field_designcolors'];
		if($defaultColor=='')
			$defaultColor = $canvasColor;
		
		$price = number_format($product['sell_price'], 2, '.', '');
		
		$printareas = json_decode($product['field_printareas'], TRUE);
		
		$colors = getProductColors($product['nid']);
		$strcolors="";
		
		foreach($colors as $color)
				$strcolors .= $color['color_value'].",";
		
		$strcolors = rtrim($strcolors, ',');
		
		if($printareas != NULL){
			foreach($printareas as $area){
				$name = $area['name'];
				$x2 = $area['x'];
				$y2 = $area['y'];
				$height = $area['height'];
				$width = $area['width'];
				
				//preview photo
				echo '<div class="fpd-product" title="'.$model.'.'.$name.'" data-thumbnail="'.$path.'preview_'.$name.'.png">';
				
				//display price, colors and other attributes in front only
				if($name == 'front')
					echo '<img src="'.$path.'base_'.$name.'.png" title="base.'.$name.'" data-parameters=\'{"x":'.$x.', "y":'.$y.', "paletteColors": "'.$strcolors.'", "colors": "'.$canvasColor.'", "removable": false, "draggable": false, "price": '.$price.', "zChangeable": false, "designColors": "'.$defaultColor.'", "nid":'.$nid.' }\' />';
				else
					echo '<img src="'.$path.'base_'.$name.'.png" title="base.'.$name.'" data-parameters=\'{"x":'.$x.', "y":'.$y.', "colors": "base.front"}\' />';
				
				//highlights
				echo '<img src="'.$path.'highlights_'.$name.'.png" title="highlights.'.$name.'" data-parameters=\'{"x":'.$x.', "y":'.$y.'}\' />';
				//shadows
				echo '<img src="'.$path.'shadows_'.$name.'.png" title="shadows.'.$name.'" data-parameters=\'{"x":'.$x.', "y":'.$y.'}\' />';
				
				//for bounding box
				echo '<img src="images/spacer.gif" title="bounding" data-parameters=\'{"x":'.$x2.', "y":'.$y2.', "width":'.$width.', "height":'.$height.' }\' />';
				
				if($name!='front')
					echo '</div>';
			}
			echo "</div>";
		}
}
function createProductSelection($filter, $products){
	
		if($filter=="all")
			$products = getProducts();
		
		foreach($products as $product){
			createSingleProduct($product, '');
		}//end of foreach product
}

function createDesignOrder($design, $preview, $price){
	db_query("insert into designorder (design_code, price) values ('%s', %d)", $design, $price);
	
	$id =  db_last_insert_id('designorder', 'id');
	
	
	//recreate the base64img to a file
	list($type, $data) = explode(",", $preview);
	file_put_contents(DESIGNORDER_FOLDER.$id.".png", base64_decode($data));
	
	if (!isset($_SESSION)){session_start();}
	$_SESSION['DESIGNRECORD'] = $id;
	$_SESSION['MODE'] = 'new';
	
	return $id;
}

function updateDesignOrder($design, $preview, $price, $designid){
	
	if(isset($_SESSION['DESIGNRECORD'])){
		$num_updated = db_query("update designorder set design_code = '%s', price = %d, timestamp = CURRENT_TIMESTAMP where id= %d", $design, $price, $designid);
	
		//recreate the base64img to a file
		list($type, $data) = explode(",", $preview);
		file_put_contents(DESIGNORDER_FOLDER.$designid.".png", base64_decode($data));
		
		$_SESSION['DESIGNRECORD'] = $designid;
		$_SESSION['MODE'] = 'update';
		  
		  return $designid;		
	}
	else {
		return;
	}
	
}

function retrieveDesignOrder($designorder_id){
	
	//$result = db_query("SELECT * FROM designorder d left join uc_cart_products u  on u.cart_item_id = d.cart_item_id where d.id=%d", $designorder_id);
	$result = db_query("SELECT * FROM designcartitem dc left outer join uc_cart_products u  on dc.cart_item_id = u.cart_item_id 
	join designorder do on dc.design_id = do.id where dc.design_id=%d", $designorder_id);
	
	
	foreach ($result as $row) {
		return $row;
	}
	
	return FALSE;
}

function getDesignOrderCartItems($designorder_id){
	$result = db_query("");
	
	
}


?>
