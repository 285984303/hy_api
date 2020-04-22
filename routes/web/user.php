<?php
Route::group(['namespace' => 'User', 'middleware' => ['api']], function () {
    Route::any('/', 'LoginController@login');
    Route::any('dologin', 'LoginController@dologin');
    Route::any('loginout', 'LoginController@loginout');
    /*Route::any('logout', 'LoginController@logout');
    Route::any('sendmsg', 'LoginController@mobile_captcha');
    Route::any('appointmentlist', 'CourseController@appointmentlist');
    Route::any('doappointment', 'CourseController@doappointment');
    Route::any('cancleappointmentlist', 'CourseController@cancleappointmentlist');
    Route::any('docancle', 'CourseController@docancle');
    Route::any('appointmentrecord', 'CourseController@appointmentrecord');
    Route::any('cancledappointmentlist', 'CourseController@cancledappointmentlist');
    Route::any('getcoach', 'CourseController@getcoach');
    Route::any('getopenid', 'GetOpenIdController@getopenid');*/
    Route::group(['middleware' => ['api', 'api.auth']], function () {

          Route::get('appointment', 'CourseController@appointment')->name('2221');
          Route::get('appointmentrecord', 'CourseController@appointmentrecord')->name('2222');
          Route::get('cancleappointment', 'CourseController@cancleappointment')->name('2223');
//        Route::get('appointment', 'CourseController@appointment'); //
//        Route::any('doappointment', 'CourseController@doappointment');
//        Route::any('appointmentlist', 'CourseController@appointmentlist');
//        Route::get('cancleappointmentlist', 'CourseController@cancleappointmentlist');
//        Route::get('cancle', 'CourseController@cancle');
//        Route::get('docancle/{id}', 'CourseController@docancle');
//        Route::get('getcoach', 'CourseController@getcoach');
    });
});







