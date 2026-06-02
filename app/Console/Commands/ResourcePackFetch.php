<?php

namespace App\Console\Commands;

use App\Services\ResourcePacks\InstallResourcePack;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Throwable;

class ResourcePackFetch extends Command implements PromptsForMissingInput
{
    protected $signature = 'resourcepack:fetch
                            {name : Filename of resource pack located on the melkypie/resource-packs repo}';

    protected $description = 'Download a RuneLite resource pack and install it into public/resource-packs/{name}/.';

    /**
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => 'What is the name of the resource pack?',
        ];
    }

    public function handle(InstallResourcePack $installer): int
    {
        $name = $this->argument('name');

        $this->info(sprintf("Installing '%s'...", $name));

        try {
            $pack = $installer->install($name);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return CommandAlias::FAILURE;
        }

        $this->info(sprintf("Resource pack '%s' is installed at /resource-packs/%s/.", $pack->alias, $pack->name));

        return CommandAlias::SUCCESS;
    }
}
