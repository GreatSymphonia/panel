<?php

namespace Pterodactyl\Console\Commands;

use Illuminate\Console\Command;
use Pterodactyl\Models\Nest;
use Pterodactyl\Services\Nests\NestCreationService;

class CreateNestCommand extends Command
{
    /**
     * @var \Pterodactyl\Services\Nests\NestCreationService
     */
    private $creationService;


    private function __construct(
        private NestCreationService $nestCreationService
    )
    {
        $this->creationService = $nestCreationService;
    }

    protected $signature = 'p:Nest:New
        {--name= : The name of the nest}
        {--description= : The description of the nest}
        {--author= : The author of the nest}
    ';

    protected $description = 'Creates a new nest';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->option('name') ?? $this->ask('What is the name of the nest?');
        $description = $this->option('description') ?? $this->ask('What is the description of the nest?');
        $author = $this->option('author') ?? $this->ask('Who is the author of the nest?');

        $outputResult = $this->creationService->handle([
            'name' => $name,
            'description' => $description,
            'author' => $author ?? 'Pterodactyl Panel',
        ]);

        $this->table(['Name', 'Description'], [
            ['Name', $outputResult->name],
            ['Description', $outputResult->description],
            ['Author', $outputResult->author],
            ['UUID', $outputResult->uuid],
        ]);
    }
}
