<?php

/*
 This php script receives a JSON encoded string (JSON.stringify) which is sent via $_POST. It gets the JSON encoded string from the getProduct() method.
 When using this script, you should use absolute pathes for your images or place this script in the same folder where you are using the product designer.
*/

?>

<!DOCTYPE HTML>
    <html>
    <head>
    
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <title>Fancy Product Designer - Recreation</title>
    
    <!-- Style sheets -->
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="css/jquery.fancyProductDesigner-fonts.css" />
	<style type="text/css">
		.fpd-recreation {
			width: 575px;
			background: white;
			position: relative;
			margin: 0 auto;
		}
		
		.fpd-recreation div {
			position: relative;
			height: 570px;
		}
		
		.fpd-recreation img, .fpd-recreation canvas, .fpd-recreation span {
			position: absolute;
		}
	</style>
    
    <!-- Include js files -->
	<script src="js/jquery.min.js" type="text/javascript"></script>
            
	<script type="text/javascript">

		jQuery(document).ready(function() {
			
			//pass the sent product from $_POST			
			recreateProduct('#recreation-container', <?php echo stripslashes($_POST['recreation_product']); ?>);
			
			/*BEGIN ADDED BY ACORRAL*/
			function isApprox(a, b, range) {
  				var d = a - b;
  				return d < range && d > -range;
			}
			/*END ADDED BY ACORRAL*/

			function recreateProduct(container, product) {

				//converts hex colors ro rgb
				var _HexToR = function(h) {return parseInt((_cutHex(h)).substring(0,2),16)};
				var _HexToG = function(h) {return parseInt((_cutHex(h)).substring(2,4),16)};
				var _HexToB = function(h) {return parseInt((_cutHex(h)).substring(4,6),16)};
				var _cutHex = function(h) {return (h.charAt(0)=="#") ? h.substring(1,7):h};
				
				var $container = $(container).addClass('fpd-recreation');

				for(var i=0; i < product.length; ++i) {
					$container.append('<div></div>');
					_createSingleProduct($container.children('div:last'), product[i]);
				}
				
				function _createSingleProduct($productContainer, product) {
					//loop through all elements
					for(var i=0; i < product.elements.length; ++i) {
					
						var element = product.elements[i],
							elementParameters = product.elements[i].parameters;
						
						
						//create text
						if(elementParameters.text) {
							
							$productContainer.append('<span>'+elementParameters.text+'</span>')
							.children('span:last').css({left: elementParameters.x, top: elementParameters.y, 'z-index': elementParameters.z, 
														color: elementParameters.currentColor, 'fontFamily': elementParameters.font, 'fontSize': elementParameters.textSize});
														
							_rotateElement($productContainer.children('span:last'), elementParameters.degree);							
						}
						//create canvas 
						else if(elementParameters.currentColor) {
							
							var image = new Image();
							image.src = element.source;
							$(image).data('params', elementParameters);
							image.onload = function() {
								var canvas = document.createElement('canvas'), canvasContext = canvas.getContext('2d'),
									params = $(this).data('params');
								canvas.width = this.width;
								canvas.height = this.height;
								canvasContext.drawImage(this, 0, 0);
								var imageData = canvasContext.getImageData(0, 0, canvas.width, canvas.height);
							    var data = imageData.data;
							    
							    /*BEGIN ADDED BY ACORRAL*/
							    var colors = {};
							    var designColors={};
							   	
            					if(typeof params.colors != 'undefined' && typeof params.designColors != 'undefined')
            					{
            						colors = params.colors;
            						designColors = params.designColors;
            					}
                				
                				if(colors.length>0 && designColors.length>0)
                				{
                					for(var i=0; i< designColors.length; i++){
                						
                						var oldColor = colors[i];
                						var newColor = designColors[i];
                						
                						var newRed =_HexToR(newColor);
            							var newGreen =_HexToG(newColor);
            							var newBlue =_HexToB(newColor);
            							
            							var oldRed =_HexToR(oldColor);
	            		 				var oldGreen =_HexToG(oldColor);
	            		 				var oldBlue =_HexToB(oldColor);
	            		 				
						                for (var j = 0; j < data.length; j += 4) {
						                	if(isApprox(data[j], oldRed, 120) && isApprox(data[j + 1], oldGreen, 120) && isApprox(data[j + 2], oldBlue, 90)){
						                		data[j] = newRed;
										        data[j + 1] = newGreen;
										        data[j + 2] = newBlue;
						                	}    
								    	}
                					}
                				}
                				/*END ADDED BY ACORRAL*/	
                				else{
                					for (var j = 0; j < data.length; j += 4) {
								        data[j] = _HexToR(params.currentColor);
								        data[j + 1] = _HexToG(params.currentColor);
								        data[j + 2] = _HexToB(params.currentColor);
								    }
                				}
								
								    
							    // overwrite original image
							    canvasContext.putImageData(imageData, 0, 0);
								$productContainer.append(canvas);
								$(canvas).width(params.width).height(params.height).css({left: params.x, top: params.y, 'z-index': params.z});
								
								_rotateElement($productContainer.children('canvas:last'), params.degree);
							}
							
						}
						//create just an image
						else {
							$productContainer.append('<img src="'+element.source+'" width='+elementParameters.width+' height='+elementParameters.height+' />')
							.children('img:last').css({left: elementParameters.x, top: elementParameters.y, 'z-index': elementParameters.z});
							
							_rotateElement($productContainer.children('img:last'), elementParameters.degree);
						}
						
						
						
					}
				};
				
				function _rotateElement(elem, degree) {
					//set a degree
					if(degree) {
						elem.css('-moz-transform', 'rotate('+degree+'deg)');
				        elem.css('-webkit-transform', 'rotate('+degree+'deg)');
				        elem.css('-o-transform', 'rotate('+degree+'deg)');
				        elem.css('-ms-transform', 'rotate('+degree+'deg)');
					}
				};
				
			};
	
		});
    </script>
    
    </head>
    
    <body>
    	<div class="container">
    		<div id="recreation-container">
    		</div>  
    	</div>
    </body>
</html>