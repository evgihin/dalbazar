/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


$(function() {
    $('.abLeft').click(function() {
        elem = $(this).siblings('.abContainer');
        elem.animate({
            scrollLeft: '-=120',
        }, 100);
            if (window.getSelection) // Not IE, используем метод getSelection
                txt = window.getSelection().removeAllRanges();
        // //scrollLeft(elem.scrollLeft() - 120);
        // alert("It works!"); 
    });
    $('.abRight').click(function() {
        elem = $(this).siblings('.abContainer');
        elem.animate({
            scrollLeft: '+=120',
        }, 100);
        if (window.getSelection) // Not IE, используем метод getSelection
            txt = window.getSelection().removeAllRanges();
    });
    $('.abContainer').bind('mousewheel',function(e,delta){
        $(this).scrollLeft($(this).scrollLeft()-(delta*20));
        return false;
    }
    );
});