//JavaScript
// Schema
// requires Situs controller, Backbone 0.9.2

window.Apps = window.Apps || {};

(function(App){
    App.API = Frontgate.location(App.schema.location);
    App.API.auth(App.schema.auth);

    // column
    App.Models.Column = Backbone.Model.extend({
        defaults: App.schema.defaults.column,

        initialize: function(attrs){
            //console.log("Column '"+attrs.name+"' initialized.");

            this.on('error', function(model, error){
                console.log(error);
            });

            this.validateConstraint = function(constraint){
                var constraints = ['PRIMARY KEY', 'UNIQUE', 'NOT NULL'];
                for(var i in constraints){
                    if(constraints[i].toLowerCase() == constraint.toLowerCase()){
                        return true;
                    }
                }
                return false;
            };

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

        validate: function(attrs){
            // Name
            if( ! $.trim(attrs.name) ){
                return "a column requires a valid name.";
            }

            // Type
            if( ! $.trim(attrs.type) ){
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
            if(!valid) return "invalid column type.";

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

            // default
            if(typeof attrs.default != 'undefined') {
                // reserved
            }
        }
    });

    // columns
    App.Collections.Columns = Backbone.Collection.extend({
        model: App.Models.Column
    });

    // table
    App.Models.Table = Backbone.Model.extend({
        defaults: App.schema.defaults.table,
        urlRoot: App.API.href(App.schema.name),

        initialize: function(attrs){
            attrs = attrs || {};

            this.addColumn = function(attrs){
                attrs = attrs || {};
                var columns = this.get('columns');
                if(!columns.find(function(item){
                        return item.get('name') === attrs.name;})) {
                    columns.add(attrs);//new App.Models.Column(attrs));
                }
                //else console.error("Column '"+attrs.name+"' already exists!");
            }

            this.set('columns', new App.Collections.Columns);

            this.validate = function(attrs){
                // Columns
                if(typeof attrs.columns._byCid == 'undefined'){
                    return "Use the addColumn method to add a column.";
                }
            };

            if (App.schema && App.schema.columns) {
                _.forEach(App.schema.columns, this.addColumn, this);
            }

            if(typeof attrs.columns != 'undefined'){
                _.forEach(attrs.columns, this.addColumn, this);
            }
        }
    });

    App.create = function(schema, callback){
        $.ajaxSetup({ beforeSend: App.API.xhrAuth() });
        (new Apps.Schema.Models.Table(schema)).save().success(callback);//{ beforeSend: App.API.xhrAuth() }
    };

    window.Apps.Schema = App;

    if (console && console.info) {
        console.info(App.name, App.version.join("."));
    }
})
({
    "name": "Schema",
    "version": [0,1,0],
    "Models": {},
    "Views": {},
    "Collections": {},
    "Router": {},
    "schema": {
        "name": "schema",
        "auth": {
            "user": "schema",
            "pw": "amehcs"
        },
        "defaults": {
            "table": {
                "PDO": "Sqlite",
                "constraints": []
            },
            "column":{
                "name": 'id',
                "type": 'INTEGER',
                //"constraints": ["PRIMARY KEY"]
            }
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
        ],
        "location": {
            "hostname": "situs.xn--stio-vpa.pt",
            "protocol": "https:"
        }
    }
});
