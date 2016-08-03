<?php

namespace DvsaCommon\Exception;

/**
 * Class NotImplementedException
 * @package DvsaCommon\Exception
 *
 * The class is used to mark some piece of functionality that is not implemented - it's still work in progress.
 * It's unacceptable in production code.
 * It can be used in dummy methods of custom test doubles (e.g. custom mocks).
 *
 */
class NotImplementedException extends \Exception
{

}
