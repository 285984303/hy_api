<?php namespace App\Library\YunpianSMS\Lib;
/*
 * config file
 */

class Config {
    // public $retry_times = 3;
    // public $api_key     = "";
    // public $api_secret  = "";
    const API_KEY     = "";
    const API_SECRET  = "";
    const RETRY_TIMES = 3;
}

class API {
    //System
    const SMS_HOST   = 'https://sms.yunpian.com';
    const VOICE_HOST = 'https://voice.yunpian.com';
    const FLOW_HOST  = 'https://flow.yunpian.com';
    const VERSION    = '/v2';

    // 短信
    const URI_SEND_SINGLE_SMS = self::SMS_HOST . self::VERSION . "/sms/single_send.json";
    const URI_SEND_BATCH_SMS  = self::SMS_HOST . self::VERSION . "/sms/batch_send.json";
    const URI_SEND_MULTI_SMS  = self::SMS_HOST . self::VERSION . "/sms/multi_send.json";
    const URI_SEND_TPL_SMS    = self::SMS_HOST . self::VERSION . '/sms/tpl_send.json';
    const URI_PULL_SMS_STATUS = self::SMS_HOST . self::VERSION . "/sms/pull_status.json";
    # 获取回复短信
    const URI_PULL_SMS_REPLY  = self::SMS_HOST . self::VERSION . "/sms/pull_reply.json";
    # 查询回复短信
    const URI_GET_SMS_REPLY   = self::SMS_HOST . self::VERSION . "/sms/get_reply.json";
    # 查短信发送记录
    const URI_GET_SMS_RECORD  = self::SMS_HOST . self::VERSION . "/sms/get_record.json";

    // 语音
    const URI_SEND_VOICE_SMS    = self::VOICE_HOST . self::VERSION . "/voice/send.json";
    const URI_PULL_VOICE_STATUS = self::VOICE_HOST . self::VERSION . "/voice/pull_status.json";

    // 流量
    const URI_GET_FLOW_PACKAGE = self::FLOW_HOST . self::VERSION . "/flow/get_package.json";
    const URI_PULL_FLOW_STATUS = self::FLOW_HOST . self::VERSION . "/flow/pull_status.json";
    const URI_RECHARGE_FLOW    = self::FLOW_HOST . self::VERSION . "/flow/recharge.json";

    // 用户操作
    const URI_GET_USER_INFO = self::SMS_HOST . self::VERSION . "/user/get.json";
    const URI_SET_USER_INFO = self::SMS_HOST . self::VERSION . "/user/set.json";

    // 模板操作
    const URI_GET_DEFAULT_TEMPLATE = self::SMS_HOST . self::VERSION . "/tpl/get_default.json";

    const URI_GET_TEMPLATE = self::SMS_HOST . self::VERSION . "/tpl/get.json";
    const URI_ADD_TEMPLATE = self::SMS_HOST . self::VERSION . "/tpl/add.json";
    const URI_UPD_TEMPLATE = self::SMS_HOST . self::VERSION . "/tpl/update.json";
    const URI_DEL_TEMPLATE = self::SMS_HOST . self::VERSION . "/tpl/del.json";
}
?>