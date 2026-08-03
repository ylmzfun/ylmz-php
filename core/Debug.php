<?php

namespace Ylmz;

use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

class Debug
{
    public static function init(): void
    {
        if (!Config::getBool('APP_DEBUG', false)) {
            ini_set('display_errors', 'Off');
            error_reporting(0);
            return;
        }

        ini_set('display_errors', 'On');
        error_reporting(E_ALL);

        $whoops = new Run();
        $handler = new PrettyPageHandler();
        $handler->setPageTitle('Ylmz Framework - Error');
        $whoops->pushHandler($handler);
        $whoops->register();
    }
}
