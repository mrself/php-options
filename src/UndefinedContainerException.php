<?php declare(strict_types=1);

namespace Mrself\Options;

class UndefinedContainerException extends OptionsException
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $ownerClass;

    public function __construct(string $namespace, string $ownerClass)
    {
        $this->namespace = $namespace;
        $this->ownerClass = $ownerClass;

        $message = "Cannot define container to use for the class '$ownerClass' . Namespace '$namespace' was defined. This can mean that container does not exist for current namespace of namespace was not defined properly. To define it either #optionsContainerNamespace or #getOptionsContainerNamespace() on optionable class";
        parent::__construct($message);
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getOwnerClass(): string
    {
        return $this->ownerClass;
    }
}