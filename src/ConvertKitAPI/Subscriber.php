<?php

namespace GiveConvertKit\ConvertKitAPI;

use Give\Donors\Models\Donor;

/**
 * @unreleased
 */
class Subscriber
{
    /**
     * @unreleased
     */
    protected $email;

    /**
     * @unreleased
     */
    protected $name;

    /**
     * @unreleased
     */
    public function __construct($email, $name = false)
    {
        $this->email = $email;
        $this->name = $name;
    }

    /**
     * @unreleased
     */
    public static function fromDonor(Donor $donor): Subscriber
    {
        return new static($donor->email, $donor->name);
    }

    /**
     * @unreleased
     */
    public function toArray(): array
    {
        return array_filter(apply_filters('give_convertkit_subscribe_vars', [
            'email' => $this->email,
            'first_name' => $this->name,
        ]));
    }
}
