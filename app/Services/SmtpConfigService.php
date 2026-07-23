<?php

namespace App\Services;

use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Config;

class SmtpConfigService
{
    public static function applyConfig(): bool
    {
        $setting = SmtpSetting::where('is_active', true)->first();
        if (!$setting) {
            return false;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', $setting->host);
        Config::set('mail.mailers.smtp.port', $setting->port);
        Config::set('mail.mailers.smtp.username', $setting->username);
        Config::set('mail.mailers.smtp.password', $setting->password);
        
        $scheme = match(strtolower($setting->encryption ?? 'tls')) {
            'ssl' => 'ssl',
            'tls' => 'tls',
            default => null,
        };

        Config::set('mail.mailers.smtp.scheme', $scheme);
        Config::set('mail.from.address', $setting->from_email);
        Config::set('mail.from.name', $setting->from_name);

        return true;
    }
}
