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

// We'll put out new fields into the checkout form's $form section
$form = 'order';
$form_fields = array(
  $form => $fields,
);

// Label for the admin edit order section
$order_details_label = 'Order Details';

// Validation filters for our new fields.
$field_filters = array(
  'purchase_order_reference' => array(
    'filter' => FILTER_CALLBACK,
    'flags' => FILTER_REQUIRE_SCALAR,
    'options' => function ($value) {
      // if the string does not contain anything unusual
      return filter_var($value, FILTER_SANITIZE_STRING) === $value ? $value : FALSE;
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
    if (FALSE === $result) // if filter failed, which is not OK
      wc_add_notice($field_filters[$field]['notice'], 'error');
});

add_action( 'woocommerce_checkout_update_order_meta', function ($order_id) use ($fields, $field_filters) {
  $filtered_fields = filter_input_array(INPUT_POST, $field_filters, TRUE);
  foreach ($filtered_fields as $field => $result)
    if (FALSE !== $result &&  NULL !== $result)
      update_post_meta($order_id, $field, $result);
});

add_filter('woocommerce_email_order_meta_fields', function ($email_fields, $sent_to_admin, $order) use ($fields) {
  foreach ($fields as $field => $def)
    $email_fields[] = array(
      'label' => $def['label'],
      'value' => get_post_meta($order->id, $field, TRUE),
    );
  return $email_fields;
});

add_action('woocommerce_admin_order_data_after_order_details', function ($order) use ($fields, $order_details_label) {
  foreach ($fields as $field => $def)
    if ($value = get_post_meta($order->id, $field, TRUE))
      $s .= sprintf('<p class="form-field form-field-wide"><label for="%1$s">%2$s:</label><input type="text" id="%1$s" value="%3$s" readonly/></p>', esc_attr($field), esc_html($def['label']), esc_attr($value));
  if ($s)
    printf('<div class="clear"></div><h4 style="clear: both">%s</h4><div class="address">%s</div>', $order_details_label, $s);
});

add_action('woocommerce_thankyou', function ($order_id) use ($fields) {
  foreach ($fields as $field => $def)
    if ($value = get_post_meta($order_id, $field, TRUE))
      $s .= sprintf('<tr><th>%s:</th><td>%s<td/></tr>', esc_html($def['label']), esc_html($value));
  if ($s)
    printf('<table class="shop_table order_details">%s</table>', $s);
},5);
