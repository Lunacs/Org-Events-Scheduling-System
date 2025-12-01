<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Office extends Model
{
    /** @use HasFactory<\Database\Factories\OfficeFactory> */
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'office_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'office_code',
        'office_name',
        'description',
    ];
    protected static array $officeIdCache = [];

    /**
     * Users assigned to this office
     */
    public function users()
    {
        return $this->hasMany(User::class, 'office_id');
    }

    /**
     * Office approvals made by this office
     */
    public function officeApprovals()
    {
        return $this->hasMany(Office_Approval::class, 'office_id');
    }

    public static function getOfficeId(string $officeCode): ?int
    {
        // Check in-memory cache first (prevents duplicate queries in same request)
        if (isset(static::$officeIdCache[$officeCode])) {
            return static::$officeIdCache[$officeCode];
        }

        // Get from persistent cache (or database if not cached)
        $officeId = Cache::rememberForever("office_id_{$officeCode}", function () use ($officeCode) {
            return Office::where('office_code', $officeCode)->value('office_id');
        });

        // Store in in-memory cache for this request
        static::$officeIdCache[$officeCode] = $officeId;

        return $officeId;
    }
}
