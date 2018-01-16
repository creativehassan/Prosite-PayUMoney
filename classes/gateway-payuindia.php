<?php

/*
Pro Sites (Gateway: Payu Money Payment Gateway)
*/
if ( ! class_exists( 'ProSites_Gateway_PayUMoneyIndia' ) ) {
	class ProSites_Gateway_PayUMoneyIndia {
		
		public static $pending_str = array();
		private static $complete_message = false;
		private static $cancel_message = false;

		function __construct() {
			global $psts;

			/* //Paypal Functions
			if ( ! class_exists( 'PaypalApiHelper' ) ) {
				require_once( $psts->plugin_dir . "gateways/gateway-paypal-files/class-paypal-api-helper.php" );
			} */
			if ( ! is_admin() ) {
				add_action( 'wp_enqueue_scripts', array( &$this, 'do_scripts' ) );
			}
			//checkout stuff
			add_filter( 'psts_force_ssl', array( &$this, 'force_ssl' ) );

			//handle IPN notifications
			add_action( 'wp_ajax_nopriv_psts_pypl_ipn', array( &$this, 'ipn_handler' ) );

			//plug management page
			add_action( 'psts_subscription_info', array( &$this, 'subscription_info' ) );
			add_action( 'psts_subscriber_info', array( &$this, 'subscriber_info' ) );
			add_action( 'psts_modify_form', array( &$this, 'modify_form' ) );
			add_action( 'psts_modify_process', array( &$this, 'process_modify' ) );
			add_action( 'psts_transfer_pro', array( &$this, 'process_transfer' ), 10, 2 );

			//filter payment info - DEPRECATED
			//add_action( 'psts_payment_info', array( &$this, 'payment_info' ), 10, 2 );

			//return next payment date for emails
			add_filter( 'psts_next_payment', array( &$this, 'next_payment' ) );

			//transaction hooks
			add_filter( 'prosites_transaction_object_create', array(
				'ProSites_Gateway_PayPalExpressPro',
				'create_transaction_object'
			), 10, 3 );

			//cancel subscriptions on blog deletion
			add_action( 'delete_blog', array( &$this, 'cancel_subscription' ) );

			/* This sets the default prefix to the paypal custom field,
			 * in case you use the same account for multiple IPN requiring scripts,
			 * and want to setup your own forwarding script somewhere to pass IPNs to
			 * the proper location. If that is the case you will also need to define
			 * PSTS_IPN_PASSWORD and post "inc_pass" along with the IPN string.
			 */
			if ( ! defined( 'PSTS_PYPL_PREFIX' ) ) {
				define( 'PSTS_PYPL_PREFIX', 'psts' );
			}

			add_action( 'psts_checkout_page_load', array( $this, 'process_checkout_form' ) );
			self::$pending_str = array(
				'address'        => __( 'The payment is pending because your customer did not include a confirmed shipping address and your Payment Receiving Preferences is set such that you want to manually accept or deny each of these payments. To change your preference, go to the Preferences  section of your Profile.', 'psts' ),
				'authorization'  => __( 'The payment is pending because it has been authorized but not settled. You must capture the funds first.', 'psts' ),
				'echeck'         => __( 'The payment is pending because it was made by an eCheck that has not yet cleared.', 'psts' ),
				'intl'           => __( 'The payment is pending because you hold a non-U.S. account and do not have a withdrawal mechanism. You must manually accept or deny this payment from your Account Overview.', 'psts' ),
				'multi_currency' => __( 'You do not have a balance in the currency sent, and you do not have your Payment Receiving Preferences set to automatically convert and accept this payment. You must manually accept or deny this payment.', 'psts' ),
				'order'          => __( 'The payment is pending because it is part of an order that has been authorized but not settled.', 'psts' ),
				'paymentreview'  => __( 'The payment is pending while it is being reviewed by PayPal for risk.', 'psts' ),
				'unilateral'     => __( 'The payment is pending because it was made to an email address that is not yet registered or confirmed.', 'psts' ),
				'upgrade'        => __( 'The payment is pending because it was made via credit card and you must upgrade your account to Business or Premier status in order to receive the funds. It can also mean that you have reached the monthly limit for transactions on your account.', 'psts' ),
				'verify'         => __( 'The payment is pending because you are not yet verified. You must verify your account before you can accept this payment.', 'psts' ),
				'other'          => __( 'The payment is pending for an unknown reason. For more information, contact PayPal customer service.', 'psts' ),
				'*'              => ''
			);
		}
		
		function settings() {
			global $psts;
			$display_paypal_pro_option = $psts->get_setting('display_paypal_pro_option', false);
			?>
			<div class="inside">
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'PayuMoney Credentials', 'psts' ) ?></th>
						<td>
							<span
								class="description"><?php _e( 'You must login to PayuMoney and get these credentials.', 'psts' ) ?></span>

							<p><label><?php _e( 'Key', 'psts' ) ?><br/>
									<input value="<?php esc_attr_e( $psts->get_setting( "pumoney_api_key" ) ); ?>"
										   style="width: 100%; max-width: 500px;" name="psts[pumoney_api_key]" type="text"/>
								</label></p>

							<p><label><?php _e( 'Salt', 'psts' ) ?><br/>
									<input value="<?php esc_attr_e( $psts->get_setting( "pumoney_api_salt" ) ); ?>"
										   style="width: 100%; max-width: 500px;" name="psts[pumoney_api_salt]" type="text"/>
								</label></p>
						</td>
					</tr>
				</table>
			</div>
			<!--		</div>-->
			<?php
		}
		
		public static function get_slug() {
			return 'payumoney';
		}
		
		function do_scripts() {
			global $psts;
			/** get_the_ID() gives a notice on wordpress files as get_post() returns null, a ticket is on the way */
			if ( ! is_page() || get_the_ID() != $psts->get_setting( 'checkout_page' ) ) {
				return;
			}

			wp_enqueue_script( 'jquery' );
			add_action( 'wp_head', array( &$this, 'checkout_js' ) );
		}
	}
}

//register the gateway
psts_register_gateway( 'ProSites_Gateway_PayUMoneyIndia', __( 'Pay U Money India', 'psts' ), __( 'PayUmoney is best payment gateway company in India. Signup and collect credit card/debit cards payments online and offline with PayUmoney payment gateway.', 'psts' ) );