<?php

namespace Meiko\Lumen\Cloud\Auth;

use Illuminate\Auth\GenericUser;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends GenericUser implements AuthenticatableContract, AuthorizableContract
{
    use Authorizable;

    /**
     * Return the name of unique identifier for the user (e.g. "id")
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Return the unique identifier for the user (e.g. their ID, 123)
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->id;
    }

    /**
     * Returns the (hashed) password for the user
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return null;
    }

    /**
     * Return the token used for the "remember me" functionality
     *
     * @return string
     */
    public function getRememberToken()
    {
        return null;
    }

    /**
     * Store a new token user for the "remember me" functionality
     *
     * @param string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        //
    }

    /**
     * Return the name of the column / attribute used to store the "remember me" token
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return null;
    }

    /**
     * Check for permission
     *
     * @param mixed $permissions
     * @param boolean $requireAll
     * @return boolean
     */
    public function hasPermission($permissions, bool $requireAll = true)
    {
        $permissions = (is_array($permissions)) ? $permissions : [$permissions];

        foreach ($permissions as $permissionName) {
            $result = in_array($permissionName, $this->permissions);

            if ($requireAll && !$result) {
                return false;
            } elseif (!$requireAll && $result) {
                return true;
            }
        }

        return $requireAll;
    }
}
