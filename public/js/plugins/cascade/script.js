/*
 author: Tony
 create: 2014-02
 */
$(function () {
    var citySelector = function () {
        var province = $(".province1");
        var city = $(".city1");
        var district = $(".district1");
        var preProvince = $(".pre_province1");
        var preCity = $(".pre_city1");
        var preDistrict = $(".pre_district1");
        var jsonProvince = "/js/plugins/cascade/content/json-array-of-province.js";
        var jsonCity = "/js/plugins/cascade/content/json-array-of-city.js";
        var jsonDistrict = "/js/plugins/cascade/content/json-array-of-district.js";
        var hasDistrict = true;
        var initProvince = "<option value='0'>请选择省</option>";
        var initCity = "<option value='0'>请选择市</option>";
        var initDistrict = "<option value='0'>请选择区</option>";

        return {
            Init: function (preProvince, province, preCity, city, preDistrict, district) {
                var that = this;
                that._LoadOptions(jsonProvince, preProvince, province, null, 0, initProvince);
                province.change(function () {
                    that._LoadOptions(jsonCity, preCity, city, province, 2, initCity);
                });
                if (hasDistrict) {
                    city.change(function () {
                        that._LoadOptions(jsonDistrict, preDistrict, district, city, 4, initDistrict);
                    });
                    province.change(function () {
                        city.change();
                    });
                }
                province.change();

            },
            _LoadOptions: function (datapath, preobj, targetobj, parentobj, comparelen, initoption) {

                $.get(
                    datapath,
                    function (r) {
                        var t = '';
                        var s;
                        var pre;
                        if (preobj === undefined) {
                            pre = 0;
                        } else {
                            pre = preobj.val();
                        }
                        for (var i = 0; i < r.length; i++) {
                            s = '';
                            if (comparelen === 0) {
                                if (pre !== "" && pre !== 0 && r[i].code === pre) {
                                    s = ' selected=\"selected\" ';
                                    pre = '';
                                }
                                t += '<option value=' + r[i].code + s + '>' + r[i].name + '</option>';
                            }
                            else {
                                var p = parentobj.val();
                                if (p.substring(0, comparelen) === r[i].code.substring(0, comparelen)) {
                                    if (pre !== "" && pre !== 0 && r[i].code === pre) {
                                        s = ' selected=\"selected\" ';
                                        pre = '';
                                    }
                                    t += '<option value=' + r[i].code + s + '>' + r[i].name + '</option>';
                                }
                            }

                        }
                        if (initoption !== '') {
                            targetobj.html(initoption + t);
                        } else {
                            targetobj.html(t);
                        }
                    },
                    "json"
                );
            }
        };
    }();

    citySelector.Init($(".pre_province1"), $(".province1"), $(".pre_city1"), $(".city1"), $(".pre_district1"), $(".district1"));
    citySelector.Init($(".pre_province2"), $(".province2"), $(".pre_city2"), $(".city2"), $(".pre_district2"), $(".district2"));
    citySelector.Init($(".pre_province3"), $(".province3"), $(".pre_city3"), $(".city3"), $(".pre_district3"), $(".district3"));

});