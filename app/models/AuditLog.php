<?php

namespace App\Models;


class AuditLog extends Model
{
    protected static $table = 'audit_log';

    public static function log($userId, $event, $table, $recordId, $oldValues = null, $newValues = null)
    {
        $db = static::getDB();
        $sql = "INSERT INTO audit_log (user_id, event, `table`, record_id, old_values, new_values)
                VALUES (:user_id, :event, :table, :record_id, :old_values, :new_values)";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':event', $event, \PDO::PARAM_STR);
        $stmt->bindValue(':table', $table, \PDO::PARAM_STR);
        $stmt->bindValue(':record_id', $recordId, \PDO::PARAM_INT);
        $stmt->bindValue(':old_values', $oldValues, \PDO::PARAM_STR);
        $stmt->bindValue(':new_values', $newValues, \PDO::PARAM_STR);

        return $stmt->execute();
    }
}