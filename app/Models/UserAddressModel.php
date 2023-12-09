<?php
/**
 * Created by PhpStorm.
 * User: wumx2
 * Date: 2020-3-13
 * Time: 14:24
 */

namespace App\Models;

use App\Services\User\VillageService;
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

    protected $appends = ['village_name', 'address_detail'];

    public function getVillageNameAttribute()
    {
        $villageId = $this->attributes['village_id'];
        return app(VillageService::class)->getVillageInfo($villageId)['name'] ?? '';
    }

    public function getAddressDetailAttribute()
    {
        $villageId = $this->attributes['village_id'];
        $villageInfo = app(VillageService::class)->getVillageInfo($villageId);
        return empty($villageInfo) ? '' : $villageInfo['province'] .  $villageInfo['city'] . $villageInfo['area'] . ' ' . $villageInfo['name'] . $this->attributes['address'];
    }
}
