<?php

declare(strict_types=1);

namespace Sunaoka\LaravelPostgres\Query;

/**
 * @property \Sunaoka\LaravelPostgres\Query\Grammars\PostgresGrammar  $grammar
 */
class Builder extends \Illuminate\Database\Query\Builder
{
    /**
     * @var array
     */
    public $returning = [];

    /**
     * @param  array  $columns
     * @return $this
     */
    public function returning(array $columns = []): self
    {
        $this->returning = $columns;

        return $this;
    }

    /**
     * Update records in the database.
     *
     * @param  array  $values
     * @return int|array
     */
    public function update(array $values)
    {
        $sql = $this->grammar->compileUpdate($this, $values);
        $bindings = $this->cleanBindings(
            $this->grammar->prepareBindingsForUpdate($this->bindings, $values)
        );

        if (empty($this->returning)) {
            return $this->connection->update($sql, $bindings);
        }

        return $this->connection->select($sql, $bindings);
    }

    /**
     * Delete records from the database.
     *
     * @param  mixed  $id
     * @return int|array
     */
    public function delete($id = null)
    {
        // If an ID is passed to the method, we will set the where clause to check the
        // ID to let developers to simply and quickly remove a single row from this
        // database without manually specifying the "where" clauses on the query.
        if (! is_null($id)) {
            $this->where($this->from.'.id', '=', $id);
        }

        $query = $this->grammar->compileDelete($this);
        $bindings = $this->cleanBindings(
            $this->grammar->prepareBindingsForDelete($this->bindings)
        );

        if (empty($this->returning)) {
            return $this->connection->delete($query, $bindings);
        }

        return $this->connection->select($query, $bindings);
    }
}
