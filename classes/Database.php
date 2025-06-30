<?php
require_once __DIR__ . '/../config/config.php';

/**
 * PDO Database Class
 *
 * Connects to the database, creates prepared statements,
 * binds values, and returns rows and results.
 */
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh; // Database handler
    private $stmt; // Statement
    private $error;

    public function __construct() {
        // Set DSN (Data Source Name)
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        
        $options = [
            PDO::ATTR_PERSISTENT => true, // Persistent connection
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch as associative array
            PDO::ATTR_EMULATE_PREPARES => false, // Use native prepared statements
        ];

        // Create a new PDO instance
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // In a real application, you would log this error, not echo it
            die("Database connection failed: " . $this->error);
        }
    }

    /**
     * Prepare statement with SQL query.
     * @param string $sql The SQL query to prepare.
     */
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }

    /**
     * Bind values to the prepared statement using named parameters.
     * @param string $param The parameter identifier (e.g., :name).
     * @param mixed $value The value to bind to the parameter.
     * @param int|null $type The PDO::PARAM_* constant. If null, auto-detects type.
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * Execute the prepared statement.
     * @return bool True on success, false on failure.
     */
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            // Again, log this error in a real app
            die("Query execution failed: " . $this->error);
        }
    }

    /**
     * Get the result set as an array of associative arrays.
     * @return array The result set.
     */
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    /**
     * Get a single record as an associative array.
     * @return array|false The single record, or false if not found.
     */
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    /**
     * Get the number of rows affected by the last SQL statement.
     * @return int The row count.
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    /**
     * Get the ID of the last inserted row.
     * @return string The last insert ID.
     */
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
} 