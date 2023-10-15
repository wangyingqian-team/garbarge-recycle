<?php


namespace App\Services\Activity;

use App\Dto\ActivityInvitationDto;

/**
 * 邀请有礼活动
 *
 * Class InvitationService
 * @package App\Services\Activity
 */
class InvitationService
{
    public function createActivity($data){
        return app(ActivityInvitationDto::class)->create($data);
    }

    public function updateActivity($id, $data) {
        return app(ActivityInvitationDto::class)->update($id, $data);
    }

    public function getInvitationList() {
        return app(ActivityInvitationDto::class)->getList();
    }

    public function getInvitationDetail($id) {
        return app(ActivityInvitationDto::class)->getDetail($id);
    }

}
