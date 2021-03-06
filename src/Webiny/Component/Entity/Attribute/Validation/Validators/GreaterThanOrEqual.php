<?php
namespace Webiny\Component\Entity\Attribute\Validation\Validators;

use Webiny\Component\Entity\Attribute\AbstractAttribute;
use Webiny\Component\Entity\Attribute\Validation\ValidatorInterface;
use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidationTrait;

class GreaterThanOrEqual implements ValidatorInterface
{
    use ValidationTrait;

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'gte';
    }

    /**
     * @inheritDoc
     */
    public function validate(AbstractAttribute $attribute, $data, $params = [])
    {
        try {
            return $this->validation()->validate($data, 'gte:' . $params[0]);
        } catch (ValidationException $e) {
            throw ExceptionFactory::getInstance()->attributeValidationException($attribute, $e);
        }
    }
}