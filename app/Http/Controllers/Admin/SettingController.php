<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Currency;
use App\Models\Tax;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Helpers\ImageHelper;

class SettingController extends Controller
{
    public function general()
    {
        $settings = Setting::pluck('value', 'key');
        return view('admin.settings.general', compact('settings'));
    }

    public function updateGeneral(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'site_name' => 'required|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'currency' => 'nullable|string|in:BDT,USD,EUR,GBP,INR',
            'timezone' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Only allow specific settings keys to prevent injection
            $allowedKeys = ['site_name', 'site_tagline', 'currency', 'timezone'];
            $settings = $request->only($allowedKeys);
            
            // Handle favicon file upload
            if ($request->hasFile('site_favicon')) {
                $imageResult = ImageHelper::processImage(
                    $request->file('site_favicon'),
                    'logo',
                    256,
                    64,
                    85
                );
                $settings['site_favicon'] = $imageResult['path'];
                
                // Delete old favicon if exists
                $oldSetting = Setting::where('key', 'site_favicon')->first();
                if ($oldSetting && $oldSetting->value) {
                    ImageHelper::deleteImage($oldSetting->value);
                }
            }
            
            foreach ($settings as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }

            return back()->with('success', 'General settings updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update settings. Please try again.');
        }
    }

    public function store()
    {
        $settings = Setting::pluck('value', 'key');
        return view('admin.settings.store', compact('settings'));
    }

    public function updateStore(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Store settings updated successfully.');
    }

    public function email()
    {
        // Get SMTP settings from config/env first, then database
        $settings = [
            'mail_mailer' => config('mail.mailer', 'smtp'),
            'mail_host' => config('mail.host', ''),
            'mail_port' => config('mail.port', '587'),
            'mail_username' => config('mail.username', ''),
            'mail_password' => config('mail.password', ''),
            'mail_encryption' => config('mail.encryption', 'tls'),
            'mail_from_address' => config('mail.from.address', 'noreply@example.com'),
            'mail_from_name' => config('mail.from.name', config('app.name')),
            'contact_email' => '',
        ];
        
        // Override with database settings if they exist
        $dbSettings = Setting::where('key', 'like', 'mail_%')->pluck('value', 'key');
        foreach ($dbSettings as $key => $value) {
            $settings[$key] = $value;
        }
        
        // Also get contact_email
        $contactEmail = Setting::where('key', 'contact_email')->value('value');
        if ($contactEmail) {
            $settings['contact_email'] = $contactEmail;
        }
        
        return view('admin.settings.email', compact('settings'));
    }

    public function updateEmail(Request $request)
    {
        // Validate SMTP settings input
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'mail_mailer' => 'required|string|in:smtp,sendmail,log,array,mailgun,ses,postmark',
            'mail_host' => 'required_if:mail_mailer,smtp|string|max:255',
            'mail_port' => 'required_if:mail_mailer,smtp|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl,',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
            'contact_email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Save SMTP settings to database and update .env
        $smtpSettings = $request->except('_token', 'test_email', 'contact_email');
        
        foreach ($smtpSettings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'smtp']
            );
        }
        
        // Save contact_email separately (not an SMTP config, just a setting)
        if ($request->has('contact_email')) {
            Setting::updateOrCreate(
                ['key' => 'contact_email'],
                ['value' => $request->contact_email, 'group' => 'general']
            );
        }
        
        // Update the .env file
        $this->updateEnvFile($request);
        
        return back()->with('success', 'Email settings updated successfully.');
    }

    protected function updateEnvFile(Request $request)
    {
        $envPath = base_path('.env');
        
        if (!file_exists($envPath)) {
            return;
        }
        
        $envContent = file_get_contents($envPath);
        
        $updates = [
            'MAIL_MAILER' => $request->mail_mailer ?? 'smtp',
            'MAIL_HOST' => $request->mail_host ?? '',
            'MAIL_PORT' => $request->mail_port ?? '587',
            'MAIL_USERNAME' => $request->mail_username ?? '',
            'MAIL_PASSWORD' => $request->mail_password ?? '',
            'MAIL_ENCRYPTION' => $request->mail_encryption ?? 'tls',
            'MAIL_FROM_ADDRESS' => $request->mail_from_address ?? '',
            'MAIL_FROM_NAME' => $request->mail_from_name ?? '',
        ];
        
        foreach ($updates as $key => $value) {
            // Check if the key exists in .env
            if (preg_match('/^' . $key . '=/m', $envContent)) {
                // Update existing key
                $envContent = preg_replace(
                    '/^' . $key . '=.*$/m',
                    $key . '="' . $value . '"',
                    $envContent
                );
            } else {
                // Add new key
                $envContent .= "\n" . $key . '="' . $value . '"';
            }
        }
        
        file_put_contents($envPath, $envContent);
        
        // Clear config cache
        $this->clearConfigCache();
    }
    
    protected function clearConfigCache()
    {
        // Clear Laravel config cache
        if (file_exists(base_path('bootstrap/cache/config.php'))) {
            // Remove config cache
            @unlink(base_path('bootstrap/cache/config.php'));
        }
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);
        
        try {
            // Temporarily use the submitted settings for testing
            config([
                'mail.mailer' => $request->input('mail_mailer', config('mail.mailer')),
                'mail.host' => $request->input('mail_host', config('mail.host')),
                'mail.port' => $request->input('mail_port', config('mail.port')),
                'mail.username' => $request->input('mail_username', config('mail.username')),
                'mail.password' => $request->input('mail_password', config('mail.password')),
                'mail.encryption' => $request->input('mail_encryption', config('mail.encryption')),
                'mail.from.address' => $request->input('mail_from_address', config('mail.from.address')),
                'mail.from.name' => $request->input('mail_from_name', config('mail.from.name')),
            ]);
            
            \Mail::raw('Test Email from ' . config('app.name'), function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject('Test Email - ' . config('app.name'));
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function seo()
    {
        $settings = Setting::pluck('value', 'key');
        return view('admin.settings.seo', compact('settings'));
    }

    public function updateSeo(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'SEO settings updated successfully.');
    }

    public function social()
    {
        $settings = Setting::pluck('value', 'key');
        return view('admin.settings.social', compact('settings'));
    }

    public function updateSocial(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Social settings updated successfully.');
    }

    public function maintenance()
    {
        $settings = Setting::pluck('value', 'key');
        return view('admin.settings.maintenance', compact('settings'));
    }

    public function updateMaintenance(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Maintenance settings updated successfully.');
    }

    public function backup()
    {
        $backupPath = storage_path('app/backups');
        $files = [];
        
        if (is_dir($backupPath)) {
            $files = collect(scandir($backupPath))
                ->filter(fn($f) => is_file($backupPath . '/' . $f) && pathinfo($f, PATHINFO_EXTENSION) === 'zip')
                ->map(function($file) use ($backupPath) {
                    $fullPath = $backupPath . '/' . $file;
                    return [
                        'name' => $file,
                        'size' => filesize($fullPath),
                        'created_at' => filemtime($fullPath),
                    ];
                })
                ->sortByDesc('created_at')
                ->values()
                ->toArray();
        }
        
        return view('admin.settings.backup', compact('files'));
    }

    public function createBackup()
    {
        // Check if ZipArchive extension is available
        if (!class_exists('ZipArchive')) {
            return back()->with('error', 'ZipArchive extension is not installed. Please enable it in your PHP configuration.');
        }

        try {
            $backupPath = storage_path('app/backups');
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            // Generate backup filename with timestamp
            $timestamp = now()->format('Y-m-d_H-i-s');
            $filename = 'backup_' . $timestamp . '.zip';
            $backupFile = $backupPath . '/' . $filename;
            
            // Create database backup using mysqldump if available
            $dbHost = config('database.connections.mysql.host');
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            
            // Create a simple SQL dump
            $sqlFile = storage_path('app/backups/temp_' . $timestamp . '.sql');
            
            // Try using mysqldump with credentials file for security
            $mysqlCredentialsFile = storage_path('app/backups/temp_my.cnf_' . $timestamp);
            $credentialsContent = "[client]\nhost={$dbHost}\nuser={$dbUser}\npassword={$dbPass}\n";
            file_put_contents($mysqlCredentialsFile, $credentialsContent);
            chmod($mysqlCredentialsFile, 0600);
            
            $command = "mysqldump --defaults-file={$mysqlCredentialsFile} {$dbName} > {$sqlFile} 2>/dev/null";
            exec($command, $output, $result);
            
            // Clean up credentials file immediately
            if (file_exists($mysqlCredentialsFile)) {
                unlink($mysqlCredentialsFile);
            }
            
            // If mysqldump failed, create an empty file with note
            if (!file_exists($sqlFile) || filesize($sqlFile) === 0) {
                // Create a simple database backup note
                file_put_contents($sqlFile, "-- Database backup created at " . now() . "\n-- Note: Full database backup requires mysqldump\n");
            }
            
            // Create ZIP archive
            $zip = new \ZipArchive();
            if ($zip->open($backupFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                // Add SQL dump to ZIP
                if (file_exists($sqlFile)) {
                    $zip->addFile($sqlFile, 'database.sql');
                }
                
                // Add important files to backup
                $filesToBackup = [
                    base_path('.env') => '.env',
                    base_path('composer.json') => 'composer.json',
                ];
                
                // Add storage/app/public directory if it exists (user uploads)
                $storagePublicPath = storage_path('app/public');
                if (is_dir($storagePublicPath)) {
                    $zip->addEmptyDir('storage_public');
                    $this->addDirectoryToZip($zip, $storagePublicPath, 'storage_public');
                }
                
                foreach ($filesToBackup as $source => $destination) {
                    if (file_exists($source)) {
                        $zip->addFile($source, $destination);
                    }
                }
                
                $zip->close();
            }
            
            // Clean up temp SQL file
            if (file_exists($sqlFile)) {
                unlink($sqlFile);
            }
            
            return back()->with('success', 'Backup created successfully: ' . $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function downloadBackup($file)
    {
        // Sanitize filename to prevent path traversal attacks
        $sanitizedFile = basename($file);
        
        // Only allow .zip files
        if (pathinfo($sanitizedFile, PATHINFO_EXTENSION) !== 'zip') {
            return back()->with('error', 'Invalid file type.');
        }
        
        $backupPath = storage_path('app/backups');
        $filePath = $backupPath . '/' . $sanitizedFile;
        
        // Verify the file is within the backup directory (prevent path traversal)
        if (realpath($filePath) === false || strpos(realpath($filePath), realpath($backupPath)) !== 0) {
            return back()->with('error', 'Invalid file path.');
        }
        
        if (!file_exists($filePath)) {
            return back()->with('error', 'Backup file not found.');
        }
        
        return response()->download($filePath);
    }

    public function restoreBackup(Request $request)
    {
        // Check if ZipArchive extension is available
        if (!class_exists('ZipArchive')) {
            return back()->with('error', 'ZipArchive extension is not installed. Please enable it in your PHP configuration.');
        }

        $request->validate([
            'backup_file' => 'required|file|mimes:zip|max:102400', // Max 100MB
        ]);
        
        try {
            $uploadedFile = $request->file('backup_file');
            $backupPath = storage_path('app/backups');
            
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            $tempPath = $backupPath . '/temp_restore_' . time() . '.zip';
            $uploadedFile->move($backupPath, basename($tempPath));
            
            // Extract ZIP
            $extractPath = $backupPath . '/temp_restore_' . time();
            mkdir($extractPath, 0755, true);
            
            $zip = new \ZipArchive();
            if ($zip->open($tempPath) === TRUE) {
                $zip->extractTo($extractPath);
                $zip->close();
                
                // Look for database.sql in extracted files
                $sqlFile = $extractPath . '/database.sql';
                if (file_exists($sqlFile)) {
                    // Restore database using mysql command with credentials file for security
                    $dbHost = config('database.connections.mysql.host');
                    $dbName = config('database.connections.mysql.database');
                    $dbUser = config('database.connections.mysql.username');
                    $dbPass = config('database.connections.mysql.password');
                    
                    // Create temporary credentials file
                    $mysqlCredentialsFile = $backupPath . '/temp_my.cnf_' . time();
                    $credentialsContent = "[client]\nhost={$dbHost}\nuser={$dbUser}\npassword={$dbPass}\n";
                    file_put_contents($mysqlCredentialsFile, $credentialsContent);
                    chmod($mysqlCredentialsFile, 0600);
                    
                    // Execute SQL restore
                    $command = "mysql --defaults-file={$mysqlCredentialsFile} {$dbName} < {$sqlFile} 2>&1";
                    exec($command, $output, $result);
                    
                    // Clean up credentials file
                    if (file_exists($mysqlCredentialsFile)) {
                        unlink($mysqlCredentialsFile);
                    }
                    
                    if ($result !== 0) {
                        $this->deleteDirectory($extractPath);
                        if (file_exists($tempPath)) {
                            unlink($tempPath);
                        }
                        return back()->with('error', 'Database restore failed. Error: ' . implode("\n", $output));
                    }
                }
                
                // Restore .env file if present
                $envFile = $extractPath . '/.env';
                if (file_exists($envFile)) {
                    $currentEnvPath = base_path('.env');
                    // Backup current .env before overwriting
                    if (file_exists($currentEnvPath)) {
                        copy($currentEnvPath, $backupPath . '/.env.backup_' . time());
                    }
                    copy($envFile, $currentEnvPath);
                }
                
                // Clean up
                $this->deleteDirectory($extractPath);
            } else {
                $this->deleteDirectory($extractPath);
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
                return back()->with('error', 'Failed to open backup file. The file may be corrupted.');
            }
            
            // Clean up temp zip
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            
            // Clear config cache after restore
            $this->clearConfigCache();
            
            return back()->with('success', 'Backup restored successfully. Please verify your data and clear your browser cache.');
        } catch (\Exception $e) {
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    public function deleteBackup(Request $request)
    {
        $request->validate([
            'file' => 'required|string',
        ]);
        
        // Sanitize filename to prevent path traversal attacks
        $sanitizedFile = basename($request->file);
        
        // Only allow .zip files
        if (pathinfo($sanitizedFile, PATHINFO_EXTENSION) !== 'zip') {
            return back()->with('error', 'Invalid file type.');
        }
        
        $backupPath = storage_path('app/backups');
        $filePath = $backupPath . '/' . $sanitizedFile;
        
        // Verify the file is within the backup directory (prevent path traversal)
        if (realpath($filePath) === false || strpos(realpath($filePath), realpath($backupPath)) !== 0) {
            return back()->with('error', 'Invalid file path.');
        }
        
        if (!file_exists($filePath)) {
            return back()->with('error', 'Backup file not found.');
        }
        
        if (unlink($filePath)) {
            return back()->with('success', 'Backup deleted successfully.');
        }
        
        return back()->with('error', 'Failed to delete backup file.');
    }

    protected function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->deleteDirectory("$dir/$file") : unlink("$dir/$file");
        }
        rmdir($dir);
    }

    /**
     * Recursively add a directory to a ZIP archive.
     */
    protected function addDirectoryToZip($zip, $directory, $zipPath)
    {
        if (!is_dir($directory)) {
            return;
        }
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipPath . '/' . substr($filePath, strlen($directory) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * Social Login Settings
     */
    public function socialLogin()
    {
        $settings = Setting::where('group', 'social_login')->pluck('value', 'key');
        return view('admin.settings.social-login', compact('settings'));
    }

    /**
     * Update Social Login Settings
     */
    public function updateSocialLogin(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'google_client_id' => 'nullable|string|max:255',
            'google_client_secret' => 'nullable|string|max:255',
            'facebook_client_id' => 'nullable|string|max:255',
            'facebook_client_secret' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $settings = [
            'google_enabled' => $request->has('google_enabled') ? '1' : '0',
            'google_client_id' => $request->google_client_id ?? '',
            'google_client_secret' => $request->google_client_secret ?? '',
            'facebook_enabled' => $request->has('facebook_enabled') ? '1' : '0',
            'facebook_client_id' => $request->facebook_client_id ?? '',
            'facebook_client_secret' => $request->facebook_client_secret ?? '',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'group' => 'social_login'],
                ['value' => $value]
            );
        }

        return back()->with('success', 'Social login settings updated successfully.');
    }

    /**
     * WhatsApp Chat Settings
     */
    public function whatsapp()
    {
        $settings = Setting::where('group', 'whatsapp')->pluck('value', 'key');
        return view('admin.settings.whatsapp', compact('settings'));
    }

    /**
     * Update WhatsApp Chat Settings
     */
    public function updateWhatsapp(Request $request)
    {
        $request->validate([
            'whatsapp_phone_number' => 'required|string|max:20',
            'whatsapp_display_name' => 'nullable|string|max:100',
            'whatsapp_welcome_message' => 'nullable|string|max:500',
            'whatsapp_position' => 'nullable|in:bottom-right,bottom-left,top-right,top-left',
            'whatsapp_button_color' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'whatsapp_predefined_messages' => 'nullable|string',
        ]);

        $settings = [
            'whatsapp_enabled' => $request->has('whatsapp_enabled') ? '1' : '0',
            'whatsapp_phone_number' => $request->whatsapp_phone_number ?? '',
            'whatsapp_display_name' => $request->whatsapp_display_name ?? 'Customer Support',
            'whatsapp_welcome_message' => $request->whatsapp_welcome_message ?? 'Hello! How can I help you today?',
            'whatsapp_position' => $request->whatsapp_position ?? 'bottom-right',
            'whatsapp_button_color' => $request->whatsapp_button_color ?? '#25D366',
            'whatsapp_predefined_messages' => $request->whatsapp_predefined_messages ?? '',
            'whatsapp_show_on_mobile' => $request->has('whatsapp_show_on_mobile') ? '1' : '0',
            'whatsapp_show_on_desktop' => $request->has('whatsapp_show_on_desktop') ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'group' => 'whatsapp'],
                ['value' => $value]
            );
        }

        return back()->with('success', 'WhatsApp chat settings updated successfully.');
    }

    /**
     * Footer Settings
     */
    public function footer()
    {
        $settings = Setting::where('group', 'footer')->pluck('value', 'key');
        return view('admin.settings.footer', compact('settings'));
    }

    /**
     * Update Footer Settings
     */
    public function updateFooter(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'footer_about_text' => 'nullable|string|max:1000',
            'footer_facebook_url' => 'nullable|url',
            'footer_instagram_url' => 'nullable|url',
            'footer_youtube_url' => 'nullable|url',
            'footer_twitter_url' => 'nullable|url',
            'footer_linkedin_url' => 'nullable|url',
            'footer_address' => 'nullable|string|max:500',
            'footer_phone' => 'nullable|string|max:50',
            'footer_email' => 'nullable|email|max:255',
            'footer_business_hours' => 'nullable|string|max:255',
            'footer_copyright_text' => 'nullable|string|max:500',
            'footer_newsletter_enabled' => 'nullable|in:1',
            'footer_newsletter_title' => 'nullable|string|max:255',
            'footer_newsletter_subtitle' => 'nullable|string|max:255',
            'footer_column1_title' => 'nullable|string|max:100',
            'footer_column2_title' => 'nullable|string|max:100',
            'footer_column3_title' => 'nullable|string|max:100',
            'footer_payment_methods' => 'nullable|string|max:500',
            'footer_show_payment_icons' => 'nullable|in:1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $settings = [
                'footer_about_text' => $request->footer_about_text ?? '',
                'footer_facebook_url' => $request->footer_facebook_url ?? '',
                'footer_instagram_url' => $request->footer_instagram_url ?? '',
                'footer_youtube_url' => $request->footer_youtube_url ?? '',
                'footer_twitter_url' => $request->footer_twitter_url ?? '',
                'footer_linkedin_url' => $request->footer_linkedin_url ?? '',
                'footer_address' => $request->footer_address ?? '',
                'footer_phone' => $request->footer_phone ?? '',
                'footer_email' => $request->footer_email ?? '',
                'footer_business_hours' => $request->footer_business_hours ?? '',
                'footer_copyright_text' => $request->footer_copyright_text ?? '',
                'footer_newsletter_enabled' => $request->has('footer_newsletter_enabled') ? '1' : '0',
                'footer_newsletter_title' => $request->footer_newsletter_title ?? '',
                'footer_newsletter_subtitle' => $request->footer_newsletter_subtitle ?? '',
                'footer_column1_title' => $request->footer_column1_title ?? 'Quick Links',
                'footer_column2_title' => $request->footer_column2_title ?? 'Customer Service',
                'footer_column3_title' => $request->footer_column3_title ?? 'Contact Us',
                'footer_payment_methods' => $request->footer_payment_methods ?? 'bkash,nagad,rocket,visa,mastercard',
                'footer_show_payment_icons' => $request->has('footer_show_payment_icons') ? '1' : '0',
            ];

            foreach ($settings as $key => $value) {
                // Use updateOrCreate with just the key as unique identifier
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => 'footer']
                );
            }

            return back()->with('success', 'Footer settings updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update footer settings. Please try again.')->withInput();
        }
    }

    /**
     * Language Settings - List all languages
     */
    public function languages()
    {
        $languages = Language::orderBy('sort_order')->get();
        $frontendLanguageSwitcher = Setting::get('frontend_language_switcher', 1);
        return view('admin.settings.languages.index', compact('languages', 'frontendLanguageSwitcher'));
    }

    /**
     * Store a new language
     */
    public function storeLanguage(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:languages,code',
            'native_name' => 'nullable|string|max:100',
            'flag' => 'nullable|string|max:10',
            'is_rtl' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->except('_token');
        $data['is_rtl'] = $request->input('is_rtl') === '1' || $request->has('is_rtl');
        $data['is_default'] = $request->input('is_default') === '1' || $request->has('is_default');
        $data['is_active'] = $request->input('is_active') === '1' || $request->has('is_active');

        $language = Language::create($data);

        if ($language->is_default) {
            $language->setAsDefault();
        }

        return back()->with('success', 'Language created successfully.');
    }

    /**
     * Update an existing language
     */
    public function updateLanguage(Request $request, $id)
    {
        $language = Language::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:languages,code,' . $id,
            'native_name' => 'nullable|string|max:100',
            'flag' => 'nullable|string|max:10',
            'is_rtl' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->except('_token', '_method');
        $data['is_rtl'] = $request->input('is_rtl') === '1' || $request->has('is_rtl');
        $data['is_default'] = $request->input('is_default') === '1' || $request->has('is_default');
        $data['is_active'] = $request->input('is_active') === '1' || $request->has('is_active');
        
        $language->update($data);

        if ($language->is_default) {
            $language->setAsDefault();
        }

        return back()->with('success', 'Language updated successfully.');
    }

    /**
     * Delete a language
     */
    public function destroyLanguage($id)
    {
        $language = Language::findOrFail($id);

        // Prevent deleting the default language
        if ($language->is_default) {
            return back()->with('error', 'Cannot delete the default language.');
        }

        $language->delete();

        return back()->with('success', 'Language deleted successfully.');
    }

    /**
     * Set a language as default
     */
    public function setDefaultLanguage($id)
    {
        $language = Language::findOrFail($id);
        $language->setAsDefault();

        return back()->with('success', 'Default language updated successfully.');
    }

    /**
     * Toggle frontend language switcher visibility
     */
    public function toggleFrontendLanguageSwitcher(Request $request)
    {
        $status = $request->input('status', 1);
        Setting::set('frontend_language_switcher', $status, 'general');

        return back()->with('success', 'Language switcher ' . ($status ? 'enabled' : 'disabled') . ' successfully.');
    }

    /**
     * Currency Settings - List all currencies
     */
    public function currency()
    {
        $currencies = Currency::orderBy('sort_order')->get();
        $defaultCurrency = Currency::getDefault();
        $frontendCurrencySwitcher = Setting::get('frontend_currency_switcher', 0);
        return view('admin.settings.currency.index', compact('currencies', 'defaultCurrency', 'frontendCurrencySwitcher'));
    }

    /**
     * Store a new currency
     */
    public function storeCurrency(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:currencies,code',
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('open_add_modal', true);
        }

        $data = $request->except('_token');
        $data['is_default'] = $request->input('is_default') === '1' || $request->has('is_default');
        $data['is_active'] = $request->input('is_active') === '1' || $request->has('is_active');

        $currency = Currency::create($data);

        if ($currency->is_default) {
            $currency->setAsDefault();
            // Also set this as the app's default currency
            config(['app.currency' => $currency->code]);
            config(['app.currency_symbol' => $currency->symbol]);
            Setting::set('currency', $currency->code, 'general');
            Setting::set('currency_symbol', $currency->symbol, 'general');
        }

        return back()->with('success', 'Currency created successfully.');
    }

    /**
     * Update an existing currency
     */
    public function updateCurrency(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10|unique:currencies,code,' . $id,
            'symbol' => 'required|string|max:10',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('edit_currency_id', $id);
        }

        $data = $request->except('_token', '_method');
        $data['is_default'] = $request->input('is_default') === '1' || $request->has('is_default');
        $data['is_active'] = $request->input('is_active') === '1' || $request->has('is_active');
        
        $currency->update($data);

        if ($currency->is_default) {
            $currency->setAsDefault();
            // Also set this as the app's default currency
            config(['app.currency' => $currency->code]);
            config(['app.currency_symbol' => $currency->symbol]);
            Setting::set('currency', $currency->code, 'general');
            Setting::set('currency_symbol', $currency->symbol, 'general');
        }

        return back()->with('success', 'Currency updated successfully.');
    }

    /**
     * Delete a currency
     */
    public function destroyCurrency($id)
    {
        $currency = Currency::findOrFail($id);

        // Prevent deleting the default currency
        if ($currency->is_default) {
            return back()->with('error', 'Cannot delete the default currency.');
        }

        $currency->delete();

        return back()->with('success', 'Currency deleted successfully.');
    }

    /**
     * Set a currency as default
     */
    public function setDefaultCurrency($id)
    {
        $currency = Currency::findOrFail($id);
        $currency->setAsDefault();
        
        // Also set this as the app's default currency
        config(['app.currency' => $currency->code]);
        config(['app.currency_symbol' => $currency->symbol]);
        Setting::set('currency', $currency->code, 'general');
        Setting::set('currency_symbol', $currency->symbol, 'general');

        return back()->with('success', 'Default currency updated successfully.');
    }

    /**
     * Toggle frontend currency switcher visibility
     */
    public function toggleFrontendCurrencySwitcher(Request $request)
    {
        $status = $request->input('status', 1);
        Setting::set('frontend_currency_switcher', $status, 'general');

        return back()->with('success', 'Currency switcher ' . ($status ? 'enabled' : 'disabled') . ' successfully.');
    }

    /**
     * VAT & Tax Settings - List all taxes
     */
    public function vatTax()
    {
        $taxes = Tax::orderBy('sort_order')->get();
        $defaultTax = Tax::getDefault();
        $taxSettings = Setting::where('group', 'vat_tax')->pluck('value', 'key');
        return view('admin.settings.vat-tax', compact('taxes', 'defaultTax', 'taxSettings'));
    }

    /**
     * Store a new tax
     */
    public function storeTax(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:percentage,fixed',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->except('_token');
        $data['is_default'] = $request->input('is_default') === '1' || $request->has('is_default');
        $data['is_active'] = $request->input('is_active') === '1' || $request->has('is_active');

        $tax = Tax::create($data);

        if ($tax->is_default) {
            $tax->setAsDefault();
        }

        return back()->with('success', 'Tax created successfully.');
    }

    /**
     * Update an existing tax
     */
    public function updateTax(Request $request, $id)
    {
        $tax = Tax::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:percentage,fixed',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data = $request->except('_token', '_method');
        $data['is_default'] = $request->input('is_default') === '1' || $request->has('is_default');
        $data['is_active'] = $request->input('is_active') === '1' || $request->has('is_active');
        
        $tax->update($data);

        if ($tax->is_default) {
            $tax->setAsDefault();
        }

        return back()->with('success', 'Tax updated successfully.');
    }

    /**
     * Delete a tax
     */
    public function destroyTax($id)
    {
        $tax = Tax::findOrFail($id);

        // Prevent deleting the default tax
        if ($tax->is_default) {
            return back()->with('error', 'Cannot delete the default tax. Set another tax as default first.');
        }

        $tax->delete();

        return back()->with('success', 'Tax deleted successfully.');
    }

    /**
     * Set a tax as default
     */
    public function setDefaultTax($id)
    {
        $tax = Tax::findOrFail($id);
        $tax->setAsDefault();

        return back()->with('success', 'Default tax updated successfully.');
    }

    /**
     * Update VAT & Tax settings
     */
    public function updateVatTax(Request $request)
    {
        $request->validate([
            'tax_type' => 'nullable|in:global,location',
            'tax_calculation_address' => 'nullable|in:shipping,billing',
        ]);

        $settings = [
            'tax_enabled' => $request->has('tax_enabled') ? '1' : '0',
            'tax_type' => $request->tax_type ?? 'global',
            'tax_per_product' => $request->has('tax_per_product') ? '1' : '0',
            'tax_calculation_address' => $request->tax_calculation_address ?? 'shipping',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'group' => 'vat_tax'],
                ['value' => $value]
            );
        }

        return back()->with('success', 'VAT & Tax settings updated successfully.');
    }

    /**
     * Order Configuration Settings
     */
    public function orderConfiguration()
    {
        $settings = Setting::where('group', 'order_configuration')->pluck('value', 'key');
        return view('admin.settings.order-configuration', compact('settings'));
    }

    /**
     * Update Order Configuration Settings
     */
    public function updateOrderConfiguration(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'order_prefix' => 'nullable|string|max:10',
            'order_suffix' => 'nullable|string|max:10',
            'order_number_length' => 'nullable|integer|min:4|max:16',
            'order_number_format' => 'nullable|in:random,sequential,date',
            
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_order_amount' => 'nullable|numeric|min:0',
            'min_order_quantity' => 'nullable|integer|min:1',
            'max_order_quantity' => 'nullable|integer|min:1',
            
            'order_validity_hours' => 'nullable|integer|min:1',
            'auto_cancel_unpaid_hours' => 'nullable|integer|min:0',
            'auto_complete_delivered_days' => 'nullable|integer|min:0',
            
            'new_order_status' => 'nullable|in:pending,confirmed,processing',
            'confirm_order_status' => 'nullable|in:pending,confirmed,processing',
            'processing_order_status' => 'nullable|in:confirmed,processing,shipped',
            'shipped_order_status' => 'nullable|in:processing,shipped,delivered',
            'delivered_order_status' => 'nullable|in:delivered,completed',
            'cancelled_order_status' => 'nullable|in:cancelled,refunded',
            'returned_order_status' => 'nullable|in:returned,refunded',
            
            'invoice_prefix' => 'nullable|string|max:10',
            'invoice_terms' => 'nullable|string',
            
            'digital_product_validity_days' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $settings = [
            // Order Number Settings
            'order_prefix' => $request->order_prefix ?? 'ORD',
            'order_suffix' => $request->order_suffix ?? '',
            'order_number_length' => $request->order_number_length ?? 8,
            'order_number_format' => $request->order_number_format ?? 'random',
            
            // Order Limits
            'min_order_amount' => $request->min_order_amount ?? 0,
            'max_order_amount' => $request->max_order_amount ?? 0,
            'min_order_quantity' => $request->min_order_quantity ?? 1,
            'max_order_quantity' => $request->max_order_quantity ?? 99,
            
            // Order Processing
            'order_validity_hours' => $request->order_validity_hours ?? 72,
            'auto_cancel_unpaid_hours' => $request->auto_cancel_unpaid_hours ?? 0,
            'auto_complete_delivered_days' => $request->auto_complete_delivered_days ?? 0,
            
            // Order Status Settings
            'new_order_status' => $request->new_order_status ?? 'pending',
            'confirm_order_status' => $request->confirm_order_status ?? 'confirmed',
            'processing_order_status' => $request->processing_order_status ?? 'processing',
            'shipped_order_status' => $request->shipped_order_status ?? 'shipped',
            'delivered_order_status' => $request->delivered_order_status ?? 'delivered',
            'cancelled_order_status' => $request->cancelled_order_status ?? 'cancelled',
            'returned_order_status' => $request->returned_order_status ?? 'returned',
            
            // Invoice Settings
            'invoice_prefix' => $request->invoice_prefix ?? 'INV',
            'show_invoice_logo' => $request->has('show_invoice_logo') ? '1' : '0',
            'show_invoice_barcode' => $request->has('show_invoice_barcode') ? '1' : '0',
            'invoice_terms' => $request->invoice_terms ?? '',
            
            // Order Notifications
            'notify_admin_on_new_order' => $request->has('notify_admin_on_new_order') ? '1' : '0',
            'notify_customer_on_status_change' => $request->has('notify_customer_on_status_change') ? '1' : '0',
            
            // Guest Checkout
            'guest_checkout_enabled' => $request->has('guest_checkout_enabled') ? '1' : '0',
            
            // Order Review
            'allow_order_review' => $request->has('allow_order_review') ? '1' : '0',
            'review_required_for_completion' => $request->has('review_required_for_completion') ? '1' : '0',
            
            // Digital Product Settings
            'digital_product_auto_deliver' => $request->has('digital_product_auto_deliver') ? '1' : '0',
            'digital_product_validity_days' => $request->digital_product_validity_days ?? 30,

            // Checkout Localization
            'checkout_mode' => $request->checkout_mode ?? 'local',
            'default_country' => $request->default_country ?? 'Bangladesh',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'order_configuration']
            );
        }

        return back()->with('success', 'Order configuration updated successfully.');
    }

    /**
     * File System & Cache Settings
     */
    public function fileSystem()
    {
        $settings = Setting::where('group', 'file_system')->pluck('value', 'key');
        return view('admin.settings.file-system', compact('settings'));
    }

    /**
     * Clear Cache
     */
    public function clearCache(Request $request)
    {
        try {
            // Clear application cache
            \Artisan::call('cache:clear');
            
            // Clear view cache
            \Artisan::call('view:clear');
            
            // Clear config cache
            \Artisan::call('config:clear');
            
            // Clear route cache
            \Artisan::call('route:clear');
            
            return back()->with('success', 'Cache cleared successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Update File System & Cache Settings
     */
    public function updateFileSystem(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            // File Upload Settings
            'max_upload_size' => 'nullable|integer|min:100|max:102400',
            'allowed_file_types' => 'nullable|string|max:500',
            'max_image_width' => 'nullable|integer|min:100|max:10000',
            'max_image_height' => 'nullable|integer|min:100|max:10000',
            
            // Image Settings
            'image_quality' => 'nullable|integer|min:10|max:100',
            'thumbnail_width' => 'nullable|integer|min:50|max:500',
            'thumbnail_height' => 'nullable|integer|min:50|max:500',
            'watermark_position' => 'nullable|in:center,top-left,top-right,bottom-left,bottom-right',
            
            // Cache Settings
            'cache_driver' => 'nullable|in:file,redis,memcached,array',
            'cache_ttl' => 'nullable|integer|min:60|max:86400',
            'query_cache_ttl' => 'nullable|integer|min:60|max:3600',
            
            // Storage Settings
            'storage_disk' => 'nullable|in:public,local,s3',
            'cloud_driver' => 'nullable|in:s3,digitalocean,wasabi',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $settings = [
            // File Upload Settings
            'max_upload_size' => $request->max_upload_size ?? 5120,
            'allowed_file_types' => $request->allowed_file_types ?? 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,mp4,mp3',
            'max_image_width' => $request->max_image_width ?? 2000,
            'max_image_height' => $request->max_image_height ?? 2000,
            
            // Image Settings
            'image_quality' => $request->image_quality ?? 85,
            'thumbnail_enabled' => $request->has('thumbnail_enabled') ? '1' : '0',
            'thumbnail_width' => $request->thumbnail_width ?? 150,
            'thumbnail_height' => $request->thumbnail_height ?? 150,
            'watermark_enabled' => $request->has('watermark_enabled') ? '1' : '0',
            'watermark_position' => $request->watermark_position ?? 'bottom-right',
            
            // Cache Settings
            'cache_driver' => $request->cache_driver ?? 'file',
            'cache_ttl' => $request->cache_ttl ?? 3600,
            'enable_query_cache' => $request->has('enable_query_cache') ? '1' : '0',
            'query_cache_ttl' => $request->query_cache_ttl ?? 300,
            
            // Storage Settings
            'storage_disk' => $request->storage_disk ?? 'public',
            'enable_cloud_storage' => $request->has('enable_cloud_storage') ? '1' : '0',
            'cloud_driver' => $request->cloud_driver ?? 's3',
            
            // Optimization
            'lazy_load_images' => $request->has('lazy_load_images') ? '1' : '0',
            'optimize_images' => $request->has('optimize_images') ? '1' : '0',
            'enable_static_cache' => $request->has('enable_static_cache') ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'file_system']
            );
        }

        return back()->with('success', 'File System & Cache settings updated successfully.');
    }

    /**
     * API: Get File System Settings (for frontend)
     */
    public function getFileSystemSettingsApi()
    {
        $settings = Setting::getFileSystemSettings();
        
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Shipping Settings
     */
    public function shipping()
    {
        $settings = Setting::where('group', 'shipping')->pluck('value', 'key');
        return view('admin.settings.shipping', compact('settings'));
    }

    /**
     * Update Shipping Settings
     */
    public function updateShipping(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'free_shipping_enabled' => 'nullable|boolean',
            'free_shipping_min_amount' => 'nullable|numeric|min:0',
            'shipping_calculation_type' => 'nullable|in:flat,weight,price,location',
            'default_shipping_cost' => 'nullable|numeric|min:0',
            'shipping_by_weight' => 'nullable|boolean',
            'shipping_by_price' => 'nullable|boolean',
            'enable_shipping_estimate' => 'nullable|boolean',
            'local_pickup_enabled' => 'nullable|boolean',
            'local_pickup_cost' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $settings = [
            'free_shipping_enabled' => $request->has('free_shipping_enabled') ? '1' : '0',
            'free_shipping_min_amount' => $request->free_shipping_min_amount ?? 0,
            'shipping_calculation_type' => $request->shipping_calculation_type ?? 'flat',
            'default_shipping_cost' => $request->default_shipping_cost ?? 0,
            'shipping_by_weight' => $request->has('shipping_by_weight') ? '1' : '0',
            'shipping_by_price' => $request->has('shipping_by_price') ? '1' : '0',
            'enable_shipping_estimate' => $request->has('enable_shipping_estimate') ? '1' : '0',
            'local_pickup_enabled' => $request->has('local_pickup_enabled') ? '1' : '0',
            'local_pickup_cost' => $request->local_pickup_cost ?? 0,
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => 'shipping']
            );
        }

        return back()->with('success', 'Shipping settings updated successfully.');
    }

    /**
     * API: Get SEO Settings (for frontend)
     */
    public function getSeoSettingsApi()
    {
        $settings = Setting::whereIn('key', [
            'site_meta_title',
            'site_meta_description',
            'site_meta_keywords',
            'google_analytics_id',
            'google_search_console',
            'facebook_pixel_id',
            'og_title',
            'og_description',
            'og_image',
            'twitter_card_type',
        ])->pluck('value', 'key');
        
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Notification Settings - Admin Panel
     */
    public function notifications()
    {
        $settings = Setting::where('group', 'notifications')->pluck('value', 'key');
        return view('admin.settings.notifications', compact('settings'));
    }

    /**
     * Update Notification Settings - Admin Panel
     */
    public function updateNotifications(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'admin_phone_for_sms' => 'nullable|string|max:20|regex:/^\+?[0-9\s\-]+$/',
            'low_stock_threshold' => 'nullable|integer|min:1|max:9999',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $settings = [
            // Email Notifications - Admin
            'notify_admin_new_order' => $request->has('notify_admin_new_order') ? '1' : '0',
            'notify_admin_new_refund' => $request->has('notify_admin_new_refund') ? '1' : '0',
            'notify_admin_new_customer' => $request->has('notify_admin_new_customer') ? '1' : '0',
            'notify_admin_new_seller' => $request->has('notify_admin_new_seller') ? '1' : '0',
            'notify_admin_low_stock' => $request->has('notify_admin_low_stock') ? '1' : '0',
            'notify_admin_out_of_stock' => $request->has('notify_admin_out_of_stock') ? '1' : '0',
            'notify_admin_new_review' => $request->has('notify_admin_new_review') ? '1' : '0',
            'notify_admin_new_support_ticket' => $request->has('notify_admin_new_support_ticket') ? '1' : '0',
            
            // Email Notifications - Customer
            'notify_customer_order_placed' => $request->has('notify_customer_order_placed') ? '1' : '0',
            'notify_customer_order_confirmed' => $request->has('notify_customer_order_confirmed') ? '1' : '0',
            'notify_customer_order_processing' => $request->has('notify_customer_order_processing') ? '1' : '0',
            'notify_customer_order_shipped' => $request->has('notify_customer_order_shipped') ? '1' : '0',
            'notify_customer_order_delivered' => $request->has('notify_customer_order_delivered') ? '1' : '0',
            'notify_customer_order_cancelled' => $request->has('notify_customer_order_cancelled') ? '1' : '0',
            'notify_customer_refund_approved' => $request->has('notify_customer_refund_approved') ? '1' : '0',
            'notify_customer_refund_rejected' => $request->has('notify_customer_refund_rejected') ? '1' : '0',
            'notify_customer_new_message' => $request->has('notify_customer_new_message') ? '1' : '0',
            'notify_customer_promo' => $request->has('notify_customer_promo') ? '1' : '0',
            
            // SMS Notifications - Admin
            'sms_notify_admin_new_order' => $request->has('sms_notify_admin_new_order') ? '1' : '0',
            'sms_notify_admin_new_refund' => $request->has('sms_notify_admin_new_refund') ? '1' : '0',
            'sms_notify_admin_low_stock' => $request->has('sms_notify_admin_low_stock') ? '1' : '0',
            
            // SMS Notifications - Customer
            'sms_notify_customer_order_status' => $request->has('sms_notify_customer_order_status') ? '1' : '0',
            'sms_notify_customer_delivery' => $request->has('sms_notify_customer_delivery') ? '1' : '0',
            'sms_notify_customer_otp' => $request->has('sms_notify_customer_otp') ? '1' : '0',
            
            // Push Notifications
            'push_notify_customer_order' => $request->has('push_notify_customer_order') ? '1' : '0',
            'push_notify_customer_promo' => $request->has('push_notify_customer_promo') ? '1' : '0',
            'push_notify_customer_new_product' => $request->has('push_notify_customer_new_product') ? '1' : '0',
            
            // Notification Sound
            'notification_sound_enabled' => $request->has('notification_sound_enabled') ? '1' : '0',
            
            // Low Stock Threshold
            'low_stock_threshold' => $request->low_stock_threshold ?? 10,
            
            // Admin Phone for SMS Notifications
            'admin_phone_for_sms' => $request->admin_phone_for_sms ?? '',
        ];

        try {
            foreach ($settings as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'group' => 'notifications']
                );
            }

            return back()->with('success', 'Notification settings updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update notification settings. Please try again.');
        }
    }

    /**
     * API: Get Notification Settings (for frontend)
     */
    public function getNotificationSettingsApi()
    {
        $settings = Setting::where('group', 'notifications')->pluck('value', 'key');
        
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }
}
