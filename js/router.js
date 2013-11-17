define([
    'jquery',
    'underscore',
    'backbone',
    'model/user',
    'model/session',
    'view/register',
    'view/login',
    'model/random'
], function($, _, Backbone, User, session, RegisterView, LoginView, Random) {
    var AppRouter = Backbone.Router.extend({
        routes: {
            '': 'index',
            'register(/)': 'register',
            'login(/)': 'login',
            'randomRequest(/)': 'randomRequest'
        },

        index: function() {
            this.register();

        },

        register: function() {
            var $el = $('.content');
            $el.html('<h1>user-management</h1>');

            var user = new User();
            var registerView = new RegisterView({model: user});
            registerView.render();
            $el.append(registerView.$el);
        },

        login: function() {
            var $el = $('.content');
            $el.html('<h1>user-management</h1>');

            var loginView = new LoginView({model: session});
            loginView.render();
            $el.append(loginView.$el);
        },

        randomRequest: function() {
            var $el = $('.content');
            $el.html('<h1>user-management</h1>');
            $el.html('<h2>user-management</h2>');

            var random = new Random();
            random.fetch();
        }
    });

    var initialize = function() {
        var appRouter = new AppRouter();

        // Extend the View class to include a navigation method goTo
        Backbone.View.prototype.navigate = function (location) {
            appRouter.navigate(location, true);
        };

        Backbone.history.start({});
    };

    return {
        initialize: initialize
    };
});