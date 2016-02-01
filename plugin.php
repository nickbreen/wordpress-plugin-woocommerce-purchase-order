<?php
/*
Plugin Name: WooCommerce Purchase Order Payment Gateway
Version: 1.0
Description: Adds a purchase order payment gateway.
Author: Nick Breen
Author URI: http://foobar.net.nz
Plugin URI: https://github.com/nickbreen/wordpress-plugin-woocommerce-gateway-po
Text Domain: woocommerce
Domain Path: /languages
*/
add_action( 'plugins_loaded', function () {
  class WC_Gateway_Purchase_Order extends WC_Gateway_Cheque {
      /**
       * Constructor for the gateway.
       */
      public function __construct() {
          $this->id = 'po';
          $this->has_fields = true;
          $this->method_title = __( 'Purchase Order', 'woocommerce' );
          $this->method_description = __( 'Allows purchase orders.', 'woocommerce' );

          $this->init_form_fields();
          $this->init_settings();

          $this->enabled = $this->get_option( 'enabled' );
          $this->title = $this->get_option( 'title' );
          $this->description = $this->get_option( 'description' );

          add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
      }

      /**
       * Initialise Gateway Settings Form Fields.
       */
      public function init_form_fields() {

          $this->form_fields = array(
              'enabled' => array(
                  'title'   => __( 'Enable/Disable', 'woocommerce' ),
                  'type'    => 'checkbox',
                  'label'   => __( 'Enable Purchase Order Payment', 'woocommerce' ),
                  'default' => 'yes'
              ),
              'title' => array(
                  'title'       => __( 'Title', 'woocommerce' ),
                  'type'        => 'text',
                  'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                  'default'     => __( 'Purchase Order', 'woocommerce' ),
                  'desc_tip'    => true,
              ),
              'description' => array(
                  'title'       => __( 'Description', 'woocommerce' ),
                  'type'        => 'textarea',
                  'description' => __( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
                  'default'     => __( 'Please enter your Purchase Order Reference.', 'woocommerce' ),
                  'desc_tip'    => true,
              ),
              'instructions' => array(
                  'title'       => __( 'Instructions', 'woocommerce' ),
                  'type'        => 'textarea',
                  'description' => __( 'Instructions that will be added to the thank you page and emails.', 'woocommerce' ),
                  'default'     => '',
                  'desc_tip'    => true,
              ),
          );
      }

      /**
       * Validate frontend fields.
       *
       * Validate payment fields on the frontend.
       *
       * @return bool
       */
      public function validate_fields() {
          $fields_defs = array(
            "{$this->id}-po-no" => array(
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

          $fields_filtered = filter_input_array(INPUT_POST, $fields_defs, TRUE);

          foreach ($fields_filtered as $field => $result)
            if (!$result)
              wc_add_notice($fields_defs[$field]['notice'], 'error');

          // If any field as missing or invalid...
          return !in_array(FALSE, $fields_filtered) && !in_array(NULL, $fields_filtered);
      }

      /**
       * Payment form on checkout page.
       */
      public function payment_fields() {
          $description = $this->get_description();

          if ( $description ) {
              echo wpautop( wptexturize( trim( $description ) ) );
          }

          // TODO change the required span to label.required:after
          $fields = array(
            'po-no' => sprintf('<p class="form-row form-row-wide validate-required"><label for="%1$s%2$s">%3$s&nbsp;<span class="required">*</span></label><input id="%1$s%2$s" class="input-text" type="text" name="%1$s%2$s" /><p>', esc_attr( $this->id ), '-po-no', __( 'Purcahse Order Reference', 'woocommerce' )),
          );

          printf('<fieldset id="%1$s%2$s">%3$s<div class="clear"></div></fieldset>', esc_attr( $this->id ), '-po-form', implode(PHP_EOL, $fields));
      }

  }

  add_filter( 'woocommerce_payment_gateways', function ($load_gateways) {
    $load_gateways[] = 'WC_Gateway_Purchase_Order';
    return $load_gateways;
  });


});
