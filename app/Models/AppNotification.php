<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'message',
        'url',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create notification for target user(s) and prune if > 100 per user.
     */
    public static function createForUser(?int $userId, string $message, string $type = 'info', ?string $url = null): void
    {
        // Only store info and warning notifications in the bell
        // EXCEPTION: Allow success notifications for payment confirmations
        if (in_array($type, ['success', 'error', 'danger'])) {
            if ($type !== 'success' || !str_contains(strtolower($message), 'pembayaran')) {
                return;
            }
        }

        if ($userId) {
            static::create([
                'user_id' => $userId,
                'message' => $message,
                'type' => $type,
                'url' => $url,
                'is_read' => false,
            ]);
            static::pruneOldForUser($userId);
        } else {
            // Send to all admin/owner/developer users
            $adminUsers = User::whereHas('roles', function($q) {
                $q->whereIn('name', ['owner', 'developer', 'admin']);
            })->get();

            if ($adminUsers->isNotEmpty()) {
                foreach ($adminUsers as $admin) {
                    static::create([
                        'user_id' => $admin->id,
                        'message' => $message,
                        'type' => $type,
                        'url' => $url,
                        'is_read' => false,
                    ]);
                    static::pruneOldForUser($admin->id);
                }
            } else if (auth()->check()) {
                // Fallback to currently authenticated user if roles table is unseeded or no admins exist
                static::create([
                    'user_id' => auth()->id(),
                    'message' => $message,
                    'type' => $type,
                    'url' => $url,
                    'is_read' => false,
                ]);
                static::pruneOldForUser(auth()->id());
            }
        }
    }

    /**
     * Prune old notifications exceeding limit (100) for a given user.
     */
    public static function pruneOldForUser(int $userId, int $maxCount = 100): void
    {
        $idsToKeep = static::where('user_id', $userId)
            ->latest('id')
            ->take($maxCount)
            ->pluck('id');

        static::where('user_id', $userId)
            ->whereNotIn('id', $idsToKeep)
            ->delete();
    }
}
