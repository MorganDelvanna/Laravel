Setup and run migration + seeder

1. Install PHP dependencies:

```bash
composer install
```

2. Generate app key:

```bash
php artisan key:generate
```

3. Run migrations and seed the admin user:

```bash
php artisan migrate
php artisan db:seed
```

Admin credentials created by seeder:
- username: admin
- password: password

Change the password after first login.

Protecting admin routes

 - Register middleware: add to `app/Http/Kernel.php` under `$routeMiddleware`:
	 `'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,`

 - Example admin routes (add to `routes/web.php` or a new `routes/admin.php`):

```php
Route::middleware(['auth','admin'])->prefix('admin')->group(function() {
		Route::get('/', function() {
				return view('admin.dashboard');
		})->name('admin.dashboard');
		// add your admin routes here
});
```

 - Notes: Ensure Laravel authentication (e.g. `laravel/ui` or `laravel/breeze`) is configured for `auth` middleware to work.
