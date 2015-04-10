<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Segment;

use Oro\Bundle\SegmentBundle\Entity\Segment;

class ColumnDefinitionList implements ColumnDefinitionListInterface
{
    /**
     * @var array
     */
    private $columns;

    /**
     * @param Segment $segment
     */
    public function __construct(Segment $segment)
    {
        $this->columns = [];
        $definition = json_decode($segment->getDefinition(), true);
        if (false === is_null($definition)) {
            $this->initialize($definition);
        }
    }

    /**
     * @param array $definition
     * @return void
     */
    protected function initialize(array $definition)
    {
        if (false === isset($definition['columns'])) {
            return;
        }
        foreach ($definition['columns'] as $column) {
            $columnDefinition = $this->createColumnDefinition($column);
            if ($columnDefinition) {
                array_push($this->columns, $columnDefinition);
            }
        }
    }

    /**
     * @param array $column
     * @return null|array
     */
    protected function createColumnDefinition(array $column)
    {
        if (false === isset($column['name']) || false ===  isset($column['label'])) {
            return null;
        }
        return ['name' => $column['name'], 'label' => $column['label']];
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }
}
