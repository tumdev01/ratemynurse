<?php

namespace App\Http\Controllers;
use App\Http\Requests\EmployeeCreateRequest;
use Illuminate\Database\QueryException;

class EmployeeController extends Controller {

    public function index() {
        return view('pages.employee.index');
    }
    
    public function create() {
        return view('pages.employee.create');
    }

    public function store(EmployeeCreateRequest $request) {
        
        try {
            dd($request->all());
        } catch (QueryException $e) {
            dd($e->getMessage());
            if ($e->errorInfo[1] == 1062) {
                return redirect()->back()
                         ->withInput()
                         ->with('error', 'อีเมลนี้มีผู้ใช้งานแล้ว');
            }
            throw $e;
        }
    }
}