<?php

namespace App\Service\User;

use Api\Service\Initializers\ApiClientInterface;
use Api\Service\Initializers\ApiClientTrait;
use Opg\Refunds\Caseworker\DataModel\Cases\User as UserModel;

class User implements ApiClientInterface
{
    use ApiClientTrait;

    /**
     * Get user details
     *
     * @param int $userId
     * @return UserModel
     */
    public function getUser(int $userId)
    {
        $userData = $this->getApiClient()->httpGet('/v1/cases/user/' . $userId);

        return $this->createDataModel($userData);
    }

    /**
     * Get all users
     *
     * @return array
     */
    public function getUsers()
    {
        $usersData = $this->getApiClient()->httpGet('/v1/cases/user');

        return $this->createModelCollection($usersData);
    }

    /**
     * Create model from array data
     *
     * @param array|null $data
     * @return null|UserModel
     */
    private function createDataModel(array $data = null)
    {
        if (is_array($data) && !empty($data)) {
            return new UserModel($data);
        }

        return null;
    }

    /**
     * Create a collection (array) of models
     *
     * @param array|null $data
     * @return array
     */
    private function createModelCollection(array $data = null)
    {
        $models = [];

        if (is_array($data)) {
            foreach ($data as $dataItem) {
                $models[] = $this->createDataModel($dataItem);
            }
        };

        return $models;
    }
}