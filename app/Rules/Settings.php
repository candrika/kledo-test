<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Reference;


class Settings implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $ref = new Reference;

        $row = $ref::where('id', $value)->first();

        if($row->code=='overtime_method'){
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'code must by overtime_method';
    }
}
