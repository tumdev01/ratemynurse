<?php
namespace App\Repositories;
use Illuminate\Support\Arr;
use App\Models\NursingHomeRoom;
use Yajra\DataTables\DataTables;

class NursingHomeRoomRepository  {

public function getRoomDataTable(array $filters = [])
    {
        $user_id = Arr::get($filters, 'user_id');
        $orderby = Arr::get($filters, 'orderby', 'id') ?: 'id';
        $order   = Arr::get($filters, 'order', 'DESC');

        $query = NursingHomeRoom::query()
            ->with([
                'images:room_id,path',
                'coverImage:room_id,path,fullpath,is_cover',
            ])
            ->where('nursing_home_rooms.user_id', $user_id)
            ->whereNull('nursing_home_rooms.deleted_at');

        return DataTables::of($query)
            ->addColumn('cover_image', fn($n) => $n->coverImage ? $n->coverImage->fullpath : '')
            ->rawColumns(['cover_image', 'action'])
            ->orderColumn($orderby, fn($query, $order) => $query->orderBy($orderby, $order))
            ->make(true);
    }
}