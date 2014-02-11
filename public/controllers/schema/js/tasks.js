//1
var task = new App.Models.Task({
    title: "Go to the store.",
    priority: 4
});

var taskView = new App.Views.Task({ model: task });

console.log( taskView.render().el );

//2
tasksCollection =  new App.Collections.Tasks([{
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
}]);

var tasksView = new App.Views.Tasks({ collection: tasksCollection });
//tasksView.render();
//$(document.body).html(tasksView.el);
tasksView.render().$el.appendTo('#tasks');
//console.log( tasksView.el );

var addTaskView = new App.Views.AddTask({
    el:'#add-task',
    collection: tasksCollection
});
