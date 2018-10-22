<?php

namespace Meiko\Lumen\Cloud\Traits;

trait SortTrait
{
    /**
     * Apply sort scope
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $args
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSorts($query, array $args = [])
    {
        $sort = (empty($args['sort'])) ? [] : $args['sort'];

        if (!empty($sort['field'])) {
            $field = snake_case($sort['field']);

            $sortables = array_merge($this->fillable, ($this->sortable) ? $this->sortable : []);
            if (in_array($field, $sortables)) {
                $direction = (empty($sort['direction']) || $sort['direction'] == 'asc') ? 'asc' : 'desc';

                $query->orderBy($field, $direction);
            }
        }

        return $query;
    }
}
