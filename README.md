# MySQLi-Database

This is a class that allows you to handle most important aspect of database queries, with basics methods that perform CRUD tasks (Create, Read, Update and Delete)

Also allows you to obtain results in the form of arrays, and create a foreach loop to make them more readable for your users.

## Features

  - Select data form the database
  - Insert new records into your database table
  - Update data with a specific condition
  - Delete desired results from the database

## How to use

this is how to use this class to make some tasks to your database.

```php
$db = Database::getInstance([
    'hostname' => 'localhost',
    'username' => 'homestead',
    'password' => 'secret',
    'database' => 'examples',
]);
```