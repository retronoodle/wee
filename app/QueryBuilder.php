<?php

namespace App;

/**
 * SQL Query Builder
 */
class QueryBuilder {

    private $table;
    private $db;
    private $select = ['*'];
    private $where = [];
    private $joins = [];
    private $orderBy = [];
    private $limit;
    private $offset;
    private $bindings = [];

    public function __construct($table, $db) {
        $this->table = $table;
        $this->db = $db;
    }

    public function select(...$columns) {
        $this->select = $columns;
        return $this;
    }

    public function where($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->where[] = [
            'type' => 'AND',
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        return $this;
    }

    public function orWhere($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->where[] = [
            'type' => 'OR',
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];

        return $this;
    }

    public function join($table, $first, $operator, $second) {
        $this->joins[] = [
            'type' => 'INNER',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];

        return $this;
    }

    public function leftJoin($table, $first, $operator, $second) {
        $this->joins[] = [
            'type' => 'LEFT',
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second
        ];

        return $this;
    }

    public function orderBy($column, $direction = 'ASC') {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset) {
        $this->offset = $offset;
        return $this;
    }

    public function get() {
        $sql = $this->buildSelectSql();
        return $this->db->fetchAll($sql, $this->bindings);
    }

    public function first() {
        $this->limit(1);
        $sql = $this->buildSelectSql();
        return $this->db->fetch($sql, $this->bindings);
    }

    public function find($id) {
        return $this->where('id', $id)->first();
    }

    public function insert($data) {
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $this->db->query($sql, array_values($data));
        return $this->db->lastInsertId();
    }

    public function update($data) {
        $setParts = [];
        $bindings = [];

        foreach ($data as $column => $value) {
            $setParts[] = "$column = ?";
            $bindings[] = $value;
        }

        $sql = sprintf("UPDATE %s SET %s", $this->table, implode(', ', $setParts));

        if (!empty($this->where)) {
            $sql .= ' WHERE ' . $this->buildWhereClause($bindings);
        }

        return $this->db->execute($sql, $bindings);
    }

    public function delete() {
        $sql = "DELETE FROM {$this->table}";

        $bindings = [];
        if (!empty($this->where)) {
            $sql .= ' WHERE ' . $this->buildWhereClause($bindings);
        }

        return $this->db->execute($sql, $bindings);
    }

    public function count() {
        $this->select = ['COUNT(*) as count'];
        $result = $this->first();
        return $result ? (int)$result->count : 0;
    }

    private function buildSelectSql() {
        $sql = sprintf("SELECT %s FROM %s", implode(', ', $this->select), $this->table);

        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $sql .= sprintf(
                    " %s JOIN %s ON %s %s %s",
                    $join['type'],
                    $join['table'],
                    $join['first'],
                    $join['operator'],
                    $join['second']
                );
            }
        }

        if (!empty($this->where)) {
            $sql .= ' WHERE ' . $this->buildWhereClause($this->bindings);
        }

        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    private function buildWhereClause(&$bindings) {
        $conditions = [];

        foreach ($this->where as $i => $condition) {
            $type = $i === 0 ? '' : $condition['type'];
            $conditions[] = sprintf(
                "%s %s %s ?",
                $type,
                $condition['column'],
                $condition['operator']
            );
            $bindings[] = $condition['value'];
        }

        return trim(implode(' ', $conditions));
    }
}
