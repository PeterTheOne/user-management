define([
    'backbone'
], function(Backbone) {
    var Session = Backbone.Model.extend({
        url: 'api/index.php/session/'
    });

    return Session;
});