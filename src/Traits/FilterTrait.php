<?php

namespace Meiko\Lumen\Cloud\Traits;

trait FilterTrait
{
    /**
     * Available operators
     *
     * @var array
     */
    protected $operators = [
        '' => '=',
        'eq' => '=',
        'ne' => '!=',
        'lk' => 'like',
        'notlk' => 'not like',
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<=',
    ];

    /**
     * Decode the global id.
     *
     * @param string $id
     *
     * @return array
     */
    protected function decodeGlobalId($id)
    {
        return explode(':', base64_decode($id))[1];
    }

    /**
     * Loop where arguments
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $where
     * @param boolean $useAnd
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function loopWhere($query, array $where, $useAnd = true)
    {
        $method = ($useAnd) ? 'where' : 'orWhere';
        $attributes = $this->fillable;

        foreach ($where as $field => $value) {
            // skip and / or
            if (in_array($field, ['AND', 'OR'])) {
                continue;
            }

            // get operator
            $operator = '=';
            if (strpos($field, '_') !== false) {
                $arr = explode('_', $field);
                $field = $arr[0];
                $operator = (isset($this->operators[$arr[1]])) ? $this->operators[$arr[1]] : '=';
            }

            // use model method if it exists
            $fieldMethod = 'get' . ucfirst($field) . 'FilterQuery';
            if (method_exists($this, $fieldMethod)) {
                $this->$fieldMethod($query, $operator, $value, $useAnd);

                continue;
            }

            // convert field
            $field = snake_case($field);

            // skip field if it is not in attributes and is not an id field
            if (!in_array($field, $attributes) && !($field == 'id' || substr($field, -3) == '_id')) {
                continue;
            }

            // decode ids
            if (($field == 'id' || substr($field, -3) == '_id') && !empty($value)) {
                $value = $this->decodeGlobalId($value);
            }

            if (in_array($operator, ['=', '!=']) && $value === null) {
                if ($operator == '=') {
                    $method = ($useAnd) ? 'whereNull' : 'orWhereNull';
                } else {
                    $method = ($useAnd) ? 'whereNotNull' : 'orWhereNotNull';
                }

                $query->$method($field);
            } else {
                $query->$method($field, $operator, $value);
            }
        }

        if (!empty($where['AND'])) {
            $query->where(function ($sub) use ($where) {
                foreach ($where['AND'] as $w) {
                    $this->loopWhere($sub, $w);
                }
            });
        }

        if (!empty($where['OR'])) {
            $query->where(function ($sub) use ($where) {
                foreach ($where['OR'] as $w) {
                    $this->loopWhere($sub, $w, false);
                }
            });
        }

        return $query;
    }

    /**
     * Apply filters scope to query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $args
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilters($query, array $args = [])
    {
        if (empty($args['where'])) {
            return $query;
        }

        return $this->loopWhere($query, $args['where']);
    }
}
