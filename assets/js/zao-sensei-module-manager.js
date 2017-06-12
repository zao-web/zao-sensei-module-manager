jQuery(document).ready( function($) {

/**************************************************************/
/* Prepares the cv to be dynamically expandable/collapsible   */
/**************************************************************/
$(function prepareList() {
    //Initially assign all to be collapsed
    $('.expList2').closest('ul')
        .addClass('collapsed')
        .children('li').hide();

    // Toggle between collapsed/expanded per module
    $('.expList').unbind('click').click(function(event) {
        if(this == event.target) {
            $(this).parents('.module-lessons').find('.expList2').toggleClass('collapsed expanded');
            $(this).parents('.module-lessons').find('.expList2').children('li').toggle('medium');
            $(this).parent().find('.tog-mod').toggleClass('fa-chevron-down fa-chevron-up');
        }
        return false;
    });

    //Hack to add links inside the cv
    $('.expList2 a').unbind('click').click(function() {
        window.open($(this).attr('href'),'_self');
        return false;
    });
    //Hack to add links inside the cv
    $('.expList a').unbind('click').click(function() {
        window.open($(this).attr('href'),'_self');
        return false;
    });

    //Create the expand/collapse all button funtionality
    $('.expandList')
        .unbind('click')
        .click( function() {
            $('.collapsed').parents('.module-lessons').find('.tog-mod').addClass('fa-chevron-up').removeClass('fa-chevron-down');
            $('.collapsed').addClass('expanded').removeClass('collapsed');
            $('.expanded').children('li').show('medium');

        })
    $('.collapseList')
        .unbind('click')
        .click( function() {
            $('.expanded').parents('.module-lessons').find('.tog-mod').addClass('fa-chevron-down').removeClass('fa-chevron-up');
            $('.expanded').removeClass('expanded').addClass('collapsed');
            $('.collapsed').children('li').hide('medium');

        })

})


/**************************************************************/
/* Functions to execute on loading the document               */
/**************************************************************/
document.addEventListener('DOMContentLoaded', function() {
    prepareList();
}, false)

});
