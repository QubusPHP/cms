<?php
/* ===========================================================================
 * Copyright 2018 Zindex Software
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================================ */

namespace TriTan\Database\SQL;

use TriTan\Database\Connection;

class Insert extends InsertStatement
{
    /** @var    Connection */
    protected $connection;

    /**
     * Insert constructor.
     * @param Connection $connection
     * @param SQLStatement|null $statement
     */
    public function __construct(Connection $connection, SQLStatement $statement = null)
    {
        parent::__construct($statement);
        $this->connection = $connection;
    }

    /**
     * @param   string $table
     *
     * @return  boolean
     */
    public function into(string $table)
    {
        parent::into($table);
        $compiler = $this->connection->getCompiler();
        return $this->connection->command($compiler->insert($this->sql), $compiler->getParams());
    }
}
