<?php
namespace MonAPI\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\Table;

class TableComponent extends Component
{
    const CACHE_DIRECTORY = WWW_ROOT . 'json/';
    private $return = true;
    private $table = null;
    private $conditions = [];
    private $tableColumns = [];
    private $tableContains = [];
    private $tableExcludedColumns = [];
    private $tableExcludedContains = [];

    public function clearAll()
    {
        $this->return = true;
        $this->table = null;
        $this->conditions = [];
        $this->tableColumns = [];
        $this->tableContains = [];
        $this->tableExcludedColumns = [];
        $this->tableExcludedContains = [];

        return $this;
    }

    public function setReturn(bool $return)
    {
        $this->return = $return;

        return $this;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function setTable(Table $table)
    {
        $this->table = $table;

        return $this;
    }

    public function checkTable()
    {
        $table = $this->getTable();
        if ($table && $table instanceof Table) {
            return true;
        }

        return false;
    }

    public function setRequestQueries(?string $separator = '-')
    {
        $request = $this->request;
        if ($request && $request->query && $separator) {
            $result = [];
            foreach ($request->query as $key => $item) {
                $exploded = explode($separator, $key);
                if (
                    isset($exploded[0]) && isset($exploded[1])
                    && $this->searchTableColumns($exploded[0], $exploded[1])
                ) {
                    $result[implode('.', $exploded)] = $item;
                }
            }
            $this->setTableConditions($result);
        }

        return $this;
    }

    public function setTableConditions(array $conditions, ?string $condition = null)
    {
        if ($condition) {
            $this->conditions[$condition] = $conditions;
        } else {
            $this->conditions = $conditions;
        }

        return $this;
    }

    public function getTableConditions(?string $condition = null)
    {
        if ($condition && isset($this->conditions[$condition])) {
            return $this->conditions[$condition];
        }

        return $this->conditions;
    }

    public function searchTableColumns(?string $searchAlias, ?string $searchColumn)
    {
        foreach ($this->getTableColumns() as $alias => $columns) {
            if ($alias === $searchAlias && in_array($searchColumn, $columns)) {
                return true;
            }
        }

        return false;
    }

    public function excludeTableColumns(array $excluded = null, ?bool $all = false)
    {
        if ($excluded) {
            $this->tableExcludedColumns = $excluded;
        } else if (!$excluded && $all) {
            $this->tableExcludedColumns = $this->getTableColumns();
        } else {
            $this->tableExcludedColumns = [];
        }

        return $this;
    }

    public function getTableExcludedColumns()
    {
        return $this->tableExcludedColumns;
    }

    public function excludeTableContains(?array $excluded = null, ?bool $all = false)
    {
        if ($excluded) {
            $this->tableExcludedContains = $excluded;
        } else if (!$excluded && $all) {
            $this->tableExcludedContains = $this->getTableContains();
        } else {
            $this->tableExcludedContains = [];
        }

        return $this;
    }

    public function getTableExcludedContains()
    {
        return $this->tableExcludedContains;
    }

    public function getTableColumns(?bool $selectColumns = false)
    {
        if ($this->tableColumns && $this->return) {
            return $this->tableColumns;
        }

        if (!$this->checkTable()) {
            return [];
        }

        $table = $this->getTable();
        $tableColumns = [$table->alias() => $table->schema()->columns()];
        foreach ($table->associations() as $association) {
            $excludedColumns = $this->getTableExcludedColumns();
            if (!in_array($association->name(), array_keys($excludedColumns))) {
                $columns = $association->target()->schema()->columns();
                $tableColumns[$association->name()] = $columns;
            }
        }

        $this->tableColumns = $tableColumns;

        return $tableColumns;
    }

    public function getTableContains()
    {
        if ($this->tableContains && $this->return) {
            return $this->tableContains;
        }

        if (!$this->checkTable()) {
            return [];
        }

        $table = $this->getTable();
        $tableContains = [];
        foreach ($table->associations() as $association) {
            $excludedContains = $this->getTableExcludedContains();
            if (!in_array($association->name(), $excludedContains)) {
                $tableContains[$association->foreignKey()] = $association->name();
            }
        }

        $this->tableContains = $tableContains;

        return $tableContains;
    }

    public function getTableData()
    {
        if (!$this->checkTable()) {
            return [];
        }

        $table = $this->getTable();

        $result = $table->find()->contain(array_values($this->getTableContains()));

        $conditions = $this->getTableConditions();
        if ($conditions) {
            $result->where($conditions);
        }

        return $result;
    }

    public function cacheTableData(?array $data, ?bool $compress = true)
    {
        if (!file_exists(self::CACHE_DIRECTORY)) {
            mkdir(self::CACHE_DIRECTORY, 0775);
        }

        if ($data) {
            $this->deleteCachedTableData();
            $data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
            $compress ? $data = gzcompress($data) : null;
            $filename = self::CACHE_DIRECTORY . date('Y-m-d') . '.json';
            file_put_contents($filename, $data);
        }

        return $this;
    }

    public function deleteCachedTableData(?int $maxFileCount = 11)
    {
        $files = glob(self::CACHE_DIRECTORY . '*.json');
        $fileCount = count($files);

        if ($fileCount > $maxFileCount) {
            $fileCount -= $maxFileCount;
            foreach ($files as $file) {
                if (is_file($file) && $fileCount > 0) {
                    unlink($file);
                    $fileCount--;
                } else {
                    break;
                }
            }
        }

        return $this;
    }

    public function getCachedTableData(?string $date = '', ?bool $isArray = false, ?bool $uncompress = true)
    {
        $date = $date ? $date : date('Y-m-d');
        $filename = self::CACHE_DIRECTORY . $date . '.json';
        if (!file_exists($filename)) {
            return false;
        }

        $cachedTableData = file_get_contents($filename);
        $uncompress ? $cachedTableData = gzuncompress($cachedTableData) : null;
        $isArray ? $cachedTableData = json_decode($cachedTableData, true) : null;

        return $cachedTableData;
    }
}
