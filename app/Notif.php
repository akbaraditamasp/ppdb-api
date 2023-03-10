<?php

namespace Siluet;

use onesignal\client\api\DefaultApi;
use onesignal\client\Configuration;
use GuzzleHttp;
use onesignal\client\model\Notification;
use onesignal\client\model\StringMap;

class Notif
{
    private static $apiInstance;

    public static function boot()
    {
        $config = Configuration::getDefaultConfiguration()
            ->setAppKeyToken($_ENV["APP_KEY_TOKEN"]);

        static::$apiInstance = new DefaultApi(
            new GuzzleHttp\Client(),
            $config
        );
    }

    public static function send($id, $title, $text)
    {
        $content = new StringMap();
        $content->setEn($text);
        $title = (new StringMap())->setEn($title);

        $notification = new Notification();
        $notification->setAppId($_ENV["APP_ID"]);
        $notification->setContents($content);
        $notification->setHeadings($title);
        $notification->setIncludePlayerIds([$id]);

        $send = static::$apiInstance->createNotification($notification);

        return $send;
    }
}
