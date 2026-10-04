<?php

namespace Database\Seeders;

use App\Enums\TodoStatus;
use App\Models\Backend\Todo;
use Illuminate\Database\Seeder;

class TodoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $todo                = new Todo();
        $todo->title         = 'Todo List 1';
        $todo->date          = '2022-07-22';
        $todo->user_id       = 1;
        $todo->description   = 'Todo list 1';

        $todo->note          = 'Lorem ipsum note';
        $todo->status        = TodoStatus::PENDING;
        $todo->save();

        $todo                = new Todo();
        $todo->title         = 'Todo List 2';
        $todo->date          = '2022-07-22';
        $todo->user_id       = 1;
        $todo->description   = 'Todo list 2';

        $todo->note          = 'Lorem ipsum note';
        $todo->status        = TodoStatus::PROCESSING;
        $todo->save();

        $todo                = new Todo();
        $todo->title         = 'Todo List 3';
        $todo->date          = '2022-07-22';
        $todo->user_id       = 2;
        $todo->description   = 'Todo list 3';

        $todo->note          = 'Lorem ipsum note';
        $todo->status        = TodoStatus::PROCESSING;
        $todo->save();


        $todo                = new Todo();
        $todo->title         = 'Todo List 4';
        $todo->date          = '2022-07-22';
        $todo->user_id       = 1;
        $todo->description   = 'Todo list 4';

        $todo->note          = 'Lorem ipsum note';
        $todo->status        = TodoStatus::COMPLETED;
        $todo->save();

        $todo                = new Todo();
        $todo->title         = 'Todo List 5';
        $todo->date          = '2022-07-22';
        $todo->user_id       = 2;
        $todo->description   = 'Todo list 5';

        $todo->note          = 'Lorem ipsum note';
        $todo->status        = TodoStatus::COMPLETED;
        $todo->save();

        $todo                = new Todo();
        $todo->title         = 'Todo List 6';
        $todo->date          = '2022-07-22';
        $todo->user_id       = 1;
        $todo->description   = 'Todo list 6';

        $todo->note          = 'Lorem ipsum note';
        $todo->status        = TodoStatus::COMPLETED;
        $todo->save();
    }
}
