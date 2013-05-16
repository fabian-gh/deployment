$.noConflict();

jQuery('document').ready(function() {

    jQuery('#checkall').click(function() {
        jQuery('.db_list_normal:checkbox').prop('checked', jQuery(this).prop('checked'));
    });

});