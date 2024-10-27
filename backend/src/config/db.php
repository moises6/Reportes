<?php

class Database
{
    private const DEFAULT_HOST = 'localhost';
    private const DEFAULT_DBNAME = 'reportes';
    private const DEFAULT_USERNAME = 'root';

    private string $host;
    private string $dbname;
    private string $username;
    private ?string $password;
    private array $options;
    private ?PDO $conn;

    public function __construct(string $host = self::DEFAULT_HOST, string $dbname = self::DEFAULT_DBNAME, string $username = self::DEFAULT_USERNAME, ?string $password = null)
    {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->username = $username;
        $this->password = $password;
        $this->options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $this->conn = null;
    }

    public function getConnection(): PDO
    {
        if ($this->conn === null) {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname}";
            $this->conn = new PDO($dsn, $this->username, $this->password, $this->options);
        }
        return $this->conn;
    }

    protected function handleConnectionError(PDOException $e): void
    {
        $errorMessage = "Error de conexión: " . $e->getMessage();
        error_log($errorMessage); // Registra el error en los logs del servidor
        throw new RuntimeException($errorMessage, $e->getCode(), $e);
    }

    public function closeConnection(): void
    {
        $this->conn = null;
    }

    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
