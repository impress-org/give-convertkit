<?php

namespace GiveConvertKit\ConvertKitAPI;

class API
{
    protected $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getForms(): array
    {
        return $this->get('forms');
    }

    public function getTags(): array
    {
        return $this->get('tags');
    }

    public function subscribeToFormList($id, Subscriber $subscriber)
    {
        $this->subscribe('forms', $id, $subscriber);
    }

    public function subscriberToTag($id, Subscriber $subscriber)
    {
        $this->subscribe('tags', $id, $subscriber);
    }

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

    protected function subscribe($entity, $id, Subscriber $subscriber): void
    {
        wp_remote_post(
            "https://api.convertkit.com/v3/$entity/$id/subscribe?api_key={$this->apiKey}",
            ['body' => $subscriber->toArray(), 'timeout' => 30]
        );
    }
}
