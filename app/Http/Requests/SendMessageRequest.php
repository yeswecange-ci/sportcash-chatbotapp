<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content'       => 'nullable|string|max:4096',
            'is_private'    => 'sometimes|boolean',
            'attachments'   => 'sometimes|array|max:5',
            'attachments.*' => 'file|max:20480|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mp3,ogg,wav,m4a,pdf,doc,docx,xls,xlsx',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // DEBUG TEMPORAIRE — à supprimer
            foreach ((array) $this->files->get('attachments') as $i => $f) {
                \Illuminate\Support\Facades\Log::debug("upload_debug[$i]", [
                    'name'     => $f?->getClientOriginalName(),
                    'error'    => $f?->getError(),
                    'valid'    => $f?->isValid(),
                    'size'     => $f?->getSize(),
                    'tmp_name' => $f?->getPathname(),
                ]);
            }

            $hasContent = filled($this->input('content'));
            $hasFiles   = $this->hasFile('attachments');

            if (!$hasContent && !$hasFiles) {
                $validator->errors()->add('content', 'Un message ou un fichier est requis.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'content.max'         => 'Le message ne peut pas depasser 4096 caracteres.',
            'attachments.max'     => 'Maximum 5 fichiers a la fois.',
            'attachments.*.max'   => 'Chaque fichier ne peut pas depasser 20 Mo.',
            'attachments.*.mimes' => 'Type de fichier non supporte.',
        ];
    }
}
