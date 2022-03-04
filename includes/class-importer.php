<?php

namespace Pantheon\BulkImporter;
use SplFileObject;

/**
 * Class to handle importing a specific table.
 */
class Importer {

	/**
	 * Instantiate the importer with the minimal settings needed.
	 *
	 * @param string $table The table name to import.
	 * @param int    $page  If paginated, which page is being imported.
	 */
	public function __construct( $table, $page = 0 ) {
		$this->table = $table;
		$this->page = $page;

		// This is hardcoded but could easily be moved to another config value.
		$this->page_size = 10;
	}

	public function empty() {
		global $wpdb;

		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}{$this->table}" );
	}

	public function start() {
		// For the sake of a demo, we're trusting that every table has a
		// matching csv file in the data directory.
		// Instantiate a new file object so we can use the `seek` method.
		$file = new SplFileObject( PATH . "/data/{$this->table}.csv" );

		// Make sure we're at the start.
		$file->seek( 0 );
		// The CSV's first row is the table headers.
		$headers = $file->fgetcsv();

		// Go to the start of this "page" of data.
		$file->seek( $this->page * $this->page_size + 1 );

		$counter = 0;
		$rows = [];

		// Loop through lines in the file until we hit either
		// the pagination size or the end of the file.
		while( $counter < $this->page_size && ! $file->eof() ) {
			$counter++;

			// The csv data may need cleaned up. Give them a whitespace haircut.
			$rows[] = array_map( 'trim', $file->fgetcsv() );
		}

		// Call the import command.
		$this->import( $headers, $rows );

		// Go to the next page.
		$this->page += 1;

		// If we're not done importing yet, schedule a cron to finish the job.
		if ( ! $file->eof() ) {
			$this->schedule_next_cron();
		}
	}

	/**
	 * Import a group of data into the table.
	 *
	 * @param array $headers The header names in a flat array.
	 * @param array $rows    Nested array with groups of row data.
	 */
	protected function import( $headers, $rows ) {
		global $wpdb;

		$sql = "INSERT INTO {$wpdb->prefix}{$this->table}( "  . implode( ', ', $headers ) . ') VALUES ';

		// Doing a little ugly string manipulation to assemble the sql string.
		foreach ( $rows as $row ) {
			$sql .= '( \'' . implode( "', '", $row ) . '\' ), ';
		}

		// We've got a trailing `, `. Kill it.
		$sql = rtrim( $sql, ', ' );

		// Import that data!
		$wpdb->query( $sql );
	}

	/**
	 * Schedule a cron job to import the next batch.
	 */
	protected function schedule_next_cron() {
		// We only need to pass two data values for the next
		// cron run. Table name and page.
		$args = [
			$this->table,
			$this->page,
		];

		// Schedule a one-off cron event 10 seconds from now to run another import.
		\wp_schedule_single_event( time() + 10, 'pantheon_bulk_import', $args );
	}
}
