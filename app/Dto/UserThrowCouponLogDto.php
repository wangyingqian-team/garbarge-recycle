<?php

namespace App\Dto;

use App\Supports\Constant\CouponConst;
use Carbon\Carbon;

class UserThrowCouponLogDto extends Dto
{
    public function create($data)
    {
        $val = [
            'user_id' => $data['user_id'],
            'status' => $data['status'],
            'origin' => $data['origin'],
            'remark' => $data['remark'],
            'begin_time' => $data['begin_time'],
            'end_time' => $data['end_time']
        ];
        return $this->query->insert($val);
    }

    public function updateStatusById($id, $status)
    {
        return $this->query->whereKey($id)->update(['status' => $status]);
    }

    public function expire()
    {
        return $this->query->where('status', '!=', CouponConst::THROW_FREEZE_STATUS)->update(
            ['status' => CouponConst::THROW_EXPIRE_STATUS]
        );
    }

    public function getCouponInfo($id)
    {
        return $this->query->whereKey($id)->macroFirst();
    }

    public function getCouponList($userId, $status)
    {
        return $this->query->where(['user_id' => $userId, 'status' => $status])->where(
            'update_time',
            '>',
            Carbon::today()->subDays(
                90
            )->toDateTimeString()
        )->get()->toArray();
    }

    public function getCouponListByIds($couponIds, $couponStatus) {
        $queryBuilder = $this->query->whereIn('id', $couponIds);

        if (!empty($couponStatus)) {
            $queryBuilder->where('status', $couponStatus);
        }

        return $queryBuilder->get()->toArray();
    }
}
