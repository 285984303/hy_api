function getNowFormatDate() {
    var date = new Date();
    var seperator1 = "-";
    var month = date.getMonth() + 1;
    var strDate = date.getDate();
    if (month >= 1 && month <= 9) {
        month = "0" + month;
    }
    if (strDate >= 0 && strDate <= 9) {
        strDate = "0" + strDate;
    }
    var currentdate = date.getFullYear() + seperator1 + month + seperator1 + strDate;

    return currentdate;
}
$(function () {
    var policyText = {
        "expiration": "2020-01-01T12:00:00.000Z", //设置该Policy的失效时间，超过这个失效时间之后，就没有办法通过这个policy上传文件了
        "conditions": [
            ["content-length-range", 0, 1048576000] // 设置上传文件的大小限制
        ]
    };
    var accessid = $("#access_id").val();
    var accesskey = $("#access_key").val();
    var host = $("#host").val();
    var policyBase64 = Base64.encode(JSON.stringify(policyText));
    var bucket = $("#bucket").val();
    var message = policyBase64;
    var up_date = getNowFormatDate();
    var bytes = Crypto.HMAC(Crypto.SHA1, message, accesskey, {asBytes: true});
    var signature = Crypto.util.bytesToBase64(bytes);
    var uploader = new plupload.Uploader({
        runtimes: 'html5,flash,silverlight,html4',
        browse_button: 'selectfiles',
        filters: {
            mime_types: [ //只允许上传图片和zip文件
                {title: "Image files", extensions: "jpg,gif,png"}
            ]
        },
        //runtimes : 'flash',
        container: document.getElementById('container'),
        flash_swf_url: 'lib/plupload-2.1.2/js/Moxie.swf',
        silverlight_xap_url: 'lib/plupload-2.1.2/js/Moxie.xap',
        rename: true,
        url: '/admin/school/student/upavatar',
        unique_names: true,
        // multipart_params: {
        //     'Filename': '${filename}',
        //     'key': 'images/avatar/' + up_date + '/${filename}',
        //     'policy': policyBase64,
        //     'OSSAccessKeyId': accessid,
        //     'success_action_status': '200', //让服务端返回200,不然，默认会返回204
        //     'signature': signature
        // },

        init: {
            FilesAdded: function (up, files) {
                // var reg = /[\u4e00-\u9fa5]/;
                //     if (reg.test(files[0].name)) {
                //         layer.msg('上传图片不能包含汉字，请修改！', {time: 2000, icon: 5});
                //     } else {
                    uploader.start();
                    return false;
                // }

            },
            FileUploaded: function (up, file, info) {
                if (info.status >= 200 || info.status < 200) {
                    $(".logoImg").attr("src", JSON.parse(info.response).result);
                    // $("#user_logo_img").val(JSON.parse(info.response).result);
                    $("input[name=user_img]").val(JSON.parse(info.response).result);
                    layer.msg('上传成功！', {time: 2000, icon: 6});
                }
                else {
                    layer.msg('上传失败：' + JSON.parse(info.response).result, {time: 2000, icon: 5});
                }
            },

            Error: function (up, err) {

                layer.msg('上传失败：' + ierr.response, {time: 2000, icon: 5});
            }
        }
    });

    uploader.init();

});
