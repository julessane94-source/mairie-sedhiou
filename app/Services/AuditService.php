<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    /**
     * Enregistre une action d'audit
     */
    public static function log(
        string $action,
        Model $model,
        array $oldValues = [],
        array $newValues = [],
        ?Request $request = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_values' => !empty($oldValues) ? $oldValues : null,
            'new_values' => !empty($newValues) ? $newValues : null,
            'ip_address' => $request?->ip() ?? request()->ip(),
            'user_agent' => $request?->userAgent() ?? request()->userAgent(),
            'logged_at' => now(),
        ]);
    }

    /**
     * Enregistre une création
     */
    public static function logCreate(Model $model, ?Request $request = null): AuditLog
    {
        return self::log('create', $model, [], $model->toArray(), $request);
    }

    /**
     * Enregistre une mise à jour
     */
    public static function logUpdate(Model $model, array $oldValues, array $newValues, ?Request $request = null): AuditLog
    {
        return self::log('update', $model, $oldValues, $newValues, $request);
    }

    /**
     * Enregistre une suppression
     */
    public static function logDelete(Model $model, ?Request $request = null): AuditLog
    {
        return self::log('delete', $model, $model->toArray(), [], $request);
    }

    /**
     * Récupère les logs d'audit pour un modèle
     */
    public static function getLogsFor(string $modelType, int $modelId)
    {
        return AuditLog::where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->with('user')
            ->orderBy('logged_at', 'desc')
            ->get();
    }

    /**
     * Récupère tous les logs récents d'un utilisateur
     */
    public static function getUserActivityLog(int $userId, int $limit = 50)
    {
        return AuditLog::where('user_id', $userId)
            ->orderBy('logged_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
