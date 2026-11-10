<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

/**
 * موديل جدول notifications (جدول إشعارات Laravel القياسي).
 *
 * يرث من DatabaseNotification الجاهز في Laravel، وبالتالي يحصل على:
 *  - الجدول: notifications
 *  - المفتاح الأساسي: uuid (غير تلقائي)
 *  - تحويل data إلى array و read_at / created_at / updated_at إلى datetime
 *  - علاقة notifiable (morphTo) + دوال markAsRead / markAsUnread / read / unread
 */
class Notification extends DatabaseNotification
{
    protected $table = 'notifications';

    protected $fillable = ['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at'];
}
