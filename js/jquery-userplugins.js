//выпадающие менюшки
var hel = {e: false, t: false};
$.fn.extend({
    dropdown: function(el) {
        if (typeof(el) == "undefined")
            el = this;
        this.click(function() {
            console.log("dropdown is triggered");
            if (!$(el).is(":visible")) {
                //сначала скрываем чужие открытые блоки
                if (hel.e) {
                    hel.e.hide();
                    hel.e = false;
                    hel.t = false;
                }
                hel.e = $(el);
                hel.t = new Date().getTime();
                $(el).show();
            } else if (hel.e && hel.e.context == $(el).context) {
                hel.e = false;
                hel.t = false;
                $(el).hide();
            }
            return false;
        });
        return this;
    }
});
$(function() {
    $(document).click(function(d) {
        console.log("clicking is triggered");
        //если что-то показано, проверяем, не на его территории произошел клик
        if (hel.e) {
            var offs = hel.e.offset();
            if (!(
                    d.clientY < offs.top + hel.e.width() &&
                    d.clientY > offs.top &&
                    d.clientX < offs.left + hel.e.height() &&
                    d.clientX > offs.left
                    )) {
                var t = new Date().getTime();
                if (t - hel.t > 500) {
                    hel.e.hide();
                    hel.e = false;
                    hel.t = false;
                }
            }
        }
    });
});