<?php
/*
Plugin Name: WooCommerce Checkout Purchase Order Field
Version: 1.0
Description: Adds a purchase order field to the checkout.
Author: Nick Breen
Author URI: http://foobar.net.nz
Plugin URI: https://github.com/nickbreen/wordpress-plugin-woocommerce-purchase-order
*/

$new_fields['order']['order_purchase_reference'] = array(
  'type' => 'text', //type of field (text, textarea, password, select)
  'label' => 'Purchase Order Reference', //label for the input field
  // 'placeholder' => '', //placeholder for the input
  // 'class' => '', //class for the input
  'required' => true, //true or false, whether or not the field is require
  // 'clear' => false, //true or false, applies a clear fix to the field/label
  // 'label_class' => '', //class for the label element
  // 'options' => '', //for select boxes, array of options (key => value pairs)
);
$field_filters = array(
  'order_purchase_reference' => array(
    'filter' => FILTER_CALLBACK,
    'flags' => FILTER_REQUIRE_SCALAR,
    'options' => function ($value) {
      $sanitised = filter_var($value, FILTER_SANITIZE_STRING);
      // if the string does not contain anything unusual
      return $sanitised === $value
      // and there's actually a value
      && strlen($sanitised);
    },
    'notice' => '<strong>Purchase Order Reference</strong> is a required field.'
  ),
);

add_filter( 'woocommerce_checkout_fields' , function ($fields) use ($new_fields) {
  return array_merge_recursive($fields, $new_fields);
});

add_action('woocommerce_checkout_process', function () use ($field_filters) {
  $fields_filtered = filter_input_array(INPUT_POST, $field_filters, TRUE);
  foreach ($fields_filtered as $field => $result)
    if (!$result)
      wc_add_notice($field_filters[$field]['notice'], 'error');
});

add_action( 'woocommerce_checkout_update_order_meta', function ($order_id) use ($field_filters) {
  $fields_filtered = filter_input_array(INPUT_POST, $field_filters, TRUE);
  foreach ($fields_filtered as $field => $result)
    if ($result)
      update_post_meta($order_id, 'Purchase Order Reference', implode(';', $result));
});
