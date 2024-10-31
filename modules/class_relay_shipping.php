<?php
function wc_point_relais_init()
{
    if (class_exists('WC_Point_relais'))
        return;
    class WC_Point_relais extends WC_Shipping_Method
    {
        public function __construct($instance_id = 0)
        {
            $this->id = 'wc_point_relais';
            $this->instance_id = absint($instance_id);
            $this->method_title = __('Relay point', 'relay-point-for-woocommerce');
            $this->method_description = __('Allow customers to choose from a list of relay points where they can collect their orders.', 'relay-point-for-woocommerce');
            $this->enabled = 'yes';
            $this->supports = array('shipping-zones', 'instance-settings-modal', 'instance-settings');

            $this->instance_form_fields = array(
                'title' => array(
                    'title' => __('Title', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                    'default' => __('Relay point', 'relay-point-for-woocommerce'),
                    'desc_tip' => true
                ),
                'rate' => array(
                    'title' => __('Cost', 'woocommerce'),
                    'type' => 'number',
                    'description' => __('Enter a cost (excluding tax) or formula, e.g. 10.00 * [qty].<br><br>Use [qty] for the number of items, [cost] for the total cost of the items, and [fee percent="10" min_fee="20" max_fee=""] for the fee on a percentage basis.', 'relay-point-for-woocommerce'),
                    'default' => 0,
                    'desc_tip' => true
                ),
                'free_amount' => array(
                    'title' => __('Free delivery', 'relay-point-for-woocommerce'),
                    'type' => 'number',
                    'description' => __('Amount from which shipping becomes free', 'relay-point-for-woocommerce'),
                    'default' => null,
                    'desc_tip' => true
                ),
            );

            $this->init();

            $this->title = null !== $this->get_option('title') ? $this->get_option('title') : __('Relay point', 'relay-point-for-woocommerce');
        }

        public function init()
        {
            // Load the settings API
            $this->init_form_fields();
            $this->init_settings();

            // Save settings in admin if you have any defined
            add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
        }

        public function calculate_shipping($package = array())
        {

            $cost = '' !== $this->get_option('rate') ? $this->get_option('rate') : 0;
            $free = '' !== $this->get_option('free_amount') ? $this->get_option('free_amount') : null;
            $isFree = $free !== null ? WC()->cart->get_subtotal() >= $free : false;

            $this->add_rate(
                array(
                    'id' => $this->id . $this->instance_id,
                    'label' => $this->title,
                    'cost' => $isFree ? 0 : $cost,
                )
            );
        }
    }
}

add_action('woocommerce_shipping_init', 'wc_point_relais_init');

function add_wc_point_relais($methods)
{
    $methods['wc_point_relais'] = 'WC_Point_relais';
    return $methods;
}

add_filter('woocommerce_shipping_methods', 'add_wc_point_relais');
