<?php

namespace Pterodactyl\Console\Commands;

use finfo;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Filesystem\Filesystem;
use Pterodactyl\Console\Kernel;
use Pterodactyl\Services\Eggs\Sharing\EggImporterService;

class CreateEggCommand extends Command
{

    public function __construct(
        private EggImporterService $eggImporterService
    )
    {
        parent::__construct();
    }

    protected $signature = 'p:Egg:New
        {--nest-id= : The ID of the nest}
        {--file-path= : The path to the egg file}
    ';


    protected $description = 'Command description';


    public function handle(): void
    {
        $nestId = $this->option('nest-id') ?? $this->ask('What is the ID of the nest?');
        $filePath = $this->option('file-path') ?? $this->ask('What is the path to the egg file?');

        if (!file_exists($filePath)) {
            $this->error('The file does not exist.');
            return;
        }

        $filesystem = new Filesystem();

        $name = $filesystem->name($filePath);
        $extension = $filesystem->extension($filePath);
        $originalName = $name . '.' . $extension;
        $mimeType = $filesystem->mimeType($filePath);
        $error = null;

        
        $fileName = basename($filePath);
        $fInfo = finfo_open(FILEINFO_MIME_TYPE);

        $outputResult = $this->eggImporterService->handle(new UploadedFile($filePath, $originalName, $mimeType, $error, true) , $nestId);

        $this->table(['Name', 'Description'], [
            ['Name', $outputResult->name],
            ['Description', $outputResult->description],
            ['Author', $outputResult->author],
            ['UUID', $outputResult->uuid],
        ]);
    }
}
