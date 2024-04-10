<?php

namespace Pterodactyl\Console\Commands\Server;

use Illuminate\Console\Command;
use Pterodactyl\Console\Kernel;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Allocation;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Egg;
use Pterodactyl\Services\Servers\ServerCreationService;

class CreateNewCSServer extends Command
{

    public function __construct(
        private ServerCreationService $serverCreationService
    )
    {
        parent::__construct();
    }

    protected $signature = 'p:server:new
        {--nodeId= : The ID of the node}
        {--allocationId= : The ID of the network allocation}
        {--nestId= : The ID of the nest}
        {--eggId= : The ID of the egg}
        {--name= : The name of the server}
        {--description= : The description of the server}
        {--owner= : The ID of the server owner}
        {--memory= : The amount of memory the server should have}
        {--swap= : The amount of swap the server should have}
        {--disk= : The amount of disk space the server should have}
        {--io= : The IO performance level the server should have}
        {--cpu= : The CPU limit the server should have}
        {--threads= : The pinned threads to this server}
        {--oomKiller : Whether the server should have the OOM killer enabled}
        {--startup= : The startup command for the server}
        {--image= : The Docker image to use for the server}
        {--databaseLimit= : The maximum number of databases the server can create}
        {--allocationLimit= : The maximum number of allocations the server can create}
        {--backupLimit= : The maximum number of backups the server can create}
        {--skipScripts : Skip running the install and startup scripts}
        {--appToken= : The steam token to use for the application}
    ';


    protected $description = 'Creates a new CS2 server for the selected node with the specified parameters';


    public function handle(): void
    {
        $nodeId          = $this->option('nodeId')          ?? $this->ask('What is the ID of the node?');
        $allocationId    = $this->option('allocationId')    ?? $this->ask('What is the ID of the network allocation?');
        $nestId          = $this->option('nestId')          ?? $this->ask('What is the ID of the nest?');
        $eggId           = $this->option('eggId')           ?? $this->ask('What is the ID of the egg?');
        $name            = $this->option('name')            ?? $this->ask('What is the name of the server?');
        $description     = $this->option('description')     ?? $this->ask('What is the description of the server?');
        $owner           = $this->option('owner')           ?? $this->ask('What is the ID of the owner of this server?');
        $memory          = $this->option('memory')          ?? $this->ask('How much memory should the server have?');
        $swap            = $this->option('swap')            ?? 0;
        $disk            = $this->option('disk')            ?? $this->ask('How much disk space should the server have? (MiB)');
        $io              = $this->option('io')              ?? 500;
        $cpu             = $this->option('cpu')             ?? $this->ask('What CPU limit (%) should the server have?');
        $threads         = $this->option('threads')         ?? "";
        $oomKiller       = $this->option('oomKiller');
        $startup         = $this->option('startup')         ?? "./srcds_run -game csgo -console -port {{SERVER_PORT}} +ip 0.0.0.0 +map {{SRCDS_MAP}} -strictportbind -norestart +sv_setsteamaccount {{STEAM_ACC}}";
        $image           = $this->option('image')           ?? "ghcr.io/pterodactyl/games:source";
        $databaseLimit   = $this->option('databaseLimit')   ?? 0;
        $allocationLimit = $this->option('allocationLimit') ?? 0;
        $backupLimit     = $this->option('backupLimit')     ?? 0;
        $skipScripts     = $this->option('skipScripts');

        $appToken        = $this->option('appToken')        ?? $this->ask('What is the steam token to use for the application?');

        $environment = [
            'SRCDS_MAP' => 'de_dust2',
            'STEAM_ACC' => $appToken,
            'SRCDS_APPID' => '740',
        ];


        $outputResult = $this->serverCreationService->handle([
            'node_id'          => $nodeId,
            'allocation_id'    => $allocationId,
            'nest_id'          => $nestId,
            'egg_id'           => $eggId,
            'name'             => $name,
            'description'      => $description,
            'owner_id'         => $owner,
            'memory'           => $memory,
            'swap'             => $swap,
            'disk'             => $disk,
            'io'               => $io,
            'cpu'              => $cpu,
            'threads'          => $threads,
            'oom_killer'       => $oomKiller,
            'startup'          => $startup,
            'image'            => $image,
            'database_limit'   => $databaseLimit,
            'allocation_limit' => $allocationLimit,
            'backup_limit'     => $backupLimit,
            'skip_scripts'     => $skipScripts,
            'environment'      => $environment,
        ]);

        $this->info('Server created successfully.');
        $this->table(['Name', 'Description'], [
            ['Name', $outputResult->name],
            ['Description', $outputResult->description],
            ['Owner', $outputResult->owner_id],
            ['UUID', $outputResult->uuid],
        ]);

    }
}
