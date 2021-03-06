<?php declare(strict_types=1);

namespace Mrself\Options;

use Mrself\Options\Annotation\Option;

class PropertyMeta
{
    /**
     * @var array
     */
    protected $annotations;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    public $name;

    public static function make(array $options): self
    {
        $self = new static();
        foreach ($options as $name => $value) {
            $self->$name = $value;
        }
        return $self;
    }

    public function getAnnotation(string $class, $default = null)
    {
        $result = array_filter($this->annotations, function ($annotation) use ($class) {
            return $annotation['class'] === $class;
        });
        return reset($result) ?: $default;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }
}