<?php

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;

class ProfileService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function updateProfile(int $userId, array $data)
    {
        return $this->userRepository->update($userId, $data);
    }

    public function changePassword(int $userId, string $newPassword)
    {
        return $this->userRepository->update($userId, [
            'password' => bcrypt($newPassword)
        ]);
    }
}
