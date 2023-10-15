<?php


namespace App\Services\Activity;

use App\Dto\ActivityNewerDto;

/**
 * 新人福利活动
 *
 * Class InvitationService
 * @package App\Services\Activity
 */
class NewerService
{
    public function createActivity($data){
        return app(ActivityNewerDto::class)->create($data);
    }

    public function updateActivity($id, $data) {
        return app(ActivityNewerDto::class)->update($id, $data);
    }

    public function getActivityList() {
        return app(ActivityNewerDto::class)->getList();
    }

    public function getActivityDetail($id) {
        return app(ActivityNewerDto::class)->getDetail($id);
    }

}
