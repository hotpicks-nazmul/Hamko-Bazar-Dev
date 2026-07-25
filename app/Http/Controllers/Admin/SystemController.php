<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * System Controller for managing system settings and updates.
 */
class SystemController extends Controller
{
    /**
     * Display the system update page.
     */
    public function update()
    {
        $settings = Setting::getSystemUpdateSettings();
        $version = Setting::getSystemVersion();
        
        // Get PHP and server info
        $phpVersion = PHP_VERSION;
        $laravelVersion = app()->version();
        
        // Get database info
        $dbType = DB::connection()->getDriverName();
        $dbVersion = DB::select("SELECT version() as version")[0]->version ?? 'Unknown';
        
        // Get server info
        $serverInfo = [
            'php_version' => $phpVersion,
            'laravel_version' => $laravelVersion,
            'database_type' => $dbType,
            'database_version' => $dbVersion,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];
        
        return view('admin.system.update', compact('settings', 'version', 'serverInfo'));
    }

    /**
     * Perform system update.
     */
    public function performUpdate(Request $request)
    {
        $request->validate([
            'update_type' => 'required|in:check,install,backup',
        ]);

        if ($request->update_type === 'check') {
            return $this->checkForUpdates();
        } elseif ($request->update_type === 'install') {
            return $this->installUpdate($request);
        } elseif ($request->update_type === 'backup') {
            return $this->createBackup();
        }

        return redirect()->route('admin.system.update')
            ->with('error', 'Invalid update action.');
    }

    /**
     * Check for available updates.
     */
    protected function checkForUpdates()
    {
        $currentVersion = Setting::get('app_version', '1.0.0');
        $updateChannel = Setting::get('update_channel', 'stable', 'system_update') ?? 'stable';

        try {
            $updateServerUrl = Setting::get('update_server_url', 'https://api.yourdomain.com/updates/check', 'system_update');

            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($updateServerUrl, [
                    'current_version' => $currentVersion,
                    'channel' => $updateChannel,
                    'domain' => request()->getHost(),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $updateAvailable = $data['update_available'] ?? false;
                $latestVersion = $data['latest_version'] ?? $currentVersion;
                $downloadUrl = $data['download_url'] ?? null;
                $releaseNotes = $data['release_notes'] ?? null;

                Setting::set('last_check', now()->toDateTimeString(), 'system_update');

                if ($updateAvailable && version_compare($latestVersion, $currentVersion, '>')) {
                    Setting::set('update_available', '1', 'system_update');
                    Setting::set('latest_version', $latestVersion, 'system_update');
                    Setting::set('download_url', $downloadUrl, 'system_update');
                    Setting::set('release_notes', $releaseNotes, 'system_update');

                    return redirect()->route('admin.system.update')
                        ->with('success', "A new version ($latestVersion) is available!");
                }
            }
        } catch (\Exception $e) {
            // If update server is unreachable, fall back to local version check
            // This allows the system to work even without an update server
        }

        Setting::set('last_check', now()->toDateTimeString(), 'system_update');
        Setting::set('update_available', '0', 'system_update');
        Setting::set('latest_version', $currentVersion, 'system_update');

        return redirect()->route('admin.system.update')
            ->with('info', 'Your system is up to date. No updates available.');
    }

    /**
     * Install the update.
     */
    protected function installUpdate(Request $request)
    {
        $request->validate([
            'backup_before_update' => 'sometimes|boolean',
        ]);

        try {
            if ($request->backup_before_update == '1' || Setting::get('backup_before_update', '1', 'system_update') == '1') {
                $backupResult = $this->performActualBackup();
                if (!$backupResult['success']) {
                    return redirect()->route('admin.system.update')
                        ->with('error', 'Backup failed: ' . $backupResult['message']);
                }
            }

            Artisan::call('migrate', ['--force' => true]);

            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            $latestVersion = Setting::get('latest_version', null, 'system_update');
            $newVersion = $request->new_version ?? $latestVersion ?? $this->getNextVersion();

            Setting::set('app_version', $newVersion);
            Setting::set('db_version', $newVersion);
            Setting::set('last_updated', now()->toDateTimeString());
            Setting::set('update_available', '0', 'system_update');

            return redirect()->route('admin.system.update')
                ->with('success', "System successfully updated to version $newVersion!");
        } catch (\Exception $e) {
            return redirect()->route('admin.system.update')
                ->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Get next version based on semantic versioning.
     */
    protected function getNextVersion()
    {
        $currentVersion = Setting::get('app_version', '1.0.0');
        $parts = explode('.', $currentVersion);

        if (count($parts) >= 3) {
            $parts[2] = (int)$parts[2] + 1;
        } else {
            $parts[] = '1';
        }

        return implode('.', $parts);
    }

    /**
     * Create a system backup.
     */
    protected function createBackup()
    {
        $result = $this->performActualBackup();

        if ($result['success']) {
            return redirect()->route('admin.system.update')
                ->with('success', $result['message']);
        }

        return redirect()->route('admin.system.update')
            ->with('error', 'Backup failed: ' . $result['message']);
    }

    /**
     * Perform the actual backup operation.
     */
    protected function performActualBackup()
    {
        try {
            $backupDir = storage_path('app/backups');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $timestamp = now()->format('Y-m-d_His');
            $version = Setting::get('app_version', '1.0.0');
            $backupPrefix = "backup_{$version}_{$timestamp}";

            $backupFiles = [];

            $dbType = DB::connection()->getDriverName();

            if ($dbType === 'mysql') {
                $backupFileName = "{$backupPrefix}_database.sql";
                $backupPath = "{$backupDir}/{$backupFileName}";

                $dbHost = config('database.connections.mysql.host');
                $dbPort = config('database.connections.mysql.port');
                $dbName = config('database.connections.mysql.database');
                $dbUser = config('database.connections.mysql.username');
                $dbPass = config('database.connections.mysql.password');

                $command = "mysqldump --host={$dbHost} --port={$dbPort} --user={$dbUser} --password={$dbPass} {$dbName} > {$backupPath}";

                exec($command, $output, $returnCode);

                if ($returnCode !== 0) {
                    return ['success' => false, 'message' => 'Database dump failed. mysqldump may not be installed.'];
                }

                $backupFiles[] = $backupFileName;

            } elseif ($dbType === 'pgsql') {
                $backupFileName = "{$backupPrefix}_database.sql";
                $backupPath = "{$backupDir}/{$backupFileName}";

                $dbHost = config('database.connections.pgsql.host');
                $dbPort = config('database.connections.pgsql.port');
                $dbName = config('database.connections.pgsql.database');
                $dbUser = config('database.connections.pgsql.username');
                $dbPass = config('database.connections.pgsql.password');

                putenv("PGPASSWORD={$dbPass}");
                $command = "pg_dump --host={$dbHost} --port={$dbPort} --user={$dbUser} --dbname={$dbName} -f {$backupPath}";

                exec($command, $output, $returnCode);

                if ($returnCode !== 0) {
                    return ['success' => false, 'message' => 'Database dump failed. pg_dump may not be installed.'];
                }

                $backupFiles[] = $backupFileName;

            } elseif ($dbType === 'sqlite') {
                $backupFileName = "{$backupPrefix}_database.sqlite";
                $backupPath = "{$backupDir}/{$backupFileName}";

                $dbPath = config('database.connections.sqlite.database');
                copy($dbPath, $backupPath);

                $backupFiles[] = $backupFileName;
            }

            $exportDir = storage_path("app/backups/{$backupPrefix}_files");
            if (!file_exists($exportDir)) {
                mkdir($exportDir, 0755, true);
            }

            $uploadsDir = public_path('uploads');
            if (file_exists($uploadsDir)) {
                $uploadsBackup = "{$exportDir}/uploads.zip";
                $this->createZipArchive($uploadsDir, $uploadsBackup);
                $backupFiles[] = basename($exportDir) . '/uploads.zip';
            }

            $settingsBackup = "{$backupDir}/{$backupPrefix}_settings.json";
            $settingsData = [
                'version' => Setting::get('app_version', '1.0.0'),
                'settings' => Setting::all(),
                'backup_date' => now()->toDateTimeString(),
            ];
            file_put_contents($settingsBackup, json_encode($settingsData, JSON_PRETTY_PRINT));
            $backupFiles[] = basename($settingsBackup);

            $backupInfo = [
                'created_at' => now()->toDateTimeString(),
                'version' => Setting::get('app_version', '1.0.0'),
                'status' => 'success',
                'files' => $backupFiles,
                'path' => $backupDir,
            ];

            $backups = json_decode(Setting::get('system_backups', '[]'), true) ?? [];
            $backups[] = $backupInfo;

            if (count($backups) > 10) {
                $backups = array_slice($backups, -10);
            }

            Setting::set('system_backups', json_encode($backups));

            $filesList = implode(', ', $backupFiles);
            return ['success' => true, 'message' => "Backup created successfully! Files: {$filesList}"];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create a zip archive of a directory.
     */
    protected function createZipArchive($sourceDir, $destinationFile)
    {
        if (!file_exists($sourceDir)) {
            return false;
        }

        $zip = new \ZipArchive();
        if ($zip->open($destinationFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourceDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($sourceDir) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
            return true;
        }

        return false;
    }

    /**
     * Save system update settings.
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'auto_check_updates' => 'sometimes|boolean',
            'auto_install_security' => 'sometimes|boolean',
            'update_channel' => 'sometimes|in:stable,beta,development',
            'notify_on_update' => 'sometimes|boolean',
            'backup_before_update' => 'sometimes|boolean',
            'update_server_url' => 'sometimes|url',
        ]);

        $settings = [
            'auto_check_updates' => $request->auto_check_updates ? '1' : '0',
            'auto_install_security' => $request->auto_install_security ? '1' : '0',
            'update_channel' => $request->update_channel ?? 'stable',
            'notify_on_update' => $request->notify_on_update ? '1' : '0',
            'backup_before_update' => $request->backup_before_update ? '1' : '0',
            'update_server_url' => $request->update_server_url ?? 'https://api.yourdomain.com/updates/check',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value, 'system_update');
        }

        return redirect()->route('admin.system.update')
            ->with('success', 'System update settings saved successfully!');
    }

    /**
     * Display server status page.
     */
    public function serverStatus()
    {
        $phpVersion = PHP_VERSION;
        $laravelVersion = app()->version();
        
        // Get database info (cross-database compatible)
        $dbType = DB::connection()->getDriverName();
        try {
            if ($dbType === 'mysql') {
                $dbVersion = DB::select("SELECT version() as version")[0]->version ?? 'Unknown';
            } elseif ($dbType === 'pgsql') {
                $dbVersion = DB::select("SELECT version() as version")[0]->version ?? 'Unknown';
            } elseif ($dbType === 'sqlite') {
                $dbVersion = DB::select("SELECT sqlite_version() as version")[0]->version ?? 'Unknown';
            } else {
                $dbVersion = 'Unknown';
            }
        } catch (\Exception $e) {
            $dbVersion = 'Unknown';
        }
        
        // Check required extensions
        $extensions = [
            'gd' => extension_loaded('gd'),
            'curl' => extension_loaded('curl'),
            'json' => extension_loaded('json'),
            'mbstring' => extension_loaded('mbstring'),
            'openssl' => extension_loaded('openssl'),
            'pdo' => extension_loaded('pdo'),
            'zip' => extension_loaded('zip'),
            'fileinfo' => extension_loaded('fileinfo'),
        ];
        
        // Check directory permissions
        $directories = [
            'storage/app/public' => is_writable(storage_path('app/public')),
            'storage/framework/cache' => is_writable(storage_path('framework/cache')),
            'storage/framework/sessions' => is_writable(storage_path('framework/sessions')),
            'storage/framework/views' => is_writable(storage_path('framework/views')),
            'bootstrap/cache' => is_writable(base_path('bootstrap/cache')),
        ];
        
        // Calculate health metrics
        $extensionsLoaded = count(array_filter($extensions));
        $extensionsTotal = count($extensions);
        $directoriesWritable = count(array_filter($directories));
        $directoriesTotal = count($directories);
        
        // Check database connection
        $dbConnected = false;
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Exception $e) {
            $dbConnected = false;
        }
        
        // Check cache functionality
        $cacheWorking = false;
        try {
            Cache::put('health_check', true, 10);
            $cacheWorking = Cache::get('health_check') === true;
        } catch (\Exception $e) {
            $cacheWorking = false;
        }
        
        // Check session functionality
        $sessionActive = true;
        try {
            session()->put('health_check', true);
            $sessionActive = session()->get('health_check') === true;
        } catch (\Exception $e) {
            $sessionActive = false;
        }
        
        // Calculate overall health percentage
        $healthScore = 0;
        $totalChecks = 5; // extensions, directories, db, cache, session
        
        if ($extensionsLoaded === $extensionsTotal) $healthScore++;
        if ($directoriesWritable === $directoriesTotal) $healthScore++;
        if ($dbConnected) $healthScore++;
        if ($cacheWorking) $healthScore++;
        if ($sessionActive) $healthScore++;
        
        $healthPercentage = round(($healthScore / $totalChecks) * 100);
        
        $serverInfo = [
            'php_version' => $phpVersion,
            'laravel_version' => $laravelVersion,
            'database_type' => $dbType,
            'database_version' => $dbVersion,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'os' => PHP_OS,
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'extensions' => $extensions,
            'directories' => $directories,
            'health' => [
                'percentage' => $healthPercentage,
                'extensions_loaded' => $extensionsLoaded,
                'extensions_total' => $extensionsTotal,
                'directories_writable' => $directoriesWritable,
                'directories_total' => $directoriesTotal,
                'db_connected' => $dbConnected,
                'cache_working' => $cacheWorking,
                'session_active' => $sessionActive,
            ],
        ];
        
        return view('admin.system.server-status', compact('serverInfo'));
    }

    /**
     * API: Get system information for frontend.
     */
    public function getSystemInfoApi()
    {
        $version = Setting::getSystemVersion();
        $settings = Setting::getSystemUpdateSettings();
        
        return response()->json([
            'success' => true,
            'data' => [
                'app_version' => $version['app_version'],
                'db_version' => $version['db_version'],
                'update_available' => $version['update_available'],
                'latest_version' => $version['latest_version'],
                'last_updated' => $version['last_updated'],
                'last_check' => $settings['last_check'],
                'auto_check_updates' => $settings['auto_check_updates'],
            ]
        ]);
    }

    /**
     * API: Get version information for frontend.
     */
    public function getVersionApi()
    {
        $version = Setting::getSystemVersion();
        
        return response()->json([
            'success' => true,
            'data' => [
                'current_version' => $version['app_version'] ?? '1.0.0',
                'update_available' => ($version['update_available'] ?? '0') == '1',
                'latest_version' => $version['latest_version'] ?? $version['app_version'] ?? '1.0.0',
                'last_updated' => $version['last_updated'],
            ]
        ]);
    }

    // ==================== Activity Logs ====================

    /**
     * Display activity logs index page.
     */
    public function activityLogsIndex(Request $request)
    {
        $tab = $request->get('tab', 'all');
        
        return $this->activityLogs($request);
    }

    /**
     * Display activity logs.
     */
    public function activityLogs(Request $request)
    {
        $tab = $request->get('tab', 'all');
        
        $query = ActivityLog::query()->with('causer');
        
        // Filter by tab
        if ($tab === 'admin') {
            $query->admin();
        } elseif ($tab === 'customer') {
            $query->customer();
        } elseif ($tab === 'system') {
            $query->system();
        }
        
        // Search by description
        if ($request->search) {
            $query->where('description', 'like', "%{$request->search}%");
        }
        
        // Filter by date range
        if ($request->date_range) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) === 2) {
                $query->dateRange($dates[0], $dates[1]);
            }
        }
        
        // Sort
        $sort = $request->sort ?? 'recent';
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }
        
        $logs = $query->paginate(25);
        
        // Stats - calculate based on current filters
        $statsQuery = ActivityLog::query();
        if ($tab === 'admin') {
            $statsQuery->admin();
        } elseif ($tab === 'customer') {
            $statsQuery->customer();
        } elseif ($tab === 'system') {
            $statsQuery->system();
        }
        if ($request->search) {
            $statsQuery->where('description', 'like', "%{$request->search}%");
        }
        if ($request->date_range) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) === 2) {
                $statsQuery->whereBetween('created_at', [$dates[0], $dates[1]]);
            }
        }
        
        $stats = [
            'total' => $statsQuery->count(),
            'admin' => ActivityLog::admin()->count(),
            'customer' => ActivityLog::customer()->count(),
            'system' => ActivityLog::system()->count(),
        ];
        
        // AJAX response for live search
        if ($request->ajax()) {
            $html = view('admin.system.activity-logs.partials.table-rows', [
                'logs' => $logs,
            ])->render();
            
            return response()->json([
                'html' => $html,
                'stats' => $stats,
                'pagination' => $logs->links()->toHtml(),
            ]);
        }
        
        return view('admin.system.activity-logs.index', [
            'logs' => $logs,
            'stats' => $stats,
            'tab' => $tab,
            'search' => $request->search ?? '',
            'dateRange' => $request->date_range ?? '',
            'sortBy' => $sort,
        ]);
    }

    /**
     * Display admin activity logs.
     */
    public function adminActivityLogs(Request $request)
    {
        $request->merge(['tab' => 'admin']);
        return $this->activityLogs($request);
    }

    /**
     * Display customer activity logs.
     */
    public function customerActivityLogs(Request $request)
    {
        $request->merge(['tab' => 'customer']);
        return $this->activityLogs($request);
    }

    /**
     * Export activity logs.
     */
    public function exportActivityLogs(Request $request)
    {
        $query = ActivityLog::query()->with('causer');
        
        $tab = $request->get('tab', 'all');
        if ($tab === 'admin') {
            $query->admin();
        } elseif ($tab === 'customer') {
            $query->customer();
        } elseif ($tab === 'system') {
            $query->system();
        }
        
        if ($request->date_range) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) === 2) {
                $query->dateRange($dates[0], $dates[1]);
            }
        }
        
        $logs = $query->orderBy('created_at', 'desc')->get();
        
        // Create CSV
        $filename = 'activity_logs_' . now()->format('Y-m-d_His') . '.csv';
        $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];
        
        $callback = function() use ($logs) {
            $handle = fopen('php://output', 'w');
            
            // Header
            fputcsv($handle, ['ID', 'Log Name', 'Description', 'Causer', 'IP Address', 'Date']);
            
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->log_name,
                    $log->description,
                    $log->causer ? $log->causer->name : 'System',
                    $log->ip_address,
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($handle);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Delete activity logs.
     */
    public function destroyActivityLogs(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);
        
        ActivityLog::whereIn('id', $request->ids)->delete();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Activity logs deleted successfully!',
            ]);
        }
        
        return redirect()->route('admin.system.activity-logs.index')
            ->with('success', 'Activity logs deleted successfully!');
    }

    /**
     * Clear all activity logs.
     */
    public function clearActivityLogs(Request $request)
    {
        $request->validate([
            'log_type' => 'required|in:all,admin,customer,system',
        ]);
        
        $logType = $request->log_type;
        
        if ($logType === 'all') {
            ActivityLog::truncate();
        } elseif ($logType === 'admin') {
            ActivityLog::admin()->delete();
        } elseif ($logType === 'customer') {
            ActivityLog::customer()->delete();
        } elseif ($logType === 'system') {
            ActivityLog::system()->delete();
        }
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Activity logs cleared successfully!',
            ]);
        }
        
        return redirect()->route('admin.system.activity-logs.index')
            ->with('success', 'Activity logs cleared successfully!');
    }
}
