//JavaScript

// creates a new table
var tasks = new Schema.Table;

// sets the table name
tasks.set('name', 'tasks');

// adds a column
tasks.addColumn({
    name: 'title',
    type: 'TEXT',
    constraints: ['NOT NULL']
});

// adds another column
tasks.addColumn({
    name: 'done',
    type: 'INTEGER',
    default: 0
});

// adds one more column
tasks.addColumn({
    name: 'priority',
    type: 'INTEGER',
    default: 5
});

// sends the table schema to server
tasks.save().success(callback);
