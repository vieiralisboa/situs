//JavaScript
// requires situs.Schema, Backbone 0.9.2

window.Apps = Apps || {};

(function(App){
    // Tasks controller's location
    App.API = Frontgate.location(App.location);
    App.API.auth(App.schema.auth || Frontgate.location());

    // create the Tasks controler on Situs server ( tasks schema )
    Apps.Schema.create(App.schema, function(data, message, XMLHR){
        if (data === 1) {
            console.log("CREATED SITUS CONTROLER tasks");
        }

        if (message == "success") {
            console.info("TASKS API OK");
        }
        else {
            console.log("TASKS API NOK");
        }

        if (message == "success") {
            App.ready.status = data;
            while(App.ready.stack.length) {
                //console.log("Tasks from stack");
                (App.ready.stack.shift())();
            }
        }
        else {
            throw "Error! RESTfull API for Tasks is not ready.";
        }
    });

    // task model
    App.Models.Task = Backbone.Model.extend({
        validate: function(attrs){
            if ( !$.trim(attrs.title) ) {
                return "a task requires a valid title.";
            }
        },
        urlRoot: App.API.href(App.schema.name),//"//situs.dev/tasks",
        defaults: {
            priority: 0
        }
    });

    // tasks collection
    App.Collections.Tasks = Backbone.Collection.extend({
        model: App.Models.Task,
        url: App.API.href(App.schema.name),//"//situs.dev/tasks",
        initialize: function(){
            //console.log('initializing a Tasks collection');
            $.ajaxSetup({
                beforeSend: App.API.xhrAuth()
            });
            this.fetch();
        }
    });

    // task view
    App.Views.Task = Backbone.View.extend({
        tagName: 'li',
        template: function(data){
            return App.template(data);
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
                $.ajaxSetup({
                    beforeSend: App.API.xhrAuth()
                });
                this.model.save();
                //console.log('editing title: '+taskTitle);
            }
            //this.render();
        },
        deleteTask: function(e){
            $.ajaxSetup({
                beforeSend: App.API.xhrAuth()
            });
            this.model.destroy();
            //console.log(tasks);
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
    App.Views.Tasks = Backbone.View.extend({
        tagName: 'ul',
        initialize: function(){
            this.collection.on('add', this.addOne, this);
        },
        render: function(){
            this.collection.each(this.addOne, this);
            return this;
        },
        addOne: function(task){
            var taskView = new App.Views.Task({model: task});
            this.$el.append( taskView.render().el );
        }
    });

    //
    App.Views.AddTask = Backbone.View.extend({
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

            var task = new App.Models.Task({
                title: taskTitle
            });

            $.ajaxSetup({
                beforeSend: App.API.xhrAuth()
            });
            task.save();

            this.collection.add(task);

            //console.log(task);
            //return false;
        }
    });

    // starts the tasks app
    App.start = function(tasksViewSelector, addTasksSelector, templateSelector){
        this.template = (function(id){
            return _.template( $(id).html() );
        })(templateSelector);

        Apps.Tasks.ready(function(){
            // create a tasks collection
            var tasksCollection =  new Apps.Tasks.Collections.Tasks;

            //TODO render tasks view without REfetching tasks
            //console.log("FETCH TASKS FROM SERVER");

            $.ajaxSetup({
                beforeSend: Apps.Tasks.API.xhrAuth()
            });

            // fetch tasks from server
            tasksCollection.fetch().success(function(){
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
            });
        });
    };

    // adds to the 'when ready' stack
    App.ready = function(f){
        if (this.ready.status) {
            f();
        }
        else {
            this.ready.stack.push(f);
        }
    };
    App.ready.status = 0;
    App.ready.stack = [];

    window.Apps.Tasks = App;

    if (window.console && console.info) {
        console.info(App.name, App.version.join("."));
    }
})
({
    "name": "Tasks",
    "version": [0, 1, 0],
    "Models": {},
    "Collections": {},
    "Views": {},
    "location": {
        "hostname": "situs.xn--stio-vpa.pt",
        "protocol": "https:"
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
