<?php
namespace Webiny\Component\Entity\Tests\Lib\Validation;

use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\Tests\Lib\Classes;

class One2Many extends EntityAbstract
{
    protected static $entityCollection = "Validation_One2Many";

    protected function entityStructure()
    {
        $this->attr('char')->char()->setValidators('required');
        $this->attr('entity')->many2one()->setEntity(Classes::ENTITY_VALIDATION);
    }
}