<?php

namespace GiveConvertKit\ConvertKitAPI;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Log\Log;

/**
 * @unreleased
 */
class API
{
    /**
     * @unreleased
     */
    protected $apiKey;

    /**
     * @unreleased
     */
    protected $apiSecret;

    /**
     * @unreleased
     */
    public function __construct($apiKey, $apiSecret)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    /**
     * @unreleased
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
     * @unreleased
     */
    public function getAccount()
    {
        return wp_remote_get("https://api.convertkit.com/v3/account?api_secret={$this->apiSecret}");
    }

    /**
     * @unreleased
     */
    public function getForms(): array
    {
        return $this->get('forms');
    }

    /**
     * @unreleased
     */
    public function getTags(): array
    {
        return $this->get('tags');
    }

    /**
     * @unreleased
     */
    public function subscribeToFormList($id, Subscriber $subscriber): void
    {
        $this->subscribe('forms', $id, $subscriber);
    }

    /**
     * @unreleased
     */
    public function subscriberToTag($id, Subscriber $subscriber): void
    {
        $this->subscribe('tags', $id, $subscriber);
    }

    /**
     * @unreleased
     */
    protected function get($entity): array
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
     * @unreleased
     */
    protected function subscribe($entity, $id, Subscriber $subscriber)
    {
        $response = wp_remote_post(
            "https://api.convertkit.com/v3/$entity/#$id/subscribe?$this->apiKey)",
            ['body' => $subscriber->toArray()]
        );

        $successfulResponse = in_array(wp_remote_retrieve_response_code($response), [200, 201]);
        $httpMessage = $this->getHttpMessages($entity, $successfulResponse);

        if (is_wp_error($response)) {
            Log::error($httpMessage, [
                'id'         => $id,
                'error'      => $response->get_error_message(),
                'subscriber' => $subscriber->toArray(),
            ]);

            return;
        }

        if ( ! $successfulResponse) {
            Log::error($httpMessage, [
                'id'         => $id,
                'error'      => wp_remote_retrieve_body($response),
                'subscriber' => $subscriber->toArray(),
            ]);

            return;
        }

        Log::success(
            $httpMessage,
            [
                'id'         => $id,
                'response'   => wp_remote_retrieve_body($response),
                'subscriber' => $subscriber->toArray(),
            ]
        );
    }

    protected function getHttpMessages($entity, $successfulResponse): string
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

        return $successfulResponse ? $httpMessages[$entity]['success'] : $httpMessages[$entity]['error'];
    }
}

