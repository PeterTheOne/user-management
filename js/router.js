define([
    'jquery',
    'underscore',
    'backbone',
    'model/session',
    'view/login'
], function($, _, Backbone, Session, LoginView) {
    var AppRouter = Backbone.Router.extend({
        routes: {
            '': 'index'
        },

        index: function() {
            var $el = $('.content');
            $el.html('<h1>user-management</h1>');

            var session = new Session();
            var loginView = new LoginView({model: session});
            loginView.render();
            $el.append(loginView.$el);
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