public function index()
{
    $users = DB::table('users')->get()->keyBy('id');

    $reports = [];

    $emissions = DB::table('emissions')->get();
    foreach ($emissions as $e) {
        $reports[] = [
            'user_id' => $e->user_id,
            'user_name' => $users[$e->user_id]->name ?? 'Unknown',
            'type' => 'Emission',
            'co2' => $e->total_co2 ?? 0,
            'waste' => 0,
            'water' => 0,
            'created_at' => $e->created_at,
            'status' => $e->status ?? 'pending',
        ];
    }

    $wastes = DB::table('wastes')->get();
    foreach ($wastes as $w) {
        $reports[] = [
            'user_id' => $w->user_id,
            'user_name' => $users[$w->user_id]->name ?? 'Unknown',
            'type' => 'Waste',
            'co2' => 0,
            'waste' => $w->total_waste ?? 0,
            'water' => 0,
            'created_at' => $w->created_at,
            'status' => $w->status ?? 'pending',
        ];
    }

    $resources = DB::table('resource_usages')->get();
    foreach ($resources as $r) {
        $reports[] = [
            'user_id' => $r->user_id,
            'user_name' => $users[$r->user_id]->name ?? 'Unknown',
            'type' => 'Resource Usage',
            'co2' => 0,
            'waste' => 0,
            'water' => $r->water_used ?? 0,
            'created_at' => $r->created_at,
            'status' => $r->status ?? 'pending',
        ];
    }

    return response()->json(['reports' => $reports]);
}
