<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class InstallCommand extends Command
{
    protected $signature = 'fewchurs:install {--force : Overwrite an existing config file}';

    protected $description = 'Publish the Fewchurs config and print the next steps.';

    public function handle(): int
    {
        $this->callSilent('vendor:publish', array_filter([
            '--tag' => 'fewchurs-config',
            '--force' => $this->option('force') ?: null,
        ]));

        $this->components->info('Published config/fewchurs.php');
        $this->addEnvPlaceholder();

        $this->newLine();
        $this->line('  Next:');
        $this->line('  1. Put your key in .env:      <fg=gray>FEWCHURS_API_KEY=fr_live_…</>');
        $this->line('  2. Drop the board in a view:  <fg=gray><x-fewchurs::board /></>');
        $this->line('  3. Or visit the ready route:  <fg=gray>/'.config('fewchurs.route.prefix', 'feedback').'</>');
        $this->line('  4. Verify it:                 <fg=gray>php artisan fewchurs:check</>');
        $this->newLine();

        return self::SUCCESS;
    }

    private function addEnvPlaceholder(): void
    {
        $path = base_path('.env');

        if (! File::exists($path) || str_contains((string) File::get($path), 'FEWCHURS_API_KEY')) {
            return;
        }

        File::append($path, PHP_EOL.'FEWCHURS_API_KEY='.PHP_EOL);
        $this->components->info('Added FEWCHURS_API_KEY to .env');
    }
}
