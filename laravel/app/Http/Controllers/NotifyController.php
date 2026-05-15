<?php

namespace App\Http\Controllers;

use App\Models\NotifyMe;
use Illuminate\Http\Request;

class NotifyController extends Controller
{
    public function notifyMe(Request $request)
    {
        $user = $request->user();
        // $data = $request->validate([
        //     'user_id' => 'required|integer',
        //     // 'filters' => '';
        // ]);
        $notify =  NotifyMe::create([
            'user_id'=>$user->id,
            'filters' => json_encode($request->filters)
        ]);
        return response()->json([
            'success' => true,
            'message' => 'Notification preferences saved successfully.',
            'data' => $notify
        ]);
    }
}
