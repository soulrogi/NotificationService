<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Request;

use App\Application\UseCase\AddMessage\Dto\AddMessageDto;
use Illuminate\Foundation\Http\FormRequest;

class SendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return mixed[]
     */
    public function rules(): array
    {
        return [
            'recipient_ids' => 'required|array|min:1|max:1000',
            'recipient_ids.*' => 'required|string|min:1',
            'channel' => 'required|string|in:sms,email',
            'message' => 'required|string|min:1|max:10000',
            'priority' => 'string|in:high,low',
            'idempotency_key' => 'sometimes|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->mergeIfMissing([
            'channel' => 'sms',
            'priority' => 'low',
        ]);
    }

    public function getDto(): AddMessageDto
    {
        return new AddMessageDto(
            recipientIds: $this->json('recipient_ids'),
            channel: $this->json('channel'),
            message: $this->json('message'),
            priority: $this->json('priority'),
            idempotencyKey: $this->json('idempotency_key'),
        );
    }
}
