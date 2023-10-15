<?php

// 基础语法
$orderInfo = null;
if ($orderInfo['status'] != 1) {
    echo "error!" . "\n";
} else {
    echo "success!" . "\n";
}

// 时间相关
echo '今天：' . date('Y-m-d') . "\n";
echo '明天：' . date('Y-m-d', strtotime('+1 day')) . "\n";
echo '后天：' . date('Y-m-d', strtotime('+2 day')) . "\n";

echo '当前时间戳：' . time() . "\n";
echo '9点时间戳：' . strtotime('09:00') . "\n";
echo '19号9点时间戳：' . strtotime('2021-09-19 09:00') . "\n";
echo '拼接的9点时间戳：' . strtotime(date('Y-m-d') . ' ' . '09:00') . "\n";

echo '前一个小时：' . date("Y-m-d H:00:00", strtotime("-1 hour")) . ' - ' . date("Y-m-d H:00:00", time()) . "\n";
$autoRateDays = 3;
echo "{$autoRateDays}天前的时间：" . date("Y-m-d H:i:s", strtotime("-{$autoRateDays} day")) . "\n";

$datetimeStr = '2021-09-05 12:31:17';
echo '截取时间中的日期：' . date('Y-m-d', strtotime($datetimeStr)) . "\n";
echo '截取时间中的时分：' . date('H:i', strtotime($datetimeStr)) . "\n";

echo '2021-10-02是星期几：' . date('w', strtotime('2021-10-02')) . "\n";
echo strtotime('2021-09-07');

$timeArray1 = ["09:00-10:00", "10:00-11:00", "14:00-15:00", "15:00-16:00", "16:00-17:00", "18:00-19:00"];
$timeArray2 = ["14:30-15:30"];

$newTimeArr = array_filter($timeArray1, function($timePeriod) use($timeArray2) {
    [$startTime1, $endTime1] = explode('-', $timePeriod);
    foreach ($timeArray2 as $overlapPeriod) {
        [$startTime2, $endTime2] = explode('-', $overlapPeriod);
        if (isOverlap($startTime1, $endTime1, $startTime2, $endTime2)) {
            return false;
        }
    }

    return true;
});
print_r($newTimeArr);

print_r(date("Y-m-d H:00:00", strtotime("+1 hour")));

function isOverlap($startDate1, $endDate1, $startDate2, $endDate2)
{
    return strtotime($endDate1) >= strtotime($startDate2) && strtotime($startDate1) <= strtotime($endDate2);
}