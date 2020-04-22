<?php namespace App\Library\YunpianSMS\Lib;

/**
 * Created by PhpStorm.
 * User: bingone
 * Date: 16/1/19
 * Time: 下午5:42
 */
class FlowOperator {
    public $apikey;
    public $api_secret;

    public function __construct($apikey = NULL, $api_secret = NULL) {
        if ($api_secret == NULL) {
            $this->api_secret = Config::API_SECRET;
        } else {
            $this->api_secret = $api_secret;
        }
        if ($apikey == NULL) {
            $this->apikey = Config::API_KEY;
        } else {
            $this->apikey = $apikey;
        }
    }

    public function encrypt(&$data) {

    }

    public function get_package($data = []) {
        $data['apikey'] = $this->apikey;

        return HttpUtil::PostCURL(API::URI_GET_FLOW_PACKAGE, $data);
    }

    public function pull_status($data = []) {
        $data['apikey'] = $this->apikey;

        return HttpUtil::PostCURL(API::URI_PULL_FLOW_STATUS, $data);
    }

    public function recharge($data = []) {
        if (!array_key_exists('mobile', $data)) {
            return new Result(NULL, $data, NULL, $error = 'mobile 为空');
        }

        $data['apikey'] = $this->apikey;

        return HttpUtil::PostCURL(API::URI_RECHARGE_FLOW, $data);
    }
}
