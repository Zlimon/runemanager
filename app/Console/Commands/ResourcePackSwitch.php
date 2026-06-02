<?php

namespace App\Console\Commands;

use App\Helpers\SettingHelper;
use App\Models\ResourcePack;
use App\Services\ResourcePacks\InstallResourcePack;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Symfony\Component\Console\Command\Command as CommandAlias;

use function Laravel\Prompts\search;

/**
 * Sets the instance-wide default resource pack (settings.resource_pack_id).
 *
 * Per-user packs are managed through {@code users.resource_pack_id} via the
 * /user/resource-pack and /api/plugin/resource-pack endpoints, and the actual
 * asset extraction is done by {@see InstallResourcePack}
 * (via the resourcepack:fetch artisan or the FetchResourcePackJob).
 *
 * This command only flips the global fallback used when a user has no override.
 */
class ResourcePackSwitch extends Command implements PromptsForMissingInput
{
    protected $signature = 'resourcepack:switch
                            {name : Name of an already-installed resource pack}';

    protected $description = 'Set the instance-wide default resource pack.';

    /**
     * @return array<string, string|callable>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'name' => fn () => search(
                label: 'Search for a resource pack:',
                options: function ($value) {
                    if (! is_string($value) || $value === '') {
                        return ['None'];
                    }

                    return array_merge(
                        ['None'],
                        ResourcePack::query()
                            ->where('name', 'like', "%{$value}%")
                            ->orWhere('alias', 'like', "%{$value}%")
                            ->pluck('name', 'id')
                            ->all(),
                    );
                },
            ),
        ];
    }

    public function handle(): int
    {
        $name = $this->argument('name');

        // "None" clears the global default.
        if ($name === 'None' || $name === '' || $name === null) {
            SettingHelper::setSetting('resource_pack_id', 0, 'int');
            $this->info('Cleared instance-wide default resource pack.');

            return CommandAlias::SUCCESS;
        }

        // Resolve to a row. Numeric input is treated as the id; everything else
        // is matched against name/alias.
        $query = ResourcePack::query()
            ->where('name', 'like', "%{$name}%")
            ->orWhere('alias', 'like', "%{$name}%");

        if (is_numeric($name)) {
            $query->orWhere('id', (int) $name);
        }

        $pack = $query->first();

        if (! $pack) {
            $this->error(sprintf("No resource pack matches '%s'. Run resourcepack:fetch first.", $name));

            return CommandAlias::FAILURE;
        }

        SettingHelper::setSetting('resource_pack_id', $pack->id, 'int');

        $this->info(sprintf("Instance default is now '%s'.", $pack->alias));

        return CommandAlias::SUCCESS;
    }
}
