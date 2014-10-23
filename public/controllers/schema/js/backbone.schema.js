//JavaScript
// Schema
// requires Situs controller, Backbone 1.1.0

(function(app){

    app.API = Frontgate.location(app.location);
    app.API.auth(app.schema.auth);

    // column model constructor
    Column = Backbone.Model.extend({
        defaults: app.schema.defaults_column,

        initialize: function(attrs){

            this.on('error', function(model, error){
                console.log(error);
            });

            // validates a constraint
            this.validateConstraint = function(constraint){
                var constraints = ['PRIMARY KEY', 'UNIQUE', 'NOT NULL'];
                for(var i in constraints){
                    if(constraints[i].toLowerCase() == constraint.toLowerCase()){
                        return true;
                    }
                }
                return false;
            };

            // adds a constraint to the constraints array
            this.addConstraint = function(constraint){
                var constraints = this.get('constraints');

                // constraint already exists?
                for(var i in constraints){
                    //console.log(constraints[i].toLowerCase()+":"+constraint.toLowerCase());
                    if(constraints[i].toLowerCase() == constraint.toLowerCase()){
                        return this;
                    }
                }

                // valid constraint?
                if(this.validateConstraint(constraint)){
                    constraints[constraints.length] = constraint;
                    this.set('constraints', constraints);
                }

                return this;
            };
        },

        // validates column attributes
        validate: function(attrs){
            // Name
            if( !$.trim(attrs.name) ){
                return "a column requires a valid name.";
            }

            // Type
            if( !$.trim(attrs.type) ){
                return "a column requires a valid type.";
            }
            var types = ['Text', 'Numeric', 'Integer', 'Float', 'None'];
            var valid = false;
            for(var i in types) {
                if(types[i].toLowerCase() == attrs.type.toLowerCase()) {
                    valid = true;
                    break;
                }
            }

            if(!valid) {
                return "invalid column type.";
            }

            // Constraints
            if(typeof attrs.constraints != 'undefined') {
                if(!$.isArray(attrs.constraints)){
                    return "Contraints must be an array.";
                }
                for(var k in attrs.constraints){
                    if(!this.validateConstraint(attrs.constraints[k])){
                        return "Invalid column constraint!";
                    }
                }
            }
        }
    }),

    // table (column collection) constructor
    Table = Backbone.Collection.extend({
        model: Column
    }),

    // schema
    Schema = Backbone.Model.extend({
        defaults: app.schema.defaults,
        urlRoot: app.API.url(),

        initialize: function(attrs){
            attrs = attrs || {};

            // create table (column collection)
            var table = this.set('table', new Table).get('table');

            // adds a column to the column collection
            this.addColumn = function(attrs){
                var column = table.find(function(col){
                    return col.get('name') === attrs.name;
                });

                if(!column){
                    table.add(attrs);
                }
                else {
                    var error = "column name '{name}' already exists";
                    throw error.replace('{name}', attrs.name);
                }
            }

            // validates schema attributes
            this.validate = function(attrs){
                //TODO validate columns
            };

            // create default columns
            if(app.schema && app.schema.columns){
                _.forEach(app.schema.columns, this.addColumn, this);
            }

            // create custom columns
            if(typeof attrs.columns != 'undefined'){
                _.forEach(attrs.columns, this.addColumn, this);
            }
        }
    });

    // creates a schema (for the Situs controller)*
    Schema.create = function(schema, callback){
        (new Schema(schema)).save(null, {
            beforeSend: app.API.xhrAuth(),
            success: callback,
            error: function(){
                console.error(arguments);
            }
        });
    };

    // put Schema on the global object
    window.Schema = Schema;

    // use credentials for Situs Schema controller
    //API.auth(app.schema.auth);

    Schema.VERSION = app.version.join(".");

    if(console && console.info){
        console.info(app.name, Schema.VERSION);
    }
})
({
    "name": "Schema",
    "version": [0,2,0],
    "Router": {},
    "location": {
        "hostname": "situs.pt",
        "protocol": "https",
        "pathname": "/schema"
    },
    "schema": {
        "name": "schema",
        "auth": {
            "user": "schema",
            "pw": "amehcs"
        },
        "defaults": {
            "PDO": "Sqlite",
            "constraints": []
        },
        "defaults_column":{
            "name": 'id',
            "type": 'INTEGER',
            //"constraints": ["PRIMARY KEY"]
        },
        "columns": [
            {
                "name": "id",
                "type": "INTEGER",
                "constraints":["PRIMARY KEY"]
            },
            {
                "name": "date_created",
                "type": "TEXT",
                "default": "CURRENT_TIMESTAMP"
            },
            {
                "name": "last_updated",
                "type": "TEXT",
                "default": "CURRENT_TIMESTAMP"
            }
        ]
    }
});
