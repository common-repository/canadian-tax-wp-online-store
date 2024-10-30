<?php
class WPOSC_TAX_SETUP_SQL {

	// Store the single instance of our class
	protected static $instance;
	
	/**
	 * Constructor - called by get_instance() 
	 */
	protected function __construct() {}
	
	/**
	 * Singleton - ensure only one instance of this class exists
	 * @return self::$instance 
	 */
	public static function get_instance()
	{
		if (!isset(self::$instance)) {
            $class = __CLASS__;
            self::$instance = new $class;
        }
		
        return self::$instance;
	}

	/**
	 * Execute SQL and fail on error
	 * @global object $wpdb
	 * @param string $sql
	 * @return object $this , enables method chaining
	 */
	protected function execute_sql($sql)
	{
		global $wpdb;
		
		$wpdb->hide_errors();
		
		$result = $wpdb->query($sql);

		$this->check_for_sql_error($result);
		
		return $this;			
	}	

	/**
	 * Reports errors from $wpdb->query() if found
	 * @global object $wpdb
	 * @param boolean $result
	 * @return boolean 
	 */
	protected function check_for_sql_error($result)
	{
		global $wpdb;
		if($result === false){
			$sql_error	= $wpdb->last_error;
			$query		= htmlspecialchars( $wpdb->last_query, ENT_QUOTES );
			$message    = array(
				'type' => 'Database Error'
			,	'text' => $sql_error
			,	'code' => $query
			);
			$this->trigger_error($message, E_USER_ERROR);
			return false;
		}		
		return true;
	}
	
	/**
	 * Insert data into specified table from a specified file
	 * @global object $wpdb
	 * @param string $tablename
	 * @param string $filename
	 * @param array $column_formats i.e array('%s','%s','%.3f','%d')
	 * @return object $this 
	 */
	protected function insert_into_table_from_csv_file($tablename,$filename,$column_formats)
	{
		global $wpdb;

		if (($handle = @fopen(WPOSC_TAX_SETUP_DATA . $filename, "r")) !== FALSE) {

			$row = 0;
			$array_keys = array();
			$line_length	 = 0;
			$field_delimiter = ",";
			$field_enclosure = '"';
			
			while (($data = fgetcsv($handle, $line_length, $field_delimiter, $field_enclosure)) !== FALSE) {
				if($row == 0){
					$array_keys = $data;
					$row++;
					continue;
				}
				$result = $wpdb->insert( $tablename, array_combine($array_keys,$data) , $column_formats );
				$this->check_for_sql_error($result);
				$row++;
			}
			fclose($handle);		
		}
		else {
			$message    = array(
				'type' => 'File Error'
			,	'text' => 'Unable to read file - ' . $filename
			);
			$this->trigger_error($message, E_USER_ERROR);
		}

		return $this;		
	}	

	/**
	 * Trigger a php error. Echo message to be displayed on WordPress plugin page.
	 * @param array $message
	 * @param PHP Error Constant $errno 
	 */
	protected function trigger_error($message, $errno){
		WPOSC_TAX_SETUP::trigger_error($message, $errno);
	}

	/**
	 * Delete the default/sample tax setup i.e. Florida
	 * @return object $this
	 */
	public function delete_default_tax_setup()
	{
		$sql = "DELETE FROM geo_zones WHERE geo_zone_name = 'Florida' AND geo_zone_id = 1";
		$this->execute_sql($sql);
		$sql = "DELETE FROM zones_to_geo_zones WHERE association_id = 1 AND geo_zone_id = 1";
		$this->execute_sql($sql);
		$sql = "DELETE FROM tax_rates WHERE tax_rates_id = 1 AND tax_zone_id = 1";		
		return $this->execute_sql($sql);
	}
	
	/**
	 * Check that the default tax class we are expecting is present, if not create it
	 * @return object $this 
	 */
	public function check_default_tax_class_exists()
	{
		$sql = "INSERT INTO tax_class ( tax_class_title, tax_class_description, date_added )
				SELECT  'Taxable Goods'															as tax_class_title
				,       'The following types of products are included non-food, services, etc'	as tax_class_description
				,       now()																	as date_added
				FROM
						tax_class					t1
						RIGHT OUTER JOIN
						(
						select 'dummy' as dummy
						)							dt1
				ON
						t1.tax_class_id = dt1.dummy
				WHERE NOT EXISTS (
					SELECT  *
					FROM
							tax_class
					WHERE
							tax_class_title = 'Taxable Goods'
				)
				LIMIT 1";
		return $this->execute_sql($sql);		
	}
	
	/**
	 * Drop the table wposc_currencies
	 * @return object $this 
	 */
	public function dt_wposc_currencies()
	{
		$sql = "DROP TABLE IF EXISTS wposc_currencies";
		return $this->execute_sql($sql);		
	}

	/**
	 * Create the table wposc_currencies
	 * @return object $this 
	 */	
	public function ct_wposc_currencies()
	{
		$sql = "CREATE TABLE wposc_currencies (
					title varchar(32) NOT NULL
				,	code char(3) NOT NULL
				,	symbol_left varchar(12) DEFAULT NULL
				,	symbol_right varchar(12) DEFAULT NULL
				,	decimal_point char(1) DEFAULT NULL
				,	thousands_point char(1) DEFAULT NULL
				,	decimal_places char(1) DEFAULT NULL
				,	value float(13,8) DEFAULT NULL
				,	PRIMARY KEY ( code )
				) ENGINE=InnoDB DEFAULT CHARSET=latin1";
		return $this->execute_sql($sql);		
	}

	/**
	 * Load wposc_currencies with data from csv file 
	 * @return object $this 
	 */
	public function load_wposc_currencies()
	{
		$tablename = 'wposc_currencies';
		$filename  = 'currencies.csv';
		$column_formats = array('%s','%s','%s','%s','%s','%s','%s','%f');
		return $this->insert_into_table_from_csv_file($tablename,$filename,$column_formats);		
	}
	
	/**
	 * Drop table wposc_geo_zones if it exists
	 * @return object $this
	 */
	public function dt_wposc_geo_zones()
	{
		$sql = 'DROP TABLE IF EXISTS wposc_geo_zones';
		return $this->execute_sql($sql);		
	}
	
	/**
	 * Create table wposc_geo_zones
	 * @return object $this 
	 */
	public function ct_wposc_geo_zones()
	{
		$sql = 'CREATE TABLE wposc_geo_zones (
					geo_zone_id int(11) DEFAULT NULL
				,	geo_zone_name varchar(32) NOT NULL
				,	geo_zone_description varchar(255) NOT NULL
				,	PRIMARY KEY (geo_zone_name)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1';
		return $this->execute_sql($sql);		
	}

	/**
	 * Load wposc_geo_zones with data from csv file 
	 * @return object $this 
	 */
	public function load_wposc_geo_zones()
	{
		$tablename		= 'wposc_geo_zones';
		$filename		= 'tax-zones.csv';
		$column_formats	= array('%s','%s');
		return $this->insert_into_table_from_csv_file($tablename,$filename,$column_formats);
	}	
	
	/**
	 * Drop table wposc_zones_to_geo_zones if it exists
	 * @return object $this 
	 */
	public function dt_wposc_zones_to_geo_zones()
	{
		$sql = 'DROP TABLE IF EXISTS wposc_zones_to_geo_zones';
		return $this->execute_sql($sql);		
	}
	
	/**
	 * Create table wposc_zones_to_geo_zones
	 * @return object $this
	 */
	public function ct_wposc_zones_to_geo_zones()
	{
		$sql = "CREATE TABLE wposc_zones_to_geo_zones (
					geo_zone_name	varchar(32)		NOT NULL
				,	zone_name		varchar(255)	NOT NULL
				,	countries_name	varchar(255)	NOT NULL
				,	PRIMARY KEY ( geo_zone_name, zone_name, countries_name )
				) ENGINE=InnoDB DEFAULT CHARSET=latin1";
		return $this->execute_sql($sql);		
	}
	
	/**
	 * Load wposc_zones_to_geo_zones with data from csv file 
	 * @return object $this 
	 */
	public function load_wposc_zones_to_geo_zones()
	{
		$tablename = 'wposc_zones_to_geo_zones';
		$filename  = 'zones-to-tax-zones.csv';
		$column_formats = array('%s','%s','%s');
		return $this->insert_into_table_from_csv_file($tablename,$filename,$column_formats);
	}	

	/**
	 * Drop the table wposc_tax_rates
	 * @return object $this 
	 */
	public function dt_wposc_tax_rates()
	{
	
		$sql = "DROP TABLE IF EXISTS wposc_tax_rates";
		return $this->execute_sql($sql);		
	}
	
	/**
	 * Create the table wposc_tax_rates
	 * @return object $this 
	 */
	public function ct_wposc_tax_rates()
	{
	
		$sql = "CREATE TABLE wposc_tax_rates (
					geo_zone_name varchar(32) NOT NULL
				,	tax_rate decimal(7,4) NOT NULL
				,	tax_description varchar(255) NOT NULL
				,	tax_priority int(5) NOT NULL
				,	PRIMARY KEY ( geo_zone_name )
				) ENGINE=InnoDB DEFAULT CHARSET=latin1";
		return $this->execute_sql($sql);		
	}	
	
	/**
	 * Load wposc_tax_rates with data from csv file 
	 * @return object $this 
	 */
	public function load_wposc_tax_rates()
	{
		$tablename = 'wposc_tax_rates';
		$filename  = 'tax-rates.csv';
		$column_formats = array('%s','%2.f','%s','%d');
		return $this->insert_into_table_from_csv_file($tablename,$filename,$column_formats);
	}

	/**
	 * Drop the table wposc_orphan_tax_zones
	 * @return object $this 
	 */
	public function dt_wposc_orphan_records()
	{
	
		$sql = "DROP TABLE IF EXISTS wposc_orphan_records";
		return $this->execute_sql($sql);		
	}	
	
	/**
	 * Create table wposc_orphan_tax_zones and populate with oprhan record geo_zone_id
	 * @return object $this 
	 */
	public function ct_wposc_orphan_records_and_populate_with_orphan_tax_zones()
	{	
		$sql = "CREATE TABLE wposc_orphan_records
				SELECT	t1.geo_zone_id AS geo_zone_id 
				FROM
						geo_zones           t1
						LEFT OUTER JOIN
						zones_to_geo_zones  t2
				ON
						t1.geo_zone_id = t2.geo_zone_id
				WHERE
						t2.geo_zone_id is null";
		return $this->execute_sql($sql);	
	} 

	/**
	 * Delete the orphan tax_zones (i.e no corresponding zones_to_geo_zones row) from geo_zones
	 * @return type 
	 */
	public function delete_orphan_tax_zone_records()
	{	
		$sql = "DELETE FROM geo_zones
				WHERE geo_zone_id in (
					SELECT	geo_zone_id
					FROM
							wposc_orphan_records
				)";
		return $this->execute_sql($sql);	
	} 	

	/**
	 * Empty the table wposc_orphan_records
	 * @return type 
	 */
	public function delete_from_wposc_orphan_records()
	{	
		$sql = "DELETE FROM wposc_orphan_records";
		return $this->execute_sql($sql);	
	} 	
	
	/**
	 * Insert orphan records from tax_rates in wposc_orphan_records
	 * @return type 
	 */
	public function insert_wposc_orphan_tax_rates()
	{	
		$sql = "INSERT INTO wposc_orphan_records
				SELECT	DISTINCT t1.tax_zone_id AS geo_zone_id
				FROM
						tax_rates			t1
						LEFT OUTER JOIN
						zones_to_geo_zones	t2
				ON
						t1.tax_zone_id = t2.geo_zone_id
				WHERE
						t2.geo_zone_id is null
		";
		return $this->execute_sql($sql);	
	} 	

	/**
	 * Delete the orphan tax_rates (i.e no corresponding zones_to_geo_zones row)
	 * @return type 
	 */
	public function delete_orphan_tax_rate_records()
	{	
		$sql = "DELETE FROM tax_rates
				WHERE tax_zone_id in (
					SELECT	geo_zone_id
					FROM
							wposc_orphan_records
				)";
		return $this->execute_sql($sql);	
	}	
	
	/**
	 * Delete existing Tax Zones for the target country
	 * @return object $this
	 */
	public function delete_tax_zones_for_target_country()
	{
		$sql = "DELETE FROM geo_zones
				WHERE geo_zone_id in (
					SELECT	DISTINCT t3.geo_zone_id 
					FROM  
							(
							SELECT  DISTINCT countries_name
							FROM
									wposc_zones_to_geo_zones
							) dt1
							INNER JOIN
							countries                 t2
					ON
							dt1.countries_name = t2.countries_name
							INNER JOIN
							zones_to_geo_zones        t3
					ON
							t2.countries_id = t3.zone_country_id 
				)";
		return $this->execute_sql($sql);		
	}
	
	/**
	 * Delete existing tax_rates for the target country
	 * @return object $this
	 */
	public function delete_tax_rates_for_target_country()
	{
		$sql = "DELETE FROM tax_rates
				WHERE tax_zone_id in (
					SELECT	DISTINCT t3.geo_zone_id 
					FROM  
							(
							SELECT  DISTINCT countries_name
							FROM
									wposc_zones_to_geo_zones
							) dt1
							INNER JOIN
							countries                 t2
					ON
							dt1.countries_name = t2.countries_name
							INNER JOIN
							zones_to_geo_zones        t3
					ON
							t2.countries_id = t3.zone_country_id 
				)";
		return $this->execute_sql($sql);				
	}
	
	/**
	 * Delete existing zones_to_tax_zones mapping for the target country
	 * @return object $this
	 */	
	public function delete_zones_to_tax_zones_for_target_country()
	{
		$sql = "DELETE FROM zones_to_geo_zones
				WHERE zone_country_id in (
					SELECT	t2.countries_id 
					FROM  
							(
							SELECT  DISTINCT countries_name
							FROM
									wposc_zones_to_geo_zones
							) dt1
							INNER JOIN
							countries                 t2
					ON
							dt1.countries_name = t2.countries_name
				)";
		return $this->execute_sql($sql);		
	}

	/**
	 * Drop the table wposc_geo_zone_copy
	 * @return object $this 
	 */
	public function dt_wposc_geo_zone_copy()
	{
		$sql = "DROP TABLE IF EXISTS wposc_geo_zones_copy";
		return $this->execute_sql($sql);		
	}

	/**
	 * Create the table wposc_geo_zone_copy as a copy of geo_zone
	 * @return object $this 
	 */
	public function ct_wposc_geo_zone_copy_and_copy_tax_zones()
	{
		$sql = "CREATE TABLE wposc_geo_zones_copy SELECT * FROM geo_zones";
		return $this->execute_sql($sql);		
	}		
	
	/**
	 * Insert tax zones from wposc_geo_zones
	 * @return object $this 
	 */
	public function insert_tax_zones()
	{
		$sql = "INSERT INTO geo_zones ( geo_zone_name, geo_zone_description, date_added )
				SELECT  geo_zone_name
				,		geo_zone_description
				,		NOW()
				FROM
						wposc_geo_zones";
		return $this->execute_sql($sql);		
	}	

	/**
	 * Update the wposc_geo_zones rows with the geo_zone_id from the rows just inserted into geo_zones
	 * @return object $this 
	 */
	public function update_wposc_geo_zones()
	{
		$sql = "UPDATE wposc_geo_zones AS t1,
				(
				SELECT  t1.geo_zone_id
				,		t1.geo_zone_name
				FROM
						geo_zones				t1
						LEFT OUTER JOIN
						wposc_geo_zones_copy	t2
				ON
						t1.geo_zone_id = t2.geo_zone_id
				WHERE
						t2.geo_zone_id is null
				) dt1
				SET	
						t1.geo_zone_id = dt1.geo_zone_id
				WHERE
						t1.geo_zone_name = dt1.geo_zone_name";
		return $this->execute_sql($sql);		
	}	
	
	/**
	 * Insert into tax_rates from wposc_tax_rates
	 * @return object $this
	 */
	public function insert_tax_rates()
	{
		$sql = "INSERT INTO tax_rates ( tax_zone_id, tax_class_id, tax_rate, tax_description, tax_priority, date_added )
				SELECT  t2.geo_zone_id      AS tax_zone_id
				,       dt1.tax_class_id    AS tax_class_id
				,       t1.tax_rate         AS tax_rate
				,       CONCAT(t1.tax_description,' ',TRIM(TRAILING '.' FROM TRIM(TRAILING '0' FROM t1.tax_rate)),'%')  AS tax_description
				,		t1.tax_priority		AS tax_priority
				,       NOW()               AS date_added
				FROM
						wposc_tax_rates		t1
						INNER JOIN
						geo_zones			t2
				ON
						t1.geo_zone_name = t2.geo_zone_name
						CROSS JOIN
						(
						SELECT  tax_class_id
						FROM
								tax_class        
						WHERE
								tax_class_title = 'Taxable Goods'
						) dt1";
		return $this->execute_sql($sql);		
	}	
	
	/**
	 * Create Zones to Tax Zones association.
	 * @return object $this 
	 */
	public function insert_zones_to_geo_zones()
	{
		$sql = "INSERT INTO zones_to_geo_zones ( zone_country_id, zone_id, geo_zone_id, date_added)
				SELECT  t3.countries_id AS zone_country_id
				,       t4.zone_id      AS zone_id
				,       t2.geo_zone_id  AS geo_zone_id
				,       NOW()           AS date_added
				FROM
						wposc_zones_to_geo_zones  t1
						INNER JOIN
						wposc_geo_zones           t2
				ON
						t1.geo_zone_name = t2.geo_zone_name
						INNER JOIN
						countries                 t3
				ON
						t1.countries_name = t3.countries_name
						INNER JOIN
						zones                      t4
				ON
						t1.zone_name = t4.zone_name";
		return $this->execute_sql($sql);		
	}

	/**
	 * Insert into currencies from wposc_currencies if currency not already present
	 * @return type 
	 */
	public function insert_currencies()
	{
		$sql = "INSERT INTO currencies
				( title
				, code
				, symbol_left
				, symbol_right
				, decimal_point
				, thousands_point
				, decimal_places
				, value
				)
				SELECT  title
				,       code
				,       symbol_left
				,       symbol_right
				,       decimal_point
				,       thousands_point
				,       decimal_places
				,       value
				FROM
						wposc_currencies
				WHERE code NOT IN (
				SELECT t2.code
				FROM
						currencies        t1
						INNER JOIN
						wposc_currencies  t2
				ON
						t1.code = t2.code
				)";
		return $this->execute_sql($sql);		
	}
	
	/**
	 * Set the default currency
	 * @return object $this; 
	 */
	public function update_default_currency()
	{
		$sql = "UPDATE configuration AS t1,
				(
				SELECT t2.code AS code
				FROM
						currencies        t1
						INNER JOIN
						wposc_currencies  t2
				ON
						t1.code = t2.code
				) dt1
				SET 
						t1.configuration_value = dt1.code
				WHERE 
						t1.configuration_key = 'DEFAULT_CURRENCY'";
		return $this->execute_sql($sql);		
	}
	
	/**
	 * Set the default country for the store - remember to update the default zone manually!
	 * @return object $this; 
	 */
	public function update_default_country_for_store()
	{
		$sql = "UPDATE configuration AS updt,
				(
				SELECT	t1.countries_id AS countries_id
				FROM
						countries	t1
						INNER JOIN
						(
						SELECT	DISTINCT countries_name
						FROM
								wposc_zones_to_geo_zones
						)			dt1
				ON
						t1.countries_name = dt1.countries_name
				) dt1
				SET 
						updt.configuration_value = dt1.countries_id
				WHERE 
						updt.configuration_key = 'STORE_COUNTRY'";
		return $this->execute_sql($sql);		
	}

	/**
	 * Set the default country for shipping
	 * @return object $this; 
	 */
	public function update_default_country_for_shipping()
	{
		$sql = "UPDATE configuration AS updt,
				(
				SELECT	t1.countries_id AS countries_id
				FROM
						countries	t1
						INNER JOIN
						(
						SELECT	DISTINCT countries_name
						FROM
								wposc_zones_to_geo_zones
						)			dt1
				ON
						t1.countries_name = dt1.countries_name
				) dt1
				SET 
						updt.configuration_value = dt1.countries_id
				WHERE 
						updt.configuration_key = 'SHIPPING_ORIGIN_COUNTRY'";
		return $this->execute_sql($sql);		
	}	

	
	
}