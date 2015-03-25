<?php

namespace WebtudorBlog\Model;
use PDO;

/**
 * Abstract model. This contains the database connection functions
 */
abstract class AbstractModel {
	/**
	 * @var PDO
	 */
	private static $connection;

	/**
	 * @return PDO
	 */
	protected function getConnection() {
		if (!self::$connection) {
			//Create new connection with static settings
			//$connection = new PDO('mysql:dbname=webtudorblog;host=localhost', 'root', '');

            $connection = new PDO('sqlite::memory:');

			//Set UTF8 character set so data isn't corrupted
			//$connection->exec('SET NAMES utf8');

			//Strict MySQL mode, reject invalid values
			//$connection->exec('SET SESSION sql_mode = "STRICT_ALL_TABLES"');

            $connection->exec(file_get_contents(PROJECT_ROOT . '/sql/0001-create.sqlite'));
            $connection->exec(file_get_contents(PROJECT_ROOT . '/sql/0002-insert.sqlite'));

			//Store connection in static variable
			self::$connection = $connection;
		}

		//Return stored connection
		return self::$connection;
	}

	/**
	 * Execute SQL query with parameters. The $sql parameters should contain placeholders in the format of
	 * :placeholder, where the placeholder name should be a key passed in $params.
	 *
	 * @param string $sql
	 * @param array  $params
	 *
	 * @return array
	 *
	 * @throws ModelException if an SQL error is encountered
	 */
	protected function query($sql, $params) {
		//Fetch connection
		$connection = $this->getConnection();

		//Prepare and execute SQL statement with parameters to avoid SQL injections
		$preparedStatement = $connection->prepare($sql);
        if (false === $preparedStatement) {
            var_dump($connection->errorInfo());
            die();
        }
		$preparedStatement->execute($params);

		//In case of an error, throw a ModelException
		if ((int)$connection->errorCode()) {
			throw new ModelException($connection->errorInfo()[2], $connection->errorCode());
		}

		//Return result rows
		return $preparedStatement->fetchAll(PDO::FETCH_ASSOC);
	}
}

