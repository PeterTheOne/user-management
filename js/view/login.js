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
                var password = loginForm.find('#password').val();
                var remember = loginForm.find('#remember:checked').length > 0;

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