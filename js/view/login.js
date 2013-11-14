define([
    'backbone',
    'underscore',
    'text!template/loginForm.html'
], function(Backbone, _, loginTemplate) {
    var LoginView = Backbone.View.extend({
        initialize: function() {

        },

        render: function() {
            var loginForm = this.$el.html(_.template(loginTemplate));
            var model = this.model;

            // login
            loginForm.find('button.submit').on('click', function(event) {
                event.preventDefault();

                var username = loginForm.find('#username').val();
                var plainPassword = loginForm.find('#password').val();
                var remember = loginForm.find('#remember:checked').length > 0;

                // todo: validate, are all fields empty?


                // this hash is not secure because of the lack of a salt value,
                // but at least the password will never be readable in plaintext
                var passwordBitArray = sjcl.hash.sha256.hash(plainPassword);
                var password = sjcl.codec.hex.fromBits(passwordBitArray);

                model.save({
                    username: username,
                    password: password,
                    remember: remember
                });
            });
        }
    });

    return LoginView;
});