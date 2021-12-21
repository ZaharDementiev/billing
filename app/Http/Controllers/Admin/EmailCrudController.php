<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\EmailCrudRequest;
use App\Models\Email;
use Backpack\CRUD\app\Http\Controllers\CrudController;

class EmailCrudController extends CrudController
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
        $this->crud->setModel(Email::class);
        $this->crud->setEntityNameStrings('Письмо', 'Письма');
        $this->crud->setRoute(backpack_url('emails'));
    }

    public function setupListOperation()
    {
        $this->crud->setColumns([
            [
                'name' => 'week',
                'label' => 'Неделя',
                'type' => 'number',
            ],
            [
                'name' => 'subject',
                'label' => 'Тема',
                'type' => 'text',
            ],
            [
                'name'     => 'attachments',
                'label'    => 'Вложения',
                'type'     => 'text',
            ],
        ]);
    }

    public function setupCreateOperation()
    {
        $this->addEmailFields();
        $this->crud->setValidation(EmailCrudRequest::class);
    }

    public function setupUpdateOperation()
    {
        $this->addEmailFields();
        $this->crud->setValidation(EmailCrudRequest::class);
    }

    public function store()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $this->crud->unsetValidation(); // validation has already been run

        return $this->traitStore();
    }

    public function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $this->crud->unsetValidation(); // validation has already been run

        return $this->traitUpdate();
    }


    protected function addEmailFields()
    {
        $this->crud->addFields([
            [
                'name' => 'week',
                'label' => 'Неделя',
                'type' => 'number',
            ],
            [
                'name' => 'subject',
                'label' => 'Тема',
                'type' => 'text',
            ],
            [
                'name'  => 'attachments',
                'label' => 'Вложения',
                'type'  => 'ckeditor',

                // optional:
                'options'       => [
                    'autoGrow_minHeight'   => 200,
                    'autoGrow_bottomSpace' => 50,
                    'removePlugins'        => 'resize,maximize',
                ]
            ],
        ]);
    }
}
