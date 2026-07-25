<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class InstallController extends Controller
{
    /**
     * Step 1: Welcome Screen
     */
    public function welcome()
    {
        // Check if already installed
        if ($this->isInstalled()) {
            return redirect()->route('home');
        }
        
        return view('install.welcome');
    }

    /**
     * Step 2: Server Requirements
     */
    public function requirements()
    {
        $requirements = $this->checkRequirements();
        
        return view('install.requirements', compact('requirements'));
    }

    /**
     * Step 3: Database Configuration
     */
    public function database()
    {
        return view('install.database');
    }

    /**
     * Process Database Configuration
     */
    public function setupDatabase(Request $request)
    {
        $request->validate([
            'db_host' => 'required',
            'db_port' => 'required|numeric',
            'db_name' => 'required',
            'db_username' => 'required',
            'db_password' => 'nullable',
        ]);

        // Test database connection
        try {
            $connection = @new \PDO(
                "mysql:host={$request->db_host};port={$request->db_port}",
                $request->db_username,
                $request->db_password ?? '',
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            // Drop database if exists, then create fresh
            $connection->exec("DROP DATABASE IF EXISTS `{$request->db_name}`");
            $connection->exec("CREATE DATABASE `{$request->db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $connection = null;

            // Update .env file
            $this->updateEnvFile([
                'DB_HOST' => $request->db_host,
                'DB_PORT' => $request->db_port,
                'DB_DATABASE' => $request->db_name,
                'DB_USERNAME' => $request->db_username,
                'DB_PASSWORD' => $request->db_password ?? '',
            ]);

            // Update database config at runtime
            config([
                'database.connections.mysql.host' => $request->db_host,
                'database.connections.mysql.port' => $request->db_port,
                'database.connections.mysql.database' => $request->db_name,
                'database.connections.mysql.username' => $request->db_username,
                'database.connections.mysql.password' => $request->db_password ?? '',
            ]);

            DB::purge('mysql');
            DB::reconnect('mysql');

            // Run migrations fresh (drop all tables and re-run)
            Artisan::call('migrate:fresh', ['--force' => true, '--seed' => false]);

            return redirect()->route('install.site-config')
                ->with('success', 'Database connected successfully!');

        } catch (\PDOException $e) {
            return back()->with('error', 'Database connection failed: ' . $e->getMessage())
                ->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Step 4: Site Configuration
     */
    public function siteConfig()
    {
        return view('install.site-config');
    }

    /**
     * Process Site Configuration
     */
    public function saveSiteConfig(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_url' => 'required|url',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'admin_password' => 'required|min:8|confirmed',
            'timezone' => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Update .env file
                $this->updateEnvFile([
                    'APP_NAME' => $request->site_name,
                    'APP_URL' => $request->site_url,
                    'APP_TIMEZONE' => $request->timezone,
                ]);

                // Create admin user
                DB::table('users')->insert([
                    'name' => $request->admin_name,
                    'email' => $request->admin_email,
                    'password' => Hash::make($request->admin_password),
                    'role' => 'super_admin',
                    'email_verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Insert default settings
                $this->insertDefaultSettings($request);
            });

            return redirect()->route('install.theme')
                ->with('success', 'Site configuration saved successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Step 5: Theme Selection
     */
    public function theme()
    {
        $themes = $this->getAvailableThemes();
        
        return view('install.theme', compact('themes'));
    }

    /**
     * Process Theme Selection
     */
    public function saveTheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|string',
        ]);

        try {
            // Update active theme in settings
            DB::table('settings')->updateOrInsert(
                ['key' => 'active_theme'],
                ['value' => $request->theme, 'updated_at' => now()]
            );

            return redirect()->route('install.payment')
                ->with('success', 'Theme selected successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Step 6: Payment Gateway Setup
     */
    public function payment()
    {
        $gateways = $this->getPaymentGateways();
        
        return view('install.payment', compact('gateways'));
    }

    /**
     * Process Payment Gateway Setup
     */
    public function savePayment(Request $request)
    {
        try {
            // Save payment gateway settings
            $gateways = ['bkash', 'nagad', 'rocket', 'sslcommerz', 'cod'];
            
            foreach ($gateways as $gateway) {
                if ($request->has($gateway . '_enabled')) {
                    DB::table('payment_gateways')->updateOrInsert(
                        ['slug' => $gateway],
                        [
                            'name' => ucfirst($gateway),
                            'is_active' => true,
                            'test_mode' => $request->has($gateway . '_test_mode'),
                            'credentials' => json_encode($request->get($gateway . '_credentials', [])),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }

            return redirect()->route('install.complete')
                ->with('success', 'Payment gateways configured successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Step 7: Installation Complete
     */
    public function complete()
    {
        // Create install.lock file
        File::put(storage_path('framework/install.lock'), 'Installed on: ' . now());

        // Generate application key if not exists
        if (empty(env('APP_KEY'))) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        // Clear cache
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        return view('install.complete');
    }

    /**
     * Check server requirements
     */
    protected function checkRequirements(): array
    {
        $requirements = [
            'php' => [
                'version' => '8.2',
                'current' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '8.2', '>='),
            ],
            'extensions' => [
                'ctype' => extension_loaded('ctype'),
                'fileinfo' => extension_loaded('fileinfo'),
                'json' => extension_loaded('json'),
                'mbstring' => extension_loaded('mbstring'),
                'openssl' => extension_loaded('openssl'),
                'pdo' => extension_loaded('pdo'),
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'tokenizer' => extension_loaded('tokenizer'),
                'xml' => extension_loaded('xml'),
                'gd' => extension_loaded('gd'),
                'curl' => extension_loaded('curl'),
                'zip' => extension_loaded('zip'),
                'intl' => extension_loaded('intl'),
            ],
            'permissions' => [
                'storage' => is_writable(storage_path()),
                'storage/framework' => is_writable(storage_path('framework')),
                'storage/logs' => is_writable(storage_path('logs')),
                'bootstrap/cache' => is_writable(base_path('bootstrap/cache')),
                'public/uploads' => is_writable(public_path('uploads')) || $this->createDirectory(public_path('uploads')),
            ],
        ];

        return $requirements;
    }

    /**
     * Create directory if not exists
     */
    protected function createDirectory($path): bool
    {
        if (!File::exists($path)) {
            return File::makeDirectory($path, 0755, true);
        }
        return is_writable($path);
    }

    /**
     * Update .env file
     */
    protected function updateEnvFile(array $data): void
    {
        $envPath = base_path('.env');
        $envContent = File::exists($envPath) ? File::get($envPath) : '';

        foreach ($data as $key => $value) {
            // Quote values containing spaces or special characters
            if (preg_match('/[^\w.-]/', $value) && !str_starts_with($value, '"') && !str_ends_with($value, '"')) {
                $value = '"' . str_replace('"', '\"', $value) . '"';
            }

            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $envContent);
    }

    /**
     * Insert default settings
     */
    protected function insertDefaultSettings(Request $request): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => $request->site_name],
            ['key' => 'site_email', 'value' => $request->admin_email],
            ['key' => 'site_url', 'value' => $request->site_url],
            ['key' => 'timezone', 'value' => $request->timezone],
            ['key' => 'currency', 'value' => 'BDT'],
            ['key' => 'currency_symbol', 'value' => '৳'],
            ['key' => 'date_format', 'value' => 'd/m/Y'],
            ['key' => 'time_format', 'value' => 'h:i A'],
            ['key' => 'items_per_page', 'value' => '12'],
            ['key' => 'enable_registration', 'value' => '1'],
            ['key' => 'enable_reviews', 'value' => '1'],
            ['key' => 'enable_wishlist', 'value' => '1'],
            ['key' => 'enable_compare', 'value' => '1'],
            ['key' => 'tax_rate', 'value' => '0'],
            ['key' => 'free_shipping_amount', 'value' => '0'],
            ['key' => 'flat_shipping_rate', 'value' => '0'],
            ['key' => 'two_factor_auth', 'value' => '0'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Get available themes
     */
    protected function getAvailableThemes(): array
    {
        $themesPath = resource_path('views/themes');
        $themes = [];

        if (File::exists($themesPath)) {
            $directories = File::directories($themesPath);
            
            foreach ($directories as $directory) {
                $themeName = basename($directory);
                $configPath = $directory . '/theme.json';
                
                if (File::exists($configPath)) {
                    $config = json_decode(File::get($configPath), true);
                    $themes[$themeName] = $config;
                } else {
                    $themes[$themeName] = [
                        'name' => ucfirst($themeName),
                        'slug' => $themeName,
                        'description' => 'A beautiful theme for e-commerce',
                        'preview_image' => '/themes/' . $themeName . '/preview.png',
                    ];
                }
            }
        }

        return $themes;
    }

    /**
     * Get payment gateways
     */
    protected function getPaymentGateways(): array
    {
        return [
            'bkash' => [
                'name' => 'bKash',
                'description' => 'Mobile Financial Service',
                'logo' => '/images/payments/bkash.png',
                'fields' => ['app_key', 'app_secret', 'username', 'password'],
            ],
            'nagad' => [
                'name' => 'Nagad',
                'description' => 'Mobile Financial Service',
                'logo' => '/images/payments/nagad.png',
                'fields' => ['merchant_id', 'merchant_number', 'public_key', 'private_key'],
            ],
            'rocket' => [
                'name' => 'Rocket',
                'description' => 'Mobile Financial Service',
                'logo' => '/images/payments/rocket.png',
                'fields' => ['merchant_id', 'merchant_number', 'password'],
            ],
            'sslcommerz' => [
                'name' => 'SSLCommerz',
                'description' => 'Payment Gateway',
                'logo' => '/images/payments/sslcommerz.png',
                'fields' => ['store_id', 'store_password'],
            ],
            'cod' => [
                'name' => 'Cash on Delivery',
                'description' => 'Pay when you receive',
                'logo' => '/images/payments/cod.png',
                'fields' => [],
            ],
        ];
    }

    /**
     * Check if application is installed
     */
    protected function isInstalled(): bool
    {
        return File::exists(storage_path('framework/install.lock'));
    }
}
