//JavaScript
// requires situs.Schema, Backbone 1.1.0

window.Apps = window.Apps || {};

(function(app){

    // Tasks controller's location
    app.API = Frontgate.location(app.location);
    app.API.auth(app.schema.auth || Frontgate.location());

    // create the Tasks controler on Situs server ( tasks schema )
    Schema.create(app.schema, function(model, response, options){

        if(response === true || response === 1){
            if(console && console.info) {
                if(response === 1){
                    console.info("NEW API FROM SCHEMA");
                }
                console.info(app.schema.name, "API OK");
            }
            app.ready.status = 1;
        }

        if(app.ready.status){
            while(app.ready.stack.length){
                (app.ready.stack.shift())();
            }
        }
        else throw "TASKS API NOK";

    });

    // task model
    app.Models.Task = Backbone.Model.extend({
        validate: function(attrs){
            if ( !$.trim(attrs.title) ) {
                return "a task requires a valid title.";
            }
        },
        urlRoot: app.API.url(app.schema.name),
        defaults: {
            priority: 0
        }
    });

    // tasks collection
    app.Collections.Tasks = Backbone.Collection.extend({
        model: app.Models.Task,
        url: app.API.url(app.schema.name),
        initialize: function(){
            //console.log('initializing a Tasks collection');
            this.fetch({
                beforeSend: app.API.xhrAuth()
            });
        }
    });

    // task view
    app.Views.Task = Backbone.View.extend({
        tagName: 'li',
        template: function(data){
            return app.template(data);
        },
        initialize: function(){
            //console.log(this.model.toJSON());
            this.model.on('change', this.render, this);
            this.model.on('destroy', this.remove, this);
        },
        events: {
            'click .edit': 'editTask',
            'click .delete': 'deleteTask'
        },
        editTask: function(e){
            var taskTitle = prompt('Edit Task', this.model.get('title'));
            if(taskTitle) {
                this.model.set('title', taskTitle);
                this.model.save(null, {
                    beforeSend: app.API.xhrAuth()
                });
                //console.log('editing title: '+taskTitle);
            }
            //this.render();
        },
        deleteTask: function(e){
            this.model.destroy({
                beforeSend: app.API.xhrAuth()
            });
        },
        remove: function(){
            this.$el.remove();
        },
        render: function(){
            //console.log( this.template(this.model.toJSON()) );
            this.$el.html( this.template(this.model.toJSON()) );
            return this;
        }
    });

    // tasks view
    app.Views.Tasks = Backbone.View.extend({
        tagName: 'ul',
        initialize: function(){
            this.collection.on('add', this.addOne, this);
        },
        render: function(){
            this.collection.each(this.addOne, this);
            return this;
        },
        addOne: function(task){
            var taskView = new app.Views.Task({model: task});
            this.$el.append( taskView.render().el );
        }
    });

    //
    app.Views.AddTask = Backbone.View.extend({
        //el: '#add-task',
        events: {
            'submit': 'submit'
        },
        initialize: function(){
           //console.log("AddTask initializing...");
           //console.log(this.el.innerHTML);
           //console.log("...AddTask initialized.");
        },
        submit: function(e){
            e.preventDefault();
            //var taskTitle = $(e.currentTarget).find('input[type=text]').val();
            var taskTitle =  prompt('Add Task', "");

            if (!taskTitle) {
                return false;
            }

            var task = new app.Models.Task({
                title: taskTitle
            });

            task.save(null, {
                beforeSend: app.API.xhrAuth()
            });

            this.collection.add(task);

            //console.log(task);
            //return false;
        }
    });

    // starts the tasks app
    app.start = function(tasksViewSelector, addTasksSelector, templateSelector){
        this.template = (function(id){
            return _.template( $(id).html() );
        })(templateSelector);

        Apps.Tasks.ready(function(){
            // create a tasks collection
            var tasksCollection =  new Apps.Tasks.Collections.Tasks;

            //TODO render tasks view without REfetching tasks
            //console.log("FETCH TASKS FROM SERVER");

            // fetch tasks from server
            tasksCollection.fetch({
                beforeSend: Apps.Tasks.API.xhrAuth(),
                success: function(){
                    //console.log("FETCH TASKS FROM SERVER RESULT: ", arguments[1]);

                    // create the 'tasks' view
                    var tasksView = new Apps.Tasks.Views.Tasks({
                        collection: tasksCollection
                    });

                    tasksView.render().$el.appendTo(tasksViewSelector);

                    // create the 'add task' view
                    var addTaskView = new Apps.Tasks.Views.AddTask({
                        el: addTasksSelector,
                        collection: tasksCollection
                    });
                }
            });
        });
    };

    // adds to the 'when ready' stack
    app.ready = function(f){
        if (this.ready.status) {
            f();
        }
        else {
            this.ready.stack.push(f);
        }
    };
    app.ready.status = 0;
    app.ready.stack = [];

    window.Apps.Tasks = app;

    if (window.console && console.info) {
        console.info(app.name, app.version.join("."));
    }
})
({
    "name": "Tasks",
    "version": [0, 2, 0],
    "Models": {},
    "Collections": {},
    "Views": {},
    "location": {
        "hostname": "situs.xn--stio-vpa.pt",
        "protocol": "https",
        //"pathname": "/tasks"
    },
    "schema": {
        "name": "tasks",
        "auth": {
            "user":"tasks",
            "pw":"sksat"
        },
        "columns": [
            {
                "name": "title",
                "type": "TEXT",
                "constraints": ["NOT NULL"]
            },
            {
                "name": "done",
                "type": "INTEGER",
                "default": 0
            },
            {
                "name": "priority",
                "type": "INTEGER",
                "default": 5
            }
        ]
    },
    "template": null
});
