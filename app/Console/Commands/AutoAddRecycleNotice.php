<?php
namespace App\Console\Commands;

use App\Supports\Constant\RedisKeyConst;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class AutoAddRecycleNotice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'garbage:user_notice_add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '添加用户通知信息';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    const NICK_NAMES = [
        '弦未尽', '浮生远离', '浅沫记忆', '倾凉血夕', '等时光的邂逅', '只对你任性', '龙卷风', '活给自己看', '追逐明天', '劣性失格', '岁月之沉淀',
        '梦远了爱淡了', '淺笑ペ安然', '忘了我就好', '风月', '转身、快乐', '人命薄', '知海無涯', '一心只容一人', '口拙嘴笨', '我萌怪我咯', '酷到乏味',
        'Bitter、泪海', '栖止你掌', '逗比卖萌无所不能', '失败统治', '故事与谁', '键盘书生', '听够珍惜', '东城冷人', '姐很高也很傲', '望春风',
        '匿名的关系', '风花雪月夜', '一白遮', '江心薄雾起', '理想', '当风起时', '对错何妨', '青柠之恋', '敷衍我吧你', '不眠之夜', '故城旧事', '栀心'
    ];

    const TOTAL_AMOUNTS = [
        12.50, 18.00, 25.00, 9.00, 52.00, 27.50, 21.50, 33.00, 40.00, 17.50, 22.50, 31.00, 36.00, 20.00, 23.50, 42.00, 45.00, 59.00, 112.50, 39.00, 58.00
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $redis = Redis::connection('recycle');
        $noticeKey = RedisKeyConst::RECYCLE_NOTICE_USER;
        $userId = mt_rand(-200, -100);
        $nickName = self::NICK_NAMES[mt_rand(0, sizeof(self::NICK_NAMES))];
        $totalAmount = self::TOTAL_AMOUNTS[mt_rand(0, sizeof(self::TOTAL_AMOUNTS))];
        $noticeMsg = '用户"' . $nickName . '"' . '在' . date("Y-m-d H:i:s") . '回收收益' . $totalAmount . '元';
        $notice = json_encode([
            'time' => time(),
            'msg' => $noticeMsg
        ]);
        if ($redis->hget($noticeKey, $userId)) {
            $redis->hdel($noticeKey, [$userId]);
        }
        $redis->hset($noticeKey, $userId, $notice);
    }
}
