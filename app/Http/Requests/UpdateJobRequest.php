<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category_job' => 'sometimes|in:assignment,unschedule',
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('jobs')->ignore($this->job->id)
            ],
            'description' => 'sometimes|string',
            'site' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:255',
            'shift' => 'sometimes|in:pagi,malam',
            'date' => 'sometimes|date',
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
            'crew.*.exists' => 'One or more selected crew members are invalid',
            'code.unique' => 'This job code is already in use',
        ];
    }
}
