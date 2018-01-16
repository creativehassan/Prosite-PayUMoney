<?php
/*
Plugin Name: Prosite PayU India (PayUmoney & PayUbiz)
Plugin URI: http://www.coresol.com.pk/
Description: PayU India supports both PayUmoney and PayUbiz.
Version: 2.0.10
Author: Hassan Ali (Coresol)
Author URI: http://www.coresol.com.pk/
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! class_exists( 'ProSites_payuMoney' ) ) {
	class ProSites_payuMoney {
		
		var $plugin_dir = '';
		 
		function __construct() {
			$this->plugin_dir = plugin_dir_path( __FILE__ );
			
			// adding tab in payment gateway
			add_filter( 'prosites_gateways_tabs', array( &$this, 'prosites_payumoney_addtab' ));
			
			// adding Module gateway registration
			add_action( 'psts_load_gateways', array( &$this, 'prosites_payumoney_gateway_registration' ));
			
			
			// tab content calling function
			add_filter( 'prosites_settings_tabs_render_callback', array( &$this, 'prosites_payumoney_settings_tabs_render_callback' ), 10, 3);
		}
		/**
		 * Prosite Add Tab
		 *
		 * @param $tabs
		 * @return array()
		 */
		public function prosites_payumoney_addtab($tabs){
			$tabs['payumoney'] = array
				(
					'header_save_button' => true,
					'button_name' => "gateways",
					'title' => __( 'Pay U Money', 'psts' ),
					'desc'               => array(
						__( 'PayUmoney is best payment gateway company in India. Signup and collect credit card/debit cards payments online and offline with PayUmoney payment gateway.', 'psts' ),
					),
					'url' => 'admin.php?page=psts-gateways&tab=payumoney'
				);
			return $tabs;
		}

		/**
		 * Prosite Gateway Payumoney Registration
		 *
		 * @param $tabs
		 * @return array()
		 */
		public function prosites_payumoney_gateway_registration(){
			include_once( $this->plugin_dir . "classes/gateway-payuindia.php" );
		}

		/**
		 * Prosite Tab Callback Function
		 *
		 * @param $render_callback
		 * @param $active_tab
		 * @return array()
		 */
		public function prosites_payumoney_settings_tabs_render_callback($render_callback, $active_tab)
		{
			if($active_tab == "payumoney"){
				$render_callback = array( "ProSites_payuMoney", "render_tab_payumoney" );
			}
			
			return $render_callback;
		}
		
		/**
		 * Render Tab Content
		 *
		 * @return string
		 */
		public static function render_tab_payumoney() {
			global $psts;

			ProSites_Helper_Settings::settings_header( ProSites_Helper_Tabs_Gateways::get_active_tab() );
			$class_name = 'ProSites_Gateway_PayUMoneyIndia';
			$active_gateways = (array) $psts->get_setting('gateways_enabled');
			$checked = in_array( $class_name, $active_gateways ) ? 'on' : 'off';

			?>
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e( 'Enable Gateway', 'psts' ) ?></th>
					<td>
						<input type="hidden" name="gateway" value="<?php echo esc_attr( $class_name ); ?>" />
						<input type="checkbox" name="gateway_active" value="1" <?php checked( $checked, 'on' ); ?> />
					</td>
				</tr>
			</table>
			<?php
			$gateway = new ProSites_Gateway_PayUMoneyIndia();
			echo $gateway->settings();

		}
		
		
		
		
		
		
	}
}

$prosites_payumoney = new ProSites_payuMoney();
?>