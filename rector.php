<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use App\Rector\PrefixInfRector;

return static function (RectorConfig $rectorConfig): void {

    $rectorConfig->paths([
        'C:/Users/user/Desktop/rector-project/httpdocs',
    ]);

    $rectorConfig->rule(PrefixInfRector::class);
};