<?php

declare(strict_types=1);

namespace Kynx\Laminas\FormShape\InputFilter;

use Laminas\Filter\FilterChain;
use Laminas\InputFilter\EmptyContextInterface;
use Laminas\InputFilter\InputInterface;
use Laminas\Validator\ValidatorChain;

use function assert;
use function is_array;

/**
 * Input for handling form collections where the target element is an actual element, not a fieldset
 *
 * This is not designed for real-world validation; it's purpose is to capture the `count` and (very probably)
 * `possibly_undefined` state of the collection, but otherwise proxy the element's actual `InputInterface`.
 *
 * @internal
 *
 * @see CollectionInputVisitor
 *
 * @psalm-internal Kynx\Laminas\FormShape
 * @psalm-internal KynxTest\Laminas\FormShape
 */
final readonly class CollectionInput implements InputInterface, EmptyContextInterface
{
    private function __construct(private InputInterface $delegate, private int $count)
    {
    }

    public static function fromInput(InputInterface $input, int $count): self
    {
        return new self($input, $count);
    }

    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param bool $continueIfEmpty
     */
    public function setContinueIfEmpty($continueIfEmpty): self
    {
        if ($this->delegate instanceof EmptyContextInterface) {
            $this->delegate->setContinueIfEmpty($continueIfEmpty);
        }
        return $this;
    }

    public function continueIfEmpty(): bool
    {
        if ($this->delegate instanceof EmptyContextInterface) {
            return $this->delegate->continueIfEmpty();
        }
        return false;
    }

    /**
     * @param bool $allowEmpty
     */
    public function setAllowEmpty($allowEmpty): self
    {
        $this->delegate->setAllowEmpty($allowEmpty);
        return $this;
    }

    /**
     * @param bool $breakOnFailure
     */
    public function setBreakOnFailure($breakOnFailure): self
    {
        $this->delegate->setBreakOnFailure($breakOnFailure);
        return $this;
    }

    /**
     * @param null|string $errorMessage
     */
    public function setErrorMessage($errorMessage): self
    {
        $this->delegate->setErrorMessage($errorMessage);
        return $this;
    }

    public function setFilterChain(FilterChain $filterChain): self
    {
        $this->delegate->setFilterChain($filterChain);
        return $this;
    }

    /**
     * @param string $name
     */
    public function setName($name): self
    {
        $this->delegate->setName($name);
        return $this;
    }

    /**
     * @param bool $required
     */
    public function setRequired($required): self
    {
        $this->delegate->setRequired($required);
        return $this;
    }

    public function setValidatorChain(ValidatorChain $validatorChain): self
    {
        $this->delegate->setValidatorChain($validatorChain);
        return $this;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): self
    {
        assert(is_array($value));
        $this->delegate->setValue($value);
        return $this;
    }

    public function merge(InputInterface $input): self
    {
        $this->delegate->merge($input);
        return $this;
    }

    public function allowEmpty(): bool
    {
        return $this->delegate->allowEmpty();
    }

    public function breakOnFailure(): bool
    {
        return $this->delegate->breakOnFailure();
    }

    public function getErrorMessage(): ?string
    {
        return $this->delegate->getErrorMessage();
    }

    public function getFilterChain(): FilterChain
    {
        return $this->delegate->getFilterChain();
    }

    public function getName(): string
    {
        return $this->delegate->getName();
    }

    public function getRawValue(): mixed
    {
        return $this->delegate->getRawValue();
    }

    public function isRequired(): bool
    {
        return $this->delegate->isRequired();
    }

    public function getValidatorChain(): ValidatorChain
    {
        return $this->delegate->getValidatorChain();
    }

    public function getValue(): mixed
    {
        return $this->delegate->getValue();
    }

    /**
     * @param mixed $context
     */
    public function isValid($context = null): bool
    {
        return $this->delegate->isValid();
    }

    public function getMessages(): array
    {
        return $this->delegate->getMessages();
    }
}
