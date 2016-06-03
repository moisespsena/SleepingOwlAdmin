<?php
namespace SleepingOwl\Admin\DataProvider;

use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;
use SleepingOwl\Admin\Model\ModelConfiguration;

/**
 * Class Env
 * @property string term The search term.
 * @property string itemFormatter The item formatter name.
 * @property string responseFormatter The response formatter name.
 * @property bool withChildren If require children on item format.
 * @property ModelConfiguration model
 * @property Configuration cfg
 * @property Request request The request.
 * @property Builder query The Select query builder.
 * @property array result The controller action result.
 * @property array items The result items.
 * @property array info The result info.
 * @property array parametersMap The default parameters map for Invoke instance.
 */
class Env extends \ArrayObject
{
    public function __construct()
    {
        parent::__construct();
        $this->setFlags(\ArrayObject::STD_PROP_LIST |
            \ArrayObject::ARRAY_AS_PROPS);
    }
}
