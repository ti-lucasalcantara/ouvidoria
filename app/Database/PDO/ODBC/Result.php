<?php

namespace App\Database\PDO\ODBC;

use CodeIgniter\Database\BaseResult;
use PDO;
use PDOStatement;
use stdClass;

class Result extends BaseResult
{
    public $resultID;

    public function __construct(PDOStatement $result)
    {
        $this->resultID = $result;
    }

    public function getFieldCount(): int
    {
        return $this->resultID->columnCount();
    }

    public function getFieldNames(): array
    {
        $names = [];
        for ($i = 0; $i < $this->resultID->columnCount(); $i++) {
            $meta = $this->resultID->getColumnMeta($i);
            $names[] = $meta['name'] ?? 'column' . $i;
        }
        return $names;
    }

    public function getFieldData(): array
    {
        $fields = [];
        for ($i = 0; $i < $this->resultID->columnCount(); $i++) {
            $meta = $this->resultID->getColumnMeta($i);
            $field = new \stdClass();
            $field->name       = $meta['name'] ?? 'column' . $i;
            $field->type       = $meta['native_type'] ?? 'string';
            $field->max_length = $meta['len'] ?? null;
            $field->primary_key = $meta['flags'] ?? false;
            $fields[] = $field;
        }
        return $fields;
    }

    public function freeResult(): void
    {
        $this->resultID = null;
    }

    public function dataSeek(int $n = 0): bool
    {
        // Não suportado diretamente por PDOStatement
        return false;
    }

    protected function fetchAssoc()
    {
        return $this->resultID->fetch(PDO::FETCH_ASSOC);
    }

    protected function fetchObject(string $className = 'stdClass')
    {
        return $this->resultID->fetchObject($className);
    }

    public function getRowArray(int $n = 0)
    {
        $result = $this->getResultArray();
        if ($result === []) {
            return null;
        }

        if ($n !== $this->currentRow && isset($result[$n])) {
            $this->currentRow = $n;
        }

        return $result[$this->currentRow];
    }

    public function getResultArray(): array
    {
        return $this->resultID->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResultObject(): array
    {
        return $this->resultID->fetchAll(PDO::FETCH_OBJ);
    }

    public function getRow($n = 0, string $type = 'object')
    {
        $all = ($type === 'object') ? $this->getResultObject() : $this->getResultArray();
        return $all[$n] ?? null;
    }

    public function setRow($key, $value = null)
    {
        if (! is_array($this->rowData)) {
            $this->rowData = $this->getRowArray();
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->rowData[$k] = $v;
            }
            return;
        }

        if ($key !== '' && $value !== null) {
            $this->rowData[$key] = $value;
        }
    }
}
