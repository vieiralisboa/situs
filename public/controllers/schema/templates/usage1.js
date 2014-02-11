//JavaScript

// sends the table schema to server
(new Schema.Table({
    name:'tasks',
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
}).save().success(callback);
