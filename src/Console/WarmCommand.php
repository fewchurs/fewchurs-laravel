<?php

declare(strict_types=1);

namespace Fewchurs\Laravel\Console;

use Fewchurs\Laravel\Exceptions\FewchursException;
use Fewchurs\Laravel\Facades\Fewchurs;
use Illuminate\Console\Command;

final class WarmCommand extends Command
{
    protected $signature = 'fewchurs:warm {--board= : Warm a named board from fewchurs.apps}';

    protected $description = 'Pre-warm the Fewchurs config cache after a deploy.';

    /**
     * Only /config is warmed: feature pages are cached per visitor, so there is no
     * shared entry a deploy could fill.
     */
    public function handle(): int
    {
        try {
            $config = Fewchurs::app(is_string($board = $this->option('board')) ? $board : null)->configResource()->fresh();
        } catch (FewchursException $e) {
            $this->components->error('Could not warm the Fewchurs cache: '.$e->errorCode);

            return self::FAILURE;
        }

        $this->components->info('Config cached (branding '.($config->showBranding ? 'on' : 'off').').');

        return self::SUCCESS;
    }
}
