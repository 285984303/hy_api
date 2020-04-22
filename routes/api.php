<?php
//短信接口
Route::any('sendmsg', 'Api\Msg\MsgController@mobile_captcha');

/*++++++++++++++学员小程序路由+++++++++++++++++++*/
Route::group(['prefix' => 'small', 'namespace' => 'Api\Small', 'middleware' => ['api']], function () {
    Route::any('login', 'LoginController@login');
    Route::any('loginout', 'LoginController@loginout');
    Route::any('sendmsg', 'LoginController@mobile_captcha');
    Route::any('appointmentlist', 'CourseController@appointmentlist');
    Route::any('doappointment', 'CourseController@doappointment');
    Route::any('cancleappointmentlist', 'CourseController@cancleappointmentlist');
    Route::any('docancle', 'CourseController@docancle');
    Route::any('appointmentrecord', 'CourseController@appointmentrecord');
    Route::any('cancledappointmentlist', 'CourseController@cancledappointmentlist');
    Route::any('getcoach', 'CourseController@getcoach');
    Route::any('getopenid', 'GetOpenIdController@getopenid');
    Route::any('scancode', 'CourseController@scancode');
    Route::any('checktoken', 'CourseController@checktoken');
    Route::any('bustimelist', 'CourseController@bustimelist');
    Route::any('sendmsginfo', 'SendMsgController@sendmsginfo');
    Route::any('getcoachlist', 'CourseController@getcoachlist');
    Route::any('student_assess', 'CourseController@student_assess');
    Route::any('student_complain', 'CourseController@student_complain');
    Route::any('myassess', 'CourseController@myassess');
    Route::any('mycomplain', 'CourseController@mycomplain');
    Route::any('uppass', 'LoginController@up_pass');
    Route::any('findpass', 'LoginController@find_pass');
});

/*++++++++++++++教练小程序路由+++++++++++++++++++*/
Route::group(['prefix' => 'article', 'namespace' => 'Api\Article', 'middleware' => ['api']], function () {
    Route::any('login', 'LoginController@login');
    Route::any('loginout', 'LoginController@loginout');
    Route::any('goodlist', 'ArticleController@good_list');
    Route::any('gooddetail', 'ArticleController@good_detail');
    Route::any('artilist', 'ArticleController@arti_list');
    Route::any('getimglist', 'ArticleController@get_img_list');
    Route::any('artidetail', 'ArticleController@arti_detail');
    Route::any('addcomment', 'ArticleController@add_comment');
    Route::any('addgood', 'ArticleController@add_good');
    Route::any('payit', 'ArticleController@payit');
    Route::any('pay', 'WxPayController@pay');
    //得到预支付交易单
    Route::any('prepay_id', 'WeiXinXPayController@requestPayment');
    //小程序支付回掉
    Route::any('notify', 'WeiXinXPayController@notifyPay');
    
    
    Route::any('getopenid', 'GetOpenIdController@getopenid');
    Route::any('scancode', 'CourseController@scancode');
    Route::any('checktoken', 'CourseController@checktoken');
    Route::any('appointmentlist', 'CourseController@appointmentlist');
//    Route::any('getcoachlist', 'CourseController@getcoachlist');
    Route::any('getcoach', 'CourseController@getcoach');
    Route::any('coachstatistics', 'CourseController@coachStatistics');
    Route::any('appointments','CourseController@appointments');

    Route::any('delit', 'ArticleController@delit');

});











