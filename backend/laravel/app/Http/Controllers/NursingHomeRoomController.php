<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Repositories\NursingHomeRoomRepository;
use App\Models\NursingHome;
use App\Models\NursingHomeRoom;
use App\Models\NursingHomeRoomImage;
use App\Models\NursingHomeProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Database\QueryException;

class NursingHomeRoomController extends Controller {

    public function index(Int $id, NursingHomeRoomRepository $repo) 
    {
        $nursingHome = NursingHomeProfile::where('id', $id)->first();
        return view('pages.nursinghome.room.index', compact('nursingHome'));
    }

    public function create(Int $user_id)
    {
        $nursingHome = NursingHomeProfile::where('id', $user_id)->first();
        return view('pages.nursinghome.room.create', compact('nursingHome'));
    }

    public function store(Int $id, Request $request, NursingHomeRoomRepository $repo)
    {
        try {
            DB::transaction(function () use ($request) {
                $room = NursingHomeRoom::create([
                    'user_id' => $request->user_id,
                    'name' => $request->name,
                    'type' => $request->type,
                    'description' => $request->description,
                    'cost_per_day' => $request->cost_per_day ?? 0,
                    'cost_per_month' => $request->cost_per_month ?? 0,
                    'active' => 1
                ]);

                if ($room && $room->id) {
                    if ($request->hasFile('images')) {
                        $first = true;

                        foreach ($request->file('images') as $file) {
                            if ($file->isValid()) {
                                $filename = time() . '_' . $file->getClientOriginalName();

                                $sourcePath = $file->getRealPath();

                                $extension = $file->getClientOriginalExtension();
                                $hashedName = md5(uniqid($room->id, true)) . '.' . $extension;
                                $destPath = 'images/' . $hashedName;
                                $destFullPath = public_path($destPath);

                                File::ensureDirectoryExists(dirname($destFullPath));
                                File::copy($sourcePath, $destFullPath);

                                NursingHomeRoomImage::create([
                                    'room_id' => $room->id,
                                    'path' => $destPath,
                                    'fullpath' => $destFullPath,
                                    'filetype' => $file->getClientMimeType(),
                                    'is_cover' => $first,
                                ]);

                                $first = false;
                            }
                        }
                    }
                }
            });
            return redirect()->route('nursing-home.room.index', $id)->with('success', 'บันทึกเรียบร้อยแล้ว');
        } catch (QueryException $e) {
            return redirect()->back()
                ->withInput();
        }
    }

    public function getRoomDataTable(Request $request, NursingHomeRoomRepository $repo) {
        $filters = $request->only(['certified','province','orderby','order']);

        if ($request->has('user_id')) {
            $filters['user_id'] = $request->get('user_id');
        }

        return $repo->getRoomDataTable($filters);
    }
}