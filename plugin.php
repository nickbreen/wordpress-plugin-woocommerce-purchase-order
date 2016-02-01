<?php
/*
Plugin Name: WooCommerce Checkout Purchase Order Field
Version: 1.0
Description: Adds a purchase order field to the checkout.
Author: Nick Breen
Author URI: http://foobar.net.nz
Plugin URI: https://github.com/nickbreen/wordpress-plugin-woocommerce-purchase-order
*/

// Our new fields.
$fields = array(
  'purchase_order_reference' => array(
    'type' => 'text', //type of field (text, textarea, password, select)
    'label' => 'Purchase Order Reference', //label for the input field
    // 'placeholder' => '', //placeholder for the input
    // 'class' => '', //class for the input
    // 'required' => false, //true or false, whether or not the field is require
    // 'clear' => false, //true or false, applies a clear fix to the field/label
    // 'label_class' => '', //class for the label element
    // 'options' => '', //for select boxes, array of options (key => value pairs)
  ),
);

// We'll put out new fields into the checkout form's billing section
$form_fields = array(
  'billing' => $fields,
);

// Validation filters for our new fields.
$field_filters = array(
  'purchase_order_reference' => array(
    'filter' => FILTER_CALLBACK,
    'flags' => FILTER_REQUIRE_SCALAR,
    'options' => function ($value) {
      // if the string does not contain anything unusual
      return $value === filter_var($value, FILTER_SANITIZE_STRING) ? $value : FALSE;
    },
    'notice' => sprintf('<strong>%s</strong> is invalid.', $fields['purchase_order_reference']['label']),
  ),
);

add_filter( 'woocommerce_checkout_fields' , function ($fields) use ($form_fields) {
  return array_merge_recursive($fields, $form_fields);
});

add_action('woocommerce_checkout_process', function () use ($field_filters) {
  $filtered_fields = filter_input_array(INPUT_POST, $field_filters, TRUE);
  foreach ($filtered_fields as $field => $result)
    // $result === NULL if unspecified, which is OK
    if ($result === FALSE) // if filter failed, which is not OK
      wc_add_notice($field_filters[$field]['notice'], 'error');
});

add_action( 'woocommerce_checkout_update_order_meta', function ($order_id) use ($fields, $field_filters) {
  $filtered_fields = filter_input_array(INPUT_POST, $field_filters, TRUE);
  foreach ($filtered_fields as $field => $result)
    if ($result !== FALSE && $result !== NULL) {
      update_post_meta($order_id, $field, serialize($result));
      update_post_meta($order_id, $fields[$field]['label'], $result);
    }
});

// TODO add to order received
// TODO add to order confirmation email
// TODO add to new order email
// TODO add to order details box next to notes
