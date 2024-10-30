<?php
/*
Plugin Name: Canadian Tax for WP Online Store
Plugin URI: http://dandelionwebdesign.com/downloads/canadian-tax-wp-online-store/
Description: Easy Canadian taxes! Sets up Canadian Federal and Provincial Sales Tax Zones and Rates as well as default currency, store country and shipping country for the WP Online Store eCommerce Plugin. Just activate! Remember to set your store zone & delete PST zones that don't apply!
Version: 2.0
License: GPL
Author:  Dandelion Web Design Inc.
Author URI: http://www.dandelionwebdesign.com/
 
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
( at your option ) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

define('WPOSC_TAX_SETUP_ROOT'    , plugin_dir_path(__FILE__)			);
define('WPOSC_TAX_SETUP_INC'     , WPOSC_TAX_SETUP_ROOT . '/includes/'	);
define('WPOSC_TAX_SETUP_DATA'    , WPOSC_TAX_SETUP_ROOT . '/data/'		);
include(WPOSC_TAX_SETUP_INC . 'sql.php' );

register_activation_hook(__FILE__,'WPOSC_TAX_SETUP::init');

class WPOSC_TAX_SETUP {
	
	/**
	 * Check the WP Online Store is active then setup the tax rates 
	 */
	public static function init()
	{
		
		$plugin = "wp-online-store/WP_online_store.php";
		
		if(is_plugin_active($plugin) === false){
			self::trigger_error(array(
					'type' => 'Activation Error'
				,	'text' => 'WP Online Store is not activated, aborting tax setup')
			,	E_USER_ERROR
			);
		}
		
		
		$sql = WPOSC_TAX_SETUP_SQL::get_instance();
		// Execute the sequence of SQL statements
		$sql->check_default_tax_class_exists()
			->delete_default_tax_setup()
			//$$ Create intermediate tables and load data from csv files
				->dt_wposc_currencies()
				->ct_wposc_currencies()
				->load_wposc_currencies()
				->dt_wposc_geo_zones()
				->ct_wposc_geo_zones()
				->load_wposc_geo_zones()
				->dt_wposc_zones_to_geo_zones()
				->ct_wposc_zones_to_geo_zones()
				->load_wposc_zones_to_geo_zones()
				->dt_wposc_tax_rates()
				->ct_wposc_tax_rates()
				->load_wposc_tax_rates()
			//$$
			//## Orphan records could possibly be generated if an error occurs
			//## during plugin activation and the plugin is unable to complete
			//## this will clean them up should such an event occur
				->dt_wposc_orphan_records()
				->ct_wposc_orphan_records_and_populate_with_orphan_tax_zones()
				->delete_orphan_tax_zone_records()
				->delete_from_wposc_orphan_records()
				->insert_wposc_orphan_tax_rates()
				->delete_orphan_tax_rate_records()
			//##
			//@@ Delete pre existing tax setup for our target country
				->delete_tax_zones_for_target_country()
				->delete_tax_rates_for_target_country()
				->delete_zones_to_tax_zones_for_target_country()
			//@@
			//&& Insert tax data into target tables
				->dt_wposc_geo_zone_copy()
				->ct_wposc_geo_zone_copy_and_copy_tax_zones()
				->insert_tax_zones()
				->update_wposc_geo_zones()
				->insert_tax_rates()
				->insert_zones_to_geo_zones()
				->insert_currencies()
			//&&
			//%% Set configuration values for country and currency
				->update_default_currency()
				->update_default_country_for_store()
				->update_default_country_for_shipping()
			//%%
			// Clean Up the intermediate tables
			->dt_wposc_currencies()
			->dt_wposc_geo_zones()
			->dt_wposc_zones_to_geo_zones()
			->dt_wposc_tax_rates()
			->dt_wposc_orphan_records()
			->dt_wposc_geo_zone_copy();
	}
	
	/**
	 * Trigger a php error. Echo message to be displayed on WordPress plugin page.
	 * http://www.squarepenguin.com/wordpress/?p=6
	 * @param array $message
	 * @param PHP Error Constant $errno 
	 */
	public static function trigger_error($message, $errno)
	{
		if(isset($_GET['action']) and $_GET['action'] == 'error_scrape') {

			$plugin_error_message  = '<div id="error">';
			$plugin_error_message .= '    <p class="wpdberror">';
			$plugin_error_message .= '        <strong>WP Online Store Tax Setup - '. $message['type'] .'</strong><br />';
			$plugin_error_message .= '        <strong>Message: </strong> ' . $message['text'] . '<br />';
			if(isset($message['code'])){
			$plugin_error_message .= '        <strong>SQL: </strong><code>' . $message['code'] . '</code>';
			}
			$plugin_error_message .= '    </p>';
			$plugin_error_message .= '</div>';	

			echo $plugin_error_message;
 
	        exit;
 
		} else {
			// $errno = E_USER_ERROR (script halted) | E_USER_WARNING | E_USER_NOTICE
 			trigger_error($message['text'], $errno);
		}		
	}

}

?>