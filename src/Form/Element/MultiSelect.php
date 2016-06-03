<?php

namespace SleepingOwl\Admin\Form\Element;

use Illuminate\Database\Eloquent\Model;
use Request;
use Illuminate\Database\Eloquent\Collection;

class MultiSelect extends Select
{
    /**
     * @var bool
     */
    protected $taggable = false;

    /**
     * @var bool
     */
    protected $deleteRelatedItem = false;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name.'[]';
    }

    /**
     * @return array
     */
    public function getValue()
    {
        $value = parent::getValue();

        if ($value instanceof Collection && $value->count() > 0) {
            $value = $value->pluck($value->first()->getKeyName())->all();
        }

        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        return $value;
    }

    /**
     * @return bool
     */
    public function isTaggable()
    {
        return $this->taggable;
    }

    /**
     * @return $this
     */
    public function taggable()
    {
        $this->taggable = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleteRelatedItem()
    {
        return $this->deleteRelatedItem;
    }

    /**
     * @return $this
     */
    public function deleteRelatedItem()
    {
        $this->deleteRelatedItem = true;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = parent::toArray() + ['tagable' => $this->isTaggable()];
        $result['attributes'][] = 'multiple';

        if ($this->isTaggable()) {
            $result['attributes']['class'] .= ' input-taggable';
        }

        return $result;
    }

    public function save()
    {
        if (is_null($this->getModelForOptions())) {
            parent::save();
        }
    }

    public function afterSave()
    {
        if (is_null($modelfo = $this->getModelForOptions())) {
            return;
        }

        $attribute = $this->getAttribute();

        if (is_null(Request::input($this->getPath()))) {
            $values = [];
        } else {
            $values = $this->getValue();
        }

        $relation = $this->getModel()->{$attribute}();

        if ($relation instanceof \Illuminate\Database\Eloquent\Relations\BelongsToMany) {
            if ($this->isTaggable()) {
                $options = $this->getOptions();

                foreach ($values as $i => $value) {
                    if (! array_key_exists($value, $options)) {
                        $model = clone $modelfo;
                        $model->{$this->getDisplay()} = $value;
                        $model->save();

                        $values[$i] = $model->getKey();
                    }
                }
            }

            $relation->sync($values);
        } elseif ($relation instanceof \Illuminate\Database\Eloquent\Relations\HasMany) {
            foreach ($relation->get() as $item) {
                if (! in_array($item->getKey(), $values)) {
                    if ($this->isDeleteRelatedItem()) {
                        $item->delete();
                    } else {
                        $item->{$relation->getPlainForeignKey()} = null;
                        $item->save();
                    }
                }
            }

            foreach ($values as $i => $value) {
                /** @var Model $model */
                $model = clone $modelfo;
                $item = $model->find($value);

                if (is_null($item)) {
                    if (! $this->isTaggable()) {
                        continue;
                    }

                    $model->{$this->getDisplay()} = $value;
                    $item = $model;
                }

                $relation->save($item);
            }
        }
    }
}
