<?php

namespace App\Http\Requests;

use App\Enums\ReadingPlanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReadingPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'book_id' => [
                'required',
                'integer',
                'exists:books,id',
                Rule::unique('reading_plans', 'book_id')
                    ->where('user_id', auth()->id())
                    ->whereIn('status', [
                        ReadingPlanStatus::NOT_STARTED->value,
                        ReadingPlanStatus::READING->value,
                        ReadingPlanStatus::OVERDUE->value,
                    ]),
            ],
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください',
            'book_id.exists' => '選択した書籍が存在しません',
            'book_id.unique' => 'この書籍はすでに読書計画に登録されています',

            'target_date.required' => '期日は必須です',
            'target_date.date' => '期日は有効な日付を入力してください',
            'target_date.after_or_equal' => '期日は今日以降の日付を指定してください',
        ];
    }
}
