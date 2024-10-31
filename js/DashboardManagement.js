/**
 * Created by dbadds on 9/19/17.
 *
 * This class object will be used to control the navigation features of the Dashboard
 */
var DashboardManagement = function($) {

    /**
     * Will set the tooltips to $.onHover();
     * @private
     */
    var _initTooltips = function() {
        $('[data-toggle="tooltip"]').tooltip();
    };

    /**
     *
     * @constructor
     */
    var Main = function() {
        _initTooltips();
    };


    return {
        init: function() {
            Main();
        }
    }
}(jQuery);
DashboardManagement.init();