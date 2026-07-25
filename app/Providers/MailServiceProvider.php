<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;

class MailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load SMTP settings from database and override config
        $this->loadMailSettings();
    }
    
    protected function loadMailSettings()
    {
        // Only run in production or when config is not cached
        if (env('APP_ENV') === 'production' && file_exists(base_path('bootstrap/cache/config.php'))) {
            return;
        }
        
        try {
            $settings = Setting::where('key', 'like', 'mail_%')->pluck('value', 'key');
            
            if ($settings->isNotEmpty()) {
                // Override mail configuration with database settings
                if (isset($settings['mail_mailer'])) {
                    config(['mail.default' => $settings['mail_mailer']]);
                }
                if (isset($settings['mail_host'])) {
                    config(['mail.mailers.smtp.host' => $settings['mail_host']]);
                }
                if (isset($settings['mail_port'])) {
                    config(['mail.mailers.smtp.port' => $settings['mail_port']]);
                }
                if (isset($settings['mail_username'])) {
                    config(['mail.mailers.smtp.username' => $settings['mail_username']]);
                }
                if (isset($settings['mail_password'])) {
                    config(['mail.mailers.smtp.password' => $settings['mail_password']]);
                }
                if (isset($settings['mail_encryption'])) {
                    config(['mail.mailers.smtp.encryption' => $settings['mail_encryption']]);
                }
                if (isset($settings['mail_from_address'])) {
                    config(['mail.from.address' => $settings['mail_from_address']]);
                }
                if (isset($settings['mail_from_name'])) {
                    config(['mail.from.name' => $settings['mail_from_name']]);
                }
            }
        } catch (\Exception $e) {
            // Silently fail if database is not ready
        }
    }
}
