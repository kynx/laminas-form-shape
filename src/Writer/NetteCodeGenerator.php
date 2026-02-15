<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\Writer;

use Kynx\Laminas\FormShape\DecoratorInterface;
use Kynx\Laminas\FormShape\InputFilter\ImportType;
use Kynx\Laminas\FormShape\Psalm\ShortNameReplacer;
use Kynx\Laminas\FormShape\Psalm\UseCollector;
use Kynx\Laminas\FormShape\TypeNamerInterface;
use Kynx\Laminas\FormShape\Writer\Tag\Method;
use Kynx\Laminas\FormShape\Writer\Tag\PsalmExtends;
use Kynx\Laminas\FormShape\Writer\Tag\PsalmImplements;
use Kynx\Laminas\FormShape\Writer\Tag\PsalmImportType;
use Kynx\Laminas\FormShape\Writer\Tag\PsalmType;
use Kynx\Laminas\FormShape\Writer\Tag\ReturnType;
use Laminas\Form\FormInterface;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\PsrPrinter;
use Psalm\Type\Union;
use ReflectionClass;
use Throwable;

use function array_filter;
use function array_pop;
use function array_search;
use function explode;
use function in_array;

/**
 * @internal
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class NetteCodeGenerator implements CodeGeneratorInterface
{
    public function __construct(
        private TypeNamerInterface $typeNamer,
        private DecoratorInterface $decorator
    ) {
    }

    public function generate(
        ReflectionClass $reflection,
        Union $type,
        array $importTypes,
        string $contents,
        bool $replaceGetDataReturn = false
    ): GeneratedCode {
        try {
            $file = PhpFile::fromCode($contents);
        } catch (Throwable $throwable) {
            throw CodeGeneratorException::cannotParse($reflection, $throwable);
        }

        $class = $namespace = null;
        foreach ($file->getNamespaces() as $namespace) {
            foreach ($namespace->getClasses() as $class) {
                if ($class->getName() === $reflection->getName()) {
                    break 2;
                }
            }
        }
        if ($class === null || $namespace === null) {
            throw CodeGeneratorException::classNotFound($reflection);
        }

        $useCollector = new UseCollector();
        $useCollector->traverse($type);
        $used = $useCollector->getUses();
        $uses = $this->addUses($namespace, $used);

        $shortNameReplacer = new ShortNameReplacer($uses);
        $shortNameReplacer->traverse($type);

        $typeName   = $this->typeNamer->name($reflection);
        $definition = $this->decorator->decorate($type);

        $classDocBlock = $this->addImportTypes(
            $namespace,
            DocBlock::fromDocComment($reflection->getDocComment()),
            $this->getUsedTypes($importTypes, $used)
        );

        $classDocBlock = $classDocBlock->withTag(new PsalmType($typeName, $definition));

        if ($replaceGetDataReturn) {
            $classDocBlock = $classDocBlock->withoutTag(new Method('getData'));
        }

        if ($reflection->implementsInterface(FormInterface::class)) {
            $tag           = $reflection->getParentClass() === false
                ? new PsalmImplements('FormInterface', $typeName)
                : new PsalmExtends($reflection->getParentClass()->getShortName(), $typeName);
            $classDocBlock = $classDocBlock->withTag($tag);
        }

        $class->setComment($classDocBlock->getContents());

        if ($replaceGetDataReturn && $class->hasMethod('getData')) {
            $method          = $class->getMethod('getData');
            $getDataDocBlock = DocBlock::fromDocComment($method->getComment() ?? '')
                ->withoutTag(new ReturnType(''));
            $method->setComment($getDataDocBlock->getContents());
        }

        return new GeneratedCode($typeName, (new PsrPrinter())->printFile($file));
    }

    /**
     * @param list<string> $new
     * @return array<string, string>
     */
    private function addUses(PhpNamespace $namespace, array $new): array
    {
        $uses     = [];
        $existing = $namespace->getUses();
        foreach ($new as $fullyQualified) {
            $alias = array_search($fullyQualified, $existing);
            if ($alias !== false) {
                $uses[$fullyQualified] = $alias;
                continue;
            }

            $alias = '';
            $parts = explode('\\', $fullyQualified);
            do {
                $alias = array_pop($parts) . $alias;
                if (! isset($existing[$alias])) {
                    break;
                }
            } while ($parts !== []);

            $uses[$fullyQualified] = $alias;
        }

        foreach ($uses as $fullyQualified => $alias) {
            $namespace->addUse($fullyQualified, $alias);
        }

        return $uses;
    }

    /**
     * @param array<ImportType> $importTypes
     * @return array<ImportType>
     */
    private function getUsedTypes(array $importTypes, array $uses): array
    {
        return array_filter(
            $importTypes,
            static fn (ImportType $type): bool => in_array($type->type->declaring_fq_classlike_name, $uses)
        );
    }

    /**
     * @param array<ImportType> $importTypes
     */
    private function addImportTypes(PhpNamespace $namespace, DocBlock $docBlock, array $importTypes): DocBlock
    {
        foreach ($importTypes as $importType) {
            $from     = $importType->type;
            $docBlock = $docBlock->withTag(new PsalmImportType(
                $from->alias_name,
                $namespace->simplifyName($from->declaring_fq_classlike_name)
            ));
        }

        return $docBlock;
    }
}
