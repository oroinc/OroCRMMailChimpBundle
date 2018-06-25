<?php
namespace Oro\Bundle\MailChimpBundle\Tests\Unit\Command;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\MailChimpBundle\Command\MailChimpExportCommand;
use Oro\Component\Testing\ClassExtensionTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class MailChimpExportCommandTest extends \PHPUnit\Framework\TestCase
{
    use ClassExtensionTrait;

    public function testShouldBeSubClassOfCommand()
    {
        $this->assertClassExtends(Command::class, MailChimpExportCommand::class);
    }

    public function testShouldImplementContainerAwareInterface()
    {
        $this->assertClassImplements(ContainerAwareInterface::class, MailChimpExportCommand::class);
    }

    public function testShouldImplementCronCommandInterface()
    {
        $this->assertClassImplements(CronCommandInterface::class, MailChimpExportCommand::class);
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new MailChimpExportCommand();
    }

    public function testShouldBeRunEveryFiveMinutes()
    {
        $command = new MailChimpExportCommand();

        self::assertEquals('*/5 * * * *', $command->getDefaultDefinition());
    }

    public function testShouldAllowSetContainer()
    {
        $container = new Container();

        $command = new MailChimpExportCommand();

        $command->setContainer($container);

        $this->assertAttributeSame($container, 'container', $command);
    }
}
