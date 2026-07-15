<?php

namespace App\Services\Documents\Validation;

use App\Models\Document;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Validates and normalizes the document-processing response returned
 * by the external AI service.
 *
 * This validator is intentionally separated from Laravel Form Requests
 * because the validated data does not originate from an incoming HTTP
 * request made by the end user. Instead, it is returned by an external
 * FastAPI service.
 */
final class ProcessDocumentResponseValidator
{
    /**
     * Validate the document-processing response.
     *
     * The response is expected to contain:
     *
     * - The same document ID that was sent to FastAPI.
     * - A valid number of document pages.
     * - The Qdrant collection name.
     * - At least one processed chunk.
     * - A unique sequential index for every chunk.
     * - A unique Qdrant vector ID for every chunk.
     *
     * @param  Document  $document  The document that was sent for processing.
     * @param  array<string, mixed>  $response  The response returned by FastAPI.
     * @return array{
     *     document_id: int,
     *     total_pages: int,
     *     qdrant_collection: string,
     *     chunks: array<int, array{
     *         chunk_index: int,
     *         content: string,
     *         page_number?: int|null,
     *         vector_id: string,
     *         metadata?: array<string, mixed>|null
     *     }>
     * }
     *
     * @throws ValidationException
     */
    public function validate(
        Document $document,
        array $response
    ): array {
        $validator = Validator::make(
            $response,
            $this->rules($document),
            $this->messages(),
            $this->attributes()
        );

        /*
         * Additional cross-field validation.
         *
         * Laravel's basic validation rules are excellent for validating
         * individual fields, but some rules require comparing multiple
         * values together, such as verifying that a chunk's page number
         * does not exceed the document's total page count.
         */
        $validator->after(
            function ($validator) use ($response): void {
                $this->validatePageNumbers(
                    validator: $validator,
                    response: $response
                );

                $this->validateSequentialChunkIndexes(
                    validator: $validator,
                    response: $response
                );
            }
        );

        /** @var array{
         *     document_id: int,
         *     total_pages: int,
         *     qdrant_collection: string,
         *     chunks: array<int, array{
         *         chunk_index: int,
         *         content: string,
         *         page_number?: int|null,
         *         vector_id: string,
         *         metadata?: array<string, mixed>|null
         *     }>
         * } $validated
         */
        $validated = $validator->validate();

        return $validated;
    }

    /**
     * Get the validation rules for the FastAPI response.
     *
     * @return array<string, array<int, mixed>>
     */
    private function rules(Document $document): array
    {
        $maxChunks = (int) config(
            'services.ai_services.max_chunks_per_document',
            100000
        );

        return [
            'document_id' => [
                'required',
                'integer',
                Rule::in([$document->getKey()]),
            ],

            'total_pages' => [
                'required',
                'integer',
                'min:1',
            ],

            'qdrant_collection' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],

            'chunks' => [
                'required',
                'array',
                'min:1',
                "max:{$maxChunks}",
            ],

            'chunks.*' => [
                'required',
                'array',
            ],

            'chunks.*.chunk_index' => [
                'required',
                'integer',
                'min:0',
                'distinct:strict',
            ],

            'chunks.*.content' => [
                'required',
                'string',
                'min:1',
            ],

            'chunks.*.page_number' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'chunks.*.vector_id' => [
                'required',
                'string',
                'min:1',
                'max:255',
                'distinct:strict',
            ],

            'chunks.*.metadata' => [
                'nullable',
                'array',
            ],
        ];
    }

    /**
     * Verify that every chunk page number is within the document range.
     *
     * For example, if total_pages is 10, a page_number of 15 must be
     * rejected because it cannot belong to the processed document.
     *
     * @param  array<string, mixed>  $response
     */
    private function validatePageNumbers(
        \Illuminate\Validation\Validator $validator,
        array $response
    ): void {
        $totalPages = $response['total_pages'] ?? null;
        $chunks = $response['chunks'] ?? null;

        if (! is_int($totalPages) || ! is_array($chunks)) {
            return;
        }

        foreach ($chunks as $index => $chunk) {
            if (! is_array($chunk)) {
                continue;
            }

            $pageNumber = $chunk['page_number'] ?? null;

            if (
                is_int($pageNumber)
                && $pageNumber > $totalPages
            ) {
                $validator->errors()->add(
                    "chunks.{$index}.page_number",
                    'رقم صفحة المقطع لا يمكن أن يكون أكبر من عدد صفحات الوثيقة.'
                );
            }
        }
    }

    /**
     * Verify that chunk indexes are sequential and start from zero.
     *
     * Valid example:
     *
     * 0, 1, 2, 3
     *
     * Invalid examples:
     *
     * 1, 2, 3
     * 0, 2, 3
     * 0, 1, 5
     *
     * @param  array<string, mixed>  $response
     */
    private function validateSequentialChunkIndexes(
        \Illuminate\Validation\Validator $validator,
        array $response
    ): void {
        $chunks = $response['chunks'] ?? null;

        if (! is_array($chunks) || $chunks === []) {
            return;
        }

        $indexes = [];

        foreach ($chunks as $chunk) {
            if (
                ! is_array($chunk)
                || ! array_key_exists('chunk_index', $chunk)
                || ! is_int($chunk['chunk_index'])
            ) {
                return;
            }

            $indexes[] = $chunk['chunk_index'];
        }

        sort($indexes, SORT_NUMERIC);

        $expectedIndexes = range(
            0,
            count($indexes) - 1
        );

        if ($indexes !== $expectedIndexes) {
            $validator->errors()->add(
                'chunks',
                'يجب أن تبدأ فهارس المقاطع من الصفر وأن تكون متسلسلة دون فجوات.'
            );
        }
    }

    /**
     * Get custom Arabic validation messages.
     *
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'document_id.required' => 'معرّف الوثيقة مطلوب في استجابة خدمة الذكاء الاصطناعي.',

            'document_id.integer' => 'يجب أن يكون معرّف الوثيقة عددًا صحيحًا.',

            'document_id.in' => 'معرّف الوثيقة الموجود في الاستجابة لا يطابق الوثيقة المرسلة للمعالجة.',

            'total_pages.required' => 'عدد صفحات الوثيقة مطلوب.',

            'total_pages.integer' => 'يجب أن يكون عدد الصفحات عددًا صحيحًا.',

            'total_pages.min' => 'يجب أن تحتوي الوثيقة على صفحة واحدة على الأقل.',

            'qdrant_collection.required' => 'اسم مجموعة Qdrant مطلوب.',

            'qdrant_collection.string' => 'يجب أن يكون اسم مجموعة Qdrant نصًا.',

            'qdrant_collection.min' => 'اسم مجموعة Qdrant لا يمكن أن يكون فارغًا.',

            'qdrant_collection.max' => 'يجب ألا يتجاوز اسم مجموعة Qdrant عدد 255 محرفًا.',

            'chunks.required' => 'قائمة المقاطع المستخرجة مطلوبة.',

            'chunks.array' => 'يجب أن تكون المقاطع مصفوفة.',

            'chunks.min' => 'يجب أن تحتوي نتيجة المعالجة على مقطع واحد على الأقل.',

            'chunks.max' => 'عدد المقاطع المستخرجة يتجاوز الحد الأعلى المسموح به.',

            'chunks.*.array' => 'يجب أن يكون كل مقطع عبارة عن مصفوفة بيانات صحيحة.',

            'chunks.*.chunk_index.required' => 'فهرس المقطع مطلوب.',

            'chunks.*.chunk_index.integer' => 'يجب أن يكون فهرس المقطع عددًا صحيحًا.',

            'chunks.*.chunk_index.min' => 'لا يمكن أن يكون فهرس المقطع أقل من الصفر.',

            'chunks.*.chunk_index.distinct' => 'يجب ألا تتكرر فهارس المقاطع.',

            'chunks.*.content.required' => 'محتوى المقطع مطلوب.',

            'chunks.*.content.string' => 'يجب أن يكون محتوى المقطع نصًا.',

            'chunks.*.content.min' => 'لا يمكن أن يكون محتوى المقطع فارغًا.',

            'chunks.*.page_number.integer' => 'يجب أن يكون رقم الصفحة عددًا صحيحًا.',

            'chunks.*.page_number.min' => 'يجب أن يبدأ ترقيم الصفحات من الرقم واحد.',

            'chunks.*.vector_id.required' => 'معرّف المتجه في Qdrant مطلوب لكل مقطع.',

            'chunks.*.vector_id.string' => 'يجب أن يكون معرّف المتجه نصًا.',

            'chunks.*.vector_id.min' => 'لا يمكن أن يكون معرّف المتجه فارغًا.',

            'chunks.*.vector_id.max' => 'يجب ألا يتجاوز معرّف المتجه عدد 255 محرفًا.',

            'chunks.*.vector_id.distinct' => 'يجب ألا يتكرر معرّف المتجه بين المقاطع.',

            'chunks.*.metadata.array' => 'يجب أن تكون البيانات الوصفية للمقطع مصفوفة.',
        ];
    }

    /**
     * Get readable Arabic names for validation attributes.
     *
     * @return array<string, string>
     */
    private function attributes(): array
    {
        return [
            'document_id' => 'معرّف الوثيقة',
            'total_pages' => 'عدد الصفحات',
            'qdrant_collection' => 'مجموعة Qdrant',
            'chunks' => 'المقاطع',
            'chunks.*.chunk_index' => 'فهرس المقطع',
            'chunks.*.content' => 'محتوى المقطع',
            'chunks.*.page_number' => 'رقم الصفحة',
            'chunks.*.vector_id' => 'معرّف المتجه',
            'chunks.*.metadata' => 'البيانات الوصفية',
        ];
    }
}
