<?php	

global $base_url;
$drupal_path = $_SERVER['DOCUMENT_ROOT'] . "/designyourown2/";
$base_url = "http://localhost/designyourown2";
$current_path = getcwd();
chdir($drupal_path);
require_once ('./includes/bootstrap.inc');

 drupal_bootstrap(DRUPAL_BOOTSTRAP_SESSION);
 chdir($current_path);
 
 include_once("productdesigner.php");
				
		$nid='';
		$type='';
		$defaultProduct='';
		$defaultColor = '';
		$defaultColorOID = '';
		$defaultSz = '';
		$designCode='';
		$defaultDesignID='';
		$cartItemID = '';
		
		/* get information for passed design id */
		if(isset($_GET['did']) && !empty($_GET['did'])){
			$defaultDesignID = $_GET['did'];
			$designOrder = retrieveDesignOrder($defaultDesignID);
			
			if($designOrder != FALSE){
				$nid = $designOrder['nid'];
				$designCode = $designOrder['design_code'];
			}
					 
		}
		
		/* get information for passed design id */
		if(isset($_GET['itemid']) && !empty($_GET['itemid'])){
			$cartItemID = $_GET['itemid'];
		}
		
		/* get information for passed node */
		if(isset($_GET['nid']) && !empty($_GET['nid'])) {
    		$nid = $_GET['nid'];	
		}
		
		if(!empty($nid))
			$type = getNodeType($nid);
		
		
		/* get information for passed color value */
		if(isset($_GET['color']) && !empty($_GET['color']) && $nid!=''){
			$defaultColorOID = $_GET['color']; 
			$defaultColor = getProductColorEquiv($nid, $_GET['color']);
		}
		else
			$defaultColorOID='-';
		
		/* get information for passed size value */
		if(isset($_GET['sz']) && !empty($_GET['sz']) && $nid!=''){
			$defaultSz = $_GET['sz'];
		}

		
?>
<!DOCTYPE HTML>
    <html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Fancy Product Designer</title>
    
    <!-- Style sheets -->
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css" />
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="stylesheet" type="text/css" href="css/smoothness/jquery-ui-1.9.2.custom.min.css" />
	<link rel="stylesheet" type="text/css" href="css/jquery.fancyProductDesigner.css" />
	<link rel="stylesheet" type="text/css" href="css/jquery.fancyProductDesigner-fonts.css" />
    
    <!-- Include js files -->
	<script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="js/jquery.fancyProductDesigner.js" type="text/javascript"></script>
    <script src="js/jquery-ui.min.js"></script>
            
	<script type="text/javascript">
	
		jQuery(document).ready(function() {
			
			//call the plugin and save it in a variable to have access to the API
			var fpd = $('#fpd').fancyProductDesigner({
				editorMode: false,
				<?php if($type=='product' || $type=='customizable_product') echo  "defaultProduct: '".$nid."',"?>
				<?php if($type=='design') echo  "defaultDesign: '".$nid."',"?>
				<?php if($defaultDesignID !='') echo  "defaultDesignID: '".$defaultDesignID."',"?>
				<?php echo  "defaultColor: '".$defaultColorOID."',"?>
				fonts: ['Arial', 'Helvetica', 'Times New Roman', 'Verdana', 'Geneva', 'Fearless'],
				//these are the parameters for the text that is added via the "Add Text" button
				customTextParamters: {x: 210, y: 250, colors: "#000000", removable: true, resizable: true, draggable: true, rotatable: true, designColors:"#000000"}
			})
			.data('fancy-product-designer');
			
						
			//ADDED BY ACORRAL
			// the change function when new product/design category is implemented here 
			$("select.fpd-designselect").change(function(){
				var tid = this.value;
			});
			//END ADDED BY ACORRAL
			
			//get current price when product is created and update it when price changes
			$('#fpd')
			.bind('productCreate', function(evt){
				$('#thsirt-output').html('Click the "Checkout" button to see the returning object with all properties.');
				$('#thsirt-price').text(fpd.getPrice());
			})
			.bind('priceChange', function(evt, price, currentPrice) {
				$('#thsirt-price').text(currentPrice);
			});
			
			//button to print the product
			$('#print-button').click(function(){
				$('.fpd-views-selection').children("li:first").click();
				setTimeout(fpd.print(), 1000);
				return false;
			});
			
			//button to print the product
			$('#checkout-button').click(function(){
				//get only editable elements
				var product = fpd.getProduct(true);
				var output = '';
				for(var i=0; i < product.length; ++i) {
					output += _getProductOutput(product[i]);
				}
				
				$('#thsirt-output').html(output);
								
				return false;
			});
			
			//recreate products
			$('#recreation-button').click(function(){
				var product = fpd.getProduct(false);
				$('#recreation-form input:first').val(JSON.stringify(product)).parent().submit();
				return false;
			});

			
			//upload image
			document.getElementById('design-upload').onchange = function (e) {
				if(window.FileReader) {
					var reader = new FileReader();
			    	reader.readAsDataURL(e.target.files[0]); 
			    	reader.onload = function (e) {
			    		var image = new Image;
			    		image.src = e.target.result;
			    		image.onload = function() {
			    			//add new image to product
				    		fpd.addElement('image', e.target.result, 'my custom design', {zChangeable: true, removable: true, draggable: true, resizable: true, rotatable: true, x: 200, y: 200});  	
			    		};	    		               
					};
				}
				else {
					alert('FileReader API is not supported in your browser, please use Firefox, Safari, Chrome or IE10!')
				}
			};
			
			//format a product object for the output panel
			function _getProductOutput(product) {
				var output = '<strong>Product:</strong> '+product.title;
				
				output += '<br /><strong>Elements:</strong>';
				output += '<p>';
				$(product.elements).each(function(i, elem) {
					output += '<strong>Title:</strong> ' + elem.title;
					output += '<br />';
					output += '<strong>Parameters:</strong><br />';
					for (var prop in elem.parameters) {
				      output += prop + ": " + elem.parameters[prop] + ', ';
				   }
				   output = output.substring(0, output.length-2);
				   output += '<br /><br />';
				});
				output += '</p>';
				return output;
			};
			
			
		});
 </script>
    
    </head>
    
    <body>
    	<div id="fpd-existingdesign" style="display: none;"><?php echo $designCode; ?></div>
    	<div id="main-container" class="container">      
    	<div id="content">
    		<div id="fpd2" style="display:none"></div>
    		<div id="fpd">
    		<?php
    			//if a product node is passed to the designer, load the product and show on the canvas
				if($nid!='' && ($type=='product' || $type=='customizable_product')){
						getAndCreateSingleProduct($nid, $defaultColor);
					}
				
				//load all products
    			createProductSelection("all", array());
				
				
    		?>
    			
    			<div class="fpd-product" title="shirt.front" data-thumbnail="images/yellow_shirt/front/preview.png">
	    			<img src="images/yellow_shirt/front/base.png" title="base.front" data-parameters='{"x": 123, "y": 81, "paletteColors": "#d59211,#000000,#ffffff", "colors": "#d59211", "removable": false, "draggable": false, "price": 20, "zChangeable": false, "designColors": "#ffffff", "nid":0 }' />
	    			<img src="images/yellow_shirt/front/body.png" title="Hightlights" data-parameters='{"x": 249, "y": 80}' />
			  		<img src="images/yellow_shirt/front/shadows.png" title="Shadow" data-parameters='{"x": 123, "y": 81}' />
			  		<img src="images/spacer.gif" title="bounding" data-parameters='{"x": 195, "y": 145, "width": 200, "height": 350}'/>
			  		<!--<span title="Any Text" data-parameters='{"x": 243, "y": 181, "removable": true, "draggable": true, "rotatable": true, "resizable": true, "colors": "#000000", "designColors": "#000000", "zChangeable": true}' >Default Text</span>-->
			  		<!-- This is another view -->
			  		<div class="fpd-product" title="shirt.back" data-thumbnail="images/yellow_shirt/back/preview.png">
		    			<img src="images/yellow_shirt/back/base.png" title="base.back" data-parameters='{"x": 123, "y": 81, "colors": "base.front", "price": 40}' />
		    			<img src="images/yellow_shirt/back/body.png" title="Hightlights" data-parameters='{"x": 277, "y": 79}' />
				  		<img src="images/yellow_shirt/back/shadows.png" title="Shadow" data-parameters='{"x": 123, "y": 81}' />
				  		<img src="images/spacer.gif" title="bounding" data-parameters='{"x": 195, "y": 145, "width": 200, "height": 150}'/>
					</div>
				</div>
				<div class="fpd-product" title="sweater.front" data-thumbnail="images/sweater/preview.png">
	    			<img src="images/sweater/basic.png" title="base.front" data-parameters='{"x": 120, "y": 76, "colors": "#ce000c", "price": 20, "designColors": "#ce000c", "nid":0}' />
			  		<img src="images/sweater/highlights.png" title="Hightlights" data-parameters='{"x": 123, "y": 76}' />
			  		<img src="images/sweater/shadow.png" title="Shadow" data-parameters='{"x": 123, "y": 81}' />
			  		<img src="images/spacer.gif" title="bounding" data-parameters='{"x": 195, "y": 145, "width": 200, "height": 250}'/>
				</div>
				<div class="fpd-product" title="scooptee.front" data-thumbnail="images/scoop_tee/preview.png">
	    			<img src="images/scoop_tee/basic.png" title="base.front" data-parameters='{"x": 100, "y": 40, "colors": "#ffffff", "price": 15, "designColors": "#ffffff", "nid":0}' />
			  		<img src="images/scoop_tee/highlights.png" title="highlights" data-parameters='{"x":110, "y": 50}' />
			  		<img src="images/scoop_tee/shadows.png" title="Shadow" data-parameters='{"x": 110, "y": 50}' />
			  		<img src="images/scoop_tee/label.png" title="Label" data-parameters='{"x": 276, "y": 86}' />
				</div>
				<div class="fpd-product" title="hoodie.front" data-thumbnail="images/hoodie/preview.png">
	    			<img src="images/hoodie/basic.png" title="base.front" data-parameters='{"x": 100, "y": 60, "colors": "#d59211", "price": 40, "designColors": "#000000", "nid":0}' />
			  		<img src="images/hoodie/highlights.png" title="Hightlights" data-parameters='{"x": 100, "y": 60}' />
			  		<img src="images/hoodie/shadows.png" title="Shadow" data-parameters='{"x": 100, "y": 60}' />
			  		<img src="images/hoodie/zip.png" title="Zip" data-parameters='{"x": 281, "y": 172}' />
				</div>
				<div class="fpd-product" title="shirt2.front" data-thumbnail="images/shirt/preview.png">
	    			<img src="images/shirt/basic.png" title="base.front" data-parameters='{"x": 140, "y": 96, "colors": "#6ebed5", "price": 10, "nid":0}' />
	    			<img src="images/shirt/collar_arms.png" title="Collars & Arms" data-parameters='{"x": 140, "y": 96}' />
			  		<img src="images/shirt/highlights.png" title="Hightlights" data-parameters='{"x": 140, "y": 96}' />
			  		<img src="images/shirt/shadow.png" title="Shadow" data-parameters='{"x": 140, "y": 96}' />
			  		<!--<span title="Any Text" data-parameters='{"x": 243, "y": 181, "removable": true, "draggable": true, "rotatable": true, "resizable": true, "colors": "#000000", "zChangeable": true}' >Default Text</span>-->
				</div>
				<div class="fpd-product" title="short.front" data-thumbnail="images/shorts/preview.png">
	    			<img src="images/shorts/basic.png" title="base.front" data-parameters='{"x": 100, "y": 96, "colors": "#81b5eb", "price": 15, "nid":0}' />
			  		<img src="images/shorts/highlights.png" title="Hightlights" data-parameters='{"x": 102, "y": 96}' />
			  		<img src="images/shorts/pullstrings.png" title="Pullstrings" data-parameters='{"x": 234, "y": 107, "colors": "#ffffff"}' />
			  		<img src="images/shorts/midtones.png" title="Midtones" data-parameters='{"x": 102, "y": 96}' />
			  		<img src="images/shorts/shadows.png" title="Shadow" data-parameters='{"x": 105, "y": 96}' />
				</div>
				<div class="fpd-product" title="basecap.front" data-thumbnail="images/cap/preview.png">
	    			<img src="images/cap/basic.png" title="base.front" data-parameters='{"x": 100, "y": 126, "colors": "#ededed", "price": 5, "nid":0}' />
			  		<img src="images/cap/highlights.png" title="Hightlights" data-parameters='{"x": 114, "y": 141}' />
			  		<img src="images/cap/shadows.png" title="Shadows" data-parameters='{"x": 114, "y": 141}' />
				</div>
		  		<div class="fpd-design">
		  			<?php
		  				//if a product node is passed to the designer, load the product and show on the canvas
						 if($nid!='' && $type=='design'){
							 getAndCreateSingleDesign($nid);
						 }
				
		  				createDesignSelection("all", array());
		  			?>
			  		<img src="images/designs/swirl.png" title="Swirl" data-parameters='{"x": 225, "y": 200, "colors": "#000000,#ffffff,#990000", "removable": true, "draggable": true, "rotatable": true, "resizable": true, "price": 10, "boundingBox": "bounding",  "zChangeable": true}' />
			  		<img src="images/designs/swirl2.png" title="Swirl 2" data-parameters='{"x": 215, "y": 200, "colors": "#000000", "removable": true, "draggable": true, "rotatable": true, "resizable": true, "boundingBox": {"x": 195, "y": 145, "width": 200, "height": 350}, "designColors":"#000000", "zChangeable": true}' />
			  		<img src="images/designs/swirl3.png" title="Swirl 3" data-parameters='{"x": 215, "y": 200, "colors": "#000000", "removable": true, "draggable": true, "rotatable": true, "resizable": true, "boundingBox": {"x": 195, "y": 145, "width": 200, "height": 350}, "designColors":"#000000", "zChangeable": true}' />
			  		<img src="images/designs/converse.png" title="Converse" data-parameters='{"x": 215, "y": 200, "colors": "#000000", "removable": true, "draggable": true, "rotatable": true, "resizable": true, "boundingBox": {"x": 195, "y": 145, "width": 200, "height": 350}, "designColors":"#000000", "zChangeable": true}' />
			  		<img src="images/designs/crown.png" title="Crown" data-parameters='{"x": 215, "y": 200, "colors": "#000000", "removable": true, "draggable": true, "rotatable": true, "resizable": true, "boundingBox": {"x": 195, "y": 145, "width": 200, "height": 350}, "designColors":"#000000", "zChangeable": true}' />
			  		<img src="images/designs/men_women.png" title="Men hits Women" data-parameters='{"x": 215, "y": 200, "colors": "#000000", "removable": true, "draggable": true, "rotatable": true, "resizable": true, "boundingBox": {"x": 195, "y": 145, "width": 200, "height": 350}, "designColors":"#000000", "zChangeable": true}' />
			  		<img src="images/designs/retro_1.png" title="Retro One" data-parameters='{"x": 210, "y": 200, "colors": "#000000", "removable": true, "draggable": true, "rotatable": true, "resizable": true, "scale": 0.25, "boundingBox": {"x": 195, "y": 145, "width": 200, "height": 350}, "designColors":"#000000", "zChangeable": true}' />
			  		<img src="images/designs/retro_2.png" title="Retro Two" data-parameters='{"x": 193, "y": 180, "colors": "#000000", "removable": true, "draggable": true, "rotatable": true, "resizable": true, "scale": 0.46, "boundingBox": {"x": 195, "y": 145, "width": 200, "height": 350}, "designColors":"#000000", "zChangeable": true}' />
			  		<img src="images/designs/retro_3.png" title="Retro Three" data-parameters='{"x": 240, "y": 200, "colors": "#000000", "removable": true, "draggable": true, "rotatable": true, "resizable": true, "scale": 0.25, "boundingBox": {"x": 195, "y": 145, "width": 200, "height": 350}, "designColors":"#000000", "zChangeable": true}' />
			  		<img src="images/designs/heart_circle.png" title="Heart Circle" data-parameters='{"x": 200, "y": 200, "colors": "#007D41", "removable": true, "draggable": true, "rotatable": true, "resizable": true, "scale": 0.4, "boundingBox": {"x": 195, "y": 145, "width": 200, "height": 350}, "designColors":"#007d41", "zChangeable": true}' />
			  		<img src="images/designs/IHeart.png" title="IHeart" data-parameters='{"x": 200, "y": 200, "colors": "#000000,#ce000c", "removable": true, "draggable": true, "rotatable": true, "resizable": true, "scale": 0.25, "boundingBox": {"x": 195, "y": 145, "width": 200, "height": 350}, "designColors": "#000000,#ce000c", "zChangeable": true}' />
		  		</div>
		  		<div id="fpd-design2" style="display:none;"></div>
		  	</div>
		  	<div id="designcategories" style="display:none">
		  		<?php
		  			$cats = getDesignCategories();
					
					foreach ($cats as $cat) {
						echo "<option value='".$cat['tid']."'>".$cat['name']."</option>";
					}
		  		?>
		  	</div>
		  	<div id="prodcategories" style="display:none">
		  		<?php
		  			$prods = getProductCategories();
					
					echo $prods;
					
		  		?>
		  	</div>
		  	<div class="api-buttons clearfix" style="text-align: right;">
			  	<a href="#" id="print-button" class="btn btn-info">Print</a>
			  	<a href="#" id="checkout-button" class="btn btn-success">Checkout</a>
			  	<!-- Only working on a webserver -->
			  	<a href="#" id="recreation-button" class="btn btn-success">Recreate product</a>
				
			  	
			  	<input type="file" id="design-upload" style="display: none;" />
			  	<form action="recreation.php" id="recreation-form" method="post">
					<input type="hidden" name="recreation_product" value="" />
				</form>
			  	
		  	</div>
		  	<div id="thsirt-output" class="output"></div>
	       </div>   
    	</div>
    	
    	<div class="size-select" title="Add Qty and Size" style="display:none">
    		<form>
    		Qty: <input type="text" id="input_qty" size="3" maxlength="3" value="1" style="width:30px"/>
  			Size: <select id="input_sz" style="width:130px;">
  				
  			</select>
  			<a href="javascript:void(0)" id="addQty-button">Add</a>
  			<div id="qty-sz-div">
  				<ul id="qty-sz-ul">
  				</ul>
  				<div class="pull-right">
	  				<a id="addtocart-cancel" href="javascript:void(0)">Cancel</a>
	  				<?php 
	  					$submitButtonText = ($cartItemID=='') ? 'Add To Cart' : 'Update Order';
	  				?>
	  					<a id="addtocart-submit" href="#" class="btn btn-success" style="margin-bottom:10px" ><?= $submitButtonText ?></a>
  				</div>
  			</div>	
  		</form>
    	</div>
    	<input type="text" id="testing" value="none" />
    </body>
</html>