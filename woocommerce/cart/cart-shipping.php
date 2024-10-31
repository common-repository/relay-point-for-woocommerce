<?php

/**
 * Shipping Methods Display
 *
 * In 2.1 we show methods per package. This allows for multiple methods per order if so desired.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-shipping.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

$formatted_destination    = isset($formatted_destination) ? $formatted_destination : WC()->countries->get_formatted_address($package['destination'], ', ');
$has_calculated_shipping  = !empty($has_calculated_shipping);
$show_shipping_calculator = !empty($show_shipping_calculator);
$calculator_text          = '';
?>
<tr class="woocommerce-shipping-totals shipping">
	<th><?php echo wp_kses_post($package_name); ?></th>
	<td data-title="<?php echo esc_attr($package_name); ?>">
		<?php if ($available_methods) : ?>
			<ul id="shipping_method" class="woocommerce-shipping-methods">
				<?php
				if (count($available_methods) <= 1) {
					foreach ($available_methods as $method) :
						echo '<li>';
						printf('<input type="hidden" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" />', $index, esc_attr(sanitize_title($method->id)), esc_attr($method->id));
						echo '</li>';
					endforeach;
				} else {
					$WCRelay = array();
					foreach ($available_methods as $method) :
						if (esc_attr(sanitize_title($method->method_id)) != 'wc_point_relais') { ?>
							<li>
								<?php
								printf('<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s />', $index, esc_attr(sanitize_title($method->id)), esc_attr($method->id), checked($method->id, $chosen_method, false));
								printf('<label for="shipping_method_%1$s_%2$s">%3$s</label>', $index, esc_attr(sanitize_title($method->id)), wc_cart_totals_shipping_method_label($method));
								do_action('woocommerce_after_shipping_rate', $method, $index);
								?>
							</li>
							<?php
						} else {
							array_push($WCRelay, $method->id);
						}
					endforeach;

					if (count($WCRelay) > 0) {
						$CurrentChecked = in_array($chosen_method, $WCRelay) ? $chosen_method : $WCRelay[0];
						echo '<div class="relay_wrapper">';
						printf('<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" style="margin: 3px .4375em 0 0;" value="%3$s" class="shipping_method" %4$s />', $index, esc_attr(sanitize_title($CurrentChecked)), esc_attr($CurrentChecked), checked($CurrentChecked, $chosen_method, false));
						printf('<label for="shipping_method_%1$s_%2$s">%3$s</label>', $index, esc_attr(sanitize_title($CurrentChecked)), __('Relay point', 'relay-point-for-woocommerce'));
						do_action('woocommerce_after_shipping_rate', $method, $index);

						printf('<select class="%1$s" style="display: block; margin-left: 25px; margin-top: 10px;">', checked($CurrentChecked, $chosen_method, false) ? '' : "disabled");
						foreach ($available_methods as $method) :
							if (esc_attr(sanitize_title($method->method_id)) == 'wc_point_relais') {
							?>
								<li>
									<?php
									printf('<option name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method">%4$s</option>', $index, esc_attr(sanitize_title($method->id)), esc_attr($method->id), wc_cart_totals_shipping_method_label($method));
									?>
								</li>
						<?php
							}
						endforeach;
						echo '</select>';
						?>
						</div>
						<style>
							.relay_wrapper .disabled {
								display: none !important;
							}
						</style>
						<script type="text/javascript">
							jQuery('.relay_wrapper input')[0].checked == false ? jQuery('.relay_wrapper select').attr('disabled', true) : jQuery('.relay_wrapper select').removeAttr('disabled');
							jQuery('.relay_wrapper select').on('change', function() {
								let name = this.selectedOptions[0].attributes.name.value;
								let data = this.selectedOptions[0].attributes['data-index'].value;
								let id = this.selectedOptions[0].attributes.id.value;
								let value = this.selectedOptions[0].attributes.value.value;

								jQuery('.relay_wrapper input').attr('name', name).attr('value', value).attr('data-index', data).attr('id', id);
								jQuery('body').trigger('update_checkout');
							});
						</script>

				<?php
						function action_woocommerce_checkout_update_order_review($array, $int)
						{
							WC()->cart->calculate_shipping();
							return;
						}
						add_action('woocommerce_checkout_update_order_review', 'action_woocommerce_checkout_update_order_review', 10, 2);
					}
				}
				?>
			</ul>

			<?php if (is_cart()) : ?>
				<p class="woocommerce-shipping-destination">
					<?php
					if ($formatted_destination) {
						// Translators: $s shipping destination.
						printf(esc_html__('Shipping to %s.', 'woocommerce') . ' ', '<strong>' . esc_html($formatted_destination) . '</strong>');
						$calculator_text = esc_html__('Change address', 'woocommerce');
					} else {
						echo wp_kses_post(apply_filters('woocommerce_shipping_estimate_html', __('Shipping options will be updated during checkout.', 'woocommerce')));
					}
					?>
				</p>
			<?php endif; ?>
		<?php
		elseif (!$has_calculated_shipping || !$formatted_destination) :
			if (is_cart() && 'no' === get_option('woocommerce_enable_shipping_calc')) {
				echo wp_kses_post(apply_filters('woocommerce_shipping_not_enabled_on_cart_html', __('Shipping costs are calculated during checkout.', 'woocommerce')));
			} else {
				echo wp_kses_post(apply_filters('woocommerce_shipping_may_be_available_html', __('Enter your address to view shipping options.', 'woocommerce')));
			}
		elseif (!is_cart()) :
			echo wp_kses_post(apply_filters('woocommerce_no_shipping_available_html', __('There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'woocommerce')));
		else :
			// Translators: $s shipping destination.
			echo wp_kses_post(apply_filters('woocommerce_cart_no_shipping_available_html', sprintf(esc_html__('No shipping options were found for %s.', 'woocommerce') . ' ', '<strong>' . esc_html($formatted_destination) . '</strong>')));
			$calculator_text = esc_html__('Enter a different address', 'woocommerce');
		endif;
		?>

		<?php if ($show_package_details) : ?>
			<?php echo '<p class="woocommerce-shipping-contents"><small>' . esc_html($package_details) . '</small></p>'; ?>
		<?php endif; ?>

		<?php if ($show_shipping_calculator) : ?>
			<?php woocommerce_shipping_calculator($calculator_text); ?>
		<?php endif; ?>
	</td>
</tr>