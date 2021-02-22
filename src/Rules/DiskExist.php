<?php

declare(strict_types=1);

namespace Alexusmai\LaravelFileManager\Rules;

use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;
use Illuminate\Contracts\Validation\Rule;

class DiskExist implements Rule
{
    /**
     * @param string $attribute
     * @param mixed $value
     *
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $config = resolve(ConfigRepository::class);

        return in_array($value, $config->getDiskList(), true) && array_key_exists($value, config('filesystems.disks'));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'The :attribute must to exist.';
    }
}
