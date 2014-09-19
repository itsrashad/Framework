<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Mocks;

use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\Encoder\Encoder;
use Webiny\Component\Security\User\UserAbstract;

/**
 * User mock.
 *
 * @package         Webiny\Component\Security\Tests\Mocks
 */
class UserMock extends UserAbstract
{

    /**
     * This method verifies the credentials of current user with the credentials provided from the Login object.
     *
     * @param Login   $login
     * @param Encoder $encoder
     *
     * @throws \Exception
     * @return bool Return true if credentials are valid, otherwise return false.
     */
    public function authenticate(Login $login, Encoder $encoder)
    {
        try {
            $result = $encoder->verifyPasswordHash($login->getPassword(), $this->getPassword());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $result;
    }
}