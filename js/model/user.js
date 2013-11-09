define([
    'backbone'
], function(Backbone) {
    var User = Backbone.Model.extend({
        url: 'api/index.php/user/'
    });

    return User;
});