<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\GetAlertDetailsTool;
use App\Mcp\Tools\GetAccountHistoryTool;
use App\Mcp\Tools\ProposeInvestigationActionTool;
use Laravel\Mcp\Server;

class FraudInvestigationServer extends Server
{
    protected string $name = 'Fraud Investigation MCP Server';
    protected string $version = '1.0.0';
    protected string $instructions = 'خادم تحقيق جنائي مالي للقراءة والتحليل وتقديم مقترحات للمحلل البشري فقط دون صلاحية حظر مباشر.';

    protected array $tools = [
        GetAlertDetailsTool::class,
        GetAccountHistoryTool::class,
        ProposeInvestigationActionTool::class,
    ];
}