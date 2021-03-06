<?php

require_once dirname(__FILE__).'/BaseBuilder.php';

/**
 * Class UpdateBuilder
 *
 * @package PicoDb\Builder
 * @author  Frederic Guillot
 */
class UpdateBuilder extends BaseBuilder
{
    /**
     * @var string[]
     */
    protected $sumColumns = array();

    /**
     * Get object instance
     *
     * @static
     * @access public
     * @param  Database         $db
     * @param  ConditionBuilder $condition
     * @return static
     */
    public static function getInstance(Database $db, ConditionBuilder $condition)
    {
        $class = get_class();
        return new $class($db, $condition);
    }

    /**
     * Set columns name
     *
     * @access public
     * @param  string[] $columns
     * @return $this
     */
    public function withSumColumns(array $columns)
    {
        $this->sumColumns = $columns;
        return $this;
    }

    /**
     * Build SQL
     *
     * @access public
     * @return string
     */
    public function build()
    {
        $columns = array();

        foreach ($this->columns as $column => $value) {
            $columns[] = $this->db->escapeIdentifier($column).'='.$value;
        }

        foreach ($this->sumColumns as $column => $value) {
            $columns[] = $this->db->escapeIdentifier($column).'='.$this->db->escapeIdentifier($column).' + '.$value;
        }

        return sprintf(
            'UPDATE %s SET %s %s',
            $this->db->escapeIdentifier($this->table),
            implode(', ', $columns),
            $this->conditionBuilder->build()
        );
    }
}
