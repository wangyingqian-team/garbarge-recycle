<?php
/**
 * Created by PhpStorm.
 * User: wumx2
 * Date: 2020-3-13
 * Time: 14:24
 */

namespace App\Models;

use App\Services\Village\VillageService;
use Illuminate\Database\Eloquent\Model;

/**
 * 用户地址
 *
 * Class UserAddressModel
 * @package App\Models
 */
class UserAddressModel extends Model
{
    protected $table = "user_address";

    public $timestamps = false;

    protected $appends = ['village_name', 'village_floor_name'];

    public function getVillageNameAttribute()
    {
        $villageId = $this->attributes['village_id'];
        return app(VillageService::class)->getVillageDetail($villageId)['name'];
    }

    public function getVillageFloorNameAttribute()
    {
        $villageFloorId = $this->attributes['village_floor_id'];
        return app(VillageService::class)->getVillageFloorDetail($villageFloorId)['floor'];
    }
}
