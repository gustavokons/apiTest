# API Rest Test in PHP using Slim Framework

### Api Reference
* List of tasks:
    * GET http://gustavokons.com.br/api/tasks
* List a single task:
    * GET http://gustavokons.com.br/api/task/:id
* Delete a task with id:
    * DELETE http://gustavokons.com.br/api/task/:id
* Update informations of a task:
    * PUT http://gustavokons.com.br/api/task
* Insert a task:
    * POST http://gustavokons.com.br/api/task

### EXTRA INFORMATION
**Entity Task**
```
{
   "uuid": "",
   "type": "",
   "content": "",
   "sort_order" : 0,
   "done" : true|false,
   "date_created": ""
}
```
