var engine = (function () {
    return {

        init: function () {
            $('#checkAll').on('click', function () {
                engine.checkAll();
            });




            $('#test').on('submit', function (e) {
                e.preventDefault();
                engine.signIn($(this));
            });


        },
        checkAll: function () {
            if($("#checkAll").prop('checked')){
                $(".check").prop('checked', true);
            } else {
                $(".check").prop('checked', false);
            }
        },
        signIn: function (form) {

        }

    }
})();

(function($){
    $.fn.formatNumber = function(options){

        options = $.extend({
            mDec : 0,
            aDec : '.',
            aSep : ' '
        }, options);

        return this.each( function() {
            $(this).val(format($(this).val()));

            $(this).on('keyup', function(e){
                $(this).val(format(e.target.value));
            });

            $(this).on('focusout', function(e) {
                $(this).val(format(e.target.value));
            });

            function format(nStr)
            {
                if (!nStr) {
                    return;
                }
                number = (nStr + '').replace(/[^0-9+\-Ee.]/g, '');
                var n = !isFinite(+number) ? 0 : +number,
                    prec = options.mDec,
                    s = '',
                    toFixedFix = function (n, prec) {
                        var k = Math.pow(10, prec);
                        return '' + Math.round(n * k) / k;
                    };
                s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
                if (s[0].length > 3) {
                    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, options.aSep);
                }
                if ((s[1] || '').length < prec) {
                    s[1] = s[1] || '';
                    s[1] += new Array(prec - s[1].length + 1).join('0');
                }
                return s.join(options.aDec);
            }
        });
    };

}(jQuery));

$(document).ready(function () {
    engine.init();
});