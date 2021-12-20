<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SettingCrudRequest;
use App\Models\Setting;
use Backpack\CRUD\app\Http\Controllers\CrudController;

class SettingCrudController extends CrudController
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
        $this->crud->setModel(Setting::class);
        $this->crud->setEntityNameStrings('Настройка', 'Настройки');
        $this->crud->setRoute(backpack_url('settings'));
        $this->crud->denyAccess('create');
        $this->crud->denyAccess('delete');
    }

    public function setupListOperation()
    {
        $this->crud->setColumns([
            [
                'name' => 'name',
                'label' => 'Имя настройки',
                'type' => 'model_function',
                'function_name' => 'settingName',
                'limit' => 1000,
            ],
            [
                'name' => 'value',
                'label' => 'Значение',
                'type' => 'text',
            ],
        ]);
    }

    public function setupUpdateOperation()
    {
        $this->addSettingFields();
        $this->crud->setValidation(SettingCrudRequest::class);
    }

    public function update()
    {
        $this->crud->setRequest($this->crud->validateRequest());
        $this->crud->unsetValidation(); // validation has already been run

        return $this->traitUpdate();
    }


    protected function addSettingFields()
    {
        $this->crud->addFields([
            [
                'name' => 'value',
                'label' => 'Значение',
                'type' => 'text',
            ],
        ]);
    }
}
