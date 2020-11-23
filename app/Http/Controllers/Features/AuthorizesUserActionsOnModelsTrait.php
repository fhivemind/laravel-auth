<?php

namespace App\Http\Controllers\Features;

use App\Enums\SpatieQuery;
use App\Models\RestfulModel;
use App\Models\Traits\AuthorizedQuery;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilderRequest;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Trait AuthorizesUsersActionsAgainstModelsTrait
 *
 * These are wrappers for Illuminate\Foundation\Auth\Access\Authorizable from the perspective of a RESTful controller
 * authorizing the access of authenticated users on a given resource model.
 */
trait AuthorizesUserActionsOnModelsTrait
{
    /**
     * Shorthand function which checks the currently logged in user against an action for the controller's model,
     * and throws a 403 if unauthorized.
     *
     * Only checks if a policy exists for that model.
     *
     * @param string $ability
     * @param array|mixed $arguments
     * @throws AccessDeniedHttpException
     */
    public function authorizeUserAction($ability, $arguments = [])
    {
        // Ability could be discarded for child controller parent resource checks
        if (is_null($ability)) {
            return true;
        }

        if (! $this->userCan($ability, $arguments)) {
            throw new AccessDeniedHttpException('Unauthorized action');
        }
    }

    /**
     * Shorthand function which validates request parameters of currently logged user,
     * and throws a 403 if the request contains unauthorized fields.
     *
     * @param Request $request
     * @param RestfulModel $model
     * @throws AccessDeniedHttpException
     */
    public static function authorizeUserRequestOnModel(Request $request, RestfulModel $model)
    {
        $modelPolicy = Gate::getPolicyFor($model);

        // If policy exists for this model, then check
        if ($modelPolicy)
        {
            $request = QueryBuilderRequest::fromRequest($request);

            // verify for each request type
            foreach (SpatieQuery::getValues() as $value) 
            {
                $type = SpatieQuery::fromValue($value);

                // does the model contain this specific type
                if( is_callable([$request, strtolower($value)]) && 
                    is_callable([$model, AuthorizedQuery::getQueryMethodForSpatie($type)]) &&
                    is_callable([$model, AuthorizedQuery::getAuthorizedQueryMethodForSpatie($type)])
                )
                {
                    static::checkQueryAuthorization(
                        $type,
                        $request->{strtolower($value)}(),
                        $model->{AuthorizedQuery::getQueryMethodForSpatie($type)}(),
                        $model->{AuthorizedQuery::getAuthorizedQueryMethodForSpatie($type)}()
                    );
                }
            }
        }
    }

    /**
     * Check if query params contain any accessible elements but for which the user lacks authorization.
     * 
     * @param SpatieQuery $type
     * @param Collection $params
     * @param array $accessible
     * @param array $authorized
     * 
     * @throws AccessDeniedHttpException
     */
    public static function checkQueryAuthorization(SpatieQuery $type, Collection $params, array $accessible, array $authorized)
    {
        // Find all requested unauthorized params
        if($type->is(SpatieQuery::Filter)) {
            // map filter collections as they are not all strings
            $remap = function ($filter) {
                if ($filter instanceof AllowedFilter) {
                    return $filter->getName();
                }
                return $filter;
            };

            // filter out from collection
            $params = $params
                        ->map($remap)
                        ->intersect(collect($accessible)->map($remap))
                        ->diff(collect($authorized)->map($remap));
        } else {
            // otherwise, if only strings in collection, use them
            $params = $params->flatten()->intersect($accessible)->diff($authorized);
        }

        // Check if any unauthorized params requested
        if ($params->count())
        {
            throw new AccessDeniedHttpException('Unauthorized action. You do not have permission to request \''. $items->join(",") .'\' field.');
        }
    }

    /**
     * This function can be used to add conditions to the query builder,
     * which will specify the currently logged in user's ownership of the model.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|null
     */
    public function qualifyCollectionQuery($query)
    {
        $user = auth()->user();

        $modelPolicy = Gate::getPolicyFor(static::model());

        // If no policy exists for this model, then there's nothing to check
        if (is_null($modelPolicy)) {
            return $query;
        }

        // Add conditions to the query, if they are defined in the model's policy
        if (method_exists($modelPolicy, 'qualifyCollectionQueryWithUser')) {
            $query = $modelPolicy->qualifyCollectionQueryWithUser($user, $query);
        }

        return $query;
    }

    /**
     * Determine if the currently logged in user can perform the specified ability on the model of the controller
     * When relevant, a specific instance of a model is used - otherwise, the model name.
     *
     * Only checks if a policy exists for that model.
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return bool
     */
    public function userCan($ability, $arguments = [])
    {
        $user = auth()->user();

        // If no arguments are specified, set it to the controller's model (default)
        if (empty($arguments)) {
            $arguments = static::model();
        }

        // Get policy for model
        if (is_array($arguments)) {
            $model = reset($arguments);
        } else {
            $model = $arguments;
        }

        $modelPolicy = Gate::getPolicyFor($model);

        // If no policy exists for this model, then there's nothing to check
        if (is_null($modelPolicy)) {
            return true;
        }

        // Check if the authenticated user has the required ability for the model
        if (Gate::forUser($user)->allows($ability, $arguments)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user does not have a given ability for the model
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return bool
     */
    public function userCant($ability, $arguments = [])
    {
        return ! $this->userCan($ability, $arguments);
    }

    /**
     * Determine if the user does not have a given ability for the model
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return bool
     */
    public function userCannot($ability, $arguments = [])
    {
        return $this->userCant($ability, $arguments);
    }
}
