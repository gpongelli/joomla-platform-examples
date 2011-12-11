#!/usr/bin/php
<?php
/**
 * An example command line application built on the Joomla Platform.
 *
 * To run this example, adjust the executable path above to suite your operating system,
 * make this file executable and run the file.
 *
 * Alternatively, run the file using:
 *
 * php -f run.php
 *
 * Note, this application requires configuration.php and the connection details
 * for PostgreSQL database may need to be changed to suit your local setup.
 *
 * @version    $Id: run.php gpongelli $
 * @package    Joomla.Examples
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// We are a valid Joomla entry point.
define('_JEXEC', 1);

// Setup the base path related constant.
define('JPATH_BASE', dirname(__FILE__));

// Bootstrap the application.
require dirname(dirname(dirname(__FILE__))) . '/bootstrap.php';

// Import the JCli class from the platform.
jimport('joomla.application.cli');

/**
 * An example command line application class.
 *
 * This application shows how to override the constructor
 * and connect to the database.
 *
 * @package  Joomla.Examples
 * @since    11.3
 */
class PostgreSQLDatabaseApp extends JCli
{
	/**
	 * A database object for the application to use.
	 *
	 * @var    JDatabase
	 * @since  11.3
	 */
	protected $dbo = null;

	/**
	 * Temporary table created and deleted for this example.
	 *
	 * @package  Joomla.Examples
 	 * @since    11.3
	 */
	protected $tmpTable = 'tmp_table';

	/**
	 * Class constructor.
	 *
	 * This constructor invokes the parent JCli class constructor,
	 * and then creates a connector to the database so that it is
	 * always available to the application when needed.
	 *
	 * @since   11.3
	 * @throws  JDatabaseException
	 */
	public function __construct()
	{
		// Call the parent __construct method so it bootstraps the application class.
		parent::__construct();

		jimport('joomla.database.database.postgresql');
		jimport('joomla.database.database.postgresqlquery');

		// Note, this will throw an exception if there is an error
		// creating the database connection.
		$this->dbo = JDatabase::getInstance(
			array(
				'driver' => $this->get('dbDriver'),
				'host' => $this->get('dbHost'),
				'user' => $this->get('dbUser'),
				'password' => $this->get('dbPass'),
				'database' => $this->get('dbName'),
				'prefix' => $this->get('dbPrefix'),
			)
		);

		// Create temporary table inside database.
		$createQuery = 'CREATE TABLE ' . $this->dbo->qn($this->tmpTable) . ' ( ' .
						$this->dbo->qn('id') . ' serial NOT NULL, ' .
						$this->dbo->qn('title') . ' character varying(50) NOT NULL, ' .
						$this->dbo->qn('start_date') . ' timestamp without time zone NOT NULL, ' .
						$this->dbo->qn('description') . ' text NOT NULL, ' .
						' PRIMARY KEY (' . $this->dbo->qn('id') . ') ' .
						');';

		// Create temporary table
		$this->dbo->setQuery($createQuery);
		$this->dbo->query();
	}

	/**
	 * Class destructor.
	 *
	 * This destructor invokes the parent JCli class destructor,
	 * deleting table created for this example.
	 *
	 * @since   11.3
	 * @throws  JDatabaseException
	 */
	public function __destruct()
	{
		$this->dbo->dropTable($this->tmpTable);
	}

	/**
	 * Insert values inside table.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @throws  JDatabaseException
	 */
	private function insertValues()
	{
		$this->out();
		$this->out('================== insertValues ==================');

		$colArray = array(
					$this->dbo->qn('id'),
					$this->dbo->qn('title'),
					$this->dbo->qn('start_date'),
					$this->dbo->qn('description')
				);

		$row1 = array(
					$this->dbo->q('1'),
					$this->dbo->q('Testing'),
					$this->dbo->q('1980-04-18 00:00:00'),
					$this->dbo->q('one')
				);

		$row2 = array(
					$this->dbo->q('2'),
					$this->dbo->q('Testing2'),
					$this->dbo->q('1980-04-18 00:00:00'),
					$this->dbo->q('one')
				);

		$row3 = array(
					$this->dbo->q('3'),
					$this->dbo->q('Testing3'),
					$this->dbo->q('1980-04-18 00:00:00'),
					$this->dbo->q('three')
				);

		$row4 = array(
					$this->dbo->q('4'),
					$this->dbo->q('Testing4'),
					$this->dbo->q('1980-04-18 00:00:00'),
					$this->dbo->q('four')
				);

		// Get the quey builder class from the database.
		$query = $this->dbo->getQuery(true);
		$query->insert($this->tmpTable)
				->columns($colArray)
				->values(implode(',', $row1))
				->values(implode(',', $row2))
				->values(implode(',', $row3))
				->values(implode(',', $row4));

		$this->dbo->setQuery($query);
		$this->dbo->query();

		$this->out();
	}

	/**
	 * Show all table's content.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	private function selectAll()
	{
		$this->out();
		$this->out('================== selectAll ==================');

		// Get the quey builder class from the database.
		$query = $this->dbo->getQuery(true);

		// Set up a query to select everything in the 'db' table.
		$query->select('*')
			->from($this->dbo->qn($this->tmpTable));

		// Push the query builder object into the database connector.
		$this->dbo->setQuery($query);

		// Get all the returned rows from the query as an array of objects.
		$rows = $this->dbo->loadObjectList();

		// Just dump the value returned.
		var_dump($rows);

		$this->out();
	}

	/**
	 * Show PostgreSQL database version.
	 * 
	 * @return void
	 * 
	 * @since 11.3
	 */
	private function showDbVersion()
	{
		$version = $this->dbo->getVersion();
		$this->out();
		$this->out('================== showDbVersion ==================');
		$this->out('PostgreSQL database version: ' . $version);
		$this->out();
	}

	/**
	 * Update second row value.
	 * 
	 * @return  void
	 *
	 * @since   11.3
	 * @throws  JDatabaseException
	 */
	private function updateSecondRow()
	{
		$this->out();
		$this->out('================== updateSecondRow ==================');

		$query = $this->dbo->getQuery(true);
		$query->update($this->dbo->qn($this->tmpTable))
				->set($this->dbo->qn('description') . ' = ' . $this->dbo->q('two'))
				->where($this->dbo->qn('id') . ' = ' . $this->dbo->q('2'));

		$this->dbo->setQuery($query);
		$this->dbo->query();
	}

	/**
	 * Show updated second row with different load function.
	 * 
	 * @return  void
	 *
	 * @since   11.3
	 * @throws  JDatabaseException
	 */
	private function printSecondRow()
	{
		$this->out();
		$this->out('================== printSecondRow ==================');

		// print changed row
		$query = $this->dbo->getQuery(true);
		$this->out('>>>> printing updated row with loadRow <<<<');
		$query->select('*')
				->from($this->dbo->qn($this->tmpTable))
				->where($this->dbo->qn('id') . ' = ' . $this->dbo->q('2'));
		$this->dbo->setQuery($query);
		$row = $this->dbo->loadRow();
		var_dump($row);

		$this->out();
		$this->out('>>>> printing updated row with loadAssoc <<<<');
		$this->dbo->setQuery($query);
		$row = $this->dbo->loadAssoc();
		var_dump($row);

		$this->out();
		$this->out('>>>> printing updated row with loadObject <<<<');
		$this->dbo->setQuery($query);
		$row = $this->dbo->loadObject();
		var_dump($row);
	}

	/**
	 * Database test
	 * 
	 * @return  void
	 *
	 * @since   11.3
	 * @throws  JDatabaseException
	 */
	private function test()
	{
		$this->out();
		$this->out('================== test ==================');
		$this->out('Test call result: ' . $this->dbo->test());
		$this->out();
	}

	/**
	 * Check database connection
	 * 
	 * @return  void
	 * 
	 * @since   11.3
	 * @throws  JDatabaseException
	 */
	private function isConnected()
	{
		$this->out();
		$this->out('================== isConnected ==================');
		$this->out('Connected call result: ' . $this->dbo->connected());
		$this->out();
	}

	/**
	 * Show database collation.
	 * 
	 * @return  void
	 *
	 * @since   11.3
	 * @throws  JDatabaseException
	 */
	private function showCollation()
	{
		$this->out();
		$this->out('================== showCollation ==================');
		$this->out('Database Collation: ' . $this->dbo->getCollation());
		$this->out();
	}

	/**
	 * Show table keys
	 * 
	 * @return  void
	 * 
	 * @since   11.3
	 * @throws  JDatabaseException
	 */
	private function showTableKeys()
	{
		$this->out();
		$this->out('================== showTableKeys ==================');
		var_dump($this->dbo->getTableKeys($this->tmpTable));
		$this->out();
	}

	/**
	 * Show table columns.
	 * 
	 * @return  void
	 * 
	 * @since   11.3
	 * @throws  JDatabaseException
	 */
	private function showTableColumns()
	{
		$this->out();
		$this->out('================== showTableColumns ==================');
		$tableCol = $this->dbo->getTableColumns($this->tmpTable);
		var_dump($tableCol);
		$this->out();
	}

	/**
	 * Print a random value.
	 * 
	 * @return  void
	 * 
	 * @since   11.3
	 * @throws  JDatabaseException
	 */
	private function getRandomValue()
	{
		$this->out();
		$this->out('================== getRandomValue ==================');
		$this->out('Random value: ' . $this->dbo->getRandom());
		$this->out();
	}

	/**
	 * Execute the application.
	 *
	 * The 'execute' method is the entry point for a command line application.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function execute()
	{
		$this->isConnected();
		$this->showCollation();
		$this->showDbVersion();
		$this->test();

		$this->insertValues();

		$this->selectAll();
		$this->showTableColumns();
		$this->showTableKeys();

		$this->updateSecondRow();
		$this->printSecondRow();
		$this->getRandomValue();
	}
}



// Wrap the execution in a try statement to catch any exceptions thrown anywhere in the script.
try
{
	$pgSqlDbIstance = JCli::getInstance('PostgreSQLDatabaseApp')->execute();
}
catch (Exception $e)
{
	// An exception has been caught, just echo the message.
	fwrite(STDOUT, $e->getMessage() . "\n");
	exit($e->getCode());
}
