<?php

namespace App\Support;

use Sentry\Tracing\SamplingContext;

class SentryTracesSampler
{
    public static function sample(SamplingContext $context): float
    {
        $data = $context->getTransactionContext()->getData();
        $url = $data['url'] ?? null;
        if (str_contains($url, config('horizon.path'))) {
            return 0.0;
        }

        return (float) (config('sentry.traces_sample_rate'));
    }
}
