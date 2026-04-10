<?php

if (! function_exists('lume_social_ready')) {
    function lume_social_ready(): bool
    {
        $db = db_connect();

        return $db->tableExists('user_follows') && $db->tableExists('notifications');
    }
}

if (! function_exists('lume_notification_create')) {
    function lume_notification_create(int $userId, string $type, string $message, ?string $link = null, ?int $actorUserId = null, ?string $relatedType = null, ?int $relatedId = null): void
    {
        if ($userId <= 0 || ! lume_social_ready()) {
            return;
        }

        db_connect()->table('notifications')->insert([
            'user_id' => $userId,
            'actor_user_id' => $actorUserId,
            'type' => $type,
            'message' => $message,
            'link' => $link,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

if (! function_exists('lume_notification_unread_count')) {
    function lume_notification_unread_count(int $userId): int
    {
        if ($userId <= 0 || ! lume_social_ready()) {
            return 0;
        }

        return (int) db_connect()->table('notifications')
            ->where('user_id', $userId)
            ->where('read_at', null)
            ->countAllResults();
    }
}

if (! function_exists('lume_notification_latest')) {
    function lume_notification_latest(int $userId, int $limit = 6): array
    {
        if ($userId <= 0 || ! lume_social_ready()) {
            return [];
        }

        return db_connect()->table('notifications')
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}
