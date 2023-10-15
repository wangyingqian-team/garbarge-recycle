<?php

namespace App\Dto;

use App\Supports\Macro\Builder;

class GarbageRecycleRateDto extends Dto
{
    /**
     * 提交评价.
     *
     * @param array $rateInfo
     *
     * @return int
     *
     */
    public function createRate($rateInfo)
    {
        return $this->query->insertGetId([
            'user_id' => $rateInfo['user_id'],
            'order_no' => $rateInfo['order_no'],
            'site_id' => $rateInfo['site_id'],
            'type' => $rateInfo['type'],
            'content' => $rateInfo['content'],
            'image' => $rateInfo['image']
        ]);
    }

    /**
     * 获取评价列表.
     *
     * @param array $where
     * @param array $select
     * @param array $orderBy
     * @param int $page
     * @param int $limit
     * @param bool $withPage
     *
     * @return mixed
     *
     */
    public function getRateList($where, $select, $orderBy, $page = 1, $limit = Builder::PER_LIMIT, $withPage = true)
    {
        return $this->query->macroQuery($where, $select, $orderBy, $page, $limit, $withPage);
    }
}