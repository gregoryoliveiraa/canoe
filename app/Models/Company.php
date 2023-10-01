<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Company model class.
 */
class Company extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Get the funds associated with the company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function funds()
    {
        return $this->belongsToMany(Fund::class, 'fund_company', 'company_id', 'fund_id');
    }
}
