!function (a) {
    "function" == typeof define && define.amd ? define(["jquery", "moment"], a) : "object" == typeof exports ? module.exports = a(require("jquery"), require("moment")) : a(jQuery, moment)
}(function (a, b) {
    !function () {
        "use strict";
        function a(a, b, c, d) {
            var e = {
                m: ["eng Minutt", "enger Minutt"],
                h: ["eng Stonn", "enger Stonn"],
                d: ["een Dag", "engem Dag"],
                M: ["ee Mount", "engem Mount"],
                y: ["ee Joer", "engem Joer"]
            };
            return b ? e[c][0] : e[c][1]
        }

        function c(a) {
            var b = a.substr(0, a.indexOf(" "));
            return e(b) ? "a " + a : "an " + a
        }

        function d(a) {
            var b = a.substr(0, a.indexOf(" "));
            return e(b) ? "viru " + a : "virun " + a
        }

        function e(a) {
            if (a = parseInt(a, 10), isNaN(a))return !1;
            if (0 > a)return !0;
            if (10 > a)return a >= 4 && 7 >= a;
            if (100 > a) {
                var b = a % 10, c = a / 10;
                return e(0 === b ? c : b)
            }
            if (1e4 > a) {
                for (; a >= 10;)a /= 10;
                return e(a)
            }
            return a /= 1e3, e(a)
        }

        var f = (b.defineLocale || b.lang).call(b, "lb", {
            months: "Januar_Februar_Mäerz_Abrëll_Mee_Juni_Juli_August_September_Oktober_November_Dezember".split("_"),
            monthsShort: "Jan._Febr._Mrz._Abr._Mee_Jun._Jul._Aug._Sept._Okt._Nov._Dez.".split("_"),
            monthsParseExact: !0,
            weekdays: "Sonndeg_Méindeg_Dënschdeg_Mëttwoch_Donneschdeg_Freideg_Samschdeg".split("_"),
            weekdaysShort: "So._Mé._Dë._Më._Do._Fr._Sa.".split("_"),
            weekdaysMin: "So_Mé_Dë_Më_Do_Fr_Sa".split("_"),
            weekdaysParseExact: !0,
            longDateFormat: {
                LT: "H:mm [Auer]",
                LTS: "H:mm:ss [Auer]",
                L: "DD.MM.YYYY",
                LL: "D. MMMM YYYY",
                LLL: "D. MMMM YYYY H:mm [Auer]",
                LLLL: "dddd, D. MMMM YYYY H:mm [Auer]"
            },
            calendar: {
                sameDay: "[Haut um] LT",
                sameElse: "L",
                nextDay: "[Muer um] LT",
                nextWeek: "dddd [um] LT",
                lastDay: "[Gëschter um] LT",
                lastWeek: function () {
                    switch (this.day()) {
                        case 2:
                        case 4:
                            return "[Leschten] dddd [um] LT";
                        default:
                            return "[Leschte] dddd [um] LT"
                    }
                }
            },
            relativeTime: {
                future: c,
                past: d,
                s: "e puer Sekonnen",
                m: a,
                mm: "%d Minutten",
                h: a,
                hh: "%d Stonnen",
                d: a,
                dd: "%d Deeg",
                M: a,
                MM: "%d Méint",
                y: a,
                yy: "%d Joer"
            },
            ordinalParse: /\d{1,2}\./,
            ordinal: "%d.",
            week: {dow: 1, doy: 4}
        });
        return f
    }(), a.fullCalendar.datepickerLang("lb", "lb", {
        closeText: "Fäerdeg",
        prevText: "Zréck",
        nextText: "Weider",
        currentText: "Haut",
        monthNames: ["Januar", "Februar", "Mäerz", "Abrëll", "Mee", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
        monthNamesShort: ["Jan", "Feb", "Mäe", "Abr", "Mee", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"],
        dayNames: ["Sonndeg", "Méindeg", "Dënschdeg", "Mëttwoch", "Donneschdeg", "Freideg", "Samschdeg"],
        dayNamesShort: ["Son", "Méi", "Dën", "Mët", "Don", "Fre", "Sam"],
        dayNamesMin: ["So", "Mé", "Dë", "Më", "Do", "Fr", "Sa"],
        weekHeader: "W",
        dateFormat: "dd.mm.yy",
        firstDay: 1,
        isRTL: !1,
        showMonthAfterYear: !1,
        yearSuffix: ""
    }), a.fullCalendar.lang("lb", {
        buttonText: {month: "Mount", week: "Woch", day: "Dag", list: "Terminiwwersiicht"},
        allDayText: "Ganzen Dag",
        eventLimitText: "méi"
    })
});