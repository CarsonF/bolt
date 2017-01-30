<?php

namespace Bolt\Storage\Database\Schema\Table;

/**
 * Table for invitation codes.
 *
 * @author Carlos Pérez <mrcarlosdev@gmail.com>
 */
class Invitation extends BaseTable
{
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        // @codingStandardsIgnoreStart
        $this->table->addColumn('token',       'string',     ['length' => 128]);
        $this->table->addColumn('owner_id',    'integer',    ['notnull' => false]);
        $this->table->addColumn('expiration',  'datetime',   ['notnull' => false, 'default' => null]);
        $this->table->addColumn('roles',       'json_array', []);

        $this->table->addColumn('username',    'string',     ['length' => 32]);
        $this->table->addColumn('email',       'string',     ['length' => 254]);
        $this->table->addColumn('display_name','string',     ['length' => 32]);
        // @codingStandardsIgnoreEnd
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function setPrimaryKey()
    {
        $this->table->setPrimaryKey(['token']);
    }
}
