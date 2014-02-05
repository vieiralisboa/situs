//JavaScript
// requires situs.Schema

window.Apps = Apps || {};

window.template = function(id){
    return _.template( $(id).html() );
};

(function(App){
    var SCHEMA = "tasks",
        SERVER = "https://xn--stio-vpa.pt/",//"//localhost/";
        URL_ROOT = SERVER+SCHEMA,
        user = "tasks",
        pw = "sksat";

    //$.ajaxSetup(auth);

    //-------------------------------------------------------------------------
    App.ready = function(f){
        if(this.ready.status) f();
        else this.ready.stack.push(f);
    };
    App.ready.status = 0;
    App.ready.stack = [];
    //-------------------------------------------------------------------------

    // create the 'tasks' schema
    // creates a Situs controler and SQLite table 'tasks'
    Apps.Schema.create({
        name: SCHEMA,
        columns: [
            {
                name: 'title',
                type: 'TEXT',
                constraints: ['NOT NULL']
            },
            {
                name: 'done',
                type: 'INTEGER',
                default: 0
            },
            {
                name: 'priority',
                type: 'INTEGER',
                default: 5
            }
        ]
    }, function(data, message, XMLHR){

        if(data === 1) console.log("CREATED SITUS CONTROLER tasks");

        if(message == "success") console.log("Tasks API OK");
        else console.log("Tasks API Failed");

        if(message == "success"){
            App.ready.status = data;
            while(App.ready.stack.length) {
                console.log("Tasks from stack");
                (App.ready.stack.shift())();
            }
        }
        else throw "Error! RESTfull API for Tasks is not ready.";
    });

    App.Models.Task = Backbone.Model.extend({
        validate: function(attrs){
            if( ! $.trim(attrs.title) ){
                return "a task requires a valid title.";
            }
        },

        urlRoot: URL_ROOT,//"//situs.dev/tasks",

        defaults: {
            priority: 0
        }
    });

    App.Collections.Tasks = Backbone.Collection.extend({
        model: App.Models.Task,
        url: URL_ROOT,//"//situs.dev/tasks",
        initialize: function(){
            console.log('initializing a Tasks collection');
            this.fetch({
                beforeSend: Frontgate.xhrAuth(user, pw)
            });
        }
    });

    App.Views.Task = Backbone.View.extend({
        tagName: 'li',

        template: function(data){
             //template('#taskTemplate')
            return App.template(data);
        },//template('#taskTemplate'),

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
                this.model.save({
                    beforeSend: Frontgate.xhrAuth(user, pw)
                });
                //console.log('editing title: '+taskTitle);
            }

            //this.render();
        },

        deleteTask: function(e){
            this.model.destroy({
                beforeSend: Frontgate.xhrAuth(user, pw)
            });
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

    App.Views.AddTask = Backbone.View.extend({
        //el: '#add-task',

        events: {
            'submit': 'submit'
        },

        initialize: function(){
           console.log("AddTask initializing...");
           console.log(this.el.innerHTML);
           console.log("...AddTask initialized.");
        },

        submit: function(e){
            e.preventDefault();
            //var taskTitle = $(e.currentTarget).find('input[type=text]').val();
            var taskTitle =  prompt('Add Task', "");

            if(!taskTitle) return false;

            //console.log(taskTitle);

            var task = new App.Models.Task({
                title: taskTitle
            });
            task.save({
                beforeSend: Frontgate.xhrAuth(user, pw)
            });
            this.collection.add(task);

            //console.log(task);
            //return false;
        }
    });

    window.Apps.Tasks = App;
    console.info("Tasks 0.0.1");

/* 1
    var task = new App.Models.Task({
        title: "Go to the store.",
        priority: 4
    });

    var taskView = new App.Views.Task({ model: task });

    console.log( taskView.render().el );
//*/

/* 2
    tasksCollection =  new App.Collections.Tasks([
        {
            title: "Go to the store.",
            priority: 4
        },
        {
            title: "Read a book.",
            priority: 5
        },
        {
            title: "Practice guitar.",
            priority: 3
        },
        {
            title: "Study Linear Algebra.",
            priority: 3
        }
    ]);

    var tasksView = new App.Views.Tasks({ collection: tasksCollection });
    //tasksView.render();
    //$(document.body).html(tasksView.el);
    tasksView.render().$el.appendTo('#tasks');
    //console.log( tasksView.el );

    var addTaskView = new App.Views.AddTask({
        el:'#add-task',
        collection: tasksCollection
    });
//*/

})({
    Models: {},
    Collections: {},
    Views: {},
    app: function(tasksViewSelector, addTasksSelector, templateSelector){
        this.template = template(templateSelector);

        Apps.Tasks.ready(function(){
            // create a tasks collection
            var tasksCollection =  new Apps.Tasks.Collections.Tasks;

            //TODO render tasks view without REfetching tasks
            /*/
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
            /*/
            //console.log("FETCH TASKS FROM SERVER");
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
            //*/
        });
    },
    template: null
});
