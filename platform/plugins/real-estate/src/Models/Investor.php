<?php

namespace Botble\RealEstate\Models;

use Botble\Base\Traits\EnumCastable;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Botble\RealEstate\Models\Project;

class Investor extends BaseModel
{
    use EnumCastable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 're_investors';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'status',
        'image',
        'location',
        'phone',
        'website',
        'description'
    ];

    /**
     * @var array
     */
    protected $casts = [
        'status' => BaseStatusEnum::class,
    ];

    protected $appends = ['projects_count'];

    public function getProjectsCountAttribute()
    {
        return $this->projects()->count();
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
