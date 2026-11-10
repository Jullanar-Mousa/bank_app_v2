<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Audit
{
    public static function log(
        Request $request,
        string $action,
        ?Model $entity = null,
        ?array $old = null,
        ?array $new = null
    ): void {
        AuditLog::forceCreate([
            'user_id'     => $request->user()?->id,
            'action'      => $action,
            'entity_type' => $entity ? class_basename($entity) : null,
            'entity_id'   => $entity?->getKey(),
            'old_values'  => $old,
            'new_values'  => $new,
            'ip_address'  => $request->ip(),
        ]);
    }
}