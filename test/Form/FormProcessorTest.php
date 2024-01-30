<?php

declare(strict_types=1);

namespace KynxTest\Laminas\FormCli\Form;

use Kynx\Laminas\FormShape\Form\FormProcessor;
use KynxTest\Laminas\FormShape\Form\Asset\TestForm;
use Laminas\Form\FormElementManager;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormProcessor::class)]
final class FormProcessorTest extends TestCase
{
    public function testGetFormFromPathReturnsForm(): void
    {
        $container          = new ServiceManager();
        $formElementManager = new FormElementManager($container);
        $formProcessor      = new FormProcessor($formElementManager);

        $actual = $formProcessor->getFormFromPath(__DIR__ . '/Asset/TestForm.php');
        self::assertInstanceOf(TestForm::class, $actual);
    }
}
