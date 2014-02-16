//JavaScript

// creates the tasks schema on the server
Schema.create({
    name:'tasks',
    auth: {
        user: 'tasks',
        pw: 'sksat'
    },
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
});
