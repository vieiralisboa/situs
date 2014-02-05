//JavaScript
// Situs Schema
// requires Situs controller and Backbone

window.Apps = window.Apps || {};

(function(App){
	var SCHEMA = "schema",
		SERVER = "https://xn--stio-vpa.pt/",
		URL_ROOT = SERVER+SCHEMA
		user = "schema",
		pw = "amehcs";

	//$.ajaxSetup({ beforeSend: Frontgate.xhrAuth(user, pw) });

	App.Models.Column = Backbone.Model.extend({
		defaults: {
			name: 'id',
			type: 'INTEGER',
			//constraints: ['PRIMARY KEY']
		},

		initialize: function(attrs){
			console.log("Column '"+attrs.name+"' initialized.");

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
			/*
			 * Name
			 */
			if( ! $.trim(attrs.name) ){
				return "a column requires a valid name.";
			}

			/*
			 * Type
			 */
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

			/*
			 * Constraints
			 */
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

			/*
			 * Default
			 */
			if(typeof attrs.default != 'undefined') {
				// reserved
			}
		}
	});

	App.Collections.Columns = Backbone.Collection.extend({
		model: App.Models.Column
	});

	/**
	 *
	 */
	App.Models.Table = Backbone.Model.extend({
		defaults: {
			PDO: "Sqlite",
			auth: { usr: "tasks", pw:"tasks"},
			constraints: []
		},

		urlRoot: URL_ROOT,

		initialize: function(attrs){
			attrs = attrs || {};

			this.addColumn = function(attrs){
				attrs = attrs || {};
				var columns = this.get('columns');
				if(!columns.find(function(item){
						return item.get('name') === attrs.name;})) {
					columns.add(attrs);//new App.Models.Column(attrs));
				}
				else console.log("Column '"+attrs.name+"' already exists!");
			}

			this.set('columns', new App.Collections.Columns);

			this.validate = function(attrs){
				/*
				 * Columns
				 */
				if(typeof attrs.columns._byCid == 'undefined'){
					return "Use the addColumn method to add a column.";
				}
			};

			this.addColumn({name: 'id', type: 'INTEGER', constraints:['PRIMARY KEY']});
			this.addColumn({name: 'date_created', type: 'TEXT', default: 'CURRENT_TIMESTAMP'});
			this.addColumn({name: 'last_updated', type: 'TEXT', default: 'CURRENT_TIMESTAMP'});

			if(typeof attrs.columns != 'undefined'){
				_.forEach(attrs.columns, this.addColumn, this);
			}
		}
	});

	if(console && console.info)
		console.info(App.name, App.version.join("."));
	window.Apps.Schema = App;
	Apps.Schema.create = function(schema, callback){
		(new Apps.Schema.Models.Table(schema)).save({
			beforeSend: Frontgate.xhrAuth(user, pw)
		}).success(callback);
	};
})({
	name: "Schema",
	version: [0,0,0],
	Models: {},
	Views: {},
	Collections: {},
	Router: {}
});
