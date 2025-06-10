<?php

namespace App\Services;

use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Factory;

class FirebaseAuthService
{
    protected $auth;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('firebase/firebase_credentials.json'));
        $this->auth = $factory->createAuth();
    }

    public function verifyIdToken(string $idToken)
    {
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);
            return $verifiedIdToken;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
