<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class MemberController extends Controller {

    public function getMembers()
    {
        
    }

    public function create()
    {
        return view('pages.member.create');
    }
}