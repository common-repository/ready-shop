/**
 * Created by dbadds on 9/15/17.
 * @link https://jqueryvalidation.org/validate/
 * @link https://developer.paypal.com/webapps/developer/docs/classic/products/adaptive-payments/
 */
var SettingsManagement = function($) {
    var debug = true;
    var form, taxForm, paypalForm;


    var _initPaypalIntegration = function( ){
        $('#associate-paypal').on({
            click: function(e) {
                e.preventDefault();
                $('#paypal-email-container').show();
            }
        })
    };

    var _initProfileValidation = function() {
        form = $('#settings-profile');
        form.validate({
            // debug: true,
            rules: {
                firstName: "required",
                lastName: "required",
                // compound rule
                email: {
                    required: true,
                    email: true
                },
                address1: "required",
                //address2: "required",
                city: "required",
                state: "required",
                zipcode: {
                    required: true,
                    digits: true,
                    minlength: 5,
                    maxlength: 5
                },
                password1: "required",
                password2: {
                    required: true,
                    equalTo: "#password2"
                }
            },
            messages: {
                firstName: {
                    required: "We need your first name to contact you."
                },
                zipcode: {
                    required: "We need your zipcode to contact you.",
                    minlength: jQuery.validator.format("At least {0} characters required!")
                },
                email: {
                    required: "We need your email address to contact you"
                }
            },
            submitHandler: function(form) {
                // do other things for a valid form
                // form.submit();

                var data = {
                    'action': 'readyshop_profile_information_management',
                    'firstName': $('#firstName').val(),     // We pass php values differently!
                    'lastName': $('#lastName').val(),
                    'email': $('#email').val(),
                    'address1': $('#address1').val(),
                    'address2': $('#address2').val(),
                    'city': $('#city').val(),
                    'state': $('#state').val(),
                    'zipcode': $('#zipcode').val(),
                    'password1': $('#password1').val(),
                    'password2': $('#password2').val()
                };


                $.ajax({
                    url: ajaxurl,
                    data: data,
                    method: 'POST',
                    success: function(response) {
                        response = JSON.parse(response);
                        if(response.status == 200) {
                            toastr.success('Successfully Updated Your Account Settings!');
                            window.location.reload();
                        } else {
                            toastr.error('Your Account Was Not Able To Be Updated!');
                        }
                    }, error: function() {
                        toastr.error('Your Account Was Not Able To Be Updated!');
                    }
                });

            },
            invalidHandler: function(event, validator) {
                // 'this' refers to the form
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var message = errors == 1
                        ? 'You missed 1 field. It has been highlighted'
                        : 'You missed ' + errors + ' fields. They have been highlighted';
                    $("div.error span").html(message);
                    $("div.error").show();
                } else {
                    $("div.error").hide();
                }
            }
        });

    };


    var _initPaypalFormValidation = function() {
        paypalForm = $('#paypal-payment-form');
        paypalForm.validate({
            rules: {
                paypalEmail: {
                    required: true,
                    email: true
                }
            },
            messages: {
                paypalEmail: {
                    required: "This needs to be a valid Paypal Email Address!"
                }
            },
            submitHandler: function (form) {
                // do other things for a valid form
                // form.submit();
                var data = {
                    'action': 'readyshop_profile_information_management',
                    'paypalEmail': $('#paypalEmail').val()
                };


                $.ajax({
                    url: ajaxurl,
                    data: data,
                    method: 'POST',
                    success: function(response) {
                        response = JSON.parse(response);
                        if(response.status == 200) {
                            toastr.success('Successfully Updated Your Account Settings!');
                            window.location.reload();
                        } else {
                            toastr.error('Your Account Was Not Able To Be Updated!');
                        }
                    }, error: function() {
                        toastr.error('Your Account Was Not Able To Be Updated!');
                    }
                });
            },
            invalidHandler: function (event, validator) {
                // 'this' refers to the form
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var message = errors == 1
                        ? 'You missed 1 field. It has been highlighted'
                        : 'You missed ' + errors + ' fields. They have been highlighted';
                    $("div.error span").html(message);
                    $("div.error").show();
                } else {
                    $("div.error").hide();
                }
            }
        });
    };


    var _initTaxFormValidation = function() {
        taxForm = $('#rs-w9-form');
        taxForm.validate({
            // debug: true,
            rules: {
                fullName: "required",
                businessName: "required",
                // compound rule
                // email: {
                //     required: true,
                //     email: true
                // },
                businessAddress1: "required",
                // businessAddress2: "required",
                businessCity: "required",
                businessState: "required",
                businessZipcode: {
                    required: true,
                    digits: true,
                    minlength: 5,
                    maxlength: 5
                },
                socialSecurity: "required",
                employerIdentificationNumber: "required",
                w9FormCertification: "required"
            },
            messages: {
                zipcode: {
                    required: "We need your zipcode to contact you.",
                    minlength: jQuery.validator.format("At least {0} characters required!")
                }
            },
            submitHandler: function (form) {
                // do other things for a valid form
                // form.submit();



                var data = {
                    'action': 'readyshop_profile_information_management',
                    'fullName': $('#fullName').val(),     // We pass php values differently!
                    'businessName': $('#businessName').val(),
                    'businessAddress1': $('#businessAddress1').val(),
                    'businessAddress2': $('#businessAddress2').val(),
                    'businessCity': $('#businessCity').val(),
                    'businessState': $('#businessState').val(),
                    'businessZipcode': $('#businessZipcode').val(),
                    'socialSecurity': $('#socialSecurity').val(),
                    'employerIdentificationNumber': $('#employerIdentificationNumber').val(),
                    'w9FormCertification': $('#w9FormCertification').val(),
                    'inputTaxClassification': $('#inputTaxClassification').val()
                };


                $.ajax({
                    url: ajaxurl,
                    data: data,
                    method: 'POST',
                    success: function(response) {
                        response = JSON.parse(response);
                        if(response.status == 200) {
                            toastr.success('Successfully Updated Your Account Settings!');
                            window.location.reload();
                        } else {
                            toastr.error('Could not successfully update your account settings!');
                        }
                    }, error: function() {
                        toastr.error('Could not successfully update your account settings!');
                    }
                });

            },
            invalidHandler: function (event, validator) {
                // 'this' refers to the form
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var message = errors == 1
                        ? 'You missed 1 field. It has been highlighted'
                        : 'You missed ' + errors + ' fields. They have been highlighted';
                    $("div.error span").html(message);
                    $("div.error").show();
                } else {
                    $("div.error").hide();
                }
            }
        });
    };


    $('#submit-settings').on({
        click: function(e) {
            e.preventDefault();
            if(debug) {
                console.log(form.submit());
            } else {
                form.submit();
            }
        }
    });

    $('#submit-paypal-email').on({
        click: function(e) {
            e.preventDefault();
            if(debug) {
                console.log(paypalForm.submit());
            } else {
                paypalForm.submit();
            }
        }
    });

    $('#submit-tax-information').on({
        click: function(e) {
            e.preventDefault();
            if(debug) {
                console.log(taxForm.submit());
            } else {
                taxForm.submit();
            }
        }
    });

    var _initAuthForm  = function() {
        var username, password;

        $('#readyshop-login-form').on({
            'click': function(e) {
                var data = {
                    'action': 'readyshop_settings_management_auth_form',
                    'loginEmail': $('#loginEmail').val(),
                    'loginPassword': $('#loginPassword').val(),
                };

                $.ajax({
                    url: ajaxurl,
                    data: data,
                    method: 'POST',
                    success: function(response) {
                        response = JSON.parse(response);
                        if(response.status === 200) {
                            toastr.success('Successfully Logged In!');
                            // window.location.reload();
                            window.location.href = 'admin.php?page=readyshop.home';
                        } else if(response.status === 404) {
                            toastr.error('Username or password incorrect, please try again!');
                        }
                    }, error: function() {
                        toastr.error('Error: Could Not Find The User In Question, Please Try Again!');
                    }
                });
            }
        });

    };

    var _initLogoutAction = function() {
        $('#readyshop-logout-action').on({
            'click': function(e) {
                e.preventDefault();
                var data = {
                    'action': 'readyshop_settings_management_logout_action'
                };

                $.ajax({
                    url: ajaxurl,
                    data: data,
                    method: 'POST',
                    success: function(response) {

                        response = JSON.parse(response);

                        if(response.status === 200) {
                            toastr.success('Successfully Logged Out!');
                            window.location.href = 'admin.php?page=readyshop.setup';
                        }
                        // window.location.reload();
                    }, error: function() {
                        toastr.error('Error: Could Not Log Out, Please Try Again!');
                    }
                });
            }
        });
    };


    /**
     *
     * @constructor
     */
    var Main = function () {
        _initPaypalIntegration();
        _initProfileValidation();
        _initPaypalFormValidation();
        _initTaxFormValidation();
        _initAuthForm();
        _initLogoutAction();
    };

    return {
        init: function() {
            Main();
        }
    }
}(jQuery);

SettingsManagement.init();