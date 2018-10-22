<?php

namespace Meiko\Lumen\Cloud\Auth;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Auth\GenericUser;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;

class JWTGuard implements Guard
{
    use GuardHelpers;

    protected $request;

    /**
     * Build a new instance of JWTGuard
     *
     * @param \Illuminate\Auth\EloquentUserProvider $provider
     * @param \Illuminate\Http\Request $request
     */
    public function __construct(EloquentUserProvider $provider, Request $request)
    {
        $this->request = $request;
        $this->provider = $provider;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if ($this->validate()) {
            return $this->user;
        }

        return null;
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        $token = $this->request->headers->get('Authorization');

        if (empty($token)) {
            return false;
        }

        $publicKey = file_get_contents(config('cloud.public_key'));
        $decoded = JWT::decode(substr($token, 7), $publicKey, config('cloud.encoding'));

        $permissions = $this->decompressPermissions((array) $decoded->user->permissions);

        $user = new User([
            'id' => $decoded->user->id,
            'name' => $decoded->user->name,
            'email' => $decoded->user->email,
            'permissions' => $permissions,
        ]);

        $this->setUser($user);

        return true;
    }

    /**
     * Decompress user permissions
     *
     * @param array $permissions
     * @return void
     */
    protected function decompressPermissions(array $permissions)
    {
        $arr = [];

        foreach ($permissions as $key => $perms) {
            $arr = array_merge($arr, $this->permissionLoop($key, $perms));
        }

        return $arr;
    }

    /**
     * Loop permission children
     *
     * @param string $parent
     * @param mixed $perm
     * @return array
     */
    protected function permissionLoop(string $parent, $permissions)
    {
        if (is_bool($permissions)) {
            return [$parent];
        }

        $arr = [];

        foreach ($permissions as $key => $permission) {
            if (is_array($permission)) {
                $name = $parent . '.' . $key;
                $arr = array_merge($arr, $this->permissionLoop($name, $permission));
            } else {
                $arr[] = $parent . '.' . $permission;
            }
        }

        return $arr;
    }
}
