<?php

namespace App\Http\Response\Format;

use App\APIHelpers;

class Json extends \Dingo\Api\Http\Response\Format\Json
{
    /**
     * Format an array or instance implementing Arrayable.
     *
     * @param array|\Illuminate\Contracts\Support\Arrayable $content
     *
     * @return string
     */
    public function formatArray($content)
    {
        if (array_key_exists('meta', $content) && is_array($content['meta'])) {
            // Change key-case of meta
            $content['meta'] = APIHelpers::formatKeyCaseAccordingToResponseFormat($content['meta']);
        }

        return parent::formatArray($content);
    }
}
