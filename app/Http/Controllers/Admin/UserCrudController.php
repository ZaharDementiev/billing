<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;

class UserCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as traitUpdate;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup()
    {
        $this->crud->setModel(User::class);
        $this->crud->setEntityNameStrings('Пользователь', 'Пользователи');
        $this->crud->setRoute(backpack_url('users'));
        $this->crud->denyAccess('create');
        $this->crud->denyAccess('update');
    }

    public function setupListOperation()
    {
        $this->crud->setColumns([
            [
                'name' => 'name',
                'label' => 'Имя',
                'type' => 'text',
            ],
            [
                'name' => 'email',
                'label' => 'Почта',
                'type' => 'text',
            ],
            [
                'name' => 'card_token',
                'label' => 'Карточка',
                'type' => 'text',
            ],
            [
                'name' => 'active_follower',
                'label' => 'Подписка активна',
                'type' => 'boolean',
            ],
            [
                'name' => 'next_payment_at',
                'label' => 'Следующая оплата',
                'type' => 'datetime',
            ],
        ]);
    }
}
