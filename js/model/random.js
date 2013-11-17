define([
    'backbone'
], function(Backbone) {
    var Random = Backbone.Model.extend({
        url: 'api/index.php/randomRequest/'
    });

    return Random;
});