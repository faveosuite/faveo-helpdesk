<?php

namespace BeyondCode\QueryDetector\Outputs;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

use Barryvdh\Debugbar\Facade as LaravelDebugbarV3;
use DebugBar\DataCollector\MessagesCollector;
use Fruitcake\LaravelDebugbar\Facades\Debugbar as LaravelDebugbar;

class Debugbar implements Output
{
    protected $collector;

    public function boot()
    {
        $this->collector = new MessagesCollector('N+1 Queries');

        if (class_exists(\Fruitcake\LaravelDebugbar\Facades\Debugbar::class)) {
            if (!LaravelDebugbar::hasCollector($this->collector->getName())) {
                LaravelDebugbar::addCollector($this->collector);
            }
            return;
        }

        if (!LaravelDebugbarV3::hasCollector($this->collector->getName())) {
            LaravelDebugbarV3::addCollector($this->collector);
        }
    }

    public function output(Collection $detectedQueries, Response $response)
    {
        foreach ($detectedQueries as $detectedQuery) {
            $this->collector->addMessage(sprintf('Model: %s => Relation: %s - You should add `with(%s)` to eager-load this relation.',
                $detectedQuery['model'],
                $detectedQuery['relation'],
                $detectedQuery['relation']
            ));
        }
    }
}
