<?php

namespace App\Console\Commands;

use App\Services\RickAndMortySyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncRickAndMortyCommand extends Command
{
    protected $signature = 'sync:rick-and-morty';

    protected $description = 'Synchronize data from the Rick and Morty API';

    public function __construct(
        private readonly RickAndMortySyncService $syncService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $this->info('Synchronizing locations...');
            $this->syncService->syncLocations();

            $this->info('Synchronizing episodes...');
            $this->syncService->syncEpisodes();

            $this->info('Synchronizing characters...');
            $this->syncService->syncCharacters();

            $this->info('Synchronization completed successfully.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Synchronization failed.');
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}