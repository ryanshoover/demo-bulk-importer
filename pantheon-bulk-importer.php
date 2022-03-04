<?php

/**
 * Pantheon Bulk Importer
 *
 * @package     Pantheon\BulkImporter
 * @author      Pantheon Professional Servies Application Performance Team
 * @license     Proprietary
 *
 * @wordpress-plugin
 * Plugin Name: Pantheon Bulk Importer
 * Plugin URI:  https://pantheon.io
 * Description: Demo process to bulk-import a large csv file.
 * Version:     1.0.0
 * Author:      Pantheon - Professional Services Application Performance Team
 * Author URI:  https://pantheon.io
 * Text Domain: pantheon
 * License:     Proprietary
 */

namespace Pantheon\BulkImporter;

use WP_CLI;

define( 'Pantheon\BulkImporter\PATH', plugin_dir_path( __FILE__ ) );

// Standard autoloader for the WPCS class file naming convention.
spl_autoload_register(
	function ($class_name) {
		if (false !== strpos($class_name, __NAMESPACE__)) {
			$class_parts = explode('\\', $class_name);

			// Remove Pantheon\BulkImporter
			unset($class_parts[0], $class_parts[1]);

			// Pull out the class name.
			$class_file = \array_pop($class_parts);
			$class_file = 'class-' . strtolower(str_replace('_', '-', $class_file)) . '.php';

			// Assemble the path.
			$path = realpath(plugin_dir_path(__FILE__)) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;

			// Assemble the nested directory path.
			foreach ($class_parts as $part) {
				$path .= $part . \DIRECTORY_SEPARATOR;
			}

			require_once $path . $class_file;
		}
	}
);

// Register the demo CLI command
if ( defined('\WP_CLI') && WP_CLI ) {
	/**
	 * Run the bulk import demo process
	 *
	 * ## OPTIONS
     *
     * <table>
     * : The table to import. One of biostats, cities, colors.
	 *
     *
     * ## EXAMPLES
     *
     *     wp bulk-import cities
	 */
	WP_CLI::add_command( 'bulk-import', function( $args ) {
		$table = $args[0];

		if ( ! $table || ! in_array( $table, ['biostats', 'cities', 'colors'], true ) ) {
			WP_CLI::error( 'The table paramter must be passed and be one of `biostats`, `cities`, `colors`' );
			return;
		}

		$importer = new Importer( $table );

		$importer->empty();

		$importer->start();
	} );
}

add_action( 'pantheon_bulk_import', function( $table, $page ) {
	$importer = new Importer( $table, $page );

	$importer->start();
}, 10, 2 );

// Create tables on activation.
register_activation_hook( __FILE__, function() {
	$db_tables = new DB_Tables();
	$db_tables->create();
} );

// Delete tables on deactivation.
register_deactivation_hook( __FILE__, function() {
	if( defined( 'WP_UNINSTALL_PLUGIN' ) ) {
		$db_tables = new DB_Tables();
		$db_tables->delete();
	}
} );
