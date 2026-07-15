<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckScormStorage extends Command
{
    protected $signature = 'scorm:check';
    protected $description = 'Check SCORM storage and extraction';

    public function handle()
    {
        $paths = [
            'storage_path("app/public")' => storage_path('app/public'),
            'storage_path("app/public/scorm")' => storage_path('app/public/scorm'),
            'public_path("storage")' => public_path('storage'),
        ];

        $this->info('Paths:');
        foreach ($paths as $label => $path) {
            $exists = file_exists($path);
            $isDir = is_dir($path);
            $isLink = is_link($path);
            $this->line("  $label => $path");
            $this->line("    exists: " . ($exists ? 'YES' : 'NO') . ", is_dir: " . ($isDir ? 'YES' : 'NO') . ", is_link: " . ($isLink ? 'YES' : 'NO'));
        }

        $this->info('Extensions:');
        $this->line('  zip: ' . (extension_loaded('zip') ? 'YES' : 'NO'));

        // List SCORM directories
        $scormDir = storage_path('app/public/scorm');
        if (is_dir($scormDir)) {
            $this->info('SCORM directories:');
            $dirs = array_diff(scandir($scormDir), ['.', '..']);
            foreach ($dirs as $dir) {
                $this->line("  $dir/");
            }
        }

        return Command::SUCCESS;
    }
}
