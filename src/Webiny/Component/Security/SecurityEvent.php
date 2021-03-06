<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security;

use Webiny\Component\EventManager\Event;
use Webiny\Component\Security\User\AbstractUser;

/**
 * This class is passed along with the events fired by Security component.
 *
 * @package         Webiny\Component\Security
 */
class SecurityEvent extends Event
{

    // invalid login credentials submitted
    const LOGIN_INVALID = 'wf.security.login_invalid';

    // valid login credentials submitted
    const LOGIN_VALID = 'wf.security.login_valid';

    // user is authenticated, but doesn't have the right role to access the current area
    const ROLE_INVALID = 'wf.security.role_invalid';

    // valid login credentials submitted
    const LOGOUT = 'wf.security.logout';

    // user not authenticated to access the requested area
    const NOT_AUTHENTICATED = 'wf.security.not_authenticated';

    // user is authenticated
    const AUTHENTICATED = 'wf.security.authenticated';

    /**
     * @var User\AbstractUser
     */
    private $user;


    /**
     * Base constructor.
     *
     * @param AbstractUser $user
     */
    public function __construct(AbstractUser $user)
    {
        $this->user = $user;

        parent::__construct();
    }

    /**
     * Returns the instance of current user.
     *
     * @return AbstractUser
     */
    public function getUser()
    {
        return $this->user;
    }
}