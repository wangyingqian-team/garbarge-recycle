<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VillageModel extends Model
{
    protected $table = 'village';

    public $timestamps = false;


    public function floor()
    {
        return $this->hasMany(VillageFloorModel::class, 'village_id');
    }

    public function recycler() {
        return $this->hasOne(RecyclerVillageModel::class, 'village_id');
    }

    public function site() {
        return $this->belongsTo(GarbageSiteModel::class, 'site_id');
    }
}
