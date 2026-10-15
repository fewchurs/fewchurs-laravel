<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Console;

use Fewchurs\Laravel\Exceptions\FewchursException;
use Fewchurs\Laravel\Exceptions\InvalidApiKeyException;
use Fewchurs\Laravel\Facades\Fewchurs;
use Fewchurs\Laravel\Support\Redactor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class CheckCommand extends Command
{
    protected $signature = 'fewchurs:check {--board= : Check a named board from fewchurs.apps}';

    protected $description = 'Verify the Fewchurs API key, connectivity and plan.';

    public function handle(): int
    {
        $board = is_string($this->option('board')) ? $this->option('board') : null;
        $key = (string) config($board ? "fewchurs.apps.{$board}.api_key" : 'fewchurs.api_key');

        if (trim($key) === '') {
            $this->components->error('FEWCHURS_API_KEY missing — add it to .env');

            return self::FAILURE;
        }

        $this->components->info('API key found: '.Redactor::mask($key));

        try {
            $config = Fewchurs::app($board)->configResource()->fresh();
        } catch (InvalidApiKeyException) {
            $this->components->error('API key rejected — check the key for this app in your Fewchurs dashboard.');

            return self::FAILURE;
        } catch (FewchursException $e) {
            $this->components->error('Board unreachable: '.$e->errorCode);

            return self::FAILURE;
        }

        $this->components->info('Board reachable at '.config('fewchurs.base_url'));
        $this->components->info('Branding badge: '.($config->showBranding ? 'shown (Free plan)' : 'hidden (Starter or Pro plan)'));

        return $this->auditLeaks();
    }

    private function auditLeaks(): int
    {
        $leaks = collect(['.env.example', '.env.dist', 'config/fewchurs.php'])
            ->filter(fn (string $file) => File::exists(base_path($file))
                && preg_match('/fr_(live|test)_/', (string) File::get(base_path($file))) === 1);

        if ($leaks->isNotEmpty()) {
            $this->components->error('A live-looking key appears in: '.$leaks->implode(', ').' — rotate it and use .env only.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
