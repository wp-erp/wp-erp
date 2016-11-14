! function(a) {
    "function" == typeof define && define.amd ? define(["jquery", "moment"], a) : a(jQuery, moment)
}(function(a, b) {
    (b.defineLocale || b.lang).call(b, "bn", {
        months: "জানুয়ারি_ফেব্রুয়ারি_মার্চ_এপ্রিল_মে_জুন_জুলাই_আগস্ট_সেপ্টেম্বর_অক্টোবর_নভেম্বর_ডিসেম্বর".split("_"),
        monthsShort: "জানু_ফেব্রু_মার্চ_এপ্রি_মে_জুন_জুল_আগ_সেপ_অক্টো_নভে_ডিসে".split("_"),
        weekdays: "রবিবার_সোমবার_মঙ্গলবার_বুধবার_বৃহস্পতিবার_শুক্রবার_শনিবার".split("_"),
        weekdaysShort: "রবি_সোম_মঙ্গল_বুধ_বৃহঃ_শুক্র_শনি".split("_"),
        weekdaysMin: "র_সো_ম_বু_বৃ_শু_শ".split("_"),
        longDateFormat: {
            LT: "HH:mm",
            LTS: "LT:ss",
            L: "DD/MM/YYYY",
            LL: "D MMMM YYYY",
            LLL: "D MMMM YYYY LT",
            LLLL: "dddd D MMMM YYYY LT"
        },
        calendar: {
            sameDay: "[আজ] LT",
            nextDay: "[আগামীকাল] LT",
            nextWeek: "dddd [থেকে] LT",
            lastDay: "[গতকাল] LT",
            lastWeek: "dddd [গত সপ্তাহে] LT",
            sameElse: "L"
        },
        relativeTime: {
            future: "%s এর মধ্যে",
            past: "%s আগে",
            s: "এক সেকেন্ড",
            m: "এক মিনিট",
            mm: "%d মিনিট",
            h: "এক ঘণ্টা",
            hh: "%d ঘণ্টা",
            d: "এক দিন",
            dd: "%d দিন",
            M: "এক মাস",
            MM: "%d মাস",
            y: "এক বছর",
            yy: "%d বছর"
        },
        ordinalParse: /\d{1,2}(er|)/,
        ordinal: function(a) {
            return a + (1 === a ? "er" : "")
        },
        week: {
            dow: 1,
            doy: 4
        }
    }), a.fullCalendar.datepickerLang("bn", "bn", {
        closeText: "বন্ধ",
        prevText: "পিছনে",
        nextText: "সামনে",
        currentText: "আজকে",
        monthNames: ["জানুয়ারি", "ফেব্রুয়ারি", "মার্চ", "এপ্রিল", "মে", "জুন", "জুলাই", "আগস্ট", "সেপ্টেম্বর", "অক্টোবর", "নভেম্বর", "ডিসেম্বর"],
        monthNamesShort: ["জানু", "ফেব্রু", "মার্চ", "এপ্রি", "মে", "জুন", "জুল", "আগ", "সেপ", "অক্টো", "নভে", "ডিসে"],
        dayNames: ["রবিবার", "সোমবার", "মঙ্গলবার", "বুধবার", "বৃহস্পতিবার", "শুক্রবার", "শনিবার"],
        dayNamesShort: ["রবি", "সোম", "মঙ্গল", "বুধ", "বৃহঃ", "শুক্র", "শনি"],
        dayNamesMin: ["র", "স", "ম", "বু", "বৃ", "শু", "শ"],
        weekHeader: "Sem.",
        dateFormat: "dd/mm/yy",
        firstDay: 1,
        isRTL: !1,
        showMonthAfterYear: !1,
        yearSuffix: ""
    }), a.fullCalendar.lang("bn", {
        buttonText: {
            month: "মাস",
            week: "সপ্তাহ",
            day: "দিন",
            list: "লিস্ট"
        },
        allDayHtml: "সারাদিন",
        eventLimitText: "আরও দেখুন"
    })
});
