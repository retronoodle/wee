# Wee Framework - Development Tasks

## Phase 1: Core Foundation

### 1.1 Framework Bootstrap
- [x] Create core `wee.php` file with basic class structure
- [x] Implement autoloader for app classes
- [x] Set up error handling and exception management
- [x] Create `public/index.php` entry point
- [x] Implement basic configuration system

### 1.2 Routing System
- [x] Build router class with HTTP method support (GET, POST, PUT, DELETE, PATCH)
- [x] Implement route parameter parsing (`:id`, `:slug`, etc.)
- [x] Add route-to-controller mapping
- [x] Implement closure-based routes
- [x] Add named routes functionality
- [x] Create route groups with prefixes
- [x] Implement RESTful resource routing (`wee::resource()`)

### 1.3 Request/Response
- [x] Create Request class for accessing input data
- [x] Implement query string, POST data, and file upload handling
- [x] Build Response class with status codes
- [x] Add JSON response helper
- [x] Implement redirect functionality
- [x] Add view rendering response
- [x] Create cookie and session helpers

## Phase 2: MVC Implementation

### 2.1 Controllers
- [ ] Create base Controller class
- [ ] Implement controller instantiation and method calling
- [ ] Add dependency injection for controller methods
- [ ] Create helper methods (view, json, redirect, etc.)
- [ ] Implement route model binding

### 2.2 Models (Active Record)
- [ ] Create base Model class
- [ ] Implement database connection (PDO)
- [ ] Build query builder (where, select, join, etc.)
- [ ] Add CRUD operations (find, create, update, delete)
- [ ] Implement `get()`, `first()`, `all()` methods
- [ ] Add relationship methods (hasMany, belongsTo, hasOne)
- [ ] Create timestamps functionality (created_at, updated_at)
- [ ] Implement soft deletes
- [ ] Add model events (creating, created, updating, updated, etc.)

### 2.3 Views
- [ ] Create view rendering engine (PHP-based)
- [ ] Implement view file location and loading
- [ ] Add data passing to views
- [ ] Create layout/section system
- [ ] Implement partial views
- [ ] Add view helpers (escape, url, asset, etc.)

## Phase 3: Essential Features

### 3.1 Middleware
- [ ] Build middleware system
- [ ] Implement before/after middleware
- [ ] Create middleware groups
- [ ] Add route-specific middleware
- [ ] Build common middleware (auth, CSRF, etc.)

### 3.2 Validation
- [ ] Create Validator class
- [ ] Implement common validation rules (required, email, numeric, min, max, etc.)
- [ ] Add custom validation rules
- [ ] Implement error message handling
- [ ] Create validation helper methods

### 3.3 Database Migrations (Optional but useful)
- [ ] Create migration system
- [ ] Implement schema builder
- [ ] Add CLI commands for migrations
- [ ] Create seeder functionality

## Phase 4: Developer Experience

### 4.1 CLI Tool
- [ ] Create `wee` CLI command
- [ ] Add `make:controller` command
- [ ] Add `make:model` command
- [ ] Add `serve` command for development server
- [ ] Add migration commands

### 4.2 Documentation
- [ ] Write getting started guide
- [ ] Document routing system
- [ ] Document models and query builder
- [ ] Document views and templates
- [ ] Create example applications
- [ ] Write API reference

### 4.3 Testing
- [ ] Set up testing framework
- [ ] Write unit tests for core components
- [ ] Add integration tests
- [ ] Create test helpers

## Phase 5: Polish & Optimization

### 5.1 Performance
- [ ] Implement route caching
- [ ] Add query result caching
- [ ] Optimize autoloader
- [ ] Profile and optimize hot paths

### 5.2 Security
- [ ] Implement CSRF protection
- [ ] Add XSS protection helpers
- [ ] Create SQL injection prevention guidelines
- [ ] Add security headers
- [ ] Implement rate limiting

### 5.3 Extensions
- [ ] Create plugin/extension system
- [ ] Build common extensions (auth, mail, cache)
- [ ] Add service container for DI
- [ ] Implement event system

## Nice-to-Have Features
- [ ] Simple asset pipeline (CSS/JS minification)
- [ ] Built-in authentication scaffolding
- [ ] API resource transformers
- [ ] Pagination
- [ ] File storage abstraction
- [ ] Queue system (basic)
- [ ] Logging system
- [ ] Environment-based configuration

## Target Metrics
- Zero required dependencies
- Boot time: < 5ms
- Memory footprint: < 1MB
