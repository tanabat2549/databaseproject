<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Multi-valued attribute: one user can have many phone numbers.
 * Primary key is composite (uid, phone_no), so delete rows through
 * the query builder, e.g. $user->phones()->where('phone_no', $no)->delete().
 */
class UserPhone extends Model
{
    protected $table = 'user_phones';

    protected $primaryKey = 'phone_no';

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uid',
        'phone_no',
    ];

    /**
     * Owner of this phone number.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uid');
    }
}
