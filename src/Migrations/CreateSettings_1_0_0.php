<?php
/**
 * Created by IntelliJ IDEA.
 * User: ckunze
 * Date: 23/2/17
 * Time: 15:54
 */

namespace Debit\Migrations;

use Plenty\Modules\Plugin\DataBase\Contracts\Migrate;
use Debit\Models\Settings;
use Debit\Services\SettingsService;


class CreateSettings_1_0_0
{
    use \Plenty\Plugin\Log\Loggable;

    public function run(Migrate $migrate)
    {
        try
        {
            $migrate->createTable(Settings::class);
            $this->setInitialSettings();
        }
        catch(\Exception $e)
        {
            echo $e->getMessage();
        }

    }

    private function setInitialSettings()
    {
        /** @var SettingsService $service */
        $service = pluginApp(SettingsService::class);
        $clients = $service->getClients();

        foreach(Settings::AVAILABLE_LANGUAGES as $lang)
        {
            foreach ($clients as $plentyId)
            {
                $service->createInitialSettingsForPlentyId($plentyId, $lang);
            }
        }
    }

}