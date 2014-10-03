<?php
// $Id: template.php,v 1.5 2010/09/17 21:36:06 eternalistic Exp $

/**
 * Changed breadcrumb separator
 */
function dyotheme_breadcrumb($breadcrumb) {
  if (!empty($breadcrumb)) {
    return '<div class="breadcrumb">'. implode(' &rarr; ', $breadcrumb) .'</div>';
  }
}
function dyotheme_uc_cart_view_form($form) {
	dsm($form);
  drupal_add_css(drupal_get_path('module', 'uc_cart') .'/uc_cart.css');
 
  $output = '<div id="cart-form-products">'
          . drupal_render($form['items']) .'</div>';
 
  foreach (element_children($form['items']) as $i) {
    foreach (array('title', 'options', 'remove', 'image', 'qty') as $column) {
      $form['items'][$i][$column]['#printed'] = TRUE;
    }
    $form['items'][$i]['#printed'] = TRUE;
  }
 
  // Add the continue shopping element and cart submit buttons.
  if (($type = variable_get('uc_continue_shopping_type', 'link')) != 'none') {
    // Render the continue shopping element into a variable.
    $cs_element = drupal_render($form['continue_shopping']);
 
    // Add the element with the appropriate markup based on the display type.
    if ($type == 'link') {
      $output .= '<div id="cart-form-buttons"><div id="continue-shopping-link">'
               . $cs_element .'</div>'. drupal_render($form) .'</div>';
    }
    elseif ($type == 'button') {
      $output .= '<div id="cart-form-buttons"><div id="update-checkout-buttons">'
               . drupal_render($form) .'</div><div id="continue-shopping-button">'
               . $cs_element .'</div></div>';
    }
  }
  else {
    $output .= '<div id="cart-form-buttons">'. drupal_render($form) .'</div>';
  }
 
  return $output;
}