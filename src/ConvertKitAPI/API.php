<?php

namespace GiveConvertKit\ConvertKitAPI;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Log\Log;

/**
 * @since 2.0.0
 */
class API
{
    /**
     * @since 2.0.0
     */
    protected $apiKey;

    /**
     * @since 2.0.0
     */
    protected $apiSecret;

    /**
     * @since 2.0.0
     */
    public function __construct($apiKey, $apiSecret)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * @since 2.0.0
     */
    public function validateApiCredentials(): bool
    {
        try {
            $statusCode = absint(wp_remote_retrieve_response_code($this->getAccount()));

            return $statusCode === 200 && $this->apiKey;
        } catch (Exception $e) {
            Log::error('CONVERTKIT API ERROR', [
                'Invalid api key' => 'Please provide a valid ConvertKit API key.',
                'Error message'   => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @since 2.0.0
     */
    public function getAccount()
    {
        return wp_remote_get("https://api.convertkit.com/v3/account?api_secret={$this->apiSecret}");
    }

    /**
     * @since 2.0.0
     */
    public function getForms(): array
    {
        return $this->get('forms');
    }

    /**
     * @since 2.0.0
     */
    public function getTags(): array
    {
        return $this->get('tags');
    }

    /**
     * @since 2.0.0
     */
    public function subscribeToFormList(string $id, Subscriber $subscriber): void
    {
        $this->subscribe('forms', $id, $subscriber);
    }

    /**
     * @since 2.0.0
     */
    public function subscriberToTag(string $id, Subscriber $subscriber): void
    {
        $this->subscribe('tags', $id, $subscriber);
    }

    /**
     * @since 2.0.0
     */
    protected function get(string $entity): array
    {
        if ( ! $this->validateApiCredentials()) {
            return [];
        }

        $response = wp_remote_get("https://api.convertkit.com/v3/$entity?api_key={$this->apiKey}");
        $list = json_decode(wp_remote_retrieve_body($response), true);

        return array_map(function ($item) {
            return [
                'id'   => (string)$item['id'],
                'name' => $item['name'],
            ];
        }, $list[$entity]);
    }

    /**
     * @since 2.0.0
     */
    protected function subscribe(string $entity, string $id, Subscriber $subscriber): void
    {
        $url = "https://api.convertkit.com/v3/$entity/$id/subscribe?api_key={$this->apiKey}";

        $response = wp_remote_post($url, ['body' => $subscriber->toArray(), 'timeout' => 30]);
        $statusCode = wp_remote_retrieve_response_code($response);

        $httpMessage = $this->getHttpMessages($entity, $statusCode);

        if (is_wp_error($response)) {
            Log::error($httpMessage, [
                'status'     => $statusCode,
                'identifier' => $id,
                'error'      => $response->get_error_message(),
                'subscriber' => $subscriber->toArray(),
            ]);

            return;
        }

        if ( ! in_array($statusCode, [200, 201], true)) {
            Log::error($httpMessage, [
                'status'     => $statusCode,
                'identifier' => $id,
                'error'      => wp_remote_retrieve_body($response),
                'subscriber' => $subscriber->toArray(),
            ]);

            return;
        }

        Log::http($httpMessage, [
            'status'     => $statusCode,
            'identifier' => $id,
            'entity'     => $entity,
            'response'   => $response,
        ]);
    }

    protected function getHttpMessages(string $entity, int $statusCode): string
    {
        $httpMessages = [
            'tags'  => [
                'success' => __('Convertkit API has successfully added a new email tag'),
                'error'   => __('Convertkit API has encountered an error while adding a new email tag'),
            ],
            'forms' => [
                'success' => __('Convertkit API has successfully subscribed a new email'),
                'error'   => __('Convertkit API has encountered an error while subscribing a new email'),
            ],
        ];

        $success = in_array($statusCode, [200, 201], true);

        return $success ? $httpMessages[$entity]['success'] : $httpMessages[$entity]['error'];
    }
}

