<?php

namespace SleepingOwl\Admin\DataProvider;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use SleepingOwl\Admin\Utils\Invoker;
use Illuminate\Database\Eloquent\Builder;

class Configuration
{
    /** @var callable */
    protected $queryHandler;
    protected $hidden = [];
    protected $fillable = [];
    /** @var class */
    protected $modelClass;
    /** @var callable[] */
    protected $itemFormatters = [];
    protected $searchable = true;
    protected $searchParam = 'q';
    /** @var callable */
    protected $searchHandler;
    /** @var callable[] */
    protected $filters = [];
    protected $all = false;
    protected $perPage = 30;
    protected $responseFormatters = [];
    protected $totalize = true;
    /** @var callable */
    protected $preResponseFormatHandler;
    /** @var callable */
    protected $postResponseFormatHandler;
    /** @var callable */
    protected $preItemsFormatHandler;
    /** @var callable */
    protected $postItemsFormatHandler;
    protected $fetchColumns = [];

    public function __construct($modelClass)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * Check items pagination is enabled.
     *
     * @return bool
     */
    public function isAll()
    {
        return $this->all;
    }

    /**
     * Perform items pagination enabled.
     *
     * @param bool $all
     * @return Configuration
     */
    public function setAll($all)
    {
        $this->all = $all;

        return $this;
    }

    /**
     * Alias for {@see Configuration::setAll(true)}.
     * @return Configuration
     */
    public function all()
    {
        $this->all = true;

        return $this;
    }

    /**
     * Return the max number of items in page.
     * @return int
     */
    public function perPage()
    {
        return $this->perPage;
    }

    /**
     * Set max number of items in page.
     * @param int $perPage number of items.
     * @return Configuration
     */
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * Return the model class.
     * @return type
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * Get the query handler.
     * @return mixed
     */
    public function getQueryHandler()
    {
        return $this->queryHandler;
    }

    /**
     * Set the Query Handler.
     *
     * @param callable $handler The handler function receives a
     *                          {@see QueryBuilder} object as first parameter.
     *                          The handler supports **arguments injection**.
     * @return Configuration
     */
    public function setQueryHandler($handler)
    {
        $this->queryHandler = $handler;

        return $this;
    }

    /**
     * Get model hidden fields.
     *
     * @return array
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set the model hidden as
     * `$model->setHidden(array_merge($model->getHidden(), $hidden))`.
     *
     * @param array $hidden list of fields names.
     * @see Illuminate\Database\Eloquent\Model::setHidden()
     * @return Configuration
     */
    public function setHidden($hidden)
    {
        $this->hidden = is_array($hidden) ? $hidden : [$hidden];

        return $this;
    }

    /**
     * Get the custom fillable fields.
     * @return array
     */
    public function getFillable()
    {
        return $this->fillable;
    }

    /**
     * Set the model fillable as
     * `$model->setFillable(array_merge($model->getFillable(), $fillable))`.
     *
     * @param array $fillable List of fields names.
     * @see Illuminate\Database\Eloquent\Model::setFillable()
     * @return Configuration
     */
    public function setFillable($fillable)
    {
        $this->fillable = is_array($fillable) ? $fillable : [$fillable];

        return $this;
    }

    /**
     * Get the item formatters.
     * @return array
     */
    public function getItemFormatters()
    {
        return $this->itemFormatters;
    }

    /**
     * Set many item formatters.
     *
     * @param array $formatters Array of formatters with KEY as name and VALUE
     *                          is the handler.
     * @return Configuration
     */
    public function setItemFormatters(array $formatters)
    {
        $this->itemFormatters = $formatters;

        return $this;
    }

    /**
     * Set item formatter.
     *
     * Examples:
     *
     * ```php
     * $cfg->setHidden(['firstName', 'secondName']);
     *
     * $cfg->setItemFormatter('first_name_only', function(&$model) {
     *     $model->name = $model->firtName;
     * });
     *
     * $cfg->setItemFormatter('full_name', function(&$model, Request $request) {
     *     $model->name = $model->firtName . " " . $model->secondName;
     * });
     *
     * $cfg->setItemFormatter('custom', function(&$model) {
     *     $model = [$model->id, $model->name];
     * });
     *
     * $cfg->setItemFormatter('name_attr', function(&$model) {
     *     $model = $model->name;
     * });
     * ```
     *
     * @param string $name The format name
     * @param callable $formatter The formatter handler with
     *                            {@see Illuminate\Database\Eloquent\Model}
     *                            object as first argument. **Parameters
     *                            injection** is available.
     * @see Configuration::setItemFormatters()
     * @return Configuration
     */
    public function setItemFormatter($name, $formatter)
    {
        $this->itemFormatters[$name] = $formatter;

        return $this;
    }

    /**
     * Get the item format by name.
     *
     * @param $name Name of the item formatter
     * @param callable $default default formatter if it not exists by name.
     * @return callable|null if item formatter with $name exists or default has
     *                       be not null, return it, otherwise, `null`.
     */
    public function getItemFormatter($name, $default = null)
    {
        if (array_key_exists($name, $this->itemFormatters)) {
            return $this->itemFormatters[$name];
        }

        return $default;
    }

    /**
     * Return if searchable by term.
     * @return bool
     */
    public function isSearchable()
    {
        return $this->searchable;
    }

    /**
     * Enable or disable term searchable.
     * @param bool $searchable
     * @return Configuration
     */
    public function setSearchable($searchable)
    {
        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Enable term searcheable.
     *
     * @see Configuration::setSearcheable()
     * @return Configuration
     */
    public function searchable()
    {
        $this->searchable = true;

        return $this;
    }

    /**
     * Disable term searcheable.
     *
     * @see Configuration::setSearcheable()
     * @return Configuration
     */
    public function notSearchable()
    {
        $this->searchable = false;

        return $this;
    }

    /**
     * Return request param name for get search term value.
     * @return string
     */
    public function getSearchParam()
    {
        return $this->searchParam;
    }

    /**
     * Set the request param name for get search term value.
     * @param string $searchParam
     * @return Configuration
     */
    public function setSearchParam($searchParam)
    {
        $this->searchParam = $searchParam;

        return $this;
    }

    /**
     * Get all filters.
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set multiple query filters.
     * @param array $filters Array of filters with KEY as name and VALUE
     *                       is the handler.
     * @see Configuration::setFilter()
     * @return Configuration
     */
    public function setFilters($filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Set one query filter.
     *
     * Examples:
     *
     * ```php
     * $cfg->setFilter('not_is_trash', function(QueryBuilder $query) {
     *     return $query->where('trash', 'like', 'false');
     * );
     * $cfg->setFilter('sample', function(QueryBuilder $query, Request $request) {
     *     return $query;
     * });
     * ```
     *
     * @param string $name The filter name.
     * @param callable $handler The filter handler with
     *                          {@see Illuminate\Database\Eloquent\Builder}
     *                          object as first argument. **Return** the $query
     *                          object. **Parameters injection** is available.
     * @see Configuration::setFilters()
     * @return Configuration
     */
    public function setFilter($name, $handler)
    {
        $this->filters[$name] = $handler;

        return $this;
    }

    /**
     * Set simple field filter.
     *
     * Examples:
     *
     * ```php
     * $cfg->addFilterValue('level');
     * $cfg->addFilterValue('level', ['op', '>']);
     * $cfg->addFilterValue('parent', ['required' => true]);
     * $cfg->addFilterValue('parent', ['required' => true, 'param' => 'p']);
     * $cfg->addFilterValue('date', ['formatter' => function($query, $value) {
     *    return str_replace('.', '-', $value)
     * }]);
     * ```
     *
     * @param $fieldName The field name
     * @param array $options {
     *     @var string $key op. The query operator. Default is `'='`.
     *     @var string $key param. The name of request param for get the value.
     *                       Default is $fieldName.
     *     @var string $key formatter. Handler for format filter value. It is
     *                       callable if filter value not is null.
     *                       The **first** argument is a filter value. Returns
     *                       the formatted value. **Arguments injection** is
     *                       available.
     *     @var string $key required. If is `true` and request param value is
     *                       `null`, aborts with HTTP 400 status. Default is
     *                       `false`.
     *     @var bool $key blank. If is `false` and request param value is
     *                           `null` or empty, the handler will not be called.
     *                           Default is `false`.
     *     @var string $key default. Default **param** value if not **required**
     *                               and value is `null` or length of value is 0.
     * }
     * @return Configuration
     */
    public function setFieldFilter($fieldName, $options = [])
    {
        $options = array_merge([
            'op' => '=',
            'param' => $fieldName,
            'formatter' => null,
            'required' => false,
            'blank' => true,
            'default' => null,
        ], $options);

        return $this->setFilter('field:'.data_get($options, 'name', $fieldName),
            function (Builder $query, Invoker $invoker, Request $request) use ($fieldName, $options) {
                $value = $request->input($options['param']);

                if (is_null($value)) {
                    if ($options['required']) {
                        abort(400, "Parameter '{$options['param']}' is empty.");
                    } else {
                        $value = $options['default'];
                    }
                }

                if (is_null($value) || strlen($value) == 0) {
                    if (! $options['blank']) {
                        return $query;
                    }
                    else if (! is_null($options['default'])) {
                        $value = $options['default'];
                    }
                }

                if (! is_null($options['formatter'])) {
                    $value = Invoker::create(
                        $options['formatter'],
                        [&$value]
                    )
                        ->setDependencyProviderByRef(
                            $invoker->getDependencyProviderByRef()
                        )
                        ->invoke();
                }

                return $query->where($fieldName, $options['op'], $value);
            }
        );
    }

    /**
     * Add filter field with default options with **op** key as `'like'`
     * and key **formatter** is a function for convertvalue as
     * `'%' . $value . '%'`.
     * @param string $fieldName The field name.
     * @param array $options The custom options.
     * @return Configuration
     */
    public function likeFieldFilter($fieldName, array $options = [])
    {
        $options = array_merge($options, [
            'op' => 'ilike',
            'blank' => false,
            'formatter' => function (&$value) {
                return '%'.$value.'%';
            },
        ]);

        return $this->setFieldFilter($fieldName, $options);
    }

    /**
     * Return the search term handler.
     *
     * @return callable
     */
    public function getSearchHandler()
    {
        return $this->searchHandler;
    }

    /**
     * Set the search term handler.
     *
     * Example:
     * ```php
     * $cfg->setSearchHandler(function(Builder $query, $term){
     *     return $query->where('name', 'like', '%' . $term . '%');
     * });
     * ```
     *
     * @param callable $handler Is a function called when search term value not
     *                          is `null`. The function receives
     *                          {@see Illuminate\Database\Eloquent\Builder}
     *                          object as **first parameter** and the term value
     *                          as **second parameter**. The function returns a
     *                          {@see Illuminate\Database\Eloquent\Builder}
     *                          object.
     * @return Configuration
     */
    public function setSearchHandler($handler)
    {
        $this->searchHandler = $handler;

        return $this;
    }

    /**
     * Return the response Formatters.
     * @return array
     */
    public function getResponseFormatters()
    {
        return $this->responseFormatters;
    }

    /**
     * Set the response formatters.
     * @param array $formatters Array with KEY is the formatter name and value
     *                          is the formatter handler.
     * @see Configuration::setResponseFormatter
     * @return Configuration
     */
    public function setResponseFormatters(array $formatters)
    {
        $this->responseFormatters = $formatters;

        return $this;
    }

    /**
     * Set response formatters.
     *
     * Examples:
     *
     * ```php
     * $cfg->setFormatter('my_formatter', function(array &$items, array &$info) {
     *     return [& $items, & $info];
     * });
     *
     * $cfg->setFormatter('my_formatter2', function(array &$items, array &$info) {
     *     return ['items' => &$items, 'info' => &$info];
     * });
     * ```
     *
     * @param string $name The formatter name
     * @param callable $formatter The formatter handler with `array & $items`
     *                            and `array & $info` parameters.
     *                            **Parameters injection** is available.
     * @return Configuration $this
     */
    public function setResponseFormatter($name, $formatter)
    {
        $this->responseFormatters[$name] = $formatter;

        return $this;
    }

    /**
     * Get the response formatter by name.
     *
     * @param $name The formatter name
     * @param callable $default Default returned formatter handler if not
     *                          contains formatter by $name. Default is null.
     * @return callable|null If formatter handler exists by name or $default
     *                       not is null. otherwise, `null`.
     */
    public function getResponseFormatter($name, $default = null)
    {
        if (array_key_exists($name, $this->responseFormatters)) {
            return $this->responseFormatters[$name];
        }

        return $default;
    }

    /**
     * @return bool
     */
    public function isTotalize()
    {
        return $this->totalize;
    }

    /**
     * If count query results.
     *
     * @param bool $totalize
     * @return Configuration
     */
    public function setTotalize($totalize)
    {
        $this->totalize = $totalize;

        return $this;
    }

    /**
     * Set totalize to `true`.
     * @return Configuration
     */
    public function totalize()
    {
        $this->totalize = true;

        return $this;
    }

    /**
     * Set totalize to `false`.
     * @return Configuration
     */
    public function notTotalize()
    {
        $this->totalize = false;

        return $this;
    }

    /**
     * Create item formatter for make response items with pairs of ID and TEXT.
     *
     * Example:
     *
     * ```php
     * $cfg->setIdTextItemFormatter(function (Model $e) {
     *     return $e->firstName . " " . $e->secondName;
     * });
     *
     * $cfg->setIdTextItemFormatter('name');
     * ```
     *
     * The `$cfg->setIdTextItemFormatter('name');` is similar to
     *
     * ```php
     * $cfg->setIdTextItemFormatter(function (Model $e) {
     *     return $e->name;
     * });
     * ```
     *
     * This function is alias of:
     *
     * ```php
     * $formatter = function (Model $e) {
     *     return $e->name;
     * };
     *
     * $cfg->setItemFormatter('idtext', function(&$model) use ($formatter) {
     *     $model = [$model->id, $formatter($model)];
     * });
     * ```
     *
     * In the example above, the list of items not similar to
     *
     * ```php
     * [
     *     [1, "Item Name"],
     *     [2, "Item 2 Name"],
     *     [5, "Item 5 Name"],
     * ];
     * ```
     *
     * @see Configuration::setItemFormatter()
     * @param callable|string $formatter if it is a 'string', it is considered
     *                                   the attribute name and will create a
     *                                   formatter to return the value of the
     *                                   respective attribute will be converted
     *                                   to 'string'. If a callable, the very
     *                                   handler should return the
     *                                   representative text.
     * @return Configuration
     */
    public function setItemTupleFormatter($formatter)
    {
        if (is_string($formatter)) {
            $formatter = function (&$e, $set) use ($formatter) {
                $set(data_get($e, $formatter));
            };
        }

        return $this->setItemFormatter(
            'tuple',
            function (Env $env) use ($formatter) {
                $le = new \stdClass();
                $le->model = null;
                $le->item = null;
                $le->i = 0;

                $set = function ($text, $children=null) use ($le) {
                    $item = [
                        $le->model->getAttribute($le->model->getKeyName()),
                        $text
                    ];

                    if (! is_null($children)) {
                        $item[] = $children;
                    }

                    $le->item = $item;
                };

                return function(& $model, $i) use ($set, $formatter, $le) {
                    $le->model = $model;
                    $le->item = null;
                    $le->i = $i;
                    $formatter($model, $set);
                    $model = $le->item;
                };
            }
        );
    }

    /**
     * Alias of {@see Configuration::setItemTupleFormatter()}
     * @param $formatter
     * @see Configuration::setItemTupleFormatter()
     * @deprecated
     */
    public function setIdTextItemFormatter($formatter)
    {
        return $this->setItemTupleFormatter($formatter);
    }

    /**
     * @return callable
     */
    public function getPreItemsFormatHandler()
    {
        return $this->preItemsFormatHandler;
    }

    /**
     * @param callable $preItemsFormatHandler
     * @return Configuration
     */
    public function setPreItemsFormatHandler($preItemsFormatHandler)
    {
        $this->preItemsFormatHandler = $preItemsFormatHandler;

        return $this;
    }

    /**
     * @return callable
     */
    public function getPostItemsFormatHandler()
    {
        return $this->postItemsFormatHandler;
    }

    /**
     * @param callable $postItemsFormatHandler
     * @return Configuration
     */
    public function setPostItemsFormatHandler($postItemsFormatHandler)
    {
        $this->postItemsFormatHandler = $postItemsFormatHandler;

        return $this;
    }



    /**
     * @return callable
     */
    public function getPreResponseFormatHandler()
    {
        return $this->preResponseFormatHandler;
    }

    /**
     * @param callable $preResponseFormatHandler
     * @return Configuration
     */
    public function setPreResponseFormatHandler($preResponseFormatHandler)
    {
        $this->preResponseFormatHandler = $preResponseFormatHandler;

        return $this;
    }

    /**
     * @return callable
     */
    public function getPostResponseFormatHandler()
    {
        return $this->postResponseFormatHandler;
    }

    /**
     * @param callable $postResponseFormatHandler
     * @return Configuration
     */
    public function setPostResponseFormatHandler($postResponseFormatHandler)
    {
        $this->postResponseFormatHandler = $postResponseFormatHandler;

        return $this;
    }

    /**
     * Return fetch columns from database.
     * @return array
     */
    public function getFetchColumns()
    {
        return $this->fetchColumns;
    }

    /**
     * Set the fetch columns on database.
     * @param array $fetchColumns
     * @return Configuration
     */
    public function setFetchColumns(array $fetchColumns)
    {
        $this->fetchColumns = $fetchColumns;

        return $this;
    }
}
