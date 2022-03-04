<?php

namespace Pantheon\BulkImporter;

/**
 * Creates the demo tables in the database
 */
class DB_Tables {

	public function __construct() {
		$this->tables = [
			'biostats' => [
				'id INTEGER NOT NULL AUTO_INCREMENT',
				'name TEXT NOT NULL',
				'sex TEXT',
				'age INTEGER',
				'height FLOAT',
				'weight FLOAT',
				'PRIMARY KEY (id)',
			],
			'cities' => [
				'id INTEGER NOT NULL AUTO_INCREMENT',
				'LatD INTEGER',
				'LatM INTEGER',
				'LatS INTEGER',
				'NS TEXT',
				'LonD INTEGER',
				'LonM INTEGER',
				'LonS INTEGER',
				'EW TEXT',
				'City TEXT',
				'State TEXT',
				'PRIMARY KEY (id)',
			],
			'colors' => [
				'id INTEGER NOT NULL AUTO_INCREMENT',
				'HEX TEXT',
				'RGB TEXT',
				'PRIMARY KEY (id)',
			],
		];
	}

	/**
	 * Create all our custom tables.
	 */
	public function create() {
		foreach ( $this->tables as $table => $fields ) {
			$this->create_table( $table, $fields );
		}
	}

	/**
	 * Delete every custom table.
	 */
	public function delete() {
		foreach ( array_keys( $this->tables ) as $table ) {
			$this->delete_table( $table );
		}
	}

	/**
	 * Helper method to delete a table.
	 *
	 * @param string $table Unprefixed name of the table.
	 */
	private function delete_table( $table ) {
		global $wpdb;

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$table}" );
	}

	/**
	 * Helper method to create a table.
	 *
	 * @param string $table Unprefixed name of the table.
	 * @param array $fields List of individual field definitions, including PRIMARY KEY definition.
	 */
	private function create_table( $table, $fields ) {
		global $wpdb;

		// Load the WP helper functions.
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();

		$field_str = implode( ', ', $fields );

		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}{$table} ( $field_str ) $charset";

		// WordPress core helper function to create a table.
		maybe_create_table( $table, $sql );
	}
}
