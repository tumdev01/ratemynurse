<?php
namespace App\Services\Member;
use App\Repositories\MemberRepository;

class ActionUpdateProfileService 
{
    public function __construct(
        protected MemberRepository $repository
    ) {}

    public function updateProfile(array $data)
    {
        return $this->repository->update($data);
    }
}