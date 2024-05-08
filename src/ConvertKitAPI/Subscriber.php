<?php

namespace GiveConvertKit\ConvertKitAPI;

use Give\Donors\Models\Donor;

/**
 * @since 2.0.0
 */
class Subscriber
{
    /**
     * @since 2.0.0
     */
    protected $email;

    /**
     * @since 2.0.0
     */
    protected $name;

    /**
     * @since 2.0.0
     */
    public function __construct($email, $name = false)
    {
        $this->email = $email;
        $this->name = $name;
    }

    /**
     * @since 2.0.0
     */
    public static function fromDonor(Donor $donor): Subscriber
    {
        return new static($donor->email, $donor->name);
    }

    /**
     * @since 2.0.0
     */
    public function toArray(): array
    {
        return array_filter(apply_filters('give_convertkit_subscribe_vars', [
            'email' => $this->email,
            'first_name' => $this->name,
        ]));
    }
}
