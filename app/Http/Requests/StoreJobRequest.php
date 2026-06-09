<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category_job' => 'required|in:assignment,unschedule',
            'code' => 'nullable|string|max:255|unique:jobs,code',
            'description' => 'required|string',
            'site' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'shift' => 'required|in:pagi,malam',
            'date' => 'required|date',
            'status' => 'sometimes|in:open,continue,closed,outstanding,cancel',
            'urgency' => 'sometimes|in:low,medium,high',
            'crew' => 'nullable|array',
            'crew.*' => 'exists:users,id',
            'sarana' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'category_job.required' => 'Job type is required',
            'description.required' => 'Description is required',
            'site.required' => 'Site is required',
            'category.required' => 'Category is required',
            'shift.required' => 'Shift is required',
            'date.required' => 'Date is required',
            'crew.*.exists' => 'One or more selected crew members are invalid',
        ];
    }
}
