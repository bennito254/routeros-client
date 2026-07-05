<?php

declare(strict_types=1);

namespace Bennito254\RouterOS\Facade;

use Bennito254\RouterOS\Query\Query;
use Bennito254\RouterOS\Response\ResponseCollection;

/**
 * Facade for managing RouterOS administration users and groups.
 *
 * API Path: /user
 */
class RouterUser extends AbstractFacade
{
    protected function getBaseCommand(): string
    {
        return '/user';
    }

    /**
     * Get list of all router administrative users.
     *
     * @return ResponseCollection
     */
    public function getUsers(): ResponseCollection
    {
        return $this->getAll();
    }

    /**
     * Add a new administrative user to the router.
     *
     * @param string $name User's login name.
     * @param string $group Group / privilege level (e.g. read, write, full).
     * @param string|null $password User's password.
     * @param array $extra Additional attributes.
     * @return ResponseCollection
     */
    public function addUser(string $name, string $group, ?string $password = null, array $extra = []): ResponseCollection
    {
        $data = array_merge([
            'name' => $name,
            'group' => $group,
        ], $extra);

        if ($password !== null) {
            $data['password'] = $password;
        }

        return $this->add($data);
    }

    /**
     * Change password for an existing administrative user.
     *
     * @param string $id RouterOS internal ID or user name.
     * @param string $password New password.
     * @return ResponseCollection
     */
    public function changePassword(string $id, string $password): ResponseCollection
    {
        return $this->set($id, ['password' => $password]);
    }
}
