<?php

namespace Sumra\PubSub\Jobs;


use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PubSub;

class QueueJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var array
     */
    public $data;

    /**
     * SendMessageToQueueJob constructor.
     *
     * @param $properties
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Handler
     */
    public function handle() {
        return PubSub::handle($this);
    }

    public function getJob() {
        return $this->job;
    }
}
