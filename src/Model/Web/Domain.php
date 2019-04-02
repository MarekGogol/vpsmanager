<?php

namespace Gogol\VpsManager\Model\Web;

use Gogol\Admin\Fields\Group;
use Gogol\Admin\Models\Model as AdminModel;
use Gogol\VpsManager\Admin\Buttons\CreateDomainButton;

class Domain extends AdminModel
{
    /*
     * Model created date, for ordering tables in database and in user interface
     */
    protected $migration_date = '2019-03-26 20:10:40';

    /*
     * Template name
     */
    protected $name = 'Zoznam domén';

    protected $publishable = false;

    /*
     * Automatic form and database generation
     * @name - field name
     * @placeholder - field placeholder
     * @type - field type | string/text/editor/select/integer/decimal/file/password/date/datetime/time/checkbox/radio
     * ... other validation methods from laravel
     */
    protected $fields = [
        'name' => 'name:Názov webu|required|max:30',
    ];

    protected $buttons = [
        CreateDomainButton::class,
    ];

}