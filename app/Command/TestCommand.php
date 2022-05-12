<?php

declare(strict_types=1);

namespace App\Command;

use App\Amqp\Producer\ShoplineProducer;
use Hyperf\Amqp\Producer;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;

/**
 * @Command
 */
#[Command]
class TestCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var Producer
     */
    protected $producer;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('t');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Hyperf Demo Command');
    }

    public function handle()
    {
        $this->line('Hello Hyperf!', 'info');
        $this->producer->produce(new ShoplineProducer('test'. date('Y-m-d H:i:s')));
    }
}
