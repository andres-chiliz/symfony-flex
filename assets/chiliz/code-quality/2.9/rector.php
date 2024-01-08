<?php

declare(strict_types=1);

use Chiliz\CodeQuality\Rector\RectorConfigurator;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $configurator = new RectorConfigurator();
    $configurator->configure($rectorConfig);
};
