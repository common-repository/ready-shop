/**
 * This will control the Signin|Signup Process When Users First Download The Plugin
 * @type {{init}}
 */
var Setup = function($) {


    /**
     * Sign-In | Sign-Up
     * @private
     */
    var _initStep1 = function() {

        $('#step-1').on('click',function(e) {
            $('#readyshop-setup-wizard-container').on('hidden.bs.collapse',function() {
                $('#step-1-content').collapse();
            });
            $('#readyshop-setup-wizard-container').collapse();

        });
    };

    /**
     * Sign-In | Sign-Up
     * @private
     */
    var _initStep2 = function() {

        $('#step-2').on('click',function(e) {
            var step2 = localStorage.getItem('rs_setup_wizard_step_2'); //'completed' || 'not-completed'

            if(step2 === 'completed') {
                $('#readyshop-setup-wizard-container').on('hidden.bs.collapse',function() {
                    $('#step-2-content').collapse();
                });
                $('#readyshop-setup-wizard-container').collapse('hide');
            } else {
                toastr.error('You must add at least 1 product to your store to complete this step.');
            }


        });

        $('#step-2-incomplete').on('click',function(e) {
            toastr.error('Step 1 Needs To Be Completed Before You Can Do Step 2.');
        });

        $('#finish-product-selection').on('click',function(e) {
            $('#step-2-getstarted').hide();
            $('#step-2-completed').show();
            $('#step-2-content').collapse('hide');
            // $('#readyshop-setup-wizard-container').collapse('show');
            $('.finish-setup-row').show();
        })
    };


    var _initFinishRegistration = function() {

        $('.finish-registration').on('click',function() {
            var data = {
                'action': 'readyshop_profile_registration_completion_action',
                'registration': 'complete'
            };

            var option = $(this).attr('data-option');


            $.ajax({
                url: ajaxurl,
                data: data,
                method: 'POST',
                success: function(response) {
                    response = JSON.parse(response);
                    if(response.status == 200) {
                        toastr.success('Successfully Completed Your Registration!');

                        if(option === 'dashboard') {
                            window.location.href = 'admin.php?page=readyshop.home';
                        } else if(option === 'store') {
                            //@todo Need To Make This Dynamic Based On The Slug That's Generated After Creating The SHOP page in-case there are duplicates on the system.
                            window.location.href = '/shop';
                            // window.location.href = 'admin.php?page=readyshop.home';
                            //window.open('/shop','_blank');
                        }
                    } else {
                        toastr.error('Could Not Complete The Registration Process!');
                    }
                }, error: function() {
                    toastr.error('Could Not Complete The Registration Process!');
                }
            });
        });
    };


    /**
     *
     * @constructor
     */
    var Main = function( ){
        _initStep1();
        _initStep2();
        _initFinishRegistration();
    };

    return {
        init: function() {
            Main();
        }
    }


}(jQuery);

/** Initialize Module **/
Setup.init();