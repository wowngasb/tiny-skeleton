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

use \GraphQL\Schema;
use \GraphQL\GraphQL;
use \GraphQL\Type\Definition\Config;
use \GraphQL\Error\FormattedError;
use Tiny\Abstracts\AbstractApi;

class GraphQLApi extends AbstractApi
{

    public function exec($query = '{hello}', array $variables = null)
    {
        if (DEV_MODEL == 'DEBUG') {
            Config::enableValidation(); // Enable additional validation of type configs (disabled by default because it is costly)
            $phpErrors = [];  // Catch custom errors (to report them in query results if debugging is enabled)
            set_error_handler(function ($severity, $message, $file, $line) use (&$phpErrors) {
                $phpErrors[] = new ErrorException($message, 0, $severity, $file, $line);
            });
        }

        try {
            // GraphQL schema to be passed to query executor:
            $schema = new Schema([
                'query' => Types::query()
            ]);

            $result = GraphQL::execute(
                $schema,
                $query,
                null,
                $this,
                $variables
            );

            // Add reported PHP errors to result (if any)
            if (DEV_MODEL == 'DEBUG' && !empty($phpErrors)) {
                $result['extensions']['phpErrors'] = array_map(['GraphQL\Error\FormattedError', 'createFromPHPError'], $phpErrors);
            }
        } catch (\Exception $error) {
            $result['extensions']['exception'] = FormattedError::createFromException($error);
        }

        return $result;
    }

}