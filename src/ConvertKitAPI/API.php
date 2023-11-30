<?php

namespace GiveConvertKit\ConvertKitAPI;

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
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
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
    public function subscribeToFormList($id, Subscriber $subscriber)
    {
        $this->subscribe('forms', $id, $subscriber);
    }

    /**
     * @unreleased
     */
    public function subscriberToTag($id, Subscriber $subscriber)
    {
        $this->subscribe('tags', $id, $subscriber);
    }

    /**
     * @unreleased
     */
    protected function get($entity): array
    {
        $response = wp_remote_get("https://api.convertkit.com/v3/$entity?api_key={$this->apiKey}");
        $list = json_decode( wp_remote_retrieve_body( $response ), true );
        return array_map(function($item) {
            return [
                'id' => $item['id'],
                'name' => $item['name'],
            ];
        }, $list[$entity]);
    }

    /**
     * @unreleased
     */
    protected function subscribe($entity, $id, Subscriber $subscriber): void
    {
        $response = wp_remote_post(
            "https://api.convertkit.com/v3/$entity/$id/subscribe?api_key={$this->apiKey}",
            ['body' => $subscriber->toArray(), 'timeout' => 30]
        );

        if (is_wp_error($response)) {
            Log::error(__('Error subscribing to ConvertKit', 'give-convertkit'), [
                'error' => $response->get_error_message(),
                'subscriber' => $subscriber->toArray(),
            ]);
        } elseif(!in_array(wp_remote_retrieve_response_code( $response ), [200, 201])) {
            Log::error(__('Error subscribing to ConvertKit', 'give-convertkit'), [
                'error' => wp_remote_retrieve_body( $response ),
                'subscriber' => $subscriber->toArray(),
            ]);
        }
    }
}
