<?php

namespace App;

/**
 * Base Model class with Active Record pattern
 */
abstract class Model {

    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $guarded = ['id'];
    protected $timestamps = true;
    protected $softDeletes = false;
    protected $attributes = [];
    protected $original = [];
    protected $exists = false;

    private static $db;
    private static $relationCache = [];

    public function __construct($attributes = []) {
        $this->fill($attributes);
    }

    protected static function db() {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    protected static function query() {
        $model = new static();
        $table = $model->getTable();
        $builder = self::db()->table($table);

        if ($model->softDeletes) {
            $builder->where('deleted_at', null);
        }

        return $builder;
    }

    public function getTable() {
        if ($this->table) {
            return $this->table;
        }

        // Auto-generate table name from class name
        $class = get_class($this);
        $class = basename(str_replace('\\', '/', $class));
        return strtolower($class) . 's';
    }

    // CRUD Operations

    public static function all() {
        $results = self::query()->get();
        return array_map(fn($item) => static::hydrate($item), $results);
    }

    public static function find($id) {
        $model = new static();
        $result = self::query()->where($model->primaryKey, $id)->first();
        return $result ? static::hydrate($result) : null;
    }

    public static function where($column, $operator, $value = null) {
        return self::query()->where($column, $operator, $value);
    }

    public static function create($attributes) {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    public function save() {
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            if (!$this->exists) {
                $this->attributes['created_at'] = $now;
            }
            $this->attributes['updated_at'] = $now;
        }

        $this->fireModelEvent('saving');

        if ($this->exists) {
            $this->fireModelEvent('updating');
            $this->performUpdate();
            $this->fireModelEvent('updated');
        } else {
            $this->fireModelEvent('creating');
            $this->performInsert();
            $this->fireModelEvent('created');
        }

        $this->fireModelEvent('saved');
        $this->original = $this->attributes;
        $this->exists = true;

        return $this;
    }

    private function performInsert() {
        $data = $this->filterFillable($this->attributes);
        $id = self::db()->table($this->getTable())->insert($data);
        $this->attributes[$this->primaryKey] = $id;
    }

    private function performUpdate() {
        $data = $this->filterFillable($this->attributes);
        unset($data[$this->primaryKey]);

        self::db()->table($this->getTable())
            ->where($this->primaryKey, $this->attributes[$this->primaryKey])
            ->update($data);
    }

    public function update($attributes) {
        $this->fill($attributes);
        return $this->save();
    }

    public function delete() {
        if (!$this->exists) {
            return false;
        }

        $this->fireModelEvent('deleting');

        if ($this->softDeletes) {
            $this->attributes['deleted_at'] = date('Y-m-d H:i:s');
            $this->save();
        } else {
            self::db()->table($this->getTable())
                ->where($this->primaryKey, $this->attributes[$this->primaryKey])
                ->delete();
        }

        $this->fireModelEvent('deleted');
        return true;
    }

    public function forceDelete() {
        if (!$this->exists) {
            return false;
        }

        self::db()->table($this->getTable())
            ->where($this->primaryKey, $this->attributes[$this->primaryKey])
            ->delete();

        return true;
    }

    public static function withTrashed() {
        $model = new static();
        return self::db()->table($model->getTable());
    }

    // Relationships

    public function hasOne($related, $foreignKey = null, $localKey = null) {
        $instance = new $related();
        $foreignKey = $foreignKey ?: strtolower(class_basename(static::class)) . '_id';
        $localKey = $localKey ?: $this->primaryKey;

        return new HasOneRelation($this, $instance, $foreignKey, $localKey);
    }

    public function hasMany($related, $foreignKey = null, $localKey = null) {
        $instance = new $related();
        $foreignKey = $foreignKey ?: strtolower(class_basename(static::class)) . '_id';
        $localKey = $localKey ?: $this->primaryKey;

        return new HasManyRelation($this, $instance, $foreignKey, $localKey);
    }

    public function belongsTo($related, $foreignKey = null, $ownerKey = null) {
        $instance = new $related();
        $foreignKey = $foreignKey ?: strtolower(class_basename($related)) . '_id';
        $ownerKey = $ownerKey ?: $instance->primaryKey;

        return new BelongsToRelation($this, $instance, $foreignKey, $ownerKey);
    }

    // Helpers

    public function fill($attributes) {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this;
    }

    private function isFillable($key) {
        if (in_array($key, $this->guarded)) {
            return false;
        }

        if (empty($this->fillable)) {
            return true;
        }

        return in_array($key, $this->fillable);
    }

    private function filterFillable($attributes) {
        $filtered = [];
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $filtered[$key] = $value;
            }
        }
        return $filtered;
    }

    private static function hydrate($data) {
        $model = new static();
        $model->exists = true;

        foreach ($data as $key => $value) {
            $model->attributes[$key] = $value;
        }

        $model->original = $model->attributes;
        return $model;
    }

    // Model Events

    private function fireModelEvent($event) {
        $method = 'on' . ucfirst($event);
        if (method_exists($this, $method)) {
            $this->$method();
        }
    }

    // Magic methods

    public function __get($key) {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        // Check for relationship method
        if (method_exists($this, $key)) {
            $cacheKey = get_class($this) . ":{$this->attributes[$this->primaryKey]}:$key";

            if (!isset(self::$relationCache[$cacheKey])) {
                self::$relationCache[$cacheKey] = $this->$key()->get();
            }

            return self::$relationCache[$cacheKey];
        }

        return null;
    }

    public function __set($key, $value) {
        $this->attributes[$key] = $value;
    }

    public function __isset($key) {
        return isset($this->attributes[$key]);
    }

    public function toArray() {
        return $this->attributes;
    }

    public function toJson() {
        return json_encode($this->attributes);
    }
}

// Relationship classes

class HasOneRelation {
    private $parent;
    private $related;
    private $foreignKey;
    private $localKey;

    public function __construct($parent, $related, $foreignKey, $localKey) {
        $this->parent = $parent;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    public function get() {
        $query = $this->related::query();
        return $query->where($this->foreignKey, $this->parent->{$this->localKey})->first();
    }
}

class HasManyRelation {
    private $parent;
    private $related;
    private $foreignKey;
    private $localKey;

    public function __construct($parent, $related, $foreignKey, $localKey) {
        $this->parent = $parent;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    public function get() {
        $results = $this->related::query()
            ->where($this->foreignKey, $this->parent->{$this->localKey})
            ->get();

        return array_map(fn($item) => $this->related::hydrate($item), $results);
    }
}

class BelongsToRelation {
    private $child;
    private $related;
    private $foreignKey;
    private $ownerKey;

    public function __construct($child, $related, $foreignKey, $ownerKey) {
        $this->child = $child;
        $this->related = $related;
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
    }

    public function get() {
        return $this->related::find($this->child->{$this->foreignKey});
    }
}

// Helper function for class_basename
if (!function_exists('class_basename')) {
    function class_basename($class) {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
}
