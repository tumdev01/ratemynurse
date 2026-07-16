<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\RateCreateRequest;
use App\Http\Requests\RateProviderRequest;
use App\Models\MemberProfile;
use App\Repositories\RateRepository;

class RateController extends Controller {

    protected $rate_repository;

    public function __construct(RateRepository $rate_repository) {
        $this->rate_repository = $rate_repository;
    }

    public function create(RateCreateRequest $request)
    {
        return $this->rate_repository->create($request->all());
    }

    public function rateProvider(RateProviderRequest $request)
    {
        $author = $request->user();

        $params = $request->validated();
        $params['author_id'] = $author->id;
        $memberProfile       = MemberProfile::where('user_id', $author->id)->first();
        $params['name']      = $memberProfile->name ?? '';
        $params['text']      = $params['description'] ?? '';

        return $this->rate_repository->create($params);
    }

}