<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Folder;
use App\Models\Resource;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupResources extends Command
{
    protected $signature = 'cleanup:all';
    protected $description = 'Delete all resources, folders, categories and tags';

    public function handle()
    {
        if (!$this->confirm('This will DELETE all resources, folders, categories and tags. Continue?')) {
            return Command::SUCCESS;
        }

        $this->info('Deleting SCORM files...');
        $dirs = Storage::disk('public')->directories('scorm');
        foreach ($dirs as $dir) {
            Storage::disk('public')->deleteDirectory($dir);
        }
        $this->line('  Deleted ' . count($dirs) . ' SCORM directories');

        $this->info('Deleting resource files...');
        $dirs = Storage::disk('public')->directories('resources');
        foreach ($dirs as $dir) {
            Storage::disk('public')->deleteDirectory($dir);
        }
        $this->line('  Deleted ' . count($dirs) . ' resource directories');

        $this->info('Force deleting all resources...');
        $count = Resource::withTrashed()->count();
        Resource::withTrashed()->each(function ($r) {
            $r->categories()->detach();
            $r->tags()->detach();
            $r->forceDelete();
        });
        $this->line("  Deleted $count resources");

        $this->info('Deleting all folders...');
        $count = Folder::count();
        Folder::query()->delete();
        $this->line("  Deleted $count folders");

        $this->info('Deleting all categories...');
        $count = Category::count();
        Category::query()->delete();
        $this->line("  Deleted $count categories");

        $this->info('Deleting all tags...');
        $count = Tag::count();
        Tag::query()->delete();
        $this->line("  Deleted $count tags");

        $this->info('Done. All data cleaned.');

        return Command::SUCCESS;
    }
}
