<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookletNotice extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'batch_code',
        'batch_name',
        'phone_number',
        'visitor_name',
        'plate_number',
        'chassis_number',
        'booklet_number',
        'end_date',
        'channel',
        'message_text',
        'sent_by',
        'sent_at',
        'status',
        'attended_at',
        'attended_transaction_id',
        'attended_type',
        'notes',
    ];

    protected $casts = [
        'end_date' => 'date',
        'sent_at' => 'datetime',
        'attended_at' => 'datetime',
    ];

    /**
     * Relationship: The original expired transaction.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    /**
     * Relationship: The new transaction created when citizen attended.
     */
    public function attendedTransaction()
    {
        return $this->belongsTo(Transaction::class, 'attended_transaction_id');
    }

    /**
     * Relationship: The user who dispatched the notice.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Normalize Iraqi phone number to standard international format (e.g. 9647701234567).
     */
    public static function normalizePhoneNumber(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        // Remove any non-digits
        $clean = preg_replace('/\D+/', '', $phone);

        if (empty($clean)) {
            return null;
        }

        // If starts with 00964, strip leading 00
        if (str_starts_with($clean, '00964')) {
            return substr($clean, 2);
        }

        // If starts with 964, keep as is
        if (str_starts_with($clean, '964')) {
            return $clean;
        }

        // If starts with 07..., convert 07 to 9647...
        if (str_starts_with($clean, '07')) {
            return '964' . substr($clean, 1);
        }

        // If starts with 7... (10 digits), add 964
        if (str_starts_with($clean, '7') && strlen($clean) === 10) {
            return '964' . $clean;
        }

        return $clean;
    }

    /**
     * Generate pre-composed default official notice message.
     */
    public static function generateDefaultMessage(string $visitorName, string $plateNumber, ?string $bookletNumber, ?string $endDate): string
    {
        $booklet = $bookletNumber ?: 'دیارینەکراو';
        $date = $endDate ?: 'بەسەرچوو';

        return "سڵاو بەڕێز {$visitorName}،\n" .
               "ئاگادارتان دەکەینەوە لە بەڕێوەبەرایەتی گومرگی سلێمانی کە دەفتەری گومرگی ئۆتۆمبێلەکەتان بە تابلۆی ({$plateNumber}) و ژمارەی دەفتەری ({$booklet}) لە بەرواری ({$date}) بەسەرچووە.\n" .
               "تکایە بە زووترین کات سەردانی بەڕێوەبەرایەتی گومرگی سلێمانی بکەن بۆ تازەکردنەوە یان پووچەڵکردنەوەی دەفتەرەکەتان، تاکو دووچاری سزای یاسایی و دارایی نەبنەوە.\n\n" .
               "— بەڕێوەبەرایەتی گومرگی سلێمانی";
    }

    /**
     * Generate WhatsApp direct link.
     */
    public function getWhatsAppUrl(?string $overrideText = null): ?string
    {
        $normalized = self::normalizePhoneNumber($this->phone_number);
        if (!$normalized) {
            return null;
        }

        $text = $overrideText ?: ($this->message_text ?: self::generateDefaultMessage($this->visitor_name ?? '', $this->plate_number ?? '', $this->booklet_number, $this->end_date?->format('Y-m-d')));
        return 'https://wa.me/' . $normalized . '?text=' . urlencode($text);
    }

    /**
     * Generate Viber direct link.
     */
    public function getViberUrl(?string $overrideText = null): ?string
    {
        $normalized = self::normalizePhoneNumber($this->phone_number);
        if (!$normalized) {
            return null;
        }

        $text = $overrideText ?: ($this->message_text ?: self::generateDefaultMessage($this->visitor_name ?? '', $this->plate_number ?? '', $this->booklet_number, $this->end_date?->format('Y-m-d')));
        // Standard mobile / desktop viber URI
        return 'viber://chat?number=%2B' . $normalized;
    }

    /**
     * Scopes.
     */
    public function scopeAttended($query)
    {
        return $query->where('status', 'attended')->orWhereNotNull('attended_at');
    }

    public function scopeUnattended($query)
    {
        return $query->where('status', 'sent')->whereNull('attended_at');
    }

    public function scopeBatch($query, $batchCode)
    {
        return $query->where('batch_code', $batchCode);
    }
}
