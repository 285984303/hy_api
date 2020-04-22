!function (a) {
    "function" == typeof define && define.amd ? define(["jquery", "moment"], a) : "object" == typeof exports ? module.exports = a(require("jquery"), require("moment")) : a(jQuery, moment)
}(function (a, b) {
    !function () {
        "use strict";
        var a = (b.defineLocale || b.lang).call(b, "gl", {
            months: "Xaneiro_Febreiro_Marzo_Abril_Maio_Xuño_Xullo_Agosto_Setembro_Outubro_Novembro_Decembro".split("_"),
            monthsShort: "Xan._Feb._Mar._Abr._Mai._Xuñ._Xul._Ago._Set._Out._Nov._Dec.".split("_"),
            monthsParseExact: !0,
            weekdays: "Domingo_Luns_Martes_Mércores_Xoves_Venres_Sábado".split("_"),
            weekdaysShort: "Dom._Lun._Mar._Mér._Xov._Ven._Sáb.".split("_"),
            weekdaysMin: "Do_Lu_Ma_Mé_Xo_Ve_Sá".split("_"),
            weekdaysParseExact: !0,
            longDateFormat: {
                LT: "H:mm",
                LTS: "H:mm:ss",
                L: "DD/MM/YYYY",
                LL: "D MMMM YYYY",
                LLL: "D MMMM YYYY H:mm",
                LLLL: "dddd D MMMM YYYY H:mm"
            },
            calendar: {
                sameDay: function () {
                    return "[hoxe " + (1 !== this.hours() ? "ás" : "á") + "] LT"
                }, nextDay: function () {
                    return "[mañá " + (1 !== this.hours() ? "ás" : "á") + "] LT"
                }, nextWeek: function () {
                    return "dddd [" + (1 !== this.hours() ? "ás" : "a") + "] LT"
                }, lastDay: function () {
                    return "[onte " + (1 !== this.hours() ? "á" : "a") + "] LT"
                }, lastWeek: function () {
                    return "[o] dddd [pasado " + (1 !== this.hours() ? "ás" : "a") + "] LT"
                }, sameElse: "L"
            },
            relativeTime: {
                future: function (a) {
                    return "uns segundos" === a ? "nuns segundos" : "en " + a
                },
                past: "hai %s",
                s: "uns segundos",
                m: "un minuto",
                mm: "%d minutos",
                h: "unha hora",
                hh: "%d horas",
                d: "un día",
                dd: "%d días",
                M: "un mes",
                MM: "%d meses",
                y: "un ano",
                yy: "%d anos"
            },
            ordinalParse: /\d{1,2}º/,
            ordinal: "%dº",
            week: {dow: 1, doy: 7}
        });
        return a
    }(), a.fullCalendar.datepickerLang("gl", "gl", {
        closeText: "Pechar",
        prevText: "&#x3C;Ant",
        nextText: "Seg&#x3E;",
        currentText: "Hoxe",
        monthNames: ["Xaneiro", "Febreiro", "Marzo", "Abril", "Maio", "Xuño", "Xullo", "Agosto", "Setembro", "Outubro", "Novembro", "Decembro"],
        monthNamesShort: ["Xan", "Feb", "Mar", "Abr", "Mai", "Xuñ", "Xul", "Ago", "Set", "Out", "Nov", "Dec"],
        dayNames: ["Domingo", "Luns", "Martes", "Mércores", "Xoves", "Venres", "Sábado"],
        dayNamesShort: ["Dom", "Lun", "Mar", "Mér", "Xov", "Ven", "Sáb"],
        dayNamesMin: ["Do", "Lu", "Ma", "Mé", "Xo", "Ve", "Sá"],
        weekHeader: "Sm",
        dateFormat: "dd/mm/yy",
        firstDay: 1,
        isRTL: !1,
        showMonthAfterYear: !1,
        yearSuffix: ""
    }), a.fullCalendar.lang("gl", {
        buttonText: {month: "Mes", week: "Semana", day: "Día", list: "Axenda"},
        allDayHtml: "Todo<br/>o día",
        eventLimitText: "máis"
    })
});