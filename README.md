# Demo file bulk importer method

This demonstrates how to import very large CSV files into WordPress on the Pantheon platform.

It uses the `SPLFileObject` class to avoid reading the entire CSV file into memory.

The import is "paginated" - it will run in batches, with the next batch tied to a single cron event.

There are three datasets provided - `cities`, `colors`, and `biostats`. (Thanks to [jburkhardt](https://people.sc.fsu.edu/~jburkardt/data/csv/csv.html)!)

## Installation

```bash
git clone git@github.com:ryanshoover/demo-bulk-importer.git wp-content/plugins/pantheon-bulk-importer
wp plugin activate pantheon-bulk-importer
```

## Usage

Everything is tied to a wp-cli command for demonstration purposes.

For example, import the cities data with

```bash
wp bulk-import cities
```
