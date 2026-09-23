<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * @method static void created(\Closure|string $callback)
 * @method static void updated(\Closure|string $callback)
 * @method static void deleted(\Closure|string $callback)
 */
trait LogsActivity
{
    // Campos que NUNCA deben aparecer en un log, sin importar el modelo.
    private static array $neverLogged = ['password', 'remember_token'];

    public static function bootLogsActivity(): void
    {
        static::created(fn ($model) => self::writeLog('created', $model, $model->attributesToArray()));

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);
            if (empty($changes)) {
                return; // nada relevante cambió (ej. solo un "touch")
            }
            self::writeLog('updated', $model, $changes);
        });

        static::deleted(fn ($model) => self::writeLog('deleted', $model, null));
    }

    private static function writeLog(string $action, $model, ?array $data): void
    {
        if ($data) {
            foreach (self::$neverLogged as $field) {
                unset($data[$field]);
            }
        }

        $name = class_basename($model);
        $label = $model->name ?? $model->id;

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $name,
            'model_id' => $model->id,
            'description' => ucfirst($action) . " {$name} #{$model->id} ({$label})",
            'changes' => $data,
            'ip_address' => Request::ip(),
        ]);
    }
}