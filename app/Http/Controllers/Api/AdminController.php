<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkingRule;

class AdminController extends Controller
{
    // POST /api/admin/rules
    public function createRule(Request $request)
    {
        $payload = $request->validate([
            'weekday' => 'required|integer|min:0|max:6',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s|after:start_time',
            'slot_interval' => 'nullable|integer|min:5|max:240'
        ]);

        $rule = WorkingRule::create($payload);
        return response()->json(['rule' => $rule], 201);
    }

    // GET /api/admin/rules
    public function listRules()
    {
        return response()->json(WorkingRule::orderBy('weekday')->get());
    }

    // DELETE /api/admin/rules/{id}
    public function deleteRule($id)
    {
        $rule = WorkingRule::findOrFail($id);
        $rule->delete();
        return response()->json(['message' => 'deleted']);
    }
}
