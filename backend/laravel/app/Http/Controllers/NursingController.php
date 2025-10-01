<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\NursingRepository;
use App\Http\Requests\NursingCreateRequest;
use Illuminate\Database\QueryException;


class NursingController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.nursing.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.nursing.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NursingCreateRequest $request, NursingRepository $repo)
    {
        try {
            $result = $repo->createNurse($request->all());
            return redirect()->route('nursing.index')->with('success', 'บันทึกเรียบร้อยแล้ว');
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return redirect()->back()
                         ->withInput()
                         ->with('error', 'อีเมลนี้มีผู้ใช้งานแล้ว');
            }
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id, NursingRepository $repo)
    {
        $nursing = $repo->getInfo($id);
        return view('pages.nursing.edit', compact('nursing'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getNursingPagination(Request $request, NursingRepository $repo)
    {
        $filters = $request->only(['certified','province','orderby','order']);
        return $repo->getNursingDataTable($filters);
    }
}
