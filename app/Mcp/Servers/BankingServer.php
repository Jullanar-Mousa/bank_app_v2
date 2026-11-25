<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server;
use App\Mcp\Tools\GetAlertDetailsTool;

class BankingServer extends Server
{
    /**
     * تعريف الأدوات التابعة لهذا الخادم
     */
    protected array $tools = [
        GetAlertDetailsTool::class,
    ];
}