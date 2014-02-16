//JavaScript

// creates a new table
var schema = new Schema;

// sets the table name
schema.set('name', 'tasks');

// authorization
schema.set('auth', {
    user: 'tasks',
    pw: 'sksat'
});

// adds a column
schema.addColumn({
    name: 'title',
    type: 'TEXT',
    constraints: ['NOT NULL']
});

// adds another column
schema.addColumn({
    name: 'done',
    type: 'INTEGER',
    default: 0
});

// adds one more column
schema.addColumn({
    name: 'priority',
    type: 'INTEGER',
    default: 5
});

// sends the schema to server
schema.save();
