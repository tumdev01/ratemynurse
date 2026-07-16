<?php

namespace App\Services\NursingHome;
use App\Models\User;
use App\Repositories\NursingHomeRepository;
use Illuminate\Support\Arr;
use App\Http\Resources\NursingHomeProfileListResource;

class NursingHomeService {
    public function __construct(
        protected NursingHomeRepository $repository
    ) {}
    public function getProfile(User $user, int $profileId)
    {
        return $this->repository->getProfileById(
            $user->id,
            $profileId
        );
    }

    public function updateGeneralProfile(User $user, Array $inputs)
    {
        return $this->repository->updateGeneralProfile($user->id, $inputs);
    }

    public function updateAboutProfile(User $user, array $inputs, array $files = [], array $staffAvatars = [])
    {
        return $this->repository->updateAboutProfile($user->id, $inputs, $files, $staffAvatars);
    }

    public function updateMoreInfoProfile(User $user, array $inputs, array $files = [])
    {
        return $this->repository->updateMoreInfoProfile($user->id, $inputs, $files);
    }

    public function getCollections($filters)
    {
        $profiles = $this->repository->getProfileByCollections($filters);
        return NursingHomeProfileListResource::collection($profiles);
    }
}