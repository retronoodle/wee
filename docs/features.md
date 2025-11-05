# Wee Framework - Features

> A tiny PHP framework with a passion for simplicity but very powerful.

## Core Philosophy

- **Tiny footprint**: Minimal, efficient core
- **Zero dependencies**: Works standalone, no composer packages required
- **MVC Architecture**: Full Model-View-Controller support
- **Flask-minimal, Laravel-elegant**: Best of both worlds
- **Convention over configuration**: Works out of the box
- **Plain PHP templates**: No new syntax to learn

## Routing

### HTTP Method Support
```php
wee::get('/users', 'UserController@index');
wee::post('/users', 'UserController@store');
wee::put('/users/:id', 'UserController@update');
wee::delete('/users/:id', 'UserController@destroy');
wee::patch('/users/:id', 'UserController@patch');
wee::any('/catch-all', 'Controller@method');
```

### Route Parameters
```php
wee::get('/users/:id', fn($id) => "User $id");
wee::get('/posts/:slug', 'PostController@show');
```

### Closure Routes
```php
wee::get('/hello', function() {
    return 'Hello World!';
});
```

### RESTful Resources
```php
// Automatically creates: index, show, create, store, edit, update, destroy
wee::resource('/posts', 'PostController');
```

### Named Routes
```php
wee::get('/dashboard', 'DashboardController@index')->name('dashboard');
// Usage: wee::route('dashboard')
```

### Route Groups
```php
wee::group(['prefix' => '/api', 'middleware' => 'auth'], function() {
    wee::get('/users', 'UserController@index');
    wee::get('/posts', 'PostController@index');
});
```

## Controllers

### Simple & Clean
```php
class UserController extends Controller {
    public function index() {
        $users = User::all();
        return $this->view('users/index', ['users' => $users]);
    }

    public function show($id) {
        $user = User::find($id);
        return $this->json($user);
    }
}
```

### Response Helpers
```php
$this->view('template', $data);
$this->json(['key' => 'value']);
$this->redirect('/path');
$this->status(404)->send('Not Found');
```

### Dependency Injection
```php
public function show(Request $request, $id) {
    // $request auto-injected
    $input = $request->input('name');
}
```

## Models (Active Record)

### Basic Usage
```php
class User extends Model {
    protected $table = 'users';
    protected $fillable = ['name', 'email'];
}

// Find by ID
$user = User::find(1);

// Get all records
$users = User::all();

// Query builder
$users = User::where('active', 1)
             ->orderBy('name')
             ->limit(10)
             ->get();

// First or single result
$user = User::where('email', 'test@example.com')->first();
```

### CRUD Operations
```php
// Create
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Update
$user = User::find(1);
$user->name = 'Jane Doe';
$user->save();

// Delete
$user->delete();
```

### Relationships
```php
class User extends Model {
    public function posts() {
        return $this->hasMany(Post::class);
    }

    public function profile() {
        return $this->hasOne(Profile::class);
    }
}

class Post extends Model {
    public function user() {
        return $this->belongsTo(User::class);
    }
}

// Usage
$user = User::find(1);
$posts = $user->posts()->get();
```

### Timestamps
```php
// Automatically manages created_at and updated_at
protected $timestamps = true;
```

### Soft Deletes
```php
protected $softDeletes = true;

$user->delete(); // Soft delete
$user->forceDelete(); // Permanent delete
User::withTrashed()->get(); // Include soft deleted
```

## Views

### Plain PHP Templates
```php
<!-- views/users/index.php -->
<h1>Users</h1>
<ul>
    <?php foreach($users as $user): ?>
        <li><?= $user->name ?></li>
    <?php endforeach; ?>
</ul>
```

### Layouts & Sections
```php
<!-- views/layout.php -->
<!DOCTYPE html>
<html>
<head>
    <title><?= $this->section('title') ?></title>
</head>
<body>
    <?= $this->content() ?>
</body>
</html>

<!-- views/page.php -->
<?php $this->layout('layout') ?>
<?php $this->section('title', 'Page Title') ?>
<h1>Content</h1>
```

### Partials
```php
<?php $this->partial('header', ['title' => 'My Title']) ?>
```

### View Helpers
```php
<?= $this->escape($userInput) ?>
<?= $this->url('/path') ?>
<?= $this->asset('/css/style.css') ?>
```

## Request & Response

### Request
```php
$request->input('name');
$request->query('page');
$request->all();
$request->has('email');
$request->file('avatar');
$request->method(); // GET, POST, etc.
$request->is('api/*');
```

### Response
```php
wee::json(['data' => $users]);
wee::json($data, 201); // With status code
wee::redirect('/home');
wee::redirect()->back();
wee::status(404)->send('Not Found');
```

## Middleware

### Global Middleware
```php
wee::before(function() {
    // Runs before all routes
});

wee::after(function($response) {
    // Runs after all routes
});
```

### Named Middleware
```php
wee::middleware('auth', function() {
    if (!Session::has('user')) {
        return wee::redirect('/login');
    }
});

// Apply to routes
wee::get('/dashboard', 'DashboardController@index')->middleware('auth');
```

### Route Group Middleware
```php
wee::group(['middleware' => 'auth'], function() {
    wee::get('/dashboard', 'DashboardController@index');
});
```

## Validation

### Simple & Powerful
```php
$validated = $this->validate($request, [
    'name' => 'required|min:3|max:255',
    'email' => 'required|email|unique:users',
    'age' => 'required|numeric|min:18',
    'website' => 'url',
    'password' => 'required|min:8|confirmed'
]);
```

### Available Rules
- `required` - Field must be present
- `email` - Valid email format
- `numeric` - Must be numeric
- `min:n` - Minimum value/length
- `max:n` - Maximum value/length
- `between:min,max` - Between range
- `url` - Valid URL format
- `unique:table,column` - Unique in database
- `confirmed` - Matches field_confirmation
- `in:val1,val2` - Must be in list
- `regex:pattern` - Match regex pattern

### Custom Rules
```php
wee::validator('uppercase', function($value) {
    return $value === strtoupper($value);
});
```

## Database

### Query Builder
```php
wee::db()->table('users')
    ->select('name', 'email')
    ->where('active', 1)
    ->where('age', '>', 18)
    ->orWhere('admin', true)
    ->orderBy('name', 'desc')
    ->limit(10)
    ->offset(20)
    ->get();
```

### Joins
```php
wee::db()->table('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.*', 'posts.title')
    ->get();
```

### Raw Queries
```php
wee::db()->query("SELECT * FROM users WHERE id = ?", [1]);
```

## CLI Tools

### Make Commands
```bash
wee make:controller UserController
wee make:model User
wee make:middleware Auth
```

### Development Server
```bash
wee serve
wee serve --port=8080
```

### Migrations
```bash
wee migrate
wee migrate:rollback
wee migrate:fresh
```

## Configuration

### Zero Config by Default
Works out of the box with sensible defaults.

### Optional Configuration
```php
// config/app.php
return [
    'debug' => true,
    'timezone' => 'UTC',
];

// config/database.php
return [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'wee',
    'username' => 'root',
    'password' => '',
];
```

### Environment Variables
```php
// .env support
$config = wee::env('APP_DEBUG', false);
```

## Security Features

- CSRF protection middleware
- XSS protection via output escaping
- SQL injection prevention (prepared statements)
- Password hashing helpers
- Secure session handling
- Rate limiting
- Security headers

## Performance

- **Tiny footprint**: < 1MB memory
- **Fast boot**: < 5ms startup time
- **Route caching**: Production optimization
- **Query result caching**: Built-in cache support
- **Lazy loading**: Components loaded on demand

## Extension Points

- Custom middleware
- Custom validation rules
- Model events and observers
- Service providers
- Helper functions
- Database drivers
