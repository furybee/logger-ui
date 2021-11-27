<?php

namespace FuryBee\LoggerUi\Http\Resources;

use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Http\Resources\Json\JsonResource;

class LogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'channel' => $this->resource->channel,
            'level_name' => $this->resource->level_name,
            'level' => $this->resource->level,
            'message' => $this->resource->message,
            'context' => $this->formatContext(),
            'extra' => $this->formatExtra(),
            'user_id' => $this->resource->user_id,
            'logged_at' => $this->resource->logged_at,
            'formatted_logged_at' => $this->formattedLogggedAt(),
            'has_details_displayed' => false,
        ];
    }

    private function formattedLogggedAt(): string
    {
        return Carbon::parse($this->resource->logged_at)->format('M d H:i:s');
    }

    private function formatContext()
    {
        $context = json_decode($this->resource->context, true);

        if (isset($context['exception']['stacktrace']) === true && is_string($context['exception']['stacktrace']) === true) {
            $context['exception']['stacktrace'] = str_replace("\n", '<br />', $context['exception']['stacktrace']);

            return $context;
        }

        if (isset($context[0]) === true) {
            return $this->resource->context;
        }

        return $context;
    }

    private function formatExtra()
    {
        $extra = json_decode($this->resource->extra, true);

        if (isset($extra[0]) === true) {
            return $this->resource->extra;
        }

        return $extra;
    }
}
