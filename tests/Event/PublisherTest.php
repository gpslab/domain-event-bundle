<?php
declare(strict_types=1);

/**
 * GpsLab component.
 *
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace GpsLab\Bundle\DomainEvent\Tests\Event;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use GpsLab\Bundle\DomainEvent\Event\Publisher;
use GpsLab\Bundle\DomainEvent\Event\Puller;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

class PublisherTest extends TestCase
{
    /**
     * @var MockObject&EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * @var MockObject&Puller
     */
    private Puller $puller;

    /**
     * @var MockObject&EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var MockObject&UnitOfWork
     */
    private UnitOfWork $uow;

    private Publisher $publisher;

    protected function setUp(): void
    {
        $this->dispatcher = $this->getMockBuilder(EventDispatcher::class)->getMock();
        $this->puller = $this->getMockBuilder(Puller::class)->getMock();
        $this->em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();
        $this->uow = $this
            ->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->publisher = new Publisher($this->puller, $this->dispatcher);
    }

    public function testNothingDispatch(): void
    {
        $this->dispatcher
            ->expects($this->never())
            ->method('dispatch')
        ;
        $this->em
            ->expects($this->never())
            ->method('flush')
        ;

        $this->publisher->dispatchEvents($this->em);
    }

    public function testDispatchEvents(): void
    {
        $this->em
            ->expects($this->once())
            ->method('getUnitOfWork')
            ->willReturn($this->uow)
        ;

        $events = [
            new Event(),
            new Event(),
            new Event(),
        ];
        $arguments = [];

        foreach ($events as $event) {
            $arguments[] = [$event];
        }

        $this->puller
            ->expects($this->once())
            ->method('pull')
            ->with($this->uow)
            ->willReturn($events)
        ;

        $this->dispatcher
            ->expects($this->atLeastOnce())
            ->method('dispatch')
            ->withConsecutive(...$arguments)
        ;

        $this->em
            ->expects($this->once())
            ->method('flush')
        ;

        $this->publisher->aggregateEvents($this->em);
        $this->publisher->dispatchEvents($this->em);
    }

    /**
     * @return Event[][][]
     */
    public function provideEvents(): array
    {
        return [
            [
                [
                    new Event(),
                    new Event(),
                    new Event(),
                ],
                [],
            ],
            [
                [],
                [
                    new Event(),
                    new Event(),
                    new Event(),
                ],
            ],
            [
                [
                    new Event(),
                    new Event(),
                    new Event(),
                ],
                [
                    new Event(),
                    new Event(),
                    new Event(),
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideEvents
     *
     * @param Event[] $first_loop
     * @param Event[] $second_loop
     */
    public function testDispatchEventsRecursive(array $first_loop, array $second_loop): void
    {
        $this->em
            ->expects($this->atLeastOnce())
            ->method('getUnitOfWork')
            ->willReturn($this->uow)
        ;

        $arguments = [];

        foreach (array_merge($first_loop, $second_loop) as $event) {
            $arguments[] = [$event];
        }

        $this->puller
            ->expects($this->atLeastOnce())
            ->method('pull')
            ->with($this->uow)
            ->willReturnOnConsecutiveCalls($first_loop, $second_loop)
        ;

        $this->dispatcher
            ->expects($this->atLeastOnce())
            ->method('dispatch')
            ->withConsecutive(...$arguments)
        ;

        $this->em
            ->expects($first_loop !== [] && $second_loop !== [] ? $this->atLeastOnce() : $this->once())
            ->method('flush')
        ;

        $this->publisher->aggregateEvents($this->em);
        $this->publisher->dispatchEvents($this->em);
        // recursive call
        $this->publisher->aggregateEvents($this->em);
        $this->publisher->dispatchEvents($this->em);
    }
}
