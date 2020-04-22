<?php
/**
 * Created by PhpStorm.
 * User: link
 * Date: 2016/11/11
 * Time: 13:18
 */
namespace App\Library\JPush;

use JPush\Client as JPush;
/**
 * 极光推送
 *
 * Class JPush
 * @package App\Library\JPush
 */
class JPushLibrary
{
    private $client = null;
    public function __construct()
    {
        $this->client = new JPush(env('JPUSH_APP_KEY'),env('JPUSH_MASTER_SECRET'),env('JPUSH_LOG_PATH'));
        return $this->client;
    }

    public function setUserNotification(array $registrationIds,$message,$plat = 'all'){

        $pusher = $this->client->push();
        $pusher->setPlatform($plat);
        //$pusher->addAllAudience();
        $pusher->addRegistrationId($registrationIds);
        $pusher->setNotificationAlert($message);
        $pusher->send();
        return true;
    }

}