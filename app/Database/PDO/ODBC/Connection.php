<?php

namespace App\Database\PDO\ODBC;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\BaseResult;
use PDO;
use PDOStatement;
use RuntimeException;

class Connection extends BaseConnection
{
    public $connID;
    protected $lastStatement;
    protected $lastQuery;

    public function connect(bool $persistent = false)
    {
        if ($this->connID) {
            return $this->connID;
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $this->connID = new PDO($this->DSN, $this->username, $this->password, $options);
        } catch (\PDOException $e) {
            throw new RuntimeException('Erro na conexão PDO ODBC: ' . $e->getMessage());
        }

        return $this->connID;
    }

    public function getPlatform(): string
    {
        return 'PDO_ODBC';
    }

    public function getVersion(): string
    {
        return (string) $this->connID->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    /*
    public function query(string $sql, $binds = null, bool $setEscapeFlags = true, string $queryClass = ''): BaseResult
    {
        $this->connect();

        $statement = $this->connID->prepare($sql);

        if ($binds) {
            foreach ($binds as $key => $val) {
                $statement->bindValue(is_int($key) ? $key + 1 : $key, $val);
            }
        }

        $statement->execute();

        return new Result($statement);
    }
    */

    public function query(string $sql, $binds = null, bool $setEscapeFlags = true, string $queryClass = ''): BaseResult
    {
        $this->connect();

        $this->lastQuery = $sql;
        $this->lastStatement = null;

        $statement = $this->connID->prepare($sql);

        if ($binds) {
            foreach ($binds as $key => $val) {
                $statement->bindValue(is_int($key) ? $key + 1 : $key, $val);
            }
        }

        $statement->execute();

        $this->lastStatement = $statement;

        return new Result($statement);
    }

    public function close(): void
    {
        $this->connID = null;
    }

    public function execute(string $sql): bool
    {
        $this->connect();
        return $this->connID->exec($sql) !== false;
    }

    public function affectedRows(): int
    {
       // return $this->connID->rowCount();
        if ($this->lastStatement instanceof \PDOStatement) {
            try {
                return $this->lastStatement->rowCount();
            } catch (\Throwable $e) {
                return 0;
            }
        }

        return 0;
    }

    public function error(): array
    {
        $errorInfo = $this->connID->errorInfo();
        return [
            'code'    => $errorInfo[1] ?? null,
            'message' => $errorInfo[2] ?? '',
        ];
    }

    public function insertID(): int
    {
        return (int) $this->connID->lastInsertId();
    }

    public function isWriteType($sql): bool
    {
        return preg_match('/^(INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|ALTER|TRUNCATE)/i', ltrim($sql)) === 1;
    }

    public function countAll(string $table): int
    {
        $query = $this->query("SELECT COUNT(*) AS count FROM " . $table);
        $row = $query->getRowArray();
        return (int) ($row['count'] ?? 0);
    }

    public function countAllResults(bool $reset = true): int
    {
        $query = $this->query("SELECT COUNT(*) AS count FROM ({$this->getLastQuery()}) AS sub");
        $row = $query->getRowArray();
        return (int) ($row['count'] ?? 0);
    }

    public function reconnect(): bool
    {
        $this->close();
        $this->connect();
        return (bool) $this->connID;
    }

    public function setDatabase(string $database): void
    {
        // ODBC não tem comando direto de troca de banco, necessário reabrir a conexão
        $this->database = $database;
        $this->reconnect();
    }

    protected function _close(): void
    {
        $this->connID = null;
    }

    protected function _transBegin(): bool
    {
        return $this->connID->beginTransaction();
    }

    protected function _transCommit(): bool
    {
        return $this->connID->commit();
    }

    protected function _transRollback(): bool
    {
        return $this->connID->rollBack();
    }

    protected function _escapeString(string $str): string
    {
        return addslashes($str);
    }

    protected function _fieldData(string $table): array
    {
        return [];
    }

    protected function _indexData(string $table): array
    {
        return [];
    }

    protected function _foreignKeyData(string $table): array
    {
        return [];
    }

    protected function _listTables(bool $constrainByPrefix = false, ?string $tableName = null): array
    {
        return [];
    }

    protected function _listFields(string $table): array
    {
        return [];
    }

    protected function _listColumns($table = ''): array
    {
        return [];
    }

    protected function _truncate(string $table): bool
    {
        return false;
    }

    protected function _insertBatch(string $table, array $keys, array $values): string
    {
        return '';
    }

    protected function _updateBatch(string $table, array $values, string $index, ?int $batchSize = 100): string
    {
        return '';
    }

    protected function _deleteBatch(string $table, array $ids): string
    {
        return '';
    }
}
