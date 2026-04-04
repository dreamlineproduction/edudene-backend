<?php

namespace App\Http\Requests;

use App\Models\SchoolUser;
use Illuminate\Foundation\Http\FormRequest;

class PartnerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

	protected function prepareForValidation()
	{
		$tutorId = auth('sanctum')->user()->id;
		$schoolInfo = SchoolUser::where('user_id',$tutorId)->first();

		$this->merge([
			'school_id' => $schoolInfo->school_id,
			'discount_category' => is_array($this->discount_category)
				? implode(',', $this->discount_category)
				: $this->discount_category,
		]);
	}

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
		
        return [
            'school_id' => 'required|integer|exists:schools,id',
            'name' => 'nullable|string|max:255',
            'type' => 'required|in:Member,Student,Employee,Others',
            'discount_title' => 'nullable|string|max:255',
            'discount_type' => 'required|in:percent,fixed',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_category' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:Active,Inactive',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'school_id.required' => 'School ID is required',
            'school_id.exists' => 'The selected school does not exist',
            'type.required' => 'Partner type is required',
            'type.in' => 'Partner type must be one of: Member, Student, Employee, Others',
            'discount_type.required' => 'Discount type is required',
            'discount_type.in' => 'Discount type must be either percent or fixed',
            'discount_category.required' => 'Discount category is required',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
        ];
    }
}
