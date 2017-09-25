<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/12 0012
 * Time: 17:08
 */

namespace app\api;

use app\api\GraphQL\Types;
use ErrorException;

use GraphQL\Error\Debug;
use GraphQL\Type\Schema;
use \GraphQL\GraphQL;
use \GraphQL\Error\FormattedError;
use Tiny\Abstracts\AbstractApi;
use Tiny\Application;

class GraphQLApi extends AbstractApi
{

    public function exec($query = '{hello}', array $variables = null)
    {
        // GraphQL schema to be passed to query executor:
        $schema = new Schema([
            'query' => Types::Query([], Types::class)
        ]);
        $debug = false;
        $phpErrors = [];  // Catch custom errors (to report them in query results if debugging is enabled)
        if (Application::dev()) {
            $schema->assertValid(); // Enable additional validation of type configs (disabled by default because it is costly)
            set_error_handler(function ($severity, $message, $file, $line) use (&$phpErrors) {
                $phpErrors[] = new ErrorException($message, 0, $severity, $file, $line);
            });
            $debug = Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE;
        }

        $result = [];
        try {
            $result = GraphQL::executeQuery(
                $schema,
                $query,
                null,
                $this,
                $variables
            )->toArray($debug);

            // Add reported PHP errors to result (if any)
            if (Application::dev() && !empty($phpErrors)) {
                $result['extensions']['phpErrors'] = array_map(['GraphQL\Error\FormattedError', 'createFromPHPError'], $phpErrors);
            }
        } catch (\Exception $error) {
            $result['extensions']['exception'] = FormattedError::createFromException($error);
        }
        return $result;
    }

}