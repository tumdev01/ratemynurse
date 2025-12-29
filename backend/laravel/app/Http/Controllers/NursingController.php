<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repositories\NursingRepository;
use App\Http\Requests\NursingCreateRequest;
use App\Http\Requests\NursingUpdateRequest;
use App\Http\Requests\NursingHistoryStoreRequest;
use App\Http\Requests\NursingDetailStoreRequest;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
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

    public function update(
        NursingUpdateRequest $request,
        NursingRepository $repo,
        int $id
    ) {
        $repo->updateNurse($request, $id);

        return redirect()->back()->with('success', 'บันทึกเรียบร้อยแล้ว');
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

    public function historyStore(
        NursingHistoryStoreRequest $request,
        NursingRepository $repo,
        int $id
    ) {
        $repo->createOrUpdateHistory(
            $request->validated(),
            $request->hasFile('cvs_images') ? $request->file('cvs_images') : null, // เปลี่ยนเป็น cvs_images
            $id
        );

        return redirect()->back()->with('success', 'บันทึกเรียบร้อยแล้ว');
    }


    public function detailView(Int $id, NursingRepository $repo)
    {
        $nursing = $repo->getInfo($id);
        return view('pages.nursing.detail', compact('nursing'));
    }

    public function detailStore(
        NursingDetailStoreRequest $request,
        NursingRepository $repo,
        int $id
    ) {
        $repo->createOrUpdateDetail(
            $request->validated(),
            $request->hasFile('detail_images') ? $request->file('detail_images') : null,
            $id
        );

        return redirect()->back()->with('success', 'บันทึกเรียบร้อยแล้ว');
    }

    public function costView(Int $id, NursingRepository $repo)
    {
        $nursing = $repo->getInfo($id);
        $costs = NursingCost::where('user_id', $id)->get();
        return view('pages.nursing.cost', compact('nursing', 'costs'));
    }

    public function updateCost(int $id, Request $request)
    {
        /* ================= VALIDATION ================= */

        $rules = [];
        $messages = [];

        $types = ['DAILY', 'MONTH'];
        $hireRules = ['FULL_ROUND', 'FULL_STAY', 'PART_ROUND', 'PART_STAY'];

        foreach ($types as $type) {
            foreach ($hireRules as $rule) {
                $rules["{$type}.{$rule}"] = 'required|numeric|min:0';
                $messages["{$type}.{$rule}.required"] = 'กรุณากรอกราคาให้ครบ';
                $messages["{$type}.{$rule}.numeric"]  = 'ราคาต้องเป็นตัวเลข';
                $messages["{$type}.{$rule}.min"]      = 'ราคาต้องไม่น้อยกว่า 0';
            }
        }

        Validator::make($request->all(), $rules, $messages)->validate();

        /* ================= SAVE ================= */

        DB::transaction(function () use ($request, $id, $types, $hireRules) {

            foreach ($types as $type) {

                $name = $type === 'DAILY' ? 'รายวัน' : 'รายเดือน';

                foreach ($hireRules as $rule) {

                    NursingCost::updateOrCreate(
                        [
                            'user_id'   => $id,
                            'type'      => $type,
                            'hire_rule' => $rule,
                        ],
                        [
                            'name'        => $name,
                            'cost'        => (float) $request->{$type}[$rule],
                            'description' => null,
                        ]
                    );
                }
            }
        });

        return redirect()->back()->with('success', 'บันทึกค่าบริการเรียบร้อยแล้ว');
    }

    public function rateView()
    {
        return view('pages.nursing.rate');
    }
}
