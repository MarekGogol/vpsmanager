<?php

namespace Gogol\VpsManager\Admin\Buttons;

use Gogol\Admin\Helpers\Button;
use Gogol\Admin\Models\Model as AdminModel;

class CreateDomainButton extends Button
{
    /*
     * Here is your place for binding button properties for each row
     */
    public function __construct(AdminModel $row)
    {
        //Name of button on hover
        $this->name = 'Vytvoriť doménu na servery';

        //Button classes
        $this->class = 'btn-success';

        //Button Icon
        $this->icon = 'fa-plus';
    }

    /*
     * Firing callback on press button
     */
    public function fire(AdminModel $row)
    {

        dd(vpsManager()->nginx());

        return $this->success('Dómena bola úspešne vytvorená.');
    }
}