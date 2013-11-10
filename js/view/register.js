define([
    'backbone',
    'underscore',
    'sjcl',
    'text!template/registerForm.html'
], function(Backbone, _, sjcl, registerTemplate) {
    var RegisterView = Backbone.View.extend({
        initialize: function() {

        },

        render: function() {
            var registerForm = this.$el.html(_.template(registerTemplate));
            var model = this.model;

            // login
            registerForm.find('button.submit').on('click', function(event) {
                event.preventDefault();

                var username = registerForm.find('#username').val();
                var firstname = registerForm.find('#firstname').val();
                var lastname = registerForm.find('#lastname').val();
                var email = registerForm.find('#email').val();
                var password1 = registerForm.find('#password1').val();
                var password2 = registerForm.find('#password2').val();

                var errors = [];
                if (username.length === 0) {
                    errors.push('username is not set.');
                } else if (username.length < 3) {
                    errors.push('username is to short, needs to be at least 3 characters long.');
                } else if (username.length > 30) {
                    errors.push('username is to long, needs to be under 30 characters long.');
                }
                if (firstname.length === 0) {
                    errors.push('firstname is not set.');
                } else if (firstname.length < 3) {
                    errors.push('firstname is to short, needs to be at least 3 characters long.');
                } else if (firstname.length > 30) {
                    errors.push('firstname is to long, needs to be under 30 characters long.');
                }
                if (lastname.length === 0) {
                    errors.push('lastname is not set.');
                } else if (lastname.length < 3) {
                    errors.push('lastname is to short, needs to be at least 3 characters long.');
                } else if (lastname.length > 30) {
                    errors.push('lastname is to long, needs to be under 30 characters long.');
                }
                if (email.length == 0) {
                    errors.push('email is not set.');
                }

                // todo: username exclude names that are used for navigation
                // todo: regex type mail

                if (password1.length === 0) {
                    errors.push('password1 is not set.');
                } else if (password1.length < 8) {
                    errors.push('password1 is to short, needs to be at least 8 characters long.');
                }
                if (password2.length === 0) {
                    errors.push('password2 is not set.');
                } else if (password1 !== password2) {
                    errors.push('password1 and password2 are not the same.');
                }

                // this hash is not secure because of the lack of a salt value,
                // but at least the password will never be readable in plaintext
                var passwordBitArray = sjcl.hash.sha256.hash(password1);
                var password = sjcl.codec.hex.fromBits(passwordBitArray);

                // todo: add captcha?

                if (errors.length > 0) {
                    for (var i = 0; i < errors.length; i++) {
                        alert(errors[i]);
                    }
                } else {
                    model.save({
                        username: username,
                        firstname: firstname,
                        lastname: lastname,
                        email: email,
                        password: password
                    });
                }
            });
        }
    });

    return RegisterView;
});