<?php

namespace App\Dto;

use App\Supports\Constant\RecyclerConst;

class RecyclerAssetsDto extends Dto
{
    public function getAssets($id)
    {
        return $this->query->where('recycler_id', $id)->macroFirst();
    }

    public function changeAssets($id, $type, $num) {
       $val = [];

        if ($type == RecyclerConst::ASSETS_THROW){
            $val = [
                'throw_total' => $type
            ];
        }

        if ($type == RecyclerConst::ASSETS_RECYCLE){
            $val = [
                'recover_total' => $type
            ];
        }

        if ($type == RecyclerConst::ASSETS_AMOUNT){
            $val = [
                'recover_amount' => $type
            ];
        }

        if (!empty($val)) {
            $this->query->where('recycler_id')->update($val);
        }

        return true;
    }
}
