<?php

namespace OroCRM\Bundle\MailChimpBundle\Model\Segment;

use Oro\Bundle\SegmentBundle\Entity\Segment;

class ColumnDefinitionList implements ColumnDefinitionListInterface
{
    /**
     * @var array
     */
    protected $columns;

    /**
     * @param Segment $segment
     */
    public function __construct(Segment $segment)
    {
        $this->columns = [];
        $definition = json_decode($segment->getDefinition(), true);
        if (!is_null($definition)) {
            $this->initialize($definition);
        }
    }

    /**
     * @param array $definition
     * @return void
     */
    protected function initialize(array $definition)
    {
        if (!isset($definition['columns']) || !is_array($definition['columns'])) {
            return;
        }
        foreach ($definition['columns'] as $column) {
            $columnDefinition = $this->createColumnDefinition($column);
            if (!empty($columnDefinition)) {
                array_push($this->columns, $columnDefinition);
            }
        }
    }

    /**
     * @param array $column
     * @return array
     */
    protected function createColumnDefinition(array $column)
    {
        if (!isset($column['name'], $column['label'])) {
            return [];
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
