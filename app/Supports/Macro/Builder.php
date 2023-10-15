<?php

namespace App\Supports\Macro;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class Builder implements MacroInterface
{
    const PER_LIMIT = 10;

    const MAX_LIMIT = 100;

    /**
     * 简单查询
     *
     * @return \Closure
     */
    public function macroQuery()
    {
        return function (
            $wheres = [],
            $columns = [],
            $orderBys = [],
            int $page = 0,
            int $limit = Builder::PER_LIMIT,
            bool $withPage = true
        ) {
            if (!empty($wheres)) {
                $this->macroWhere($wheres);
            }

            if (!empty($columns)) {
                $this->macroSelect($columns);
            }

            if (!empty($orderBys)) {
                $this->macroOrderBy($orderBys);
            }

            if (!empty($page) && $withPage) {
                $ret = $this->paginate($limit, ['*'], 'page', $page)->toArray();
                $ret = [
                    'items' => Arr::pull($ret, 'data'),
                    'page' => $ret
                ];
            } else {
                if (!empty($page)) {
                    $this->macroPage($page, $limit);
                }
                $ret = $this->get()->toArray();
            }

            return $ret;
        };
    }

    /**
     * 过滤处理
     *
     * 支持简单的字段查询条件过滤
     * 可用操作符： '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
     *             'like', 'like binary', 'not like', 'ilike',
     *             '&', '|', '^', '<<', '>>',
     *             'rlike', 'regexp', 'not regexp',
     *             '~', '~*', '!~', '!~*', 'similar to',
     *             'not similar to', 'not ilike', '~~*', '!~~*',
     *             'in', 'not in
     *
     * @return \Closure
     */
    public function macroWhere()
    {
        return function ($wheres, $able = []) {
            $wheres = is_array($wheres) ? $wheres : ['id' => $wheres];
            foreach ($wheres as $key => $value) {
                [$column, $operator] = strrpos($key, '|') !== false ? explode('|', $key, 2) : [$key, '='];

                if (!empty($able) && !isset($able[$column])) {
                    continue;
                }

                if (isset($able[$column]) && !in_array($operator, (array)$able[$column])) {
                    $operators = array_map(
                        function ($operator) {
                            return "'{$operator}'";
                        },
                        (array)$able[$column]
                    );

                    throw new \InvalidArgumentException(
                        sprintf(
                            '操作符 %s 不能作用于 %s 字段，可使用操作符为：%s',
                            $operator,
                            $column,
                            implode(', ', $operators)
                        )
                    );
                }

                if (($pos = strrpos($column, '.')) !== false) {
                    $relation = substr($column, 0, $pos);
                    $column = substr($column, $pos + 1);

                    $this->macroWhereHas($relation, $column, $operator, $value);

                    continue;
                }


                if (in_array($operator, ['in', 'not in'])) {
                    $this->whereIn($column, $value, 'and', $operator == 'not in');
                } else {
                    $this->where($column, $operator, $value);
                }
            }

            return $this;
        };
    }

    /**
     * 选择处理
     *
     * 支持简单的主表和关联子表字段选择过滤
     * 选择字段示例：['id', 'type', 'status', 'sku.id', 'sku.status']
     *
     * @return \Closure
     */
    public function macroSelect()
    {
        return function ($columns, $ables = []) {
            $groups = [];

            foreach (Arr::wrap($columns) as $with => $column) {
                if (!empty($ables) && !in_array($column, $ables)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            '字段 %s 不可选取，可选取字段有：%s',
                            $column,
                            implode(', ', $ables)
                        )
                    );
                }

                if (is_array($column)) {
                    $groups[$with] = $column;
                } elseif (($pos = strrpos($column, '.')) !== false) {
                    $relation = substr($column, 0, $pos);
                    $reColumn = substr($column, $pos + 1);
                    $groups[$relation][] = $reColumn;
                } else {
                    $groups['*'][] = $column;
                }
            }

            if (!isset($groups['*'])) {
                throw new \InvalidArgumentException('没有选择主表字段');
            }

            $this->select(Arr::pull($groups, '*'));

            foreach ($groups as $relation => $reColumn) {
                $this->with(
                    [
                        $relation => function ($qb) use ($reColumn) {
                            $qb->select($reColumn);
                        }
                    ]
                );
            }

            return $this;
        };
    }

    /**
     * 排序处理
     *
     * @return \Closure
     */
    public function macroOrderBy()
    {
        return function ($orderBys, $able = []) {
            $orderBys = is_array($orderBys) ? $orderBys : [$orderBys => 'asc'];
            foreach ($orderBys as $column => $direction) {
                if (is_int($column)) {
                    $column = $direction;
                    $direction = 'asc';
                }
                if (!empty($able) && !in_array($column, $able)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            '字段 %s 不可用于排序，可排序字段有：%s',
                            $column,
                            implode(', ', $able)
                        )
                    );
                }

                if (!in_array(strtolower($direction), ['desc', 'asc'])) {
                    throw new \InvalidArgumentException(sprintf('排序规则 %s 不可用于排序', $direction));
                }

                $this->orderBy($column, $direction);
            }

            return $this;
        };
    }

    /**
     * 分页处理
     *
     * @return \Closure
     */
    public function macroPage()
    {
        return function ($page, $perPage = Builder::PER_LIMIT, $maxLimit = Builder::MAX_LIMIT) {
            $page = max($page, 1);
            $skip = ($page - 1) * $perPage;
            $maxLimit > 0 && ($perPage = min($perPage, $maxLimit));

            $this->skip($skip)->limit($perPage);

            return $this;
        };
    }

    protected function macroWhereHas()
    {
        return function ($relation, $column, $operator, $value) {
            $this->whereHas(
                $relation,
                function ($qb) use ($column, $operator, $value) {
                    if (in_array($operator, ['in', 'not in'])) {
                        $qb->whereIn($column, $value, 'and', $operator == 'not in');
                    } else {
                        $qb->where($column, $operator, $value);
                    }
                }
            );
        };
    }

    public function macroFirst()
    {
        return function ($column = ['*']) {
            $row = $this->first((array)$column);
            return empty($row) ? [] : $row->toArray();
        };
    }

    public function extend()
    {
        $macros = get_class_methods($this);

        foreach ($macros as $macro) {
            if (Str::startsWith($macro, 'macro')) {
                EloquentBuilder::macro($macro, $this->{$macro}());
            }
        }
    }
}
