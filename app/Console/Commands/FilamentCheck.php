<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FilamentCheck extends Command
{
    protected $signature = 'filament:check';

    protected $description = 'Validate application Filament classes, traits, and interfaces can be autoloaded.';

    public function handle(): int
    {
        $paths = [
            app_path('Filament/Resources') => 'App\\Filament\\Resources',
            app_path('Filament/Pages') => 'App\\Filament\\Pages',
            app_path('Filament/Widgets') => 'App\\Filament\\Widgets',
        ];

        $checked = 0;

        foreach ($paths as $path => $namespace) {
            if (! File::isDirectory($path)) {
                continue;
            }

            foreach (File::allFiles($path) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $relative = str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
                $class = $namespace.'\\'.$relative;

                if (! class_exists($class) && ! trait_exists($class) && ! interface_exists($class)) {
                    $this->error("Filament class cannot be loaded: {$class}");

                    return self::FAILURE;
                }

                $checked++;
            }
        }

        $this->info("Filament check passed. {$checked} classes, traits, and interfaces loaded.");

        return self::SUCCESS;
    }
}
