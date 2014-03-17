<?php 

require_once 'unframed/Unframed.php';

/**
 * Opens a database connection, sets its error mode to PDO::ERRMODE_EXCEPTION 
 * and return a PDO object.
 *
 * @param string $dsn the distinguished name of the database
 * @param string $username
 * @param string $password
 *
 * @return PDO
 */
function unframed_sql_open($dsn, $username=NULL, $password=NULL) {
	$pdo = new PDO($dsn, $username, $password);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $pdo;
}

/**
 * Opens a PDO connection to an SQLite `$database` in the application's sql path
 * sets its error mode to PDO::ERRMODE_EXCEPTION and return a PDO object.
 *
 * @param string $database the name of the database file to open
 *
 * @return PDO
 */
function unframed_sqlite_open($database) {
	return unframed_sql_open('sqlite:'.unframed_sql_path($database), NULL, NULL);
}

/**
 * Begin a transaction on $pdo, apply $fun($pdo), commit the transaction and return the $fun 
 * result or catch any PDOException, roll back the transaction and throw an Unframed exception.
 *
 * @param PDO $pdo the database connection to use
 * @param function $fun to apply
 *
 * @return the $fun result
 *
 * @throws Unframed
 */
function unframed_sql_transaction($pdo, $fun) {
	$transaction = FALSE;
	try {
		$transaction = $pdo->beginTransaction();
		$result = $fun($pdo);
		$pdo->commit();
		return $result;
	} catch (PDOException $e) {
		if ($transaction) {
			$pdo->rollBack();
		}
		throw new Unframed($e->getMessage());
	}
}

/**
 * Prepare, bind, execute and return a PDOStatement or throw 
 * an Unframed exception if the SQL statement's execution failed 
 * without PDOException.
 *
 * @param PDO $pdo the database connection to use
 * @param string $statement to execute
 * @param array $parameters to apply
 *
 * @return PDOStatement
 *
 * @throws PDOException if $pdo error mode was set to exceptions
 * @throws Unframed if the execution failed without PDOException
 */
function unframed_sql_execute($pdo, $sql, $parameters) {
	$st = $pdo->prepare($sql);
	if ($st->execute($parameters)) {
		return $st;
	}
	throw new Unframed($st->errorInfo()[2]);
}

/**
 * Prepare and execute an array of SQL statement or throw an Unframed
 * exception if an execution failed without PDOException.
 *
 * @param PDO $pdo the database connection to use
 * @param array $statements to execute
 *
 * @return void
 *
 * @throws PDOException if $pdo error mode was set to exceptions
 * @throws Unframed if the execution failed without PDOException
 */
function unframed_sql_declare($pdo, $statements) {
	unframed_sql_transaction($pdo, function($pdo) use ($statements) {
		foreach ($statements as $sql) {
			$st = $pdo->prepare($sql);
			if (!$st->execute()) {
				throw new Unframed($st->errorInfo()[2]);
			}
		}
	});
}

?>