<?php

namespace App\Models;

use App\Model;

/**
 * User Model - Example
 */
class User extends Model {

    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
    protected $timestamps = true;

    // Example: User has many posts
    public function posts() {
        return $this->hasMany(\App\Models\Post::class);
    }

    // Example: User has one profile
    public function profile() {
        return $this->hasOne(\App\Models\Profile::class);
    }

    // Model event example
    protected function onCreating() {
        // Hash password before creating
        if (isset($this->attributes['password'])) {
            $this->attributes['password'] = password_hash($this->attributes['password'], PASSWORD_DEFAULT);
        }
    }
}
