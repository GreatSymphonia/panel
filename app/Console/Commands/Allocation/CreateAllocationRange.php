<?php

namespace Pterodactyl\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Filesystem\Filesystem;
use Pterodactyl\Console\Kernel;
use Pterodactyl\Models\Node;
use Pterodactyl\Services\Allocations\AssignmentService;

class CreateAllocationRange extends Command
{

    public function __construct(
        private AssignmentService $assignmentService
    )
    {
        parent::__construct();
    }

    protected $signature = 'p:Allocation:New
        {--node= : The ID of the node}
        {--ip= : The IP for the allocation}
        {--port= : The port (or port range) for the allocation}
    ';


    protected $description = 'Creates a new allocation for the selected node with the ip and port specified';


    public function handle(): void
    {
        $nodeId = $this->option('node') ?? $this->ask('What is the ID of the node?');
        $allocationIp = $this->option('ip') ?? $this->ask('What is the IP for the allocation?');
        $port = $this->option('port') ?? $this->ask('What is the port (or port range) for the allocation?');

        try {
            $node = Node::query()->where('id', $nodeId)->firstOrFail();
        } catch (\Throwable $th) {
            throw $th;
        }

        try {
            $explode = explode('-', $port);
            if (count($explode) === 2) {
                if ($explode[0] > $explode[1]) {
                    throw new \Exception('Invalid port range provided.');
                }
                $range = range($explode[0], $explode[1]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }

        $this->assignmentService->handle($node, [
            'allocation_ip' => $allocationIp,
            'allocation_ports' => $range,
        ]);

        $this->info('Allocation created successfully.');
        $this->info('Node: ' . $node->name);
        $this->info('IP: ' . $allocationIp);
        $this->info('Port: ' . $port);
    }
}
