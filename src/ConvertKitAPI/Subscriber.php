<?php

namespace GiveConvertKit\ConvertKitAPI;

use Give\Donors\Models\Donor;

class Subscriber
{
    protected $email;
    protected $name;

    public function __construct($email, $name = false)
    {
        $this->email = $email;
        $this->name = $name;
    }

    public static function fromDonor(Donor $donor): Subscriber
    {
        return new static($donor->email, $donor->name);
    }

    public function toArray(): array
    {
        return array_filter(apply_filters('give_convertkit_subscribe_vars', [
            'email' => $this->email,
            'first_name' => $this->name,
        ]));
    }
}
