<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\NursingRepository;
use App\Http\Requests\NursingCreateRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Models\NursingCost;


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

    public function historyView(Int $id, NursingRepository $repo)
    {
        $nursing = $repo->getInfo($id);
        return view('pages.nursing.history', compact('nursing'));
    }

    public function detailView(Int $id, NursingRepository $repo)
    {
        $nursing = $repo->getInfo($id);
        return view('pages.nursing.detail', compact('nursing'));
    }

    public function costView(Int $id, NursingRepository $repo)
    {
        $nursing = $repo->getInfo($id);
        $costs = NursingCost::where('user_id', $id)->get();
        return view('pages.nursing.cost', compact('nursing', 'costs'));
    }

    public function updateCost(Int $id, Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'type'          => 'required|in:DAY,MONTH',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'cost_per_day'  => 'nullable|numeric|min:0',
            'cost_per_month'=> 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput(); // ส่งค่าเก่ากลับไปด้วย
        }

        $cost = NursingCost::updateOrCreate(
            [
                'user_id' => $id,
                'type'    => $request->type, // มีแค่ DAILY หรือ MONTHLY
            ],
            [
                'name'           => $request->name,
                'description'    => $request->description,
                'cost_per_day'   => $request->cost_per_day ?? 0,
                'cost_per_month' => $request->cost_per_month ?? 0,
            ]
        );

        return redirect()->back()->with('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function rateView()
    {
        return view('pages.nursing.rate');
    }
}
