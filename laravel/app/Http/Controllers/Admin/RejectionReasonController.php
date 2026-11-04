<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RejectionReason;
use Illuminate\Http\Request;

/**
 * Готовые причины отказа: список нужен модератору при отказе, редактирует — только админ.
 */
class RejectionReasonController extends Controller
{
    public function index(Request $request)
    {
        // модератору — только включённые; админу (?all=1) — все, чтобы можно было включить обратно
        $all = $request->boolean('all') && $request->user()->hasRole('admin');
        return response()->json(RejectionReason::when(!$all, fn ($q) => $q->where('active', true))->orderBy('sort')->orderBy('id')->get());
    }

    public function store(Request $request)
    {
        $reason = RejectionReason::create($this->validated($request) + ['sort' => (int) RejectionReason::max('sort') + 1]);
        return response()->json($reason, 201);
    }

    public function update(Request $request, int $id)
    {
        $reason = RejectionReason::findOrFail($id);
        $reason->update($this->validated($request, true));
        return response()->json($reason);
    }

    public function destroy(int $id)
    {
        RejectionReason::findOrFail($id)->delete();
        return response()->json(['message' => 'Причина удалена']);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $rule = $partial ? 'sometimes|' : '';
        return $request->validate([
            'title' => $rule . 'required|string|min:3|max:100',
            'text' => $rule . 'required|string|min:5|max:500',
            'title_en' => 'sometimes|nullable|string|max:100',
            'text_en' => 'sometimes|nullable|string|max:500',
            'active' => 'sometimes|boolean',
            'sort' => 'sometimes|integer|min:0|max:1000',
        ], [
            'title.required' => 'Короткое название — его видит модератор в списке',
            'text.required' => 'Текст, который получит владелец',
        ]);
    }
}
