<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Fund model class.
 */
class Fund extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'start_year', 'manager_id', 'aliases'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'aliases' => 'json',
    ];

    /**
     * Get the manager associated with the fund.
     *
     * @return BelongsTo
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(FundManager::class, 'manager_id');
    }

    /**
     * Get the companies associated with the fund.
     *
     * @return BelongsToMany
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'fund_company', 'fund_id', 'company_id');
    }
}
