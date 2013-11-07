require.config({
    paths: {
        jquery: 'lib/jquery-1.10.2',
        underscore: 'lib/underscore',
        backbone: 'lib/backbone',
        text: 'lib/text',
        bootstrap: '../bootstrap/dist/js/bootstrap'
    },
    shim: {
        'backbone': {
            deps: ['underscore', 'jquery'],
            exports: 'Backbone'
        },

        'underscore': {
            'exports': '_'
        },

        'bootstrap': {
            deps: ['jquery'],
            exports: 'bootstrap'
        }
    }
});

require([
    'app'
], function(app) {
    app.initialize();
});