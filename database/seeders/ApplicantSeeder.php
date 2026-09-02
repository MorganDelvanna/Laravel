<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Applicant;
use Illuminate\Support\Facades\Hash;

class ApplicantSeeder extends Seeder
{
    public function run(): void
    { 
        Applicant::firstOrCreate(
            ['uuid' => 'unique-uuid-1'],
            [
                'type' => 'applicant',
                'data' => 'eFNfcBP/CgZXRdx2KBck8OP9WGiNhLAcgPkVFMukZBghcn31PkP5aO9ZTgaaHlbVbN9zNmQ5AP/fJyv95zBUvXcvp4mElfrD8+GD6XZBB1pIIQjNqAZnbIh2eAFdo6//gSx+JJRgxLRjORpbMCKnWiYXUj92PfZFJGziq6I+BqP6vf37qBS7mtlU56p5vwLrS0A5b+J1WODmjFM9SF5QaGPET5DnCn+ZvieG1joBhCOCRJRj0cGQ54v4dPVQwcVbbCKf5aK1p2ovr5CgOR18lg6Pd+RShecNnAq+J9mJi7eO/SSDaEZlHpnuvMcG2MIzNcDOl1ZMwr6IdzWxcs5u89F9FWeuUd8g8XmQsoKnHdXviuZK8+u3+7K6e0z2TZesHK6zgh/FKG1xUbmuwE5lZYrF7Lq6+JGMUKkTK/b1cGAUWfwZZXy7NZ0wnNShT23yd5wOfve8N1dsXPhA0xQ5dU/2MPwmwJtWceZ6C22DBqog+WCpbUzjlUK0DOmvSPsFuysydoXwj2t7L1gSMI1YVRmHgnYruZHpYDA4iPDfc6oORqm/RarmzABrGh6CNW4ODWYcH0YpT5TNa8atrB5t7QjfXZqPkrHaZPUSaZ8kxFkfHqD2qXTGtwzUwn5LJoZI',
                'created_at' => now(),
                'emailed' => false,
                'emailed_at' => null,
                'canceled_at' => null,
                'transaction_id' => 'pi_3TxGz3B2ie8bsWmg1amWaVPk'
            ]
        );
    }
}