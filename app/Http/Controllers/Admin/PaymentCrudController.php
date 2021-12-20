<?php

namespace App\Http\Controllers\Admin;

use App\Models\Payment;
use Backpack\CRUD\app\Http\Controllers\CrudController;

class PaymentCrudController extends CrudController
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
        $this->crud->setModel(Payment::class);
        $this->crud->setEntityNameStrings('Платежь', 'Платежи');
        $this->crud->setRoute(backpack_url('payments'));
        $this->crud->denyAccess('create');
        $this->crud->denyAccess('update');
    }

    public function setupListOperation()
    {
        $this->crud->setColumns([
            [
                'name' => 'userId',
                'label' => 'Пользователь',
                'type' => 'model_function',
                'function_name' => 'userContact',
                'limit' => 1000,
            ],
            [
                'name' => 'amount',
                'label' => 'Сумма',
                'type' => 'number',
            ],
            [
                'name' => 'status_name',
                'label' => 'Статус',
                'type' => 'model_function',
                'function_name' => 'statusName',
                'limit' => 1000,
            ],
            [
                'name' => 'uuid',
                'label' => 'ID платежа',
                'type' => 'text',
            ],
        ]);
    }
}
