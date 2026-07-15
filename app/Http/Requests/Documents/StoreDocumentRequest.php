<?php

namespace App\Http\Requests\Documents;

#use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the document upload validation rules.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $allowedExtensions = config(
            'documents.allowed_extensions',
            ['pdf', 'docx', 'txt']
        );

        $maxUploadSize = (int) config(
            'documents.max_upload_size_kb',
            20 * 1024
        );
        return [
            'title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'file' => [
                'required',
                'file',
                'mimes:' . implode(',', $allowedExtensions),
                "max:{$maxUploadSize}",
            ],
        ];
    }
    /**
     * Get Arabic validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.string' => 'يجب أن يكون عنوان الوثيقة نصًا.',
            'title.max' => 'يجب ألا يتجاوز عنوان الوثيقة 255 محرفًا.',

            'file.required' => 'يجب اختيار وثيقة لرفعها.',
            'file.file' => 'الملف المرفوع غير صالح.',
            'file.mimes' => 'يجب أن تكون الوثيقة من نوع PDF أو DOCX أو TXT.',
            'file.max' => 'حجم الوثيقة يتجاوز الحد الأعلى المسموح به.',
        ];
    }

    /**
     * Get readable Arabic attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'عنوان الوثيقة',
            'file' => 'الوثيقة',
        ];
    }
}
